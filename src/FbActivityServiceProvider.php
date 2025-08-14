<?php

namespace Mortezamasumi\FbActivity;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use Livewire\Features\SupportTesting\Testable;
use Mortezamasumi\FbActivity\Policies\FbActivityPolicy;
use Mortezamasumi\FbActivity\Testing\TestsFbActivity;
use Spatie\Activitylog\Models\Activity;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FbActivityServiceProvider extends PackageServiceProvider
{
    public static string $name = 'fb-activity';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations();
            })
            ->hasConfigFile()
            ->hasMigrations($this->getMigrations())
            ->hasTranslations();
    }

    public function packageBooted(): void
    {
        Gate::policy(Activity::class, FbActivityPolicy::class);

        Testable::mixin(new TestsFbActivity);
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            'create_activity_log_table',
        ];
    }
}
