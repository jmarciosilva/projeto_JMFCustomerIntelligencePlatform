<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Throwable;

class StatusController extends Controller
{
    public function __invoke(): View
    {
        return view('status', [
            'appName' => config('app.name'),
            'appEnv' => config('app.env'),
            'phpVersion' => PHP_VERSION,
            'laravelVersion' => app()->version(),
            'databaseStatus' => $this->databaseStatus(),
            'queueConnection' => config('queue.default'),
            'cacheStore' => config('cache.default'),
            'sessionDriver' => config('session.driver'),
        ]);
    }

    private function databaseStatus(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
