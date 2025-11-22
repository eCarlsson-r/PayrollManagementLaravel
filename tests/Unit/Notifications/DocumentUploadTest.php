<?php

namespace Tests\Unit\Notifications;

use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Employee;
use App\Models\Document;
use App\Notifications\DocumentUpload;

class DocumentUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_to_database_payload()
    {
        $employee = Employee::create([
            'id' => 'SF900002',
            'first_name' => 'Doc',
            'last_name' => 'Tester',
            'position' => 'Employee',
            'dob' => '1990-01-02',
            'email' => 'docunit@example.test',
            'contact' => '000',
            'address' => 'addr',
            'pay_method' => 'cash'
        ]);

        $document = Document::create([
            'employee_id' => $employee->id,
            'manager' => 'MG900002',
            'subject' => 'Time Card',
            'file_name' => 'dut.pdf',
            'file_path' => 'documents/dut.pdf',
            'file' => '/storage/documents/dut.pdf',
            'verified' => 'U'
        ]);

        $notif = new DocumentUpload($document);
        $payload = $notif->toBroadcast($employee);

        $this->assertIsArray($payload);
        $this->assertEquals($document->id, $payload['id']);
        $this->assertStringContainsString($employee->first_name, $payload['employee_name']);
        $this->assertEquals('Time Card', $payload['title']);
    }
}
