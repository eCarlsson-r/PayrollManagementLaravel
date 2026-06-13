<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollFlag extends Model
{
    protected $table = 'payroll_flags';

    protected $fillable = [
        'employee_id',
        'period',
        'reason',
        'severity',
        'gross_amount',
        'net_amount',
        'data',
        'resolved',
    ];

    protected $casts = [
        'data' => 'array',
        'resolved' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
