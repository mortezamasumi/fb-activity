<?php

namespace Mortezamasumi\FbActivity\Resources\Table;

use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Mortezamasumi\FbActivity\Facades\FbActivity;
use Mortezamasumi\FbEssentials\Facades\FbPersian;
use Spatie\Activitylog\Models\Activity;

class FbActivitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('log_name')
                    ->label(__('fb-activity::fb-activity.table.type'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => ucwords($state))
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
                TextColumn::make('subject')
                    ->label(__('fb-activity::fb-activity.table.subject'))
                    ->state(fn (Activity $record): ?string => FbActivity::resolveSubjectTitle($record))
                    ->description(fn (Activity $record): ?string => config('fb-activity.subject.show_model_label', true)
                        ? FbActivity::subjectModelLabel($record->getAttribute('subject_type'))
                        : null)
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy('subject_type', in_array($direction, ['asc', 'desc'], true) ? $direction : 'asc'))
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query
                        ->where(fn (Builder $query) => $query
                            ->where('subject_type', 'like', "%{$search}%")
                            ->orWhere('subject_id', 'like', "%{$search}%"))),
                TextColumn::make('causer')
                    ->label(__('fb-activity::fb-activity.table.causer'))
                    ->state(fn (Activity $record): ?string => FbActivity::resolveCauserTitle($record))
                    ->default('-')
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query
                        ->when(
                            $search === '-',
                            fn (Builder $query): Builder => $query->whereDoesntHave('causer'),
                            fn (Builder $query): Builder => $query->whereHas(
                                'causer',
                                function (Builder $query) use ($search) {
                                    $potentialColumns = ['name', 'first_name', 'last_name'];

                                    $tableName = $query->getModel()->getTable();
                                    $existingColumns = Schema::getColumnListing($tableName);
                                    $validColumns = array_intersect($potentialColumns, $existingColumns);

                                    if (! empty($validColumns)) {
                                        return $query->whereAny($validColumns, 'like', "%{$search}%");
                                    }

                                    return $query;
                                }
                            )
                        ))
                    ->visible((bool) Auth::user()?->can('ViewAllUsers:Activity')),
                TextColumn::make('created_at')
                    ->label(__('fb-activity::fb-activity.table.created_at'))
                    ->formatStateUsing(fn ($state) => FbActivity::formatDateTime($state))
                    ->sortable(),
            ])
            ->filters([
                Filter::make('created_at')
                    ->label(__('fb-activity::fb-activity.table.created_at'))
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
                                    ->seconds(false)
                                    ->jDateTime(),
                                DateTimePicker::make('created_until')
                                    ->label(__('fb-activity::fb-activity.filter.created_until'))
                                    ->seconds(false)
                                    ->jDateTime(),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', FbActivity::toStorageDate($date)),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', FbActivity::toStorageDate($date)),
                            );
                    }),
            ])
            ->headerActions([
                DeleteBulkAction::make()->visible((bool) Auth::user()?->can('Delete:Activity')),
            ])
            ->defaultSort('created_at', 'desc')
            ->persistSortInSession()
            ->persistSearchInSession()
            ->persistFiltersInSession();
    }
}
