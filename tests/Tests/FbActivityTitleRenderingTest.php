<?php

use Filament\Facades\Filament;
use Illuminate\Support\Facades\Gate;
use Mortezamasumi\FbActivity\Facades\FbActivity;
use Mortezamasumi\FbActivity\Resources\Exports\ActivityExporter;
use Mortezamasumi\FbActivity\Resources\FbActivityResource;
use Mortezamasumi\FbActivity\Resources\Pages\ListActivity;
use Mortezamasumi\FbActivity\Tests\Services\Podcast;
use Mortezamasumi\FbActivity\Tests\Services\User;
use Spatie\Activitylog\Models\Activity;

use function Pest\Livewire\livewire;

beforeEach(function () {
    Filament::setCurrentPanel('fb-activity');
    $this->actingAs(User::factory()->create());
    Gate::before(fn () => true);
});

it('shows the resolved subject title on the list page instead of the class name', function () {
    config()->set('fb-activity.subject.titles', [Podcast::class => 'text']);

    $activity = Podcast::create(['text' => 'my favourite podcast'])->activities->first();

    livewire(ListActivity::class)
        ->assertTableColumnFormattedStateSet('subject', 'my favourite podcast', $activity);
});

it('shows the model label as the subject description on the list page', function () {
    $activity = Podcast::create(['text' => 'with label'])->activities->first();

    livewire(ListActivity::class)
        ->assertTableColumnExists('subject')
        ->assertSuccessful();
});

it('falls back to label and id when nothing resolves', function () {
    config()->set('fb-activity.subject.use_filament_record_title', false);

    $activity = Podcast::create(['text' => 'fallback'])->activities->first();

    livewire(ListActivity::class)
        ->assertTableColumnFormattedStateSet('subject', 'Podcast #'.$activity->getAttribute('subject_id'), $activity);
});

it('shows the resolved title on the view page in subject_name format', function () {
    config()->set('fb-activity.subject.titles', [Podcast::class => 'text']);

    $activity = Podcast::create(['text' => 'viewable podcast'])->activities->first();

    $this
        ->get(FbActivityResource::getUrl('view', ['record' => $activity]))
        ->assertSuccessful()
        ->assertSee('Podcast')
        ->assertSee('viewable podcast');
});

it('renders a title for a deleted subject on the list page', function () {
    config()->set('fb-activity.subject.use_filament_record_title', false);
    config()->set('fb-activity.subject.attribute_cascade', ['text']);

    $podcast = Podcast::create(['text' => 'deleted but remembered']);
    $podcast->delete();

    $activity = $podcast->activities()->latest('id')->first();

    livewire(ListActivity::class)
        ->assertTableColumnFormattedStateSet('subject', 'deleted but remembered', $activity);
});

it('renders the causer title on the view page', function () {
    $user = User::factory()->create(['name' => 'Causing User']);
    $activity = Activity::create([
        'log_name' => 'default',
        'description' => 'test',
        'subject_type' => Podcast::class,
        'subject_id' => Podcast::create(['text' => 'x'])->id,
        'causer_type' => User::class,
        'causer_id' => $user->id,
    ]);

    $this
        ->get(FbActivityResource::getUrl('view', ['record' => $activity]))
        ->assertSuccessful()
        ->assertSee('Causing User');
});

it('exports the resolved subject and causer titles', function () {
    config()->set('fb-activity.subject.titles', [Podcast::class => 'text']);

    $user = User::factory()->create(['name' => 'Exporting User']);
    $activity = Activity::create([
        'log_name' => 'default',
        'description' => 'test',
        'subject_type' => Podcast::class,
        'subject_id' => Podcast::create(['text' => 'exported podcast'])->id,
        'causer_type' => User::class,
        'causer_id' => $user->id,
    ]);
    $activity->load(['subject', 'causer']);

    $columns = ActivityExporter::getColumns();

    $subjectColumn = collect($columns)->first(fn ($c) => $c->getName() === 'subject');
    $causerColumn = collect($columns)->first(fn ($c) => $c->getName() === 'causer');

    // ExportColumn format callbacks receive the record via the column closure;
    // evaluate them directly with the loaded activity.
    $subjectState = (fn () => $subjectColumn->evaluate(
        $subjectColumn->formatStateUsing ?? fn () => null,
        ['record' => $activity, 'state' => $activity->getAttribute('subject')],
    ))->call($subjectColumn);

    expect($subjectState)->toBe('exported podcast');
});

it('keeps getSubject backwards compatible without a fresh query', function () {
    config()->set('fb-activity.subject.titles', [Podcast::class => 'text']);

    $activity = Podcast::create(['text' => 'bc podcast'])->activities->first();

    expect(FbActivity::getSubject($activity, $activity->getAttribute('subject_type')))
        ->toBe(__('fb-activity::fb-activity.infolist.subject_name', [
            'a' => 'Podcast',
            'b' => 'bc podcast',
        ]));
});
