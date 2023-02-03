<?php

namespace App\Http\Controllers;

use App\Models\OpenPositions;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Imports\OpenPositionsImport;
use App\Models\Batch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class OpenPositionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\OpenPositions  $openPositions
     * @return \Illuminate\Http\Response
     */
    public function show(OpenPositions $openPositions)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\OpenPositions  $openPositions
     * @return \Illuminate\Http\Response
     */
    public function edit(OpenPositions $openPositions)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\OpenPositions  $openPositions
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OpenPositions $openPositions)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\OpenPositions  $openPositions
     * @return \Illuminate\Http\Response
     */
    public function destroy(OpenPositions $openPositions)
    {
        //
    }

    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function downloadFile(Request $request)
    {
        set_time_limit(0);

        $start_date = $request->start_date;
        $end_date = $request->end_date;

        while ($start_date <= $end_date) {
            $date = date('Y-m-d', strtotime($start_date));
            $file_name = 'LendingOpenPositionFile' . str_replace('-', '', $date) . '_1.csv';
            $client = new Client();

            try {
                // Primeira requisição para pegar o token
                $tokenResponse = $client->get('https://arquivos.b3.com.br/api/download/requestname?fileName=LendingOpenPositionFile&date=' . $date . '&recaptchaToken=');
                $token = json_decode($tokenResponse->getBody()->getContents())->token;
                // Segunda requisição para o download do arquivo
                $downloadResponse = $client->get('https://arquivos.b3.com.br/api/download/?token=' . $token, [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                    ]
                ]);
                $file_contents = $downloadResponse->getBody()->getContents();
                $dateFromFile = substr($file_name, 23, 8);
                $dateFormatted = substr($dateFromFile, 0, 4) . '-' . substr($dateFromFile, 4, 2) . '-' . substr($dateFromFile, 6, 2);

                if (Batch::where('file_name', $file_name)->exists()) {
                    echo "Arquivo com a data " . date('d/m/Y', strtotime($date)) . " já existe\n";
                } else {
                    Storage::put('public/' . $file_name, $file_contents);

                    $rows = Excel::toCollection(new OpenPositionsImport, $file_name, 'public');
                    $totalRows = $rows[0]->count();
                    Batch::create([
                        'file_name' => $file_name,
                        'file_date' => $dateFormatted,
                        'total_records' => $totalRows,
                    ]);

                    Excel::import(new OpenPositionsImport, $file_name, 'public');
                    echo "Arquivo com a data " . date('d/m/Y', strtotime($date)) . " salvo com sucesso!\n";
                }
            } catch (\Exception $e) {
                Log::error($e->getMessage());
                echo "Arquivo com a data " . date('d/m/Y', strtotime($date)) . " não encontrado!\n";
            }

            $start_date = date('Y-m-d', strtotime($start_date . ' +1 day'));
        }
    }

    public function listAllAssets()
    {
        $assets = DB::table('open_positions')->select('Asst')->distinct()->get();
        return response()->json($assets);
    }

    public function getOpenPositionsByAsset(Request $request)
    {
        $openPositions = OpenPositions::where('Asst', $request->asset)->get();
        return response()->json($openPositions);
    }
}
