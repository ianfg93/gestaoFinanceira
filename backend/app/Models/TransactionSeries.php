<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TransactionSeries extends Model {
    protected $fillable = ['group_id','series_type','recurrence_type','interval_days','starts_at','ends_at','total_installments','base_amount','transaction_name_id','category_id','responsible_id','notes','created_by'];
    protected $casts = ['starts_at' => 'date', 'ends_at' => 'date'];

    public function group() { return $this->belongsTo(Group::class); }
    public function transactions() { return $this->hasMany(Transaction::class, 'series_id'); }
    public function transactionName() { return $this->belongsTo(TransactionName::class); }
    public function category() { return $this->belongsTo(Category::class); }
    public function responsible() { return $this->belongsTo(User::class, 'responsible_id'); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}
