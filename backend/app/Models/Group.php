<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Group extends Model {
    use HasFactory, SoftDeletes;
    protected $fillable = ['name','slug','description','owner_id','currency','settings'];
    protected $casts = ['settings' => 'array'];

    public function owner() { return $this->belongsTo(User::class, 'owner_id'); }
    public function members() { return $this->belongsToMany(User::class, 'group_user')->withPivot('role','accepted_at','invited_by'); }
    public function categories() { return $this->hasMany(Category::class); }
    public function tags() { return $this->hasMany(Tag::class); }
    public function transactionNames() { return $this->hasMany(TransactionName::class); }
    public function transactions() { return $this->hasMany(Transaction::class); }
    public function series() { return $this->hasMany(TransactionSeries::class); }
    public function invites() { return $this->hasMany(GroupInvite::class); }
}
