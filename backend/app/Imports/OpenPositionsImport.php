<?php

namespace App\Imports;

use App\Models\OpenPositions;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\Batch;


class OpenPositionsImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        
        return new OpenPositions([
            'RptDt' => $row['rptdt'],
            'TckrSymb' => $row['tckrsymb'],
            'ISIN' => $row['isin'],
            'Asst' => $row['asst'],
            'BalQty' => $row['balqty'],
            'TradAvrgPric' => $row['tradavrgpric'],
            'PricFctr' => $row['pricfctr'],
            'BalVal' => $row['balval'],
            'batch_id' => Batch::latest()->first()->id,
        ]);
    }
}
