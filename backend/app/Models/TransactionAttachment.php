<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TransactionAttachment extends Model {
    public $timestamps = false;
    const CREATED_AT = 'created_at';
    protected $fillable = ['transaction_id','filename','original_name','mime_type','size_bytes','storage_path','uploaded_by'];

    public function transaction() { return $this->belongsTo(Transaction::class); }
    public function uploader() { return $this->belongsTo(User::class, 'uploaded_by'); }
}
