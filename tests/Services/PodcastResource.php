<?php

namespace Mortezamasumi\FbActivity\Tests\Services;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Mortezamasumi\FbActivity\Tests\Services\Pages\ManagePodcasts;
use Mortezamasumi\FbActivity\Tests\Services\Pages\ViewPodcast;

class PodcastResource extends Resource
{
    protected static ?string $model = Podcast::class;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePodcasts::route('/'),
            'view' => ViewPodcast::route('/{record}'),
        ];
    }
}
