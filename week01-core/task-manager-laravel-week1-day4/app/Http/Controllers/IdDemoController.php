<?php

namespace App\Http\Controllers;

use App\Services\Contracts\IdGeneratorInterface;
use Illuminate\Http\JsonResponse;

class IdDemoController extends Controller
{
    public function demonstrate(): JsonResponse
    {
        $first = app(IdGeneratorInterface::class)->next();
        $second = app(IdGeneratorInterface::class)->next();
        $third = app()->make(IdGeneratorInterface::class)->next();

        return response()->json([
            'resolutions' => [$first, $second, $third],
            'explanation' => 'IdGeneratorInterface is bound as a singleton() — '
                .'all three "instance #" values should be identical, and the '
                .'counter should genuinely increment: id-1, id-2, id-3.',
        ]);
    }
}
