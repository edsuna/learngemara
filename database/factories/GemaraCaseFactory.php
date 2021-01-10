<?php

namespace Database\Factories;

use App\Models\GemaraCase;
use App\Models\User;
use App\Models\Tractate;
use Illuminate\Database\Eloquent\Factories\Factory;

class GemaraCaseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = GemaraCase::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $tractate = Tractate::all()->random();
        $data = [
            'user_id' => User::all()->random()->user_id,
            'masechet' => $tractate->english_name,
            'daf' => rand(1, $tractate->pages-1) . 'a',
            'gemara_text' => $this->faker->realText(35),
            'title' => $this->faker->realText(25),
            'din_type' => GemaraCase::dinTypes[rand(0, 5)],
            'act' => $this->faker->word,
            'public' => rand(0,1),
        ];

        foreach (GemaraCase::inputConditions as $condition) {
            $data[$condition] = $this->faker->word;
            $data[$condition . '_nr'] = rand(0, 1);
        }

        return $data;
    }
}
