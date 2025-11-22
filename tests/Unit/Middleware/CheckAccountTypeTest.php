<?php

namespace Tests\Unit\Middleware;

use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Middleware\CheckAccountType;
use App\Models\Account;
use App\Models\Employee;
use Illuminate\Http\Request;

class CheckAccountTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_handles_guest_redirects_to_login()
    {
        $middleware = new CheckAccountType();

        $response = $middleware->handle(Request::capture(), function () {} , 'Employee');

        // guest should be redirected (302) to '/'
        $this->assertTrue($response->isRedirection());
        $this->assertStringContainsString('/', $response->headers->get('location'));
    }

    public function test_handles_authorized_user_allows_request()
    {
        $employee = Employee::create([
            'id' => 'SF900003',
            'first_name' => 'Mid',
            'last_name' => 'User',
            'position' => 'Employee',
            'dob' => '1990-01-03',
            'email' => 'miduser@example.test',
            'contact' => '000',
            'address' => 'addr',
            'pay_method' => 'cash'
        ]);

        $account = Account::create(['employee_id' => $employee->id, 'email' => 'midacct@example.test', 'password' => bcrypt('password'), 'type' => 'Employee']);

        $this->actingAs($account);

        $middleware = new CheckAccountType();

        $request = Request::capture();
        $called = false;
        $result = $middleware->handle($request, function ($req) use (&$called) { $called = true; return response('ok'); }, 'Employee');

        $this->assertTrue($called);
        $this->assertEquals('ok', $result->getContent());
     }
}
