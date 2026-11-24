<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class HealthController extends Controller
{
    public function index(): JsonResponse
    {
        $status = [
            'app' => true,
            'db' => false,
            'redis' => false,
        ];

        try {
            DB::select('select 1');
            $status['db'] = true;
        } catch (\Throwable $e) {
            $status['db'] = false;
        }

        try {
            Redis::connection()->ping();
            $status['redis'] = true;
        } catch (\Throwable $e) {
            $status['redis'] = false;
        }

        $overall = $status['app'] && $status['db'] && $status['redis'];

        return response()->json([
            'status' => $overall ? 'ok' : 'degraded',
            'services' => $status,
            'timestamp' => now()->toIso8601String(),
        ], $overall ? 200 : 503);
    }
}
