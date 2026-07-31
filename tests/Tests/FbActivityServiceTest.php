<?php

use Illuminate\Database\Eloquent\Model;
use Mortezamasumi\FbActivity\Facades\FbActivity;
use Mortezamasumi\FbActivity\Tests\Services\Podcast;

it('returns a dash when the subject type is empty', function () {
    expect(FbActivity::getSubjectName(null, ''))
        ->toBe('-');
});

it('returns a humanized short class name for the subject type', function () {
    expect(FbActivity::getSubjectName(null, Podcast::class))
        ->toBe('Podcast');
});

it('returns a dash when the subject state is empty', function () {
    expect(FbActivity::getSubject(null, null))
        ->toBe('-');
});

it('resolves the subject display name from its name attribute', function () {
    $podcast = Podcast::factory()->create();

    $activity = $podcast->activities->first();

    expect(FbActivity::getSubject($activity, Podcast::class))
        ->toContain($podcast->text);
});

it('falls back to the subject id for unknown subject classes', function () {
    $record = new class extends Model
    {
        protected $fillable = ['subject_id'];
    };
    $record->subject_id = 42;

    expect(FbActivity::getSubject($record, 'App\\Models\\MissingModel'))
        ->toContain('42');
});

it('returns null when the subject name cannot be derived', function () {
    $record = new class extends Model
    {
        protected $fillable = ['subject_id'];
    };
    $record->subject_id = 42;

    expect(FbActivity::getSubject($record, ''))
        ->toBe('-');
});
