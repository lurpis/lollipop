<?php
/**
 * Create by lurrpis
 * Date 22/03/2017 12:47 PM
 * Blog lurrpis.com
 */

namespace App\Jobs;

use App\User;
use Illuminate\Support\Facades\Log;

class EggClickJob extends Job
{
    /**
     * @var User $user
     */
    protected $user;

    const WEEK_EFFECTIVE = 7;
    const DAY_EFFECTIVE = 1;
    const EGG_RATIO = 30;

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
            $this->user->pu_egg_touch_total++;

            if ($this->user->pu_egg_today < self::DAY_EFFECTIVE && $this->user->pu_egg_tswk < self::WEEK_EFFECTIVE) {
                $this->user->pu_egg_today++;
                $this->user->pu_egg_tswk++;
                $this->user->pu_egg_total += self::EGG_RATIO;
            }

            $data = collect($response)->merge(collect($this->user)->forget(['pu_egg_url', 'pu_egg_full_url']));

            if ($this->user->save()) {
                Log::info($data->toJson(JSON_UNESCAPED_UNICODE));

                return TRUE;
            }
        }

        Log::error(collect($response)->toJson(JSON_UNESCAPED_UNICODE));

        return FALSE;
    }
}