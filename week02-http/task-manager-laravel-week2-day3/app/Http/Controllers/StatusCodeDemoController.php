<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// ── HTTP status codes that matter — an interactive review ───────────────
// Pass ?code=XXX and get back a response actually carrying that exact
// status code, with a description of when it's the right choice. This is
// deliberately generic/explorable, separate from TaskController's actual,
// specific uses of these codes — try GET /api/status-demo?code=409 here,
// then compare it against actually triggering a real 409 via
// PATCH /api/tasks/3/done.
class StatusCodeDemoController extends Controller
{
    private const DESCRIPTIONS = [
        200 => 'OK — a successful GET/PUT/PATCH with a response body.',
        201 => 'Created — a successful POST that created a new resource. '
            .'Should be paired with a Location header (see TaskController::store()).',
        204 => 'No Content — a successful action with nothing to return '
            .'(see TaskController::destroy()).',
        400 => 'Bad Request — the request itself is malformed, independent '
            .'of any specific field.',
        401 => 'Unauthorized — authentication is required and missing/invalid. '
            .'Not built in this project yet — arrives Week 5.',
        403 => 'Forbidden — authenticated, but not allowed to perform this '
            .'specific action. Not built in this project yet — arrives Week 5–6.',
        404 => 'Not Found — the resource doesn\'t exist. Laravel returns '
            .'this AUTOMATICALLY when route model binding fails (Week 2 Day 1) '
            .'— never written explicitly anywhere in this project.',
        409 => 'Conflict — the request is well-formed and the resource '
            .'exists, but the action conflicts with its CURRENT STATE. '
            .'See TaskController::markAsDone().',
        422 => 'Unprocessable Entity — validation failed. Laravel returns '
            .'this AUTOMATICALLY from a failed Form Request (Week 2 Day 2) '
            .'— never written explicitly anywhere in this project.',
        500 => 'Internal Server Error — something broke unexpectedly on '
            .'the server, not something the client did wrong.',
        501 => 'Not Implemented — used honestly in this project on Week 2 '
            .'Day 1, before real store()/update()/destroy() logic existed. '
            .'Not currently returned by any live endpoint anymore — kept '
            .'here for reference.',
    ];

    public function __invoke(Request $request): JsonResponse
    {
        $code = (int) $request->query('code', 200);

        return response()->json([
            'requested_status' => $code,
            'description' => self::DESCRIPTIONS[$code]
                ?? 'Not one of the codes this demo describes — try 200, 201, '
                    .'204, 400, 401, 403, 404, 409, 422, 500, or 501.',
        ], $code);
    }
}
