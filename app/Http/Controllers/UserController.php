<?php
/**
 * @author lurrpis
 * @date 16/9/5 下午2:48
 * @blog http://lurrpis.com
 */

namespace App\Http\Controllers;

use App\Openid;
use App\Order;
use Pingpp\Pingpp;
use Pingpp\RedEnvelope;
use stdClass;
use App\Http\Response\Status;
use App\Key;
use App\User;
use App\Jobs\PopClickJob;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    const STATS_TOUCH = 'lollipop:statistics:touch';
    const STATS_POP = 'lollipop:statistics:pop';
    const STATS_USER = 'lollipop:statistics:user';


    protected $gmAppid = '000379c4-bfbc-47f5-b577-062ea148e1a2';

    public function index()
    {
        $user = User::all()->maps();

        return self::response($user);
    }

    public function send($openid, $amount)
    {
        Pingpp::setApiKey('sk_live_Da1ez9SOqzzLj5GuX1z50W5O');

        $response = RedEnvelope::create([
            'subject'     => '爸爸的测试红包',
            'body'        => '测试 Api, 爸爸爱你',
            'amount'      => $amount,
            'order_no'    => Order::createOrderNo(),
            'currency'    => 'cny',
            'extra'       => [
                'send_name' => 'GMCloud.io 接口 '
            ],
            'recipient'   => $openid,
            'channel'     => 'wx_pub',
            'app'         => ['id' => 'app_XPyrXL0afDe1b588'],
            'description' => '爸爸的测试红包 {Description}'
        ]);

        print_r($response);exit;
    }

    public function test()
    {
        session_start();

        echo sha1($_SESSION['_time']. env('APP_KEY'));
    }

    public function show(Request $request)
    {
        $id = $request->input('id');
        $url = $request->input('url');

        if ($url) {
            if (!$user = User::findOrCreateByUrl($url)) {
                return self::responseCode(Status::USER_NOT_FOUND);
            }
        } else if ($id) {
            $user = User::find($id);
        }

        if (!empty($user)) {
            return self::response($user->maps());
        } else {
            return self::responseCode(Status::USER_NOT_FOUND);
        }
    }

    public function store(Request $request)
    {
        /**
         * @var User $user
         */
        $url = $request->input('url');
        $key = $request->input('key');
        $token = $request->input('token');
        $openid = $request->input('open_id');

//        if(!$key && !self::verifyCsrfToken($token)) {
//            return self::responseCode(Status::API_FORBIDDEN);
//        }

        return self::handleProgress($url, $key, $openid);
    }

    public function stats(Request $request)
    {
        $stats = new stdClass;
        $users = User::all();

        $trust = $request->input('trust') ?: 0;

        if ($trust) {
            $stats->user = 0;
            $stats->pop = 0;
            $stats->touch = 0;
        } else {
            $stats->user = 18350;
            $stats->pop = 1835000;
            $stats->touch = 110100;
        }

        $stats->user += $users->count();

        foreach ($users as $user) {
            $stats->pop += $user->pu_pop_total;
            $stats->touch += $user->pu_touch_total;
        }

        return self::response($stats);
    }

    public static function handleProgress($url, $key, $openid = false)
    {
        if (!$user = User::findOrCreateByUrl($url)) {
            return self::responseCode(Status::USER_NOT_FOUND);
        }

        // TODO 可能需要测试
        if($openid && Openid::find($openid)) {
            $user->setOpenid($openid);
        }

        $response = self::responseCode(Status::QUEUE_SUCCESS);

        $key && $response = self::usedKey($user, $key);

        if (dispatch(new PopClickJob($user))) {
            return $response;
        }

        return self::responseCode(Status::QUEUE_FAILED);
    }

    public static function booleanProgress($url, $key, $openid = false)
    {
        if (!$user = User::findOrCreateByUrl($url)) {
            return false;
        }

        if($openid && Openid::find($openid)) {
            $user->setOpenid($openid);
        }

        $key && self::usedKey($user, $key);

        if (dispatch(new PopClickJob($user))) {
            return true;
        }

        return false;
    }

    public static function usedKey(User $user, $key)
    {
        /**
         * @var Key $key
         * @var User $user
         */
        $key = Key::findOrFail($key);
        if (!$key->isUsed()) {
            if ($user->isVip()) {
                $expire_at = Carbon::createFromTimestamp(strtotime($user->pu_vip_expire_at));

                if($key->pk_day < 6000) {
                    if($expire_at <= '2030-01-01 00:00:00') {
                        $user->pu_vip_expire_at = $expire_at->addDays($key->pk_day);
                    }
                } else if ($expire_at <= '2021-01-01 00:00:00') {
                    $user->pu_vip_expire_at = $expire_at->addDays($key->pk_day);
                }
            } else {
                $user->pu_is_vip = 1;
                $user->pu_vip_expire_at = Carbon::now()->addDays($key->pk_day);
            }

            if ($user->save() && $key->setUsed()) {
                Log::critical("Key:{$key->pk_key} 被 {$user->pu_id} 消费成功");

                return self::responseCode(Status::KEY_USED_SUCCESS);
            }

            Log::error("Key:{$key->pk_key} 被 {$user->pu_id} 消费失败");

            return self::responseCode(Status::KEY_USED_FAILED);
        }

        Log::error("Key:{$key->pk_key} 已被使用, {$user->pu_id} 消费失败");

        return self::responseCode(Status::KEY_IS_USED);
    }

    public function login($channel, Request $request)
    {
        $from = $request->input('from');
        $invite = $request->input('i');

        if (!$from) {
            return self::responseCode(Status::REDIRECT_URL_NOT_FOUND);
        }

        $authApiUrl = env('auth_api_url') . 'auth/authorize?';
        $redirectUrl = env('api_url') . "api/token?i=$invite&from=$from";

        $params = [
            'appid'        => $this->gmAppid,
            'to'           => $channel,
            'redirect_uri' => $redirectUrl
        ];

        return redirect($authApiUrl . http_build_query($params));
    }

    public static function verifyCsrfToken($token)
    {
        session_start();

        $time = isset($_SESSION['_time']) ? $_SESSION['_time'] : 0;

        if((time() - $time) < 3600 && self::generateCsrfToken($time) == $token) {
            return true;
        }

        return false;
    }

    public static function getCsrfToken()
    {
        session_start();

        $_SESSION['_time'] = time();

        return self::generateCsrfToken($_SESSION['_time']);
    }

    public static function generateCsrfToken($time)
    {
        return sha1($time . env('APP_KEY'));
    }

    public function token(Request $request)
    {
        $openid = $request->input('openid');
        $from = $request->input('from');
        $invite = $request->input('i');

        Openid::handleInvite($openid, $invite);

        $params = [
            'open_id' => $openid,
        ];

        return redirect($from . http_build_query($params));
    }

//    public function login($channel, Request $request)
//    {
//        $authApiUrl = env('auth_api_url').'auth/authorize?';
//        $redirectUrl = env('api_url').'api/token';
//
//        $params = [
//            'appid'        => $this->gmAppid,
//            'to'           => $channel,
//            'redirect_uri' => $redirectUrl
//        ];
//
//        return redirect($authApiUrl.http_build_query($params));
//    }
//
//    public function token(Request $request)
//    {
//        $openid = $request->input('openid');
//        $redirectUrl = env('front_url').'store?';
//
//        $params = [
//            'open_id' => $openid,
//        ];
//
//        return redirect($redirectUrl.http_build_query($params));
//    }
}