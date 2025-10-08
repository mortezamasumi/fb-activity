<?php

use Illuminate\Support\Facades\Gate;
use Mortezamasumi\FbActivity\Facades\FbActivity;
use Mortezamasumi\FbActivity\Resources\Pages\ListActivity;
use Mortezamasumi\FbActivity\Resources\Pages\ViewActivity;
use Mortezamasumi\FbActivity\Resources\FbActivityResource;
use Mortezamasumi\FbActivity\Tests\Services\Podcast;
use Mortezamasumi\FbActivity\Tests\Services\User;

it('can render index page', function () {
    Gate::before(fn () => true);

    $this
        ->actingAs(User::factory()->create())
        ->get(FbActivityResource::getUrl('index'))
        ->assertSuccessful();
});

it('can show empty table', function () {
    Gate::before(fn () => true);

    $this
        ->actingAs(User::factory()->create())
        ->livewire(ListActivity::class)
        ->assertCanSeeTableRecords([])
        ->assertCountTableRecords(0);
});

it('can show data in table', function () {
    Gate::before(fn () => true);

    $podcast = Podcast::factory()->create();

    $count = 6;

    for ($i = 1; $i < $count; $i++) {
        $podcast->update(['text' => fake()->sentence()]);
    }

    $this
        ->actingAs(User::factory()->create())
        ->livewire(ListActivity::class)
        ->assertCanSeeTableRecords($podcast->activities)
        ->assertCountTableRecords($count);
});

it('can view by url', function () {
    Gate::before(fn () => true);

    $podcast = Podcast::factory()->create();

    $this
        ->actingAs(User::factory()->create())
        ->get(FbActivityResource::getUrl('view', [
            'record' => $podcast->activities->first(),
        ]))
        ->assertSuccessful();
});

it('can retrieve data', function () {
    Gate::before(fn () => true);

    $activity = Podcast::factory()->create()->activities->first();

    $this
        ->actingAs(User::factory()->create())
        ->livewire(ViewActivity::class, [
            'record' => $activity->getRouteKey(),
        ])
        ->assertSeeText(FbActivity::getSubject($activity, $activity['subject_type']));
});
