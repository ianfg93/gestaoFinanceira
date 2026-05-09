<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class NotificationPreference extends Model {
    public $timestamps = false;
    const UPDATED_AT = 'updated_at';
    protected $fillable = ['user_id','email_enabled','email_due_tomorrow','email_due_today','email_overdue_daily','email_monthly_summary','in_app_enabled'];
    protected $casts = ['email_enabled'=>'boolean','email_due_tomorrow'=>'boolean','email_due_today'=>'boolean','email_overdue_daily'=>'boolean','email_monthly_summary'=>'boolean','in_app_enabled'=>'boolean'];

    public function user() { return $this->belongsTo(User::class); }
}
