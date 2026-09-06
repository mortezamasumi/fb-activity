<?php

namespace Mortezamasumi\FbActivity\Tests\Services\Pages;

use Filament\Resources\Pages\ManageRecords;
use Mortezamasumi\FbActivity\Tests\Services\PodcastResource;

class ManagePodcasts extends ManageRecords
{
    protected static string $resource = PodcastResource::class;
}
