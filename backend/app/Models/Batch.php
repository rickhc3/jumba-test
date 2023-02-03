<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\Filterable;

class Batch extends Model
{
    use HasFactory, Filterable;

    protected $fillable = [
        'file_name', 
        'file_date',
        'total_records'
    ];
}
