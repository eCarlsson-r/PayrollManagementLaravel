<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Account;

class AccountTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_employee(): void
    {
        $this->assertEquals(Account::factory()->create_employee()['type'], 'Employee');
    }

    public function test_manager(): void
    {
        $this->assertEquals(Account::factory()->create_manager()['type'], 'Manager');
    }
}
