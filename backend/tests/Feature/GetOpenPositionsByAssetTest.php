<?php

namespace Tests\Unit;

use App\Http\Controllers\OpenPositionsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DownloadFileTest extends TestCase
{
    use RefreshDatabase;

    public function testDownloadFile()
    {
        $controller = new OpenPositionsController();
        $request = new Request();

        $request->start_date = '2023-01-01';
        $request->end_date = '2023-01-10';
        // Aqui você pode testar a execução do método downloadFile e verificar se os arquivos foram baixados e salvos corretamente.
        $controller->downloadFile($request);

        $this->assertTrue(Storage::disk('public')->exists('LendingOpenPositionFile20230102_1.csv'));
        
        //check if name of file is in database on table batches

        $this->assertDatabaseHas('batches', [
            'file_name' => 'LendingOpenPositionFile20230102_1.csv'
        ]);
        
    }
}
