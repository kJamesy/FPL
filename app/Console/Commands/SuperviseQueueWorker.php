<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SuperviseQueueWorker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'supervise:queue-worker';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Keep an eye on the queue worker. If it\'s dead, start it.';

    protected $fileName;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->fileName = 'worker_pid.txt';
    }

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        if ( ! $this->isWorkerRunning() )
            $this->recordWorkerPID($this->startWorker());

        $this->info($this->getLastRecordedPID());
    }

    /**
     * Determine if worker is running based on the last recorded PID
     * @return bool
     */
    protected function isWorkerRunning()
    {
        $PID = $this->getLastRecordedPID();

        if ( ! $PID )
            return false;

        return str_contains(exec("ps -p $PID -opid=,cmd="), 'queue:work');
    }

    /**
     * Get the worker working
     * @return string
     */
    protected function startWorker()
    {
        $command = "php " . base_path() . "/artisan queue:work --queue=high,medium,low > /dev/null & echo $!";
        return exec($command); //PID
    }

    /**
     * Record process ID of current worker
     * @param $PID
     */
    protected function recordWorkerPID($PID)
    {
        Storage::disk('local')->put($this->fileName, $PID);
    }

    /**
     * Get recorded process ID of last worker
     * @return bool|string
     */
    protected function getLastRecordedPID()
    {
        return file_exists($this->getFilePath()) ? file_get_contents($this->getFilePath()) : false;
    }

    /**
     * Get the storage path for the PID file
     * @return string
     */
    protected function getFilePath()
    {
        return storage_path('app' . '/' . $this->fileName);
    }

}
