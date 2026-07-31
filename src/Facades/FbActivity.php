<?php

namespace Mortezamasumi\FbActivity\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static ?string getSubjectName(?\Illuminate\Database\Eloquent\Model $record, ?string $state)
 * @method static ?string getSubject(?\Illuminate\Database\Eloquent\Model $record, ?string $state)
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
