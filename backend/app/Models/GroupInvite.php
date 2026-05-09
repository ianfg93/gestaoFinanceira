<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GroupInvite extends Model {
    public $timestamps = false;
    const CREATED_AT = 'created_at';
    protected $fillable = ['group_id','invited_by','email','token','role','expires_at','accepted_at'];
    protected $casts = ['expires_at' => 'datetime', 'accepted_at' => 'datetime'];

    public function group() { return $this->belongsTo(Group::class); }
    public function inviter() { return $this->belongsTo(User::class, 'invited_by'); }

    public function isValid(): bool {
        return !$this->accepted_at && $this->expires_at->isFuture();
    }
}
