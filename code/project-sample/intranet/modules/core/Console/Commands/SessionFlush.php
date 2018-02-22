<?php

namespace Rikkei\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use \Illuminate\Support\Facades\Redis;

class SessionFlush extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'session:flush';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flush session';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     * 
     * @return mixed
     */
    public function handle()
    {
        $drive = Config::get('session.driver');
        if ($drive == 'redis') {
            $this->redis();
        }
        $this->info('Flush session success');
    }
    
    /**
     * flush session in redis
     */
    protected function redis()
    {
        $connect = Config::get('session.connection');
        if (! $connect) {
            return;
        }
        $redis = Redis::connection($connect);
        $redis->flushdb();
    }
}
