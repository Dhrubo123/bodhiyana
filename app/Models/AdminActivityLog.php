<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminActivityLog extends Model
{
    protected $fillable = ['admin_user_id', 'action', 'entity_type', 'entity_id', 'description', 'ip_address'];
}
