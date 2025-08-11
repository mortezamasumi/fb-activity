<?php

namespace Mortezamasumi\FbActivity\Exports;

use Filament\Actions\Exports\Models\Export;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;

class ActivityExporter extends Exporter
{
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

    public static function getCompletedNotificationBody(Export $export): string
    {
        if (App::getLocale() === 'fa') {
            $body = 'برون برد انجام شد و '.Number::format(number: number_format($export->successful_rows), locale: App::getLocale()).' سطر ایجاد شد';

            if ($failedRowsCount = $export->getFailedRowsCount()) {
                $postfix = $failedRowsCount > 1 ? '' : '';

                $body .= 'و تعداد '
                    .Number::format(number: number_format($failedRowsCount), locale: App::getLocale())
                    .' سطر دارای خطا بود و ایجاد نشد';
            }
        } else {
            $body = 'Your education export has completed and '.number_format($export->successful_rows).' '.Str::plural('row', $export->successful_rows).' exported.';

            if ($failedRowsCount = $export->getFailedRowsCount()) {
                $body .= ' '.number_format($failedRowsCount).' '.Str::plural('row', $failedRowsCount).' failed to export.';
            }
        }

        return $body;
    }
}
