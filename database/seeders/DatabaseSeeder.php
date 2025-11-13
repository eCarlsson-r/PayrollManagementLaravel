<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Account;
use App\Models\Employee;
use App\Models\Career;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $employee = Employee::create([
            'id'=>'admin', 'first_name' => 'Admin', 'last_name' => '', 
            'position'=>'', 'dob'=>'1988-03-20', 'email' => 'admin@payroll.com',
            'contact'=>'082349473628', 'address' => '', 'manager' => '', 
            'pay_method' => 'cash', 'bank' => '', 'bank_account' => ''
        ]);

        Account::create([
            'email' => 'admin@payroll.com',
            'password' => Hash::make('112233'),
            'type' => 'Admin',
            'employee_id' => $employee->id
        ]);

        Career::create([
            'employee_id' => $employee->id,
            'position' => 'Employee',
            'start_date' => '2025-01-01',
            'end_date' => '2025-06-30',
            'description' => ''
        ]);

        Career::create([
            'employee_id' => $employee->id,
            'position' => 'Manager',
            'start_date' => '2025-07-01',
            'end_date' => '2025-12-31',
            'description' => ''
        ]);
    }
}
