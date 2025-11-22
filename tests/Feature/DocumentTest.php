<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use App\Models\Employee;
use App\Models\Account;
use App\Models\Document;
use App\Models\Timecard;
use App\Notifications\DocumentUpload;

class DocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_saves_document_and_notifies_manager()
    {
        Notification::fake();
        Storage::fake('public');

        // Create manager employee and account
        $manager = Employee::create(['id' => 'MG000001', 'first_name' => 'Mana', 'last_name' => 'Ger', 'position' => 'Manager', 'dob' => '1980-01-01', 'email' => 'manager_emp@example.test', 'contact' => '000', 'address' => 'addr', 'pay_method' => 'cash']);
        $managerAccount = Account::create(['employee_id' => $manager->id, 'email' => 'manager@example.test', 'password' => bcrypt('password'), 'type' => 'Manager']);

        // Create regular employee assigned to manager
        $employee = Employee::create(['id' => 'SF000001', 'first_name' => 'Em', 'last_name' => 'Ployee', 'position' => 'Employee', 'manager' => $manager->id, 'dob' => '1990-01-01', 'email' => 'employee_emp@example.test', 'contact' => '111', 'address' => 'addr', 'pay_method' => 'cash']);
        $employeeAccount = Account::create(['employee_id' => $employee->id, 'email' => 'employee@example.test', 'password' => bcrypt('password'), 'type' => 'Employee']);

        // Act as the employee and upload a file
        $this->actingAs($employeeAccount);

        $file = UploadedFile::fake()->image('document.jpg');

        $response = $this->post('/document', [
            'file' => $file,
            'subject' => 'Time Card'
        ]);

        $response->assertStatus(302);

        // File should be stored on public disk
        Storage::disk('public')->assertExists('documents/document.jpg');

        // Document record should exist
        $this->assertDatabaseHas('documents', [
            'employee_id' => $employee->id,
            'manager' => $manager->id,
            'subject' => 'Time Card',
            'file_name' => 'document.jpg',
            'verified' => 'U'
        ]);

        // Manager notified
        Notification::assertSentTo($managerAccount, DocumentUpload::class);
    }

    public function test_update_creates_timecard_and_marks_verified()
    {
        // Create manager and account and act as manager
        $manager = Employee::create(['id' => 'MG000002', 'first_name' => 'Mana2', 'last_name' => 'Ger2', 'position' => 'Manager', 'dob' => '1980-01-02', 'email' => 'manager2_emp@example.test', 'contact' => '000', 'address' => 'addr', 'pay_method' => 'cash']);
        $managerAccount = Account::create(['employee_id' => $manager->id, 'email' => 'manager2@example.test', 'password' => bcrypt('password'), 'type' => 'Manager']);
        $employee = Employee::create(['id' => 'SF000002', 'first_name' => 'Em', 'last_name' => 'Ployee2', 'position' => 'Employee', 'manager' => $manager->id, 'dob' => '1990-01-01', 'email' => 'employee_emp@example.test', 'contact' => '111', 'address' => 'addr', 'pay_method' => 'cash']);

        // Create a document with subject Time Card
        $document = Document::create([
            'employee_id' => $employee->id,
            'manager' => $manager->id,
            'subject' => 'Time Card',
            'file_name' => 'tc.pdf',
            'file_path' => 'documents/tc.pdf',
            'file' => '/storage/documents/tc.pdf',
            'verified' => 'U'
        ]);

        $this->actingAs($managerAccount);

        $response = $this->put('/document/'.$document->id, [
            'date' => '2025-11-20',
            'time_start' => '09:00',
            'time_end' => '17:00'
        ]);

        $response->assertStatus(302);

        // Timecard created
        $this->assertDatabaseHas('timecards', [
            'document_id' => $document->id,
            'date' => '2025-11-20',
            'time_start' => '09:00',
            'time_end' => '17:00'
        ]);

        // Document marked verified
        $this->assertDatabaseHas('documents', ['id' => $document->id, 'verified' => 'V']);
    }

    public function test_index_shows_documents_for_manager()
    {
        // Create manager employee and account
        $manager = Employee::create(['id' => 'MG000010', 'first_name' => 'ManagerX', 'last_name' => 'One', 'position' => 'Manager', 'dob' => '1980-01-01', 'email' => 'manx@example.test', 'contact' => '000', 'address' => 'addr', 'pay_method' => 'cash']);
        $managerAccount = Account::create(['employee_id' => $manager->id, 'email' => 'manxacct@example.test', 'password' => bcrypt('password'), 'type' => 'Manager']);

        // Create an employee belonging to the manager
        $employee = Employee::create(['id' => 'SF000010', 'first_name' => 'Worker', 'last_name' => 'Bee', 'position' => 'Employee', 'manager' => $manager->id, 'dob' => '1990-01-01', 'email' => 'worker@example.test', 'contact' => '111', 'address' => 'addr', 'pay_method' => 'cash']);

        // Create a document for that employee assigned to the manager
        $document = Document::create([
            'employee_id' => $employee->id,
            'manager' => $manager->id,
            'subject' => 'Time Card',
            'file_name' => 'doc.jpg',
            'file_path' => '/storage/documents/doc.jpg',
            'file' => '/storage/documents/doc.jpg',
            'verified' => 'U'
        ]);

        $this->actingAs($managerAccount);

        $response = $this->get('/document');

        $response->assertStatus(200);
        $response->assertSee('Time Card');
        $response->assertSee('Worker');
    }

    public function test_store_without_file_does_not_create_document()
    {
        Storage::fake('public');

        $manager = Employee::create(['id' => 'MG000020', 'first_name' => 'NoFileMgr', 'last_name' => 'One', 'position' => 'Manager', 'dob' => '1980-01-01', 'email' => 'nofilemgr@example.test', 'contact' => '000', 'address' => 'addr', 'pay_method' => 'cash']);
        $employee = Employee::create(['id' => 'SF000020', 'first_name' => 'NoFileEmp', 'last_name' => 'One', 'position' => 'Employee', 'manager' => $manager->id, 'dob' => '1990-01-01', 'email' => 'nofileemp@example.test', 'contact' => '111', 'address' => 'addr', 'pay_method' => 'cash']);
        $employeeAccount = Account::create(['employee_id' => $employee->id, 'email' => 'nofileempacct@example.test', 'password' => bcrypt('password'), 'type' => 'Employee']);

        $this->actingAs($employeeAccount);

        // post without file input
        $response = $this->post('/document', [
            'subject' => 'Time Card'
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseMissing('documents', ['employee_id' => $employee->id, 'subject' => 'Time Card']);
    }

    public function test_store_with_wrong_field_name_does_not_create_document()
    {
        Storage::fake('public');

        $manager = Employee::create(['id' => 'MG000021', 'first_name' => 'WrongFieldMgr', 'last_name' => 'One', 'position' => 'Manager', 'dob' => '1980-01-01', 'email' => 'wrongfieldmgr@example.test', 'contact' => '000', 'address' => 'addr', 'pay_method' => 'cash']);
        $employee = Employee::create(['id' => 'SF000021', 'first_name' => 'WrongFieldEmp', 'last_name' => 'One', 'position' => 'Employee', 'manager' => $manager->id, 'dob' => '1990-01-01', 'email' => 'wrongfieldemp@example.test', 'contact' => '111', 'address' => 'addr', 'pay_method' => 'cash']);
        $employeeAccount = Account::create(['employee_id' => $employee->id, 'email' => 'wrongfieldempacct@example.test', 'password' => bcrypt('password'), 'type' => 'Employee']);

        $this->actingAs($employeeAccount);

        // Upload under a different key ('document') often used elsewhere
        $file = UploadedFile::fake()->image('document.jpg');

        $response = $this->post('/document', [
            'document' => $file,
            'subject' => 'Time Card'
        ]);

        $response->assertStatus(302);

        Storage::disk('public')->assertMissing('documents/document.jpg');
        $this->assertDatabaseMissing('documents', ['employee_id' => $employee->id, 'subject' => 'Time Card']);
    }

    public function test_update_creates_receipt_when_sales_receipt()
    {
        $manager = Employee::create(['id' => 'MG000030', 'first_name' => 'ReceiptMgr', 'last_name' => 'One', 'position' => 'Manager', 'dob' => '1980-01-01', 'email' => 'receiptmgr@example.test', 'contact' => '000', 'address' => 'addr', 'pay_method' => 'cash']);
        $managerAccount = Account::create(['employee_id' => $manager->id, 'email' => 'receiptmgracct@example.test', 'password' => bcrypt('password'), 'type' => 'Manager']);
        $employee = Employee::create(['id' => 'SF000030', 'first_name' => 'Em', 'last_name' => 'Ployee', 'position' => 'Employee', 'manager' => $manager->id, 'dob' => '1990-01-01', 'email' => 'employee_emp@example.test', 'contact' => '111', 'address' => 'addr', 'pay_method' => 'cash']);

        $document = Document::create([
            'employee_id' => $employee->id,
            'manager' => $manager->id,
            'subject' => 'Sales Receipt',
            'file_name' => 'rcpt.pdf',
            'file_path' => 'documents/rcpt.pdf',
            'file' => '/storage/documents/rcpt.pdf',
            'verified' => 'U'
        ]);

        $this->actingAs($managerAccount);

        $response = $this->put('/document/'.$document->id, [
            'date' => '2025-11-20',
            'amount' => '123.45'
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('receipts', [
            'document_id' => $document->id,
            'date' => '2025-11-20',
            'amount' => '123.45'
        ]);

        $this->assertDatabaseHas('documents', ['id' => $document->id, 'verified' => 'V']);
    }
}
