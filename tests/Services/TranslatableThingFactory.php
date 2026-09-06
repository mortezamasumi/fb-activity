<?php

namespace Mortezamasumi\FbActivity\Tests\Services;

use Illuminate\Database\Eloquent\Factories\Factory;

class TranslatableThingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => ['en' => $this->faker->words(2, true), 'fa' => 'چیز ترجمه‌پذیر'],
        ];
    }
}
