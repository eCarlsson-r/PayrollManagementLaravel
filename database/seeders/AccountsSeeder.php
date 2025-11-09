<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Account;
use App\Models\Employee;

class AccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Account::create([
            'email' => 'admin@payroll.com',
            'password' => Hash::make('112233'),
            'type' => 'Admin',
            'employee_id' => Employee::create([
                'id'=>'admin', 'first_name' => 'Admin', 'last_name' => '', 
                'position'=>'', 'dob'=>'1988-03-20', 'email' => 'admin@payroll.com',
                'contact'=>'082349473628', 'address' => '', 'qualification' => '', 'manager' => '', 
                'career' => '', 'pay_method' => 'cash', 'bank' => '', 'bank_account' => ''
            ])->id
        ]);
    }
}
