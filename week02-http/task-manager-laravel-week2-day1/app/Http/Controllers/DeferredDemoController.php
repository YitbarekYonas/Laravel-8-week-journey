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

    public function status(): JsonResponse
    {
        $hasRun = File::exists($this->proofLogPath());

        return response()->json([
            'deferred_provider_has_run' => $hasRun,
            'explanation' => $hasRun
                ? 'ReportServiceProvider::register() has run at least once '
                    .'this app lifetime.'
                : 'ReportServiceProvider::register() has NEVER run — call '
                    .'GET /api/demo/deferred/generate, then check this '
                    .'endpoint again.',
            'proof_log_contents' => $hasRun ? File::get($this->proofLogPath()) : null,
        ]);
    }

    public function generate(): JsonResponse
    {
        $report = app(ReportGeneratorInterface::class);

        return response()->json([
            'report' => $report->generate(),
            'resolved_implementation' => get_class($report),
            'note' => 'Resolving ReportGeneratorInterface just now is what '
                .'triggered ReportServiceProvider::register() to actually run.',
        ]);
    }

    public function reset(): JsonResponse
    {
        if (File::exists($this->proofLogPath())) {
            File::delete($this->proofLogPath());
        }

        return response()->json([
            'message' => 'Proof log cleared.',
        ]);
    }
}
