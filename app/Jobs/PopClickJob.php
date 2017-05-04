<?php
/**
 * Create by lurrpis
 * Date 16/9/3 下午5:37
 * Blog lurrpis.com
 */

namespace App\Jobs;

use App\User;
use Illuminate\Support\Facades\Log;

class PopClickJob extends Job
{
    /**
     * @var User $user
     */
    protected $user;

    const WEEK_EFFECTIVE = 4;
    const DAY_EFFECTIVE = 1;
    const POP_RATIO = 5;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function handle()
    {
//        $response = $this->user->socketQuery();
//        $response = $this->user->xzfuliQuery();
        $response = $this->user->cnwolfQuery();

        if ($response->success) {
            $this->user->pu_touch_total++;

            if ($this->user->pu_today < self::DAY_EFFECTIVE && $this->user->pu_tswk < self::WEEK_EFFECTIVE) {
                $this->user->pu_today++;
                $this->user->pu_tswk++;
                $this->user->pu_pop_total += self::POP_RATIO;
            }

            $data = collect($response)->merge(collect($this->user)->forget(['pu_url', 'pu_full_url']));

            if ($this->user->save()) {
                Log::info($data->toJson(JSON_UNESCAPED_UNICODE));

                return TRUE;
            }
        }

        Log::error(collect($response)->toJson(JSON_UNESCAPED_UNICODE));

        return FALSE;
    }
}