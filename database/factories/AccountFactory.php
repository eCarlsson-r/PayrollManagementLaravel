<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Employee;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        /*return [
            'email' => fake()->email(),
            'password' => static::$password ??= Hash::make('password'),
            'type' => "Employee"
        ];*/
    }

    public function create_manager(): array {
        $empID = Employee::where('position', 'MG')->count();
        $email = fake()->email();
        return [
            'email' => $email,
            'password' => Hash::make(Str::random(10)),
            'type' => "Manager",
            'employee_id' => Employee::factory()->create([
                'id' => sprintf("MG%06d", $empID+1), 
                'email' => $email,
                'position' => "Manager"
            ]),
        ];
    }

    public function create_employee(): array {
        $empID = Employee::where('position', 'SF')->count();
        $email = fake()->email();
        return [
            'email' => $email,
            'password' => Hash::make(Str::random(10)),
            'type' => "Employee",
            'employee_id' => Employee::factory()->create([
                'id' => sprintf("SF%06d", $empID+1), 
                'email' => $email,
                'position' => "Employee"
            ]),
        ];
    }
}
