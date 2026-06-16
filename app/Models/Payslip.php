<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payslip extends Model
{
    protected $fillable = [
        'employee_id',
        'period',
        'gross_amount',
        'pph21',
        'bpjs_total',
        'net_amount',
        'corrected_amount',
        'file_path',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function paidAmount(): int
    {
        return $this->corrected_amount ?? $this->net_amount;
    }
}
