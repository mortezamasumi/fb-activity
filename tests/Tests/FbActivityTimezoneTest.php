<?php

use Carbon\Carbon;
use Mortezamasumi\FbActivity\Facades\FbActivity;
use Mortezamasumi\FbEssentials\Facades\FbPersian;

it('formats datetime identically to the fb-essentials macro when no timezone config is set', function () {
    $stored = '2026-09-06 05:07:26';

    expect(FbActivity::formatDateTime($stored, 'Y/m/d H:i'))
        ->toBe(FbPersian::jDateTime('Y/m/d H:i', $stored));
});

it('reinterprets stored utc wall time into the display timezone', function () {
    config()->set('fb-activity.timezone.storage', 'UTC');
    config()->set('fb-activity.timezone.display', 'Asia/Tehran');

    // App tz is UTC in tests (phpunit.xml); 05:07 UTC == 08:37 Tehran (+3:30).
    $formatted = FbActivity::formatDateTime('2026-09-06 05:07:26', 'H:i');

    expect($formatted)->toBe('08:37');
});

it('falls back to the app timezone when only storage is configured', function () {
    config()->set('fb-activity.timezone.storage', 'UTC');
    config()->set('fb-activity.timezone.display', null);

    // App tz in tests is UTC, so the wall time is unchanged.
    expect(FbActivity::formatDateTime('2026-09-06 05:07:26', 'H:i'))
        ->toBe('05:07');
});

it('round-trips a display date into the storage day', function () {
    config()->set('fb-activity.timezone.storage', 'UTC');
    config()->set('fb-activity.timezone.display', 'Asia/Tehran');

    // 2026-09-06 00:30 Tehran == 2026-09-05 21:00 UTC -> storage day is the 5th.
    expect(FbActivity::toStorageDate('2026-09-06'))->toBe('2026-09-05');
});

it('returns the date unchanged when no timezone config is set', function () {
    expect(FbActivity::toStorageDate('2026-09-06'))->toBe('2026-09-06');
});

it('returns an empty string for blank input', function () {
    expect(FbActivity::formatDateTime(null))
        ->toBe('')
        ->and(FbActivity::formatDateTime(''))
        ->toBe('');
});

it('returns null for unparseable values instead of throwing', function () {
    config()->set('fb-activity.timezone.storage', 'UTC');

    expect(FbActivity::formatDateTime('not-a-date'))->toBeNull();
});

it('handles carbon instances passed through with storage tz configured', function () {
    config()->set('fb-activity.timezone.storage', 'UTC');
    config()->set('fb-activity.timezone.display', 'Asia/Tehran');

    $instance = Carbon::createFromFormat('Y-m-d H:i:s', '2026-09-06 05:07:26', 'UTC');

    expect(FbActivity::formatDateTime($instance, 'H:i'))->toBe('08:37');
});
