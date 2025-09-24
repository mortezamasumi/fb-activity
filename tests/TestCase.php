<?php

namespace Mortezamasumi\FbActivity\Tests;

use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Filament\Actions\ActionsServiceProvider;
use Filament\Facades\Filament;
use Filament\Forms\FormsServiceProvider;
use Filament\Infolists\InfolistsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Filament\Widgets\WidgetsServiceProvider;
use Filament\FilamentServiceProvider;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Gate;
use Livewire\LivewireServiceProvider;
use Mortezamasumi\FbActivity\Tests\Services\FbActivityPanelProvider;
use Mortezamasumi\FbActivity\FbActivityServiceProvider;
use Mortezamasumi\FbEssentials\FbEssentialsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider;

use function Orchestra\Testbench\default_migration_path;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        // Factory::guessFactoryNamesUsing(
        //     fn (string $modelName) => 'Mortezamasumi\\FbActivity\\Database\\Factories\\'.class_basename($modelName).'Factory'
        // );
        // Gate::before(fn () => true);
    }

    protected function defineEnvironment($app)
    {
        // Filament::registerPanel(
        //     Panel::make()
        //         ->id('admin')
        //         ->path('/')
        //         ->login()
        //         ->default()
        //         ->authGuard('web')  // important for actingAs to work
        // );

        // config()->set('filament-shield', include __DIR__.'/../vendor/bezhansalleh/filament-shield/config/filament-shield.php');
        // config()->set('app.key', 'base64:Hupx3yAySikrM2/edkZQNQHslgDWYfiBfCuSThJ5SK8=');
        // config()->set('database.default', 'testing');
        // config()->set('queue.batching.database', 'testing');
        // config()->set('auth.providers.users.model', '\Tests\Models\User');

        /*
         * $migration = include __DIR__.'/../database/migrations/create_page-test_table.php.stub';
         * $migration->up();
         */
        // View::addLocation(__DIR__.'/resources/views');
        // View::addLocation(__DIR__.'/../resources/views');
    }

    protected function defineDatabaseMigrations()
    {
        /** @var Orchestra $this */
        $this->loadMigrationsFrom(default_migration_path());
        $this->loadMigrationsFrom(default_migration_path().'/notifications');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
    }

    protected function getPackageProviders($app)
    {
        return [
            ActionsServiceProvider::class,
            BladeCaptureDirectiveServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            BladeIconsServiceProvider::class,
            FilamentServiceProvider::class,
            FormsServiceProvider::class,
            InfolistsServiceProvider::class,
            LivewireServiceProvider::class,
            NotificationsServiceProvider::class,
            SupportServiceProvider::class,
            TablesServiceProvider::class,
            WidgetsServiceProvider::class,
            FbEssentialsServiceProvider::class,
            FbActivityServiceProvider::class,
            FbActivityPanelProvider::class,
        ];
    }
}
