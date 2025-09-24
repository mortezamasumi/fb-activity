<?php

namespace Mortezamasumi\FbActivity\Exports;

use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Illuminate\Database\Eloquent\Model;
use Mortezamasumi\FbEssentials\Traits\ExportCompletedNotificationBody;
use Spatie\Activitylog\Models\Activity;

class ActivityExporter extends Exporter
{
    use ExportCompletedNotificationBody;

    protected static ?string $model = Activity::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('log_name')
                ->label(__('fb-activity::fb-activity.table.type')),
            ExportColumn::make('event')
                ->label(__('fb-activity::fb-activity.table.event')),
            ExportColumn::make('subject_type')
                ->label(__('fb-activity::fb-activity.table.subject')),
            ExportColumn::make('subject_id')
                ->label(__('fb-activity::fb-activity.table.subject_id')),
            ExportColumn::make('causer')
                ->label(__('fb-activity::fb-activity.table.causer'))
                ->formatStateUsing(fn (Model $record) => $record?->causer?->name ?? '-'),
            ExportColumn::make('causer_id')
                ->label(__('fb-activity::fb-activity.table.causer_id')),
            ExportColumn::make('properties')
                ->label(__('fb-activity::fb-activity.table.properties')),
            ExportColumn::make('description')
                ->label(__('fb-activity::fb-activity.table.description')),
            ExportColumn::make('created_at')
                ->label(__('fb-activity::fb-activity.table.created_at'))
                ->jDateTime(),
        ];
    }
}
