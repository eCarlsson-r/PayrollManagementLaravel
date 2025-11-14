<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Scheme extends Model
{
    protected $table = "scheme";
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = ['employee_id', 'scheme', 'base_amount', 'base_commission_rate'];
    protected $guarded = ['id'];

    protected $attributes = [
        'base_amount' => 0,
        'base_commission_rate' => 0
    ];

    public function employee() {
        return $this->belongsTo(Employee::class);
    }
}
