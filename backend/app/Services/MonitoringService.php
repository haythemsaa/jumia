<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class MonitoringService
{
    /**
     * Check system health
     */
    public function getHealthStatus(): array
    {
        return [
            'status' => 'healthy',
            'timestamp' => now()->toIso8601String(),
            'checks' => [
                'database' => $this->checkDatabase(),
                'cache' => $this->checkCache(),
                'redis' => $this->checkRedis(),
                'storage' => $this->checkStorage(),
                'queue' => $this->checkQueue(),
            ],
            'metrics' => $this->getMetrics(),
        ];
    }

    /**
     * Check database connectivity
     */
    protected function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            return [
                'status' => 'ok',
                'message' => 'Database connection successful'
            ];
        } catch (\Exception $e) {
            Log::channel('security')->error('Database health check failed', [
                'error' => $e->getMessage()
            ]);

            return [
                'status' => 'error',
                'message' => 'Database connection failed'
            ];
        }
    }

    /**
     * Check cache functionality
     */
    protected function checkCache(): array
    {
        try {
            $key = 'health_check_' . time();
            Cache::put($key, 'test', 10);
            $value = Cache::get($key);
            Cache::forget($key);

            if ($value === 'test') {
                return [
                    'status' => 'ok',
                    'message' => 'Cache working properly'
                ];
            }

            return [
                'status' => 'error',
                'message' => 'Cache test failed'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Cache error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check Redis connectivity
     */
    protected function checkRedis(): array
    {
        try {
            Redis::ping();
            return [
                'status' => 'ok',
                'message' => 'Redis connection successful'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Redis connection failed'
            ];
        }
    }

    /**
     * Check storage availability
     */
    protected function checkStorage(): array
    {
        try {
            $path = storage_path();
            $free = disk_free_space($path);
            $total = disk_total_space($path);
            $used = $total - $free;
            $percentage = ($used / $total) * 100;

            return [
                'status' => $percentage < 90 ? 'ok' : 'warning',
                'message' => 'Storage usage: ' . round($percentage, 2) . '%',
                'free_gb' => round($free / 1024 / 1024 / 1024, 2),
                'total_gb' => round($total / 1024 / 1024 / 1024, 2),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Storage check failed'
            ];
        }
    }

    /**
     * Check queue status
     */
    protected function checkQueue(): array
    {
        try {
            // Check failed jobs
            $failedJobs = DB::table('failed_jobs')->count();

            return [
                'status' => $failedJobs < 10 ? 'ok' : 'warning',
                'message' => "Failed jobs: {$failedJobs}",
                'failed_jobs' => $failedJobs
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Queue check failed'
            ];
        }
    }

    /**
     * Get system metrics
     */
    protected function getMetrics(): array
    {
        try {
            return [
                'users' => [
                    'total' => DB::table('users')->count(),
                    'active_today' => DB::table('users')
                        ->where('last_login_at', '>=', now()->subDay())
                        ->count(),
                ],
                'orders' => [
                    'total' => DB::table('orders')->count(),
                    'today' => DB::table('orders')
                        ->whereDate('created_at', today())
                        ->count(),
                    'pending' => DB::table('orders')
                        ->where('status', 'pending')
                        ->count(),
                ],
                'products' => [
                    'total' => DB::table('products')->count(),
                    'active' => DB::table('products')
                        ->where('status', 'active')
                        ->count(),
                    'out_of_stock' => DB::table('products')
                        ->where('stock', 0)
                        ->count(),
                ],
                'revenue' => [
                    'today' => DB::table('orders')
                        ->whereDate('created_at', today())
                        ->where('status', 'completed')
                        ->sum('total'),
                    'month' => DB::table('orders')
                        ->whereMonth('created_at', now()->month)
                        ->where('status', 'completed')
                        ->sum('total'),
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Metrics collection failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Log security event
     */
    public function logSecurityEvent(string $event, array $data = []): void
    {
        Log::channel('security')->warning($event, array_merge($data, [
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'user_id' => auth()->id(),
            'timestamp' => now()->toIso8601String(),
        ]));
    }

    /**
     * Log payment event
     */
    public function logPaymentEvent(string $event, array $data = []): void
    {
        Log::channel('payments')->info($event, array_merge($data, [
            'user_id' => auth()->id(),
            'timestamp' => now()->toIso8601String(),
        ]));
    }
}
