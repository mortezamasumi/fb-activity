<?php

namespace Mortezamasumi\FbActivity\Resources;

use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Number;
use Mortezamasumi\FbActivity\Resources\Pages\ListActivity;
use Mortezamasumi\FbActivity\Resources\Pages\ViewActivity;
use Mortezamasumi\FbActivity\Resources\Schemas\FbActivityInfolist;
use Mortezamasumi\FbActivity\Resources\Table\FbActivitiesTable;
use Spatie\Activitylog\Models\Activity;

class FbActivityResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Activity::class;

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'delete',
            'export',
            'view_all_users',
        ];
    }

    public static function getNavigationIcon(): string
    {
        return config('fb-activity.navigation.icon');
    }

    public static function getNavigationSort(): ?int
    {
        return config('fb-activity.navigation.sort');
    }

    public static function getNavigationLabel(): string
    {
        return __(config('fb-activity.navigation.label'));
    }

    public static function getNavigationGroup(): ?string
    {
        return __(config('fb-activity.navigation.group'));
    }

    public static function getModelLabel(): string
    {
        return __(config('fb-activity.navigation.model_label'));
    }

    public static function getPluralModelLabel(): string
    {
        return __(config('fb-activity.navigation.plural_model_label'));
    }

    public static function getNavigationBadge(): ?string
    {
        return config('fb-activity.navigation.show_count')
            ? Number::format(number: static::getModel()::count(), locale: App::getLocale())
            : null;
    }

    public static function infolist(Schema $schema): Schema
    {
        return FbActivityInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FbActivitiesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivity::route('/'),
            'view' => ViewActivity::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->when(
                Auth::user()->can('view_all_users_fb::activity'),
                fn (Builder $query) => $query,
                fn (Builder $query) => $query->where('causer_id', '=', Auth::id()),
            );
    }
}
