<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TruncateDatabaseTables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:truncate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Truncate given database tables';

    /**
     * Database tables
     * @var array
     */
    protected $tables;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->tables = [
            'leagues',
            'players',
	        'league_player',
            'scores',
            'jobs',
            'failed_jobs',
        ];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        foreach( $this->tables as $table )
            DB::table($table)->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        return true;
    }

}
