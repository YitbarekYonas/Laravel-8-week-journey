<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TraceMiddleware
{
    public function handle(Request $request, Closure $next, string $label): Response
    {
        $this->appendTrace($request, "{$label} → entering (before controller)");

        $response = $next($request);

        $this->appendTrace($request, "{$label} → exiting (after controller, before response sent)");

        if ($response instanceof JsonResponse) {
            $data = $response->getData(true);
            $data['pipeline_trace'] = $request->attributes->get('__trace', []);
            $response->setData($data);
        }

        return $response;
    }

    private function appendTrace(Request $request, string $entry): void
    {
        $trace = $request->attributes->get('__trace', []);
        $trace[] = $entry;
        $request->attributes->set('__trace', $trace);
    }
}
