<?php

namespace App\Http\Controllers;

use App\Models\Payslip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PayslipController extends Controller
{
    /** GET /payslip — list payslips; employees see only their own. */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Payslip::with('employee')->orderByDesc('period')->orderByDesc('id');

        if ($user->type !== 'Admin') {
            $query->where('employee_id', $user->employee->id);
        }

        $payslips = $query->get();

        return view('PayslipList', compact('payslips'));
    }

    /** GET /payslip/{id}/download — stream the PDF. */
    public function download(int $id)
    {
        $user = auth()->user();
        $payslip = Payslip::findOrFail($id);

        if ($user->type === 'Employee' && $payslip->employee_id !== $user->employee->id) {
            abort(403);
        }

        abort_unless(Storage::exists($payslip->file_path), 404);

        $filename = "payslip_{$payslip->period}_emp{$payslip->employee_id}.pdf";

        return response()->streamDownload(
            fn () => print(Storage::get($payslip->file_path)),
            $filename,
            ['Content-Type' => 'application/pdf'],
        );
    }
}
