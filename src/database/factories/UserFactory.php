<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    // public function definition()
    // {
    //     static $emails = [
    //         'test1@example.com',
    //         'test2@example.com',
    //         'test3@example.com',
    //         'test4@example.com',
    //         'test@example.com',
    //     ];

    //     return [
    //         'name' => $this->faker->name(),
    //         'email' => array_shift($emails),
    //         'email_verified_at' => now(),
    //         'password' => bcrypt('pass12345'),
    //         'role' => 'user',
    //         'remember_token' => Str::random(10),
    //     ];
    // }

    public function definition()
    {
        static $emails = [
            'test1@example.com',
            'test2@example.com',
            'test3@example.com',
            'test4@example.com',
            'test5@example.com',
        ];

        static $usedIndexes = [];

        $availableIndexes = array_diff(array_keys($emails), $usedIndexes);
        if (empty($availableIndexes)) {
            static $fallbackCount = 1;
            $email = "test-fallback{$fallbackCount}@example.com";
            $fallbackCount++;
        } else {
            $index = $availableIndexes[array_rand($availableIndexes)];
            $email = $emails[$index];
            $usedIndexes[] = $index;
        }

        return [
            'name' => $this->faker->name(),
            'email' => $email,
            'email_verified_at' => now(),
            'password' => bcrypt('pass12345'),
            'role' => 'user',
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}
