<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name', 'registration_number', 'email', 'phone', 'website',
        'address', 'gst_number', 'pan_number', 'logo', 'favicon', 'login_banner',
    ];
}
