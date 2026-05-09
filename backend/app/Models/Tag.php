<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model {
    public $timestamps = false;
    protected $fillable = ['group_id','name','color'];
    const CREATED_AT = 'created_at';

    public function group() { return $this->belongsTo(Group::class); }
    public function transactions() { return $this->belongsToMany(Transaction::class, 'transaction_tags'); }
}
