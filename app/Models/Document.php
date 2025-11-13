<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    /** @use HasFactory<\Database\Factories\DocumentFactory> */
    use HasFactory;
    protected $table = "documents";
    protected $primaryKey = 'id';
    protected $fillable = ['employee_id', 'manager', 'subject', 'file', 'file_name', 'file_path', 'verified'];
    protected $guarded = ['id'];

    public function employee() {
        return $this->belongsTo(Employee::class);
    }
}
