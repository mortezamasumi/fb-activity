<?php

use Filament\Facades\Filament;
use Illuminate\Support\Facades\Gate;
use Mortezamasumi\FbActivity\Resources\FbActivityResource;
use Mortezamasumi\FbActivity\Resources\Schemas\FbActivityInfolist;
use Mortezamasumi\FbActivity\Tests\Services\Podcast;
use Mortezamasumi\FbActivity\Tests\Services\User;
use Spatie\Activitylog\Models\Activity;

beforeEach(function () {
    Filament::setCurrentPanel('fb-activity');
    $this->actingAs(User::factory()->create());
    Gate::before(fn () => true);
});

it('renders the changes diff section on the view page for old/attributes properties', function () {
    $podcast = Podcast::create(['text' => 'initial text']);

    $podcast->update(['text' => 'updated text']);

    $activity = $podcast->activities()->latest('id')->first();

    expect($activity->getAttribute('properties')->has('old'))
        ->toBeTrue()
        ->and($activity->getAttribute('properties')->has('attributes'))
        ->toBeTrue();

    $this
        ->get(FbActivityResource::getUrl('view', ['record' => $activity]))
        ->assertSuccessful()
        ->assertSee(__('fb-activity::fb-activity.infolist.changes'))
        ->assertSee('Text')
        ->assertSee('initial text')
        ->assertSee('updated text');
});

it('keeps old and attributes out of the flat properties list when the diff renders', function () {
    $podcast = Podcast::create(['text' => 'before']);
    $podcast->update(['text' => 'after']);

    $activity = $podcast->activities()->latest('id')->first();

    $this
        ->get(FbActivityResource::getUrl('view', ['record' => $activity]))
        ->assertSuccessful()
        ->assertSee(__('fb-activity::fb-activity.infolist.changes'));
});

it('renders the flat properties list when there is no old/attributes diff', function () {
    $activity = Activity::create([
        'log_name' => 'default',
        'description' => 'custom event',
        'subject_type' => Podcast::class,
        'subject_id' => Podcast::create(['text' => 'x'])->id,
        'properties' => ['custom_key' => 'custom_value'],
    ]);

    $this
        ->get(FbActivityResource::getUrl('view', ['record' => $activity]))
        ->assertSuccessful()
        ->assertSee('custom_key')
        ->assertSee('custom_value')
        ->assertDontSee(__('fb-activity::fb-activity.infolist.changes'));
});

it('stringifies diff values like the flat list (bools, arrays, dates)', function () {
    $stringify = new ReflectionMethod(FbActivityInfolist::class, 'stringify');

    expect($stringify->invoke(null, true))
        ->toBe('1')
        ->and($stringify->invoke(null, false))
        ->toBe('0')
        ->and($stringify->invoke(null, ['a' => 'ب']))
        ->toBe(json_encode(['a' => 'ب'], JSON_UNESCAPED_UNICODE))
        ->and($stringify->invoke(null, null))
        ->toBe('')
        ->and($stringify->invoke(null, '2026-09-06'))
        ->toContain('2026/09/06');
});
