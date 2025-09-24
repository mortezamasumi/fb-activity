<?php

namespace Mortezamasumi\FbActivity\Resources;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Number;
use Mortezamasumi\FbActivity\Resources\Pages\ListActivity;
use Mortezamasumi\FbActivity\Resources\Pages\ViewActivity;
use Mortezamasumi\FbActivity\Resources\Schemas\FbActivityInfolist;
use Mortezamasumi\FbActivity\Resources\Table\FbActivitiesTable;
use Spatie\Activitylog\Models\Activity;
use BackedEnum;
use UnitEnum;

class FbActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    public static function getModelLabel(): string
    {
        return __(config('fb-activity.navigation.model_label'));
    }

    public static function getPluralModelLabel(): string
    {
        return __(config('fb-activity.navigation.plural_model_label'));
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __(config('fb-activity.navigation.group'));
    }

    public static function getNavigationParentItem(): ?string
    {
        return __(config('fb-activity.navigation.parent_item'));
    }

    public static function getNavigationIcon(): string|BackedEnum|Htmlable|null
    {
        return config('fb-activity.navigation.icon');
    }

    public static function getActiveNavigationIcon(): string|BackedEnum|Htmlable|null
    {
        return config('fb-activity.navigation.active_icon') ?? static::getNavigationIcon();
    }

    public static function getNavigationBadge(): ?string
    {
        if (! config('fb-activity.navigation.badge')) {
            return null;
        }

        Number::useLocale(App::getLocale());

        return Number::format(static::getModel()::count());
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return config('fb-activity.navigation.badge_tooltip');
    }

    public static function getNavigationSort(): ?int
    {
        return config('fb-activity.navigation.sort');
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
}
