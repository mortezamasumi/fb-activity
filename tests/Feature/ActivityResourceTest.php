<?php

use Illuminate\Support\Facades\Gate;
use Mortezamasumi\FbActivity\Facades\FbActivity;
use Mortezamasumi\FbActivity\Resources\FbActivityResource;
use Mortezamasumi\FbActivity\Resources\Pages\ListActivity;
use Mortezamasumi\FbActivity\Resources\Pages\ViewActivity;
use Mortezamasumi\FbActivity\Tests\Services\Podcast;
use Mortezamasumi\FbActivity\Tests\Services\User;
use Mortezamasumi\FbEssentials\Facades\FbPersian;

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

it('can retrieve data', function () {
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
