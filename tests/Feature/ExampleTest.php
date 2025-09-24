<?php

use Filament\Facades\Filament;
use function Pest\Laravel\get;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

use Mortezamasumi\FbActivity\Resources\FbActivityResource;

// beforeEach(function () {
//     Gate::before(fn () => true);
// });

it('can test', function () {
    expect(true)->toBeTrue();
});

it('can', function () {
    // // Gate::allows('all');
    Gate::before(fn () => true);

    $user = new User;
    $user->id = 999;

    // Auth::setUser($user);

    // dd(Auth::check());

    $this
        ->actingAs($user)
        ->get(Filament::getUrl())
        // ->get(FbActivityResource::getUrl('index'))
        ->assertSuccessful();
})->skip();
