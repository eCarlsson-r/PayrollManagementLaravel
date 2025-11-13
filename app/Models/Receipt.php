<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    protected $table = "receipts";
    protected $primaryKey = 'id';
    protected $fillable = ['document_id', 'date', 'amount'];
    protected $guarded = ['id'];
    public $timestamps = false;

    public function document() {
        return $this->belongsTo(Document::class);
    }
}
