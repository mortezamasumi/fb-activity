<?php

namespace Mortezamasumi\FbActivity\Resources\Pages;

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
            // ExportAction::make('export-activities')
            //     ->label(__('activity::activity.export.label'))
            //     ->modalHeading(__('activity::activity.export.heading'))
            //     ->modalSubmitActionLabel(__('activity::activity.export.action'))
            //     ->exporter(config('activity.export.exporter'))
            //     ->maxRows(config('activity.export.max_export_rows'))
            //     ->visible(Auth::user()->can('export_activity')),
        ];
    }
}
