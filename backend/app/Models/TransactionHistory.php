<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TransactionHistory extends Model {
    protected $table = 'transaction_history';
    public $timestamps = false;
    const CREATED_AT = 'changed_at';
    protected $fillable = ['transaction_id','user_id','action','field_name','old_value','new_value'];

    public function transaction() { return $this->belongsTo(Transaction::class); }
    public function user() { return $this->belongsTo(User::class); }
}
