<?php

namespace Mortezamasumi\FbActivity\Resources\Schemas;

use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Mortezamasumi\FbActivity\Facades\FbActivity;
use Mortezamasumi\FbPersian\Facades\FbPersian;

class FbActivityInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Flex::make([
                    Section::make([
                        TextEntry::make('causer.name')
                            ->label(__('fb-activity::fb-activity.columns.causer'))
                            ->default('-'),
                        TextEntry::make('subject_type')
                            ->label(__('fb-activity::fb-activity.columns.subject'))
                            ->formatStateUsing(fn (?Model $record, $state) => FbActivity::getSubject($record, $state)),
                        TextEntry::make('description')
                            ->label(__('fb-activity::fb-activity.columns.description')),
                    ]),
                    Section::make([
                        TextEntry::make('log_name')
                            ->label(__('fb-activity::fb-activity.columns.type'))
                            ->formatStateUsing(fn (?Model $record): string => $record->log_name ? ucwords($record->log_name) : '-'),
                        TextEntry::make('event')
                            ->label(__('fb-activity::fb-activity.columns.event'))
                            ->formatStateUsing(fn (?Model $record): string => $record?->event ? ucwords($record?->event) : '-'),
                        TextEntry::make('created_at')
                            ->label(__('fb-activity::fb-activity.columns.created_at'))
                            ->formatStateUsing(fn ($state): string => FbPersian::jDateTime(null, $state))
                    ])->grow(false),
                ])->from('md'),
                Section::make()
                    ->visible(fn ($record) => $record->properties?->count() > 0)
                    ->schema(fn (?Model $record) => $record
                        ->properties
                        ->map(fn ($value, $key) => KeyValueEntry::make($key)->state($value))
                        ->toArray())
                    ->columns(1),
            ])
            ->columns(1);
    }
}
