<?php

use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Facades\Gate;
use Mortezamasumi\FbActivity\Facades\FbActivity;
use Mortezamasumi\FbActivity\Resources\Exports\ActivityExporter;
use Mortezamasumi\FbActivity\Resources\Pages\ListActivity;
use Mortezamasumi\FbActivity\Resources\Pages\ViewActivity;
use Mortezamasumi\FbActivity\Resources\FbActivityResource;
use Mortezamasumi\FbActivity\Tests\Services\Podcast;
use Mortezamasumi\FbActivity\Tests\Services\User;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

describe('as guest/un-authorized user', function () {
    it('guests cannot access the resource', function () {
        Podcast::factory()->create();

        $this
            ->get(FbActivityResource::getUrl('index'))
            ->assertRedirect(config('filament.auth.pages.login'));

        $this
            ->get(FbActivityResource::getUrl('view', ['record' => Activity::first()]))
            ->assertRedirect(config('filament.auth.pages.login'));
    });

    it('un-authorized users cannot access the resource', function () {
        $this->actingAs(User::factory()->create());

        Podcast::factory()->create();

        $this
            ->get(FbActivityResource::getUrl('index'))
            ->assertForbidden();

        $this
            ->get(FbActivityResource::getUrl('view', ['record' => Activity::first()]))
            ->assertForbidden();
    });
});

describe('as authorized user', function () {
    beforeEach(function () {
        Gate::before(fn() => true);

        $this->actingAs(User::factory()->create());
    });

    it('can render index page', function () {
        $this
            ->get(FbActivityResource::getUrl('index'))
            ->assertSuccessful();
    });

    it('can show empty table', function () {
        $this
            ->livewire(ListActivity::class)
            ->assertCanSeeTableRecords([])
            ->assertCountTableRecords(0);
    });

    it('can show data in table', function () {
        $podcast = Podcast::factory()->create();

        $count = 6;

        for ($i = 1; $i < $count; $i++) {
            $podcast->update(['text' => fake()->sentence()]);
        }

        $this
            ->livewire(ListActivity::class)
            ->assertCanSeeTableRecords($podcast->activities)
            ->assertCountTableRecords($count);
    });

    it('can view by url', function () {
        $activity = Podcast::factory()->create()->activities->first();

        $this
            ->get(FbActivityResource::getUrl('view', [
                'record' => $activity->getRouteKey(),
            ]))
            ->assertSuccessful();
    });

    it('can view data', function () {
        $activity = Podcast::factory()->create()->activities->first();

        $this
            ->livewire(ViewActivity::class, [
                'record' => $activity->getRouteKey(),
            ])
            ->assertSeeText(FbActivity::getSubject($activity, $activity['subject_type']))
            ->assertSeeText($activity->description)
            ->assertSeeText(ucwords($activity->log_name))
            ->assertSeeText(ucwords($activity->event));
        // ->assertSeeText(FbPersian::jDateTime(null, $activity->created_at));
    });

    it('can export activities and verify downloaded csv file', function () {
        Activity::truncate();

        $count = 6;
        Podcast::factory($count)->create();

        $this
            ->actingAs(User::factory()->create())
            ->livewire(ListActivity::class)
            ->callAction('export-activities');

        $export = Export::query()->latest()->first();

        expect($export)
            ->not
            ->toBeNull()
            ->processed_rows
            ->toBe($count)
            ->successful_rows
            ->toBe($count)
            ->completed_at
            ->not
            ->toBeNull();

        $disk  = $export->getFileDisk();
        $files = $export->getFileDisk()->files($export->getFileDirectory());

        $headersFile = collect($files)->first(fn($file) => str_ends_with($file, 'headers.csv'));
        expect($headersFile)->not->toBeNull('The headers.csv file was not generated.');

        $rawHeaders      = str_replace("\u{FEFF}", '', $disk->get($headersFile));
        $expectedHeaders = collect(ActivityExporter::getColumns())->map(fn($col) => $col->getLabel())->all();
        expect(str_getcsv($rawHeaders))->toContain(...$expectedHeaders);

        $dataFiles = collect($files)->reject(fn($file) => str_ends_with($file, 'headers.csv'));
        expect($dataFiles)->not->toBeEmpty('No data files were generated.');

        $dataContent = '';
        foreach ($dataFiles as $dataFile) {
            $dataContent .= $disk->get($dataFile);
        }
        $propertyRows = collect(str_getcsv(str_replace("\u{FEFF}", '', $dataContent)))
            ->filter(fn($value) => is_string($value) && str_starts_with($value, '{"attributes"'))
            ->map(fn($value) => json_decode($value, true));

        $podcasts = Podcast::all();
        foreach ($podcasts as $podcast) {
            $found = $propertyRows->contains(
                fn($row) => ($row['attributes']['id'] ?? null) === $podcast->id &&
                    ($row['attributes']['text'] ?? null) === $podcast->text
            );

            expect($found)->toBeTrue("Podcast #{$podcast->id} not found in exported CSV.");
        }

        // $this
        //     ->get(route(
        //         'filament.exports.download',
        //         ['export' => $export, 'format' => 'csv'],
        //         absolute: false
        //     ))
        //     ->assertDownload()
        // ->tap(function ($response) {
        //     $content = $response->streamedContent();

        //     foreach (collect(ActivityExporter::getColumns())->map(fn($column) => $column->getLabel()) as $label) {
        //         expect($content)
        //             ->toContain($label);
        //     };

        //     foreach (Podcast::all() as $podcast) {
        //         expect($content)
        //             ->toContain($podcast->id)
        //             ->toContain($podcast->text);
        //     }
        // });
    });
});
