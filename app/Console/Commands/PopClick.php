<?php
/**
 * Create by lurrpis
 * Date 16/9/3 下午5:08
 * Blog lurrpis.com
 */

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;
use App\Jobs\PopClickJob;
use Illuminate\Support\Facades\Log;

class PopClick extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pop:click';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '球球大作战代点棒棒糖';

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
        Log::notice('*** 棒棒糖自动入队开始 ***');

        $users = User::getVip();

        foreach($users as $user) {
            if(dispatch(new PopClickJob($user))) {
                Log::notice($user->pu_id . ' 已入队');
            } else {
                Log::error($user->pu_id . ' 未入队');
            }
        }

        Log::notice('*** 棒棒糖自动入队结束 ***');
    }
}
