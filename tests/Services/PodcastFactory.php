<?php

namespace Mortezamasumi\FbActivity\Tests\Services;

use Illuminate\Database\Eloquent\Factories\Factory;

class PodcastFactory extends Factory
{
    public function definition(): array
    {
        return [
            'text' => fake()->sentence(),
        ];
    }
}
