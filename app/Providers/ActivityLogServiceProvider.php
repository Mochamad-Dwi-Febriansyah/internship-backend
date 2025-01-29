<?php

namespace App\Providers;

use App\Models\ActivityLog;
use Illuminate\Support\ServiceProvider;

class ActivityLogServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (!function_exists('logActivity')) {
            function logActivity($user_id, $nama=null, $action, $model=null, $model_id=null, $changes = null)
            {
            $changes = $changes ? json_encode($changes) : null;
               ActivityLog::create([
                    'user_id' => $user_id,
                    'nama' => $nama,
                    'action' => $action,
                    'model' => $model,
                    'model_id' => $model_id,
                    'changes' => $changes,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]); 
                // dd([
                //     'user_id' => $user_id,
                //     'nama' => $nama,
                //     'action' => $action,
                //     'model' => $model,
                //     'model_id' => $model_id,
                //     'changes' => $changes,
                //     'ip_address' => request()->ip(),
                //     'user_agent' => request()->userAgent()
                // ]);
            }
        }
    } 
}
