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
use Illuminate\Support\Collection;
use Mortezamasumi\FbActivity\Facades\FbActivity;

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
                            ->formatStateUsing(fn (?Model $record): string => $record?->getAttribute('log_name') ? ucwords((string) $record->getAttribute('log_name')) : '-')
                            ->weight(FontWeight::SemiBold)
                            ->size(TextSize::Large),
                        TextEntry::make('event')
                            ->label(__('fb-activity::fb-activity.infolist.event'))
                            ->formatStateUsing(fn (?Model $record): string => $record?->getAttribute('event') ? ucwords((string) $record->getAttribute('event')) : '-')
                            ->weight(FontWeight::SemiBold)
                            ->size(TextSize::Large),
                        TextEntry::make('created_at')
                            ->label(__('fb-activity::fb-activity.infolist.created_at'))
                            ->jDateTime()
                            ->weight(FontWeight::SemiBold)
                            ->size(TextSize::Large),
                    ])
                        ->grow(false),
                ])
                    ->from('md'),
                Section::make()
                    ->visible(function (?Model $record): bool {
                        /** @var Collection<int|string, mixed>|null $properties */
                        $properties = $record?->getAttribute('properties');

                        return $properties !== null && $properties->isNotEmpty();
                    })
                    ->schema(function (?Model $record): array {
                        if ($record === null) {
                            return [];
                        }

                        /** @var Collection<int|string, mixed> $properties */
                        $properties = $record->getAttribute('properties');

                        return $properties
                            ->mapWithKeys(fn (mixed $value, mixed $key) => [
                                $key => collect((array) $value)
                                    ->mapWithKeys(function (mixed $v, mixed $k) {
                                        $v = match (true) {
                                            is_string($v) => $v,
                                            is_null($v) => '',
                                            is_bool($v) => $v ? '1' : '0',
                                            is_array($v), is_object($v) => json_encode($v, JSON_UNESCAPED_UNICODE) ?: '',
                                            default => (string) $v
                                        };

                                        if (preg_match('/^\d{1,4}[-\/]\d{1,2}[-\/]\d{2,4}$/', $v)) {
                                            $v = __jdatetime(null, Carbon::parse($v));
                                        }

                                        return [$k => $v];
                                    })
                                    ->toArray(),
                            ])
                            ->map(fn ($value, $key) => KeyValueEntry::make((string) $key)->state($value))
                            ->toArray();
                    })
                    ->columns(1),
            ])
            ->columns(1);
    }
}
