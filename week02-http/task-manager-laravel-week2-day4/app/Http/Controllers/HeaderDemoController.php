<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// ── Headers — reading and writing, explicitly ────────────────────────────
// This project has already used headers incidentally: the Location header
// on TaskController::store() (Week 2 Day 2), and X-Request-Id from
// RequestIdMiddleware (Week 1 Day 6). This controller isolates the
// mechanism on its own, both directions.
class HeaderDemoController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        return response()->json([
            'you_sent_this_header' => $request->header(
                'X-Custom-Header',
                '(you didn\'t send one — try -H "X-Custom-Header: hello")'
            ),
        ])
            // ->header() — set ONE header.
            ->header('X-Powered-By', 'Laravel Roadmap Week 2 Day 3')
            // ->withHeaders() — set SEVERAL at once, from an array.
            ->withHeaders([
                'X-Multiple-Example-1' => 'first',
                'X-Multiple-Example-2' => 'second',
            ]);
    }
}
