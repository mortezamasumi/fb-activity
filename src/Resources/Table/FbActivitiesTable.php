<?php

namespace Mortezamasumi\FbActivity\Resources\Table;

use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Mortezamasumi\FbActivity\Facades\FbActivity;
use Mortezamasumi\FbEssentials\Facades\FbPersian;
use Spatie\Activitylog\Models\Activity;

class FbActivitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query
                ->unless(
                    Auth::user()->can('ViewAllUsers:Activity'),
                    fn (Builder $query) => $query->where('causer_id', '=', Auth::id()),
                ))
            ->columns([
                TextColumn::make('log_name')
                    ->label(__('fb-activity::fb-activity.table.type'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => ucwords($state))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('event')
                    ->label(__('fb-activity::fb-activity.table.event'))
                    ->formatStateUsing(fn ($state) => ucwords($state))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'updated' => 'warning',
                        'created' => 'success',
                        'deleted' => 'danger',
                        default => 'primary',
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subject_type')
                    ->label(__('fb-activity::fb-activity.table.subject'))
                    ->formatStateUsing(fn ($state) => FbActivity::getSubjectName(null, $state))
                    ->tooltip(fn (Model $record): ?string => $record->subject_id)
                    ->sortable()
                    ->searchable()
                    ->copyable()
                    ->copyableState(fn (Model $record): ?string => $record->subject_id),
                TextColumn::make('causer.name')
                    ->label(__('fb-activity::fb-activity.table.causer'))
                    ->default('-')
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query
                        ->when(
                            $search === '-',
                            fn (Builder $query): Builder => $query->whereDoesntHave('causer'),
                            fn (Builder $query): Builder => $query->whereHas(
                                'causer',
                                fn (Builder $query): Builder => $query
                                    ->whereAny(['name', 'reverseName', 'first_name', 'last_name', 'profile'], 'like', "%{$search}%")
                            )
                        ))
                    ->visible(Auth::user()->can('ViewAllUsers:Activity')),
                TextColumn::make('created_at')
                    ->label(__('fb-activity::fb-activity.table.created_at'))
                    ->jDateTime()
                    ->sortable(),
            ])
            ->filters([
                Filter::make('created_at')
                    ->label('fb-activity::fb-activity.table.created_at')
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = __('fb-activity::fb-activity.filter.created_from').': '.FbPersian::jDateTime(null, $data['created_from']);
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = __('fb-activity::fb-activity.filter.created_until').': '.FbPersian::jDateTime(null, $data['created_until']);
                        }

                        return $indicators;
                    })
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                DateTimePicker::make('created_from')
                                    ->label(__('fb-activity::fb-activity.filter.created_from'))
                                    ->when(
                                        value: App::getLocale() === 'fa',
                                        callback: fn ($component) => $component->jalali(weekdaysShort: true)
                                    )
                                    ->seconds(false)
                                    ->jDateTime(),
                                DateTimePicker::make('created_until')
                                    ->label(__('fb-activity::fb-activity.filter.created_until'))
                                    ->when(
                                        value: App::getLocale() === 'fa',
                                        callback: fn ($component) => $component->jalali(weekdaysShort: true)
                                    )
                                    ->seconds(false)
                                    ->jDateTime(),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
                SelectFilter::make('event')
                    ->label(__('fb-activity::fb-activity.table.event'))
                    ->multiple()
                    ->options(Activity::distinct()->pluck('event', 'event')->filter()),
                SelectFilter::make('subject_type')
                    ->label(__('fb-activity::fb-activity.table.subject'))
                    ->options(Activity::distinct()->pluck('subject_type', 'subject_type')->filter()->mapWithKeys(fn ($item, $key) => [
                        $key => Str::of($item)->afterLast('\\')->headline(),
                    ])),
                SelectFilter::make('causer_id')
                    ->label(__('fb-activity::fb-activity.table.causer'))
                    ->relationship(
                        'causer',
                        'id',
                        fn (Builder $query) => $query->whereNotNull('causer_id')
                    )
                    ->getOptionLabelFromRecordUsing(fn (Model $record) => $record->causer?->name ?? '-')
                    ->visible(Auth::user()->can('ViewAllUsers:Activity')),
            ])
            ->headerActions([
                DeleteBulkAction::make()->visible(Auth::user()->can('Delete:Activity')),
            ])
            ->defaultSort('created_at', 'desc')
            ->persistSortInSession()
            ->persistSearchInSession()
            ->persistFiltersInSession();
    }
}
