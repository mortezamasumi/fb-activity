<?php

use Filament\Facades\Filament;
use Illuminate\Support\Facades\Gate;
use Mortezamasumi\FbActivity\Resources\Pages\ListActivity;
use Mortezamasumi\FbActivity\Tests\Services\Podcast;
use Mortezamasumi\FbActivity\Tests\Services\User;
use Spatie\Activitylog\Models\Activity;

use function Pest\Livewire\livewire;

it('renders list-page created_at through the timezone formatter', function () {
    Filament::setCurrentPanel('fb-activity');
    $this->actingAs(User::factory()->create());
    Gate::before(fn () => true);

    config()->set('fb-activity.timezone.storage', 'UTC');
    config()->set('fb-activity.timezone.display', 'Asia/Tehran');

    Podcast::create(['text' => 'hello']);

    $activity = Activity::query()->latest('id')->first();

    // Force a known UTC wall-clock string, bypassing model events.
    $activity->forceFill(['created_at' => '2026-09-06 05:07:26'])->saveQuietly();
    $activity->refresh();

    // The table default format is fb-essentials time_full ('l j F Y  H:i').
    $expected = 'Sunday 6 September 2026  08:37';

    livewire(ListActivity::class)
        ->assertTableColumnFormattedStateSet('created_at', $expected, $activity);
});

it('keeps list-page created_at rendering identical to the fb-essentials macro when unconfigured', function () {
    Filament::setCurrentPanel('fb-activity');
    $this->actingAs(User::factory()->create());
    Gate::before(fn () => true);

    Podcast::create(['text' => 'world']);

    $activity = Activity::query()->latest('id')->first();
    $activity->forceFill(['created_at' => '2026-09-06 05:07:26'])->saveQuietly();
    $activity->refresh();

    // App tz is UTC in tests and no reinterpretation is configured, so the wall
    // time is rendered unchanged through FbPersian::jDateTime's default format
    // (fb-essentials time_full: 'l j F Y  H:i').
    livewire(ListActivity::class)
        ->assertTableColumnFormattedStateSet('created_at', 'Sunday 6 September 2026  05:07', $activity);
});

it('passes filter bounds through toStorageDate so display dates find utc-stored rows', function () {
    Filament::setCurrentPanel('fb-activity');
    $this->actingAs(User::factory()->create());
    Gate::before(fn () => true);

    config()->set('fb-activity.timezone.storage', 'UTC');
    config()->set('fb-activity.timezone.display', 'Asia/Tehran');

    Podcast::create(['text' => 'filtered']);

    $activity = Activity::query()->latest('id')->first();
    // Stored UTC wall 2026-09-05 21:00 == 2026-09-06 00:30 Tehran: a Tehran-day
    // filter for the 6th must match it through the storage-day conversion.
    $activity->forceFill(['created_at' => '2026-09-05 21:00:00'])->saveQuietly();
    $activity->refresh();

    livewire(ListActivity::class)
        ->filterTable('created_at', [
            'created_from' => '2026-09-06',
            'created_until' => '2026-09-06',
        ])
        ->assertCanSeeTableRecords([$activity]);
});
