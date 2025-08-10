<?php

namespace Mortezamasumi\FbActivity\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static ?string getSubjectName(?Model $record, ?string $state)
 * @method static ?string getSubject(?Model $record, ?string $state)
 *
 * @see \Mortezamasumi\FbActivity\FbActivity
 */
class FbActivity extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Mortezamasumi\FbActivity\FbActivity::class;
    }
}
