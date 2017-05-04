<?php
/**
 * Create by lurrpis
 * Date 16/9/8 上午1:41
 * Blog lurrpis.com
 */

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PopRefreshDay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pop:refresh-day';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '球球大作战代点棒棒糖日刷新';

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
        $user = new User();
        $user->timestamps = FALSE;
        $user->where('pu_today', '!=', 0)->update(['pu_today' => 0]);

        Log::notice('*** 龙蛋日数据刷新成功 ***');
    }
}