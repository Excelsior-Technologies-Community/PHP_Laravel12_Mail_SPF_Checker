<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailCheck extends Model
{
    protected $fillable = [
        'email',
        'domain',
        'mailserver',
        'spf',
        'dkim',
        'dmarc',
        'score'
    ];
}