<?php

namespace App\Jobs;

use App\Http\Controllers\OpenPositionsController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DownloadDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $start_date;
    public $end_date;


    //protected $signature = 'DownloadDataJob {--start-date=} {--end-date=}';

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($start_date, $end_date)
    {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = [
            'start_date' => $this->start_date,
            'end_date' => $this->end_date
        ];

        $data = new \Illuminate\Http\Request($data);


        (new OpenPositionsController())->downloadFile($data);
    }
}
