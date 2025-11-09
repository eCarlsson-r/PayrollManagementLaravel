<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    /** @use HasFactory<\Database\Factories\FeedbackFactory> */
    use HasFactory;
    protected $table = "feedback";
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = ['employee_id', 'manager', 'time', 'date', 'title', 'feedback', 'read'];
    protected $guarded = ['id'];

    public function employee() {
        return $this->belongsTo(Employee::class);
    }
}
