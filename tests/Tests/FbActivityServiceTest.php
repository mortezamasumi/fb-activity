<?php

use Illuminate\Database\Eloquent\Model;
use Mortezamasumi\FbActivity\Facades\FbActivity;
use Mortezamasumi\FbActivity\Tests\Services\Podcast;
use Spatie\Activitylog\Models\Activity;

it('returns a dash when the subject type is empty', function () {
    expect(FbActivity::getSubjectName(null, ''))
        ->toBe('-');
});

it('returns a humanized short class name for the subject type', function () {
    expect(FbActivity::getSubjectName(null, Podcast::class))
        ->toBe('Podcast');
});

it('returns null when the activity is not an Activity model', function () {
    expect(FbActivity::getSubject(null, null))
        ->toBeNull();
});

it('resolves the subject display name from the resolver (legacy getSubject)', function () {
    config()->set('fb-activity.subject.titles', [Podcast::class => 'text']);

    $podcast = Podcast::factory()->create();

    $activity = $podcast->activities->first();

    expect(FbActivity::getSubject($activity, Podcast::class))
        ->toContain($podcast->text);
});

it('falls back to the label and id for unknown subject classes (legacy getSubject)', function () {
    $activity = Activity::create([
        'log_name' => 'default',
        'description' => 'test',
        'subject_type' => 'App\Models\MissingModel',
        'subject_id' => 42,
    ]);

    expect(FbActivity::getSubject($activity, 'App\Models\MissingModel'))
        ->toContain('42');
});

it('returns the label when the subject cannot be derived (legacy getSubject)', function () {
    $activity = Activity::create([
        'log_name' => 'default',
        'description' => 'test',
        'subject_type' => 'App\Models\MissingModel',
        'subject_id' => 42,
    ]);

    // Label headline "Missing Model" + resolver fallback "Missing Model #42".
    expect(FbActivity::getSubject($activity, 'App\Models\MissingModel'))
        ->toBe('Missing Model ↣ Missing Model #42');
});
