<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model {
    use SoftDeletes;
    protected $fillable = [
        'group_id','series_id','installment_number','total_installments',
        'type','transaction_name_id','category_id','amount','status',
        'due_date','paid_date','reference_month','responsible_id',
        'notes','is_recurring','notify_before_days','notifications_muted',
        'created_by','updated_by'
    ];
    protected $casts = [
        'due_date' => 'date',
        'paid_date' => 'date',
        'amount' => 'decimal:2',
        'is_recurring' => 'boolean',
        'notifications_muted' => 'boolean',
    ];

    public function group() { return $this->belongsTo(Group::class); }
    public function series() { return $this->belongsTo(TransactionSeries::class); }
    public function transactionName() { return $this->belongsTo(TransactionName::class); }
    public function category() { return $this->belongsTo(Category::class); }
    public function responsible() { return $this->belongsTo(User::class, 'responsible_id'); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function updater() { return $this->belongsTo(User::class, 'updated_by'); }
    public function tags() { return $this->belongsToMany(Tag::class, 'transaction_tags'); }
    public function attachments() { return $this->hasMany(TransactionAttachment::class); }
    public function history() { return $this->hasMany(TransactionHistory::class); }

    public function getIsOverdueAttribute(): bool {
        return $this->status === 'pending' && $this->due_date->lt(today());
    }
    public function getDueSoonAttribute(): bool {
        return $this->status === 'pending' && $this->due_date->between(today(), today()->addDays(7));
    }
}
