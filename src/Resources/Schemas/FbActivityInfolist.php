<?php

namespace Mortezamasumi\FbActivity\Resources\Schemas;

use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Mortezamasumi\FbActivity\Facades\FbActivity;
use Spatie\Activitylog\Models\Activity;

class FbActivityInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Flex::make([
                    Section::make([
                        TextEntry::make('causer')
                            ->label(__('fb-activity::fb-activity.infolist.causer'))
                            ->state(fn (?Model $record): ?string => $record instanceof Activity
                                ? FbActivity::resolveCauserTitle($record)
                                : null)
                            ->default('-')
                            ->weight(FontWeight::SemiBold)
                            ->size(TextSize::Large),
                        TextEntry::make('subject')
                            ->label(__('fb-activity::fb-activity.infolist.subject'))
                            ->state(function (?Model $record): ?string {
                                if (! $record instanceof Activity) {
                                    return null;
                                }

                                $title = FbActivity::resolveSubjectTitle($record);
                                $label = FbActivity::subjectModelLabel($record->getAttribute('subject_type'));

                                if ($title === null || $label === '-') {
                                    return $title ?? $label;
                                }

                                return __('fb-activity::fb-activity.infolist.subject_name', [
                                    'a' => $label,
                                    'b' => $title,
                                ]);
                            })
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
                            ->formatStateUsing(fn (?Model $record): ?string => FbActivity::formatDateTime($record?->getAttribute('created_at')))
                            ->weight(FontWeight::SemiBold)
                            ->size(TextSize::Large),
                    ])
                        ->grow(false),
                ])
                    ->from('md'),
                Section::make(__('fb-activity::fb-activity.infolist.changes'))
                    ->columns(1)
                    ->visible(fn (?Model $record): bool => self::hasDiffFor($record))
                    ->schema(fn (?Model $record): array => self::changesSchema($record)),
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

                        // When an old/new diff is rendered in its own section,
                        // keep those two keys out of the flat list.
                        $skipKeys = self::hasDiffFor($record) ? ['old', 'attributes'] : [];

                        return $properties
                            ->reject(fn (mixed $value, mixed $key) => in_array($key, $skipKeys, true))
                            ->mapWithKeys(fn (mixed $value, mixed $key) => [
                                $key => collect((array) $value)
                                    ->mapWithKeys(function (mixed $v, mixed $k) {
                                        return [$k => self::stringify($v)];
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

    /**
     * Dedicated "Changes" section rendering one row per changed attribute
     * (attribute | old | new) when spatie stored both `old` and `attributes`
     * property keys (spec V2, plan Task 7).
     */
    protected static function hasDiffFor(?Model $record): bool
    {
        if ($record === null) {
            return false;
        }

        /** @var Collection<int|string, mixed>|null $properties */
        $properties = $record->getAttribute('properties');

        if ($properties === null) {
            return false;
        }

        return $properties->has('old') &&
            $properties->has('attributes') &&
            filled((array) $properties->get('old')) &&
            filled((array) $properties->get('attributes'));
    }

    /**
     * @return array<int, Grid>
     */
    protected static function changesSchema(?Model $record): array
    {
        if ($record === null || ! self::hasDiffFor($record)) {
            return [];
        }

        /** @var Collection<int|string, mixed> $properties */
        $properties = $record->getAttribute('properties');

        $old = (array) ($properties->get('old') ?? []);
        $new = (array) ($properties->get('attributes') ?? []);

        return collect($new)
            ->map(fn (mixed $value, mixed $attribute): Grid => Grid::make(3)
                ->schema([
                    TextEntry::make('attribute')
                        ->label(__('fb-activity::fb-activity.infolist.attribute'))
                        ->state(self::humanizeAttribute((string) $attribute))
                        ->weight(FontWeight::SemiBold),
                    TextEntry::make('old')
                        ->label(__('fb-activity::fb-activity.infolist.old'))
                        ->state(self::stringify($old[$attribute] ?? null))
                        ->color('danger'),
                    TextEntry::make('new')
                        ->label(__('fb-activity::fb-activity.infolist.new'))
                        ->state(self::stringify($value))
                        ->color('success'),
                ]))
            ->values()
            ->all();
    }

    protected static function stringify(mixed $value): string
    {
        $value = match (true) {
            is_string($value) => $value,
            is_null($value) => '',
            is_bool($value) => $value ? '1' : '0',
            is_array($value), is_object($value) => json_encode($value, JSON_UNESCAPED_UNICODE) ?: '',
            default => (string) $value
        };

        if (preg_match('/^\d{1,4}[-\/]\d{1,2}[-\/]\d{2,4}$/', $value)) {
            $value = FbActivity::formatDateTime($value, __('fb-activity::fb-activity.table.date_format'));
        }

        return $value ?? '';
    }

    protected static function humanizeAttribute(string $attribute): string
    {
        return Str::of($attribute)->replace('_', ' ')->headline()->toString();
    }
}
