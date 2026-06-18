<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class Controller
{
    protected function respond(bool $success, string $msg = '', array $data = [], int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => $success,
            'msg' => $msg,
            'data' => $data,
            'code' => $code,
        ], $code);
    }
}
