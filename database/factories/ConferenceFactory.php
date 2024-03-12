<?php

namespace Database\Factories;

use App\Enums\Region;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Conference;
use function date;
use function now;

class ConferenceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Conference::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $start_date = now()->addMonths(8);
        $end_date = now()->addMonths(8)->addDays(2);
        return [
            'name' => $this->faker->sentence(),
            'description' => $this->faker->text(),
            'start_date' => $start_date,
            'end_date' => $end_date,
            'status' => $this->faker->randomElement(['Draft', 'Published', 'Archived']),
            'region' => $this->faker->randomElement(Region::class),
            'venue_id' => null,
        ];
    }
}
