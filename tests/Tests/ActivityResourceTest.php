<?php

use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Mortezamasumi\FbActivity\Facades\FbActivity;
use Mortezamasumi\FbActivity\Resources\Exports\ActivityExporter;
use Mortezamasumi\FbActivity\Resources\Pages\ListActivity;
use Mortezamasumi\FbActivity\Resources\Pages\ViewActivity;
use Mortezamasumi\FbActivity\Resources\FbActivityResource;
use Mortezamasumi\FbActivity\Tests\Services\Podcast;
use Mortezamasumi\FbActivity\Tests\Services\User;
use Mortezamasumi\FbEssentials\Facades\FbPersian;
use Spatie\Activitylog\Models\Activity;

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
        Gate::before(fn () => true);

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
            ->assertSeeText(ucwords($activity->event))
            ->assertSeeText(FbPersian::jDateTime(null, $activity->created_at));
    });

    it('can export activities and verify downloaded csv file', function () {
        Activity::truncate();

        $count = 6;

        Podcast::factory($count)->create();

        $this
            ->actingAs(User::factory()->create())
            ->livewire(ListActivity::class)
            ->callAction('export-activities')
            ->assertNotified();

        $export = Export::latest()->first();

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

        $this
            ->get(route(
                'filament.exports.download',
                ['export' => $export, 'format' => 'csv'],
                absolute: false
            ))
            ->assertDownload()
            ->tap(function ($response) {
                $content = $response->streamedContent();

                foreach (collect(ActivityExporter::getColumns())->map(fn ($column) => $column->getLabel()) as $label) {
                    expect($content)
                        ->toContain($label);
                };

                foreach (Podcast::all() as $podcast) {
                    expect($content)
                        ->toContain($podcast->id)
                        ->toContain($podcast->text);
                }
            });
    });
});
