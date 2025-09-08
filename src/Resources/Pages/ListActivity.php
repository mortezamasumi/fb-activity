<?php

namespace Mortezamasumi\FbActivity\Resources\Pages;

use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use Mortezamasumi\FbActivity\Resources\FbActivityResource;

class ListActivity extends ListRecords
{
    protected static string $resource = FbActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make('export-activities')
                ->label(__('fb-activity::fb-activity.export.label'))
                ->modalHeading(__('fb-activity::fb-activity.export.heading'))
                ->modalSubmitActionLabel(__('fb-activity::fb-activity.export.action'))
                ->exporter(config('fb-activity.export.exporter'))
                ->maxRows(config('fb-activity.export.max_export_rows'))
                ->visible(Auth::user()->can('Export:Activity')),
        ];
    }
}
