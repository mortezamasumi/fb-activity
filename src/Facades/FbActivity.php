<?php

namespace Mortezamasumi\FbActivity\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Mortezamasumi\FbActivity\FbActivity
 */
class FbActivity extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Mortezamasumi\FbActivity\FbActivity::class;
    }
}
