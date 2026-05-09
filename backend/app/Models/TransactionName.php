<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TransactionName extends Model {
    protected $fillable = ['group_id','name','normalized','type','category_id','usage_count'];

    public function group() { return $this->belongsTo(Group::class); }
    public function category() { return $this->belongsTo(Category::class); }
    public function transactions() { return $this->hasMany(Transaction::class); }

    public static function resolve(int $groupId, string $name): self {
        $normalized = mb_strtolower(trim($name));
        $entity = self::firstOrCreate(
            ['group_id' => $groupId, 'normalized' => $normalized],
            ['name' => trim($name), 'group_id' => $groupId]
        );
        if (!$entity->wasRecentlyCreated) {
            $entity->increment('usage_count');
        }
        return $entity;
    }
}
