<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\Filterable;

class OpenPositions extends Model
{
    use HasFactory, Filterable;

    protected $table = 'open_positions';

    protected $fillable = [
        'RptDt',
        'TckrSymb',
        'ISIN',
        'Asst',
        'BalQty',
        'TradAvrgPric',
        'PricFctr',
        'BalVal',
        'batch_id',
    ];

    function batch()
    {
        return $this->belongsTo(Batch::class);
    }
}
