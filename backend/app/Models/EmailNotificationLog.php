<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailNotificationLog extends Model
{
    protected $table = 'email_notification_logs';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'transaction_id',
        'type',
        'sent_at',
        'reference_date',
    ];
}

