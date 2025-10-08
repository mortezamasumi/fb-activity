<?php

namespace Mortezamasumi\FbActivity\Tests;

use Filament\Facades\Filament;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Mortezamasumi\FbActivity\FbActivityPlugin;
use Mortezamasumi\FbActivity\FbActivityServiceProvider;
use Mortezamasumi\FbEssentials\FbEssentialsPlugin;
use Mortezamasumi\FbEssentials\FbEssentialsServiceProvider;
use Orchestra\Testbench\TestCase as TestbenchTestCase;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\Permission\PermissionServiceProvider;

class TestCase extends TestbenchTestCase
{
    use RefreshDatabase;

    protected function defineEnvironment($app)
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('force_change_password')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('podcasts', function (Blueprint $table) {
            $table->id();
            $table->string('text');
            $table->timestamps();
        });

        Filament::registerPanel(
            Panel::make()
                ->id('admin')
                ->path('/')
                ->login()
                ->default()
                ->pages([
                    Dashboard::class,
                ])
                ->middleware([
                    \Illuminate\Cookie\Middleware\EncryptCookies::class,
                    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
                    \Illuminate\Session\Middleware\StartSession::class,
                    \Filament\Http\Middleware\AuthenticateSession::class,
                    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
                    \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
                    \Illuminate\Routing\Middleware\SubstituteBindings::class,
                    \Filament\Http\Middleware\DisableBladeIconComponents::class,
                    \Filament\Http\Middleware\DispatchServingFilamentEvent::class,
                ])
                ->authMiddleware([
                    \Filament\Http\Middleware\Authenticate::class,
                ])
                ->plugins([
                    FbEssentialsPlugin::make(),
                    FbActivityPlugin::make(),
                ])
        );
    }

    protected function defineDatabaseMigrations()
    {
        $this->artisan('vendor:publish', ['--tag' => 'fb-activity-migrations']);
    }

    protected function getPackageProviders($app)
    {
        return [
            \BladeUI\Heroicons\BladeHeroiconsServiceProvider::class,
            \BladeUI\Icons\BladeIconsServiceProvider::class,
            \Filament\FilamentServiceProvider::class,
            \Filament\Actions\ActionsServiceProvider::class,
            \Filament\Forms\FormsServiceProvider::class,
            \Filament\Infolists\InfolistsServiceProvider::class,
            \Filament\Notifications\NotificationsServiceProvider::class,
            \Filament\Schemas\SchemasServiceProvider::class,
            \Filament\Support\SupportServiceProvider::class,
            \Filament\Tables\TablesServiceProvider::class,
            \Filament\Widgets\WidgetsServiceProvider::class,
            \Livewire\LivewireServiceProvider::class,
            \RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider::class,
            \Orchestra\Workbench\WorkbenchServiceProvider::class,
            ActivitylogServiceProvider::class,
            FbEssentialsServiceProvider::class,
            FbActivityServiceProvider::class,
        ];
    }
}
