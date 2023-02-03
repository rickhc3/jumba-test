<?php
// app/Console/Commands/DispatchJob.php
namespace App\Console\Commands;

use Illuminate\Console\Command;

class DispatchJob extends Command
{
  protected $signature = 'job:dispatch {job} {start-date?} {end-date?}';
  protected $description = 'Manually dispatches jobs on the command line.';
  public function __construct()
  {
    parent::__construct();
  }
  public function handle()
  {
    $class = '\\App\\Jobs\\' . $this->argument('job');
    dispatch(new $class($this->argument('start-date'), $this->argument('end-date')));

    $this->call('queue:work', [
      '--tries' => 3,
      '--timeout' => 10,
      '--once' => true
    ]);
  }
}
