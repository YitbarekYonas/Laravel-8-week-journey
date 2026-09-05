<?php

namespace App\Http\Controllers;

use App\Services\Contracts\ReportGeneratorInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;

class DeferredDemoController extends Controller
{
    private function proofLogPath(): string
    {
        return storage_path('logs/deferred-provider-proof.log');
    }

    // ── Check whether ReportServiceProvider has EVER actually run ──────
    // This action deliberately does NOT type-hint or resolve
    // ReportGeneratorInterface anywhere — it only checks whether the
    // proof file exists. On a fresh app (proof log deleted / never
    // created), this should report "not loaded" even after several
    // requests to THIS endpoint, because nothing has ever asked the
    // container for ReportGeneratorInterface yet.
    public function status(): JsonResponse
    {
        $hasRun = File::exists($this->proofLogPath());

        return response()->json([
            'deferred_provider_has_run' => $hasRun,
            'explanation' => $hasRun
                ? 'ReportServiceProvider::register() has run at least once '
                    .'this app lifetime — check /api/deferred/generate '
                    .'history, or the proof log contents below.'
                : 'ReportServiceProvider::register() has NEVER run — hitting '
                    .'this status endpoint does not resolve '
                    .'ReportGeneratorInterface, so the deferred provider stays '
                    .'completely unloaded. Call GET /api/deferred/generate, '
                    .'then check this endpoint again.',
            'proof_log_contents' => $hasRun ? File::get($this->proofLogPath()) : null,
        ]);
    }

    // ── Actually resolve the deferred binding ───────────────────────────
    // THIS is the moment ReportServiceProvider::register() runs for the
    // first time (if it hasn't already) — triggered by this line alone,
    // nothing else in the request lifecycle causes it.
    public function generate(): JsonResponse
    {
        $report = app(ReportGeneratorInterface::class);

        return response()->json([
            'report' => $report->generate(),
            'resolved_implementation' => get_class($report),
            'note' => 'Resolving ReportGeneratorInterface just now is what '
                .'triggered ReportServiceProvider::register() to actually '
                .'run, if it hadn\'t already this app lifetime. Check '
                .'GET /api/deferred/status — it should now report true.',
        ]);
    }

    // Resets the demo so you can observe the "not loaded yet" state again
    // without restarting the whole app process.
    public function reset(): JsonResponse
    {
        if (File::exists($this->proofLogPath())) {
            File::delete($this->proofLogPath());
        }

        return response()->json([
            'message' => 'Proof log cleared. Call GET /api/deferred/status — '
                .'it should report false again (though note: within the SAME '
                .'PHP process/request-serving worker, Laravel may still have '
                .'the binding cached in memory from an earlier resolution in '
                .'that worker\'s lifetime — a fresh `php artisan serve` '
                .'restart guarantees a completely clean slate).',
        ]);
    }
}
