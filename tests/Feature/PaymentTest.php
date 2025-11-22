<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Account;
use App\Models\Employee;
use App\Models\Payment;
use App\Models\Scheme;
use App\Models\Document;
use App\Models\Timecard;
use App\Models\Receipt;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_shows_admin_payments()
    {
        // create an employee and payment
        $emp = Employee::create(['id' => 'SF500001', 'first_name' => 'Pay', 'last_name' => 'ee', 'position' => 'Employee', 'dob' => '1990-01-01', 'email' => 'payee@example.test', 'contact' => '111', 'address' => 'addr', 'pay_method' => 'cash']);
        Payment::create(['employee_id' => $emp->id, 'date' => '2025-11-20', 'amount' => 123000000, 'method' => 'cash']);

        // admin account
        $adminEmp = Employee::create(['id' => 'AD500001', 'first_name' => 'Admin', 'last_name' => 'One', 'position' => 'Admin', 'dob' => '1980-01-01', 'email' => 'admin@example.test', 'contact' => '000', 'address' => 'addr', 'pay_method' => 'cash']);
        $admin = Account::create(['employee_id' => $adminEmp->id, 'email' => 'adminacct@example.test', 'password' => bcrypt('password'), 'type' => 'Admin']);

        $this->actingAs($admin);

        $response = $this->get('/payment');

        $response->assertStatus(200);
        $response->assertSee('123000000');
        $response->assertSee($emp->id);
    }

    public function test_create_calculates_hourly_amount()
    {
        // employee with hourly scheme
        $employee = Employee::create(['id' => 'SF500002', 'first_name' => 'Hourly', 'last_name' => 'User', 'position' => 'Employee', 'dob' => '1990-01-02', 'email' => 'hourly@example.test', 'contact' => '111', 'address' => 'addr', 'pay_method' => 'cash']);
        $account = Account::create(['employee_id' => $employee->id, 'email' => 'hourlyacct@example.test', 'password' => bcrypt('password'), 'type' => 'Admin']);

        // scheme: HOURLY base_amount 10
        Scheme::create(['employee_id' => $employee->id, 'scheme' => 'HOURLY', 'base_amount' => 10]);

        // create document and timecard in current week for 09:00-17:00
        $doc = Document::create(['employee_id' => $employee->id, 'manager' => '', 'subject' => 'Time Card', 'file_name' => 't.jpg', 'file_path' => '/storage/t.jpg', 'file' => '/storage/t.jpg', 'verified' => 'U']);

        $monday = date('Y-m-d', strtotime('monday this week'));
        // place timecard on monday with 09:00 to 17:00
        Timecard::create(['document_id' => $doc->id, 'date' => $monday, 'time_start' => '09:00:00', 'time_end' => '17:00:00']);

        $this->actingAs($account);

        $response = $this->get('/payment/create');

        $response->assertStatus(200);
        // expected amount = 8 hours * base_amount 10 = 80
        $response->assertSee($employee->id);
        $response->assertSee('80');
    }

    public function test_store_creates_payment_records()
    {
        $employee = Employee::create(['id' => 'SF500003', 'first_name' => 'Store', 'last_name' => 'User', 'position' => 'Employee', 'dob' => '1990-01-03', 'email' => 'store@example.test', 'contact' => '111', 'address' => 'addr', 'pay_method' => 'bank']);
        $account = Account::create(['employee_id' => $employee->id, 'email' => 'storeacct@example.test', 'password' => bcrypt('password'), 'type' => 'Admin']);

        $this->actingAs($account);

        $payload = [
            'payments' => [
                ['employee_id' => $employee->id, 'amount' => 5600000]
            ]
        ];

        $response = $this->post('/payment', $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('payments', ['employee_id' => $employee->id, 'amount' => 5600000, 'method' => 'bank']);
    }

    public function test_create_includes_commission_scheme_non_month_end()
    {
        // employee with commission scheme
        $employee = Employee::create(['id' => 'SF500004', 'first_name' => 'Comm', 'last_name' => 'User', 'position' => 'Employee', 'dob' => '1990-01-04', 'email' => 'comm@example.test', 'contact' => '111', 'address' => 'addr', 'pay_method' => 'cash']);
        $account = Account::create(['employee_id' => $employee->id, 'email' => 'commacct@example.test', 'password' => bcrypt('password'), 'type' => 'Admin']);

        // scheme: COMMISSION
        Scheme::create(['employee_id' => $employee->id, 'scheme' => 'COMMISSION', 'base_amount' => 100, 'base_commission_rate' => 5]);

        // create a sales receipt document and receipt record
        $doc = Document::create(['employee_id' => $employee->id, 'manager' => '', 'subject' => 'Sales Receipt', 'file_name' => 's.jpg', 'file_path' => '/storage/s.jpg', 'file' => '/storage/s.jpg', 'verified' => 'U']);
        Receipt::create(['document_id' => $doc->id, 'date' => date('Y-m-d'), 'amount' => 200]);

        $this->actingAs($account);

        $response = $this->get('/payment/create');

        $response->assertStatus(200);
        // view should include the employee id and commission input
        $response->assertSee($employee->id);
        $response->assertSee('commision');
    }
}
