<?php

namespace Mortezamasumi\FbActivity\Resources\Schemas;

use Carbon\Carbon;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
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
                            ->label(__('fb-activity::fb-activity.infolist.causer'))
                            ->default('-')
                            ->weight(FontWeight::SemiBold)
                            ->size(TextSize::Large),
                        TextEntry::make('subject_type')
                            ->label(__('fb-activity::fb-activity.infolist.subject'))
                            ->formatStateUsing(fn (?Model $record, $state) => FbActivity::getSubject($record, $state))
                            ->weight(FontWeight::SemiBold)
                            ->size(TextSize::Large),
                        TextEntry::make('description')
                            ->label(__('fb-activity::fb-activity.infolist.description'))
                            ->weight(FontWeight::SemiBold)
                            ->size(TextSize::Large),
                    ]),
                    Section::make([
                        TextEntry::make('log_name')
                            ->label(__('fb-activity::fb-activity.infolist.type'))
                            ->formatStateUsing(fn (?Model $record): string => $record->log_name ? ucwords($record->log_name) : '-')
                            ->weight(FontWeight::SemiBold)
                            ->size(TextSize::Large),
                        TextEntry::make('event')
                            ->label(__('fb-activity::fb-activity.infolist.event'))
                            ->formatStateUsing(fn (?Model $record): string => $record?->event ? ucwords($record?->event) : '-')
                            ->weight(FontWeight::SemiBold)
                            ->size(TextSize::Large),
                        TextEntry::make('created_at')
                            ->label(__('fb-activity::fb-activity.infolist.created_at'))
                            ->formatStateUsing(fn ($state): string => FbPersian::jDateTime(null, $state))
                            ->weight(FontWeight::SemiBold)
                            ->size(TextSize::Large)
                    ])
                        ->grow(false),
                ])
                    ->from('md'),
                Section::make()
                    ->visible(fn ($record) => $record->properties?->count() > 0)
                    ->schema(fn (?Model $record) => $record
                        ->properties
                        ->mapWithKeys(fn ($value, $key) => [
                            $key => collect($value)
                                ->mapWithKeys(function ($v, $k) {
                                    try {
                                        throw_unless(preg_match('/[- \/]/', $v));
                                        $v = FbPersian::jDateTime(null, Carbon::parse($v));
                                    } catch (\Exception $e) {
                                        $v = is_array($v) ? json_encode($v) : $v;
                                    }

                                    return [$k => $v];
                                })
                                ->toArray()
                        ])
                        ->map(fn ($value, $key) => KeyValueEntry::make($key)->state($value))
                        ->toArray())
                    ->columns(1),
            ])
            ->columns(1);
    }
}
