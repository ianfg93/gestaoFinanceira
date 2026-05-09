<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class FinancialNotification extends Model {
    public $timestamps = false;
    const CREATED_AT = 'created_at';
    protected $table = 'financial_notifications';
    protected $fillable = ['user_id','group_id','transaction_id','type','title','body','read_at','action_url'];
    protected $casts = ['read_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }
    public function group() { return $this->belongsTo(Group::class); }
    public function transaction() { return $this->belongsTo(Transaction::class); }
}
