<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentFactory> */
    use HasFactory;

    protected $table = "payments";
    protected $primaryKey = 'id';
    protected $fillable = ['employee_id', 'date', 'amount', 'method'];
    protected $guarded = ['id'];
    public $timestamps = false;

    public function employee() {
        return $this->belongsTo(Employee::class);
    }
}
