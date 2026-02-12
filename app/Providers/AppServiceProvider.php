<?php

namespace App\Providers;

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use App\Policies\ProjectPolicy;
use App\Policies\TeamPolicy;
use App\Support\QueueFailureRecorder;
use Carbon\CarbonImmutable;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureAuthorization();
        $this->configureQueueReliability();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null
        );
    }

    protected function configureAuthorization(): void
    {
        Gate::policy(Team::class, TeamPolicy::class);
        Gate::policy(Project::class, ProjectPolicy::class);

        Gate::define('access-admin', function (User $user): bool {
            return $user->isAdmin();
        });
    }

    protected function configureQueueReliability(): void
    {
        Queue::failing(function (JobFailed $event): void {
            if (! config('queue.dead_letter.enabled', true)) {
                return;
            }

            app(QueueFailureRecorder::class)->recordFromFailedEvent($event);
        });
    }
}
