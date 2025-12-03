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
        $admin = Employee::create([
            'id'=>'admin', 'first_name' => 'Admin', 'last_name' => '', 
            'position'=>'', 'dob'=>'1988-03-20', 'email' => 'admin@payroll.com',
            'contact'=>'082349473628', 'address' => '', 'manager' => '', 
            'pay_method' => 'cash', 'bank' => '', 'bank_account' => ''
        ]);

        Account::create([
            'email' => 'admin@payroll.com',
            'password' => Hash::make('112233'),
            'type' => 'Admin',
            'employee_id' => $admin->id
        ]);

        Career::create([
            'employee_id' => $admin->id,
            'position' => 'Employee',
            'start_date' => '2025-01-01',
            'end_date' => '2025-06-30',
            'description' => ''
        ]);

        Career::create([
            'employee_id' => $admin->id,
            'position' => 'Manager',
            'start_date' => '2025-07-01',
            'end_date' => '2025-12-31',
            'description' => ''
        ]);

        $manager = Employee::create([
            'first_name' => 'Manager', 'last_name' => '', 
            'position'=>'', 'dob'=>'1994-06-23', 'email' => 'manager@payroll.com',
            'contact'=>'082779475368', 'address' => '', 'manager' => $admin->id, 
            'pay_method' => 'cash', 'bank' => '', 'bank_account' => ''
        ]);

        Account::create([
            'email' => 'manager@payroll.com',
            'password' => Hash::make('TestingManager'),
            'type' => 'Manager',
            'employee_id' => $manager->id
        ]);

        Career::create([
            'employee_id' => $manager->id,
            'position' => 'Employee',
            'start_date' => '2025-01-01',
            'end_date' => '2025-06-30',
            'description' => ''
        ]);

        Career::create([
            'employee_id' => $manager->id,
            'position' => 'Manager',
            'start_date' => '2025-07-01',
            'end_date' => '2025-12-31',
            'description' => ''
        ]);

        $employee = Employee::create([
            'first_name' => 'Employee', 'last_name' => '', 
            'position'=>'', 'dob'=>'2003-05-18', 'email' => 'employee@payroll.com',
            'contact'=>'089976465628', 'address' => '', 'manager' => $manager->id, 
            'pay_method' => 'cash', 'bank' => '', 'bank_account' => ''
        ]);

        Account::create([
            'email' => 'employee@payroll.com',
            'password' => Hash::make('TestingStaff'),
            'type' => 'Employee',
            'employee_id' => $employee->id
        ]);

        Career::create([
            'employee_id' => $employee->id,
            'position' => 'Employee',
            'start_date' => '2025-07-01',
            'end_date' => '2025-12-31',
            'description' => ''
        ]);
    }
}
