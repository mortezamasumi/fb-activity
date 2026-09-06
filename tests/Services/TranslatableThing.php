<?php

namespace Mortezamasumi\FbActivity\Tests\Services;

use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Mortezamasumi\FbActivity\Contracts\HasActivityTitle;
use Spatie\Activitylog\Models\Activity;
use Spatie\Translatable\HasTranslations;

#[UseFactory(TranslatableThingFactory::class)]
class TranslatableThing extends Model implements HasActivityTitle
{
    use HasFactory;
    use HasTranslations;

    protected $guarded = [];

    public array $translatable = ['name'];

    public function activityTitle(Activity $activity): ?string
    {
        return $this->getAttribute('name');
    }
}
