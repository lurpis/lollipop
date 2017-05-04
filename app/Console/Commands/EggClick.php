<?php
/**
 * Create by lurrpis
 * Date 22/03/2017 12:40 PM
 * Blog lurrpis.com
 */

namespace App\Console\Commands;

use App\User;
use App\Jobs\EggClickJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class EggClick extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'egg:click';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '球球大作战代点龙蛋';

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
        Log::notice('*** 龙蛋自动入队开始 ***');

        $users = User::getEggVip();

        foreach($users as $user) {
            if(dispatch(new EggClickJob($user))) {
                Log::notice($user->pu_id . ' 已入队');
            } else {
                Log::error($user->pu_id . ' 未入队');
            }
        }

        Log::notice('*** 龙蛋自动入队结束 ***');
    }
}