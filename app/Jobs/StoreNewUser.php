<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Log\Logger;
use App\User;
use Carbon\Carbon;

class StoreNewUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * User data.
     *
     * @var array
     */
    protected $data;

    /**
     * Create a new job instance.
     *
     * @param array $data User data.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    protected function hasTargetAge(User $user)
    {
        $from = now()->subYears(65);
        $to = now()->subYears(18);

        return $user->date_of_birth->between($from, $to);
    }

    /**
     * Save new user if user complice to target age
     * or user as unknown age.
     *
     * @return void
     */
    public function handle(Logger $log)
    {
        $user = new User($this->data);

        if (!$user->date_of_birth || $this->hasTargetAge($user)) {
            try {
                $user->save();
            } catch (Throwable $e) {
                $log->info($user->toJson());
            }
        }
    }
}
