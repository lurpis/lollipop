<?php
/**
 * Create by lurrpis
 * Date 16/10/11 上午12:02
 * Blog lurrpis.com
 */

namespace App\Http\Controllers;

use App\Http\Response\Status;
use App\Jobs\EggClickJob;
use App\Jobs\PopClickJob;
use App\Key;
use App\Openid;
use App\User;
use EasyWeChat\Foundation\Application;
use EasyWeChat\Message\Image;
use EasyWeChat\Message\Text;
use Illuminate\Http\Request;

class WechatController extends Controller
{
    static $wxConfig;

    static $app;
    static $openid;
    static $username;

    public function __construct()
    {
        static::$wxConfig = [
            'debug'   => true,
            'app_id'  => env('WECHAT_APP_ID'),
            'secret'  => env('WECHAT_SECRET'),
            'token'   => env('WECHAT_TOKEN'),
            'aes_key' => env('WECHAT_AES_KEY'),
            'log'     => [
                'level' => 'error',
                'file'  => APP_PATH . 'storage/logs/wechat.log',
            ],
        ];

        self::$app = new Application(static::$wxConfig);
    }


    public function index()
    {
        self::$app->server->setMessageHandler(function ($message) {
            self::$username = self::$app->user->get($message->FromUserName)->nickname;
            self::$openid = $message->FromUserName;

            return $this->{$message->MsgType}($message);
        });

        $response = self::$app->server->serve();

        return $response->send();
    }

    public function __call($name, $arguments)
    {
        return '你好 ' . self::$username . ', 你的留言我们已经收到, 客服看到后会及时给予回复';
    }

    public function freshMenu()
    {
        self::$app->menu->destroy();

        $buttons = [
            [
                "name"       => "糖袋子",
                "sub_button" => [
                    [
                        "type" => "click",
                        "name" => "查询",
                        "key"  => "GetQuery"
                    ],
                    [
                        "type" => "click",
                        "name" => "兑换",
                        "key"  => "GetExchange"
                    ],
                    [
                        "type" => "click",
                        "name" => "我的二维码",
                        "key"  => "GetQrCode"
                    ],
                    [
                        "type" => "click",
                        "name" => "我邀请的人",
                        "key"  => "GetReward"
                    ],
                    [
                        "type" => "view",
                        "name" => "千万不要点",
                        "url"  => "http://pop.gmcloud.io/game/catch"
                    ],

                ],
            ],
            [
                "type" => "view",
                "name" => "球球代点",
                "url"  => "http://pop.gmcloud.io/"
            ]
        ];

        return self::$app->menu->add($buttons);
    }

    public function event($message)
    {
        switch ($message->Event) {
            case 'subscribe':
                return $this->eventSubscribe($message->EventKey);
                break;
            case 'CLICK':
                return $this->{'click' . $message->EventKey}();
                break;
        }
    }

    public function reward(Request $request)
    {
        $openid = $request->input('open_id');

        $keys = Key::findByRecommend($openid);

        return self::response($keys->maps());
    }

    public function invite(Request $request)
    {
        $inviteCode = $request->input('code');

        if ($inviteCode && $user = Openid::findByInviteCode($inviteCode)) {
            return self::response(self::$app->qrcode->url($user->poi_code_ticket));
        }

        return self::responseCode(Status::WARING_PARAM);
    }

    public function eventSubscribe($eventKey)
    {
        if ($key = Key::findBySubscribe(self::$openid)) {
            if ($key->pk_is_used != Key::USED) {
                $customMessage = self::$username . " Welcome back！~\n\n";
                $customMessage .= "喏! 这是送你的代点卡 ~\n";
                $customMessage .= "还没使用, 别忘使用啦!\n";

                self::sendMsg($customMessage);
                self::sendMsg($key->pk_key);

                $customMessage = "还想要代点卡么? \n";
                $customMessage .= "带来一个伙伴, 送一张代点卡, 说到做到\n";

                self::sendMsg($customMessage);
            } else {
                $customMessage = self::$username . " Welcome back！~\n\n";
                $customMessage .= "别傻了, 只有首次关注才送代点卡~\n";
                $customMessage .= "还想要代点卡么? \n";
                $customMessage .= "带来一个伙伴, 送一张代点卡, 说到做到\n";

                self::sendMsg($customMessage);
            }
        } else {
            $key = Key::createKey('day', 3);

            // 关注人获得 Key
            if ($key->setSubscribe(self::$openid)) {
                $invite = false;

                if ($eventKey && strpos($eventKey, 'qrscene_') !== false) {
                    $id = explode('_', $eventKey);
                    $invite = Openid::findById(last($id))->poi_open_id;
                }

                $user = Openid::handleInvite(self::$openid, $invite);

                if ($user->setSubscribe() && $user->poi_rec_open_id) {
                    // 推荐人获得 Key
                    $recommendKey = Key::createKey('day', 3);
                    $recommendKey->setRecommend($user->poi_rec_open_id);
                }

                $customMessage = self::$username . " 恭喜你!\n\n";
                $customMessage .= "首次关注获得「棒棒糖/龙蛋代点 3天卡一张」\n";

                self::sendMsg($customMessage);
                self::sendMsg($key->pk_key);

                $customMessage = "什么? 还想要? <a href='http://pop.gmcloud.io'>点击这里</a>购买";

                self::sendMsg($customMessage);

                $customMessage = "纳尼? 不想花钱?\n";
                $customMessage .= "分享你的「专属二维码」(糖袋子 > 我的二维码) 拉个伙伴\n";
                $customMessage .= "我就再送你一张, 不信你试试\n";
                $customMessage .= "\nQQ群: 592566652";

                self::sendMsg($customMessage);
            }
        }

        return '';
    }

    public function clickGetQrCode()
    {
        $user = Openid::find(self::$openid);

        if (!$user->poi_code_ticket || !$user->poi_invite_code) {
            $code = self::$app->qrcode->forever($user->poi_id);

            $user->poi_code_url = $code->url;
            $user->poi_code_ticket = $code->ticket;
            $user->poi_invite_code = str_random(6);

            $user->save();
        }

        if (!$user->poi_media_id || $user->poi_media_expire_at < time()) {
            $imageUrl = self::$app->qrcode->url($user->poi_code_ticket);

            $path = sys_get_temp_dir() . '/' . str_random('10') . '.jpg';

            if (file_put_contents($path, file_get_contents($imageUrl))) {
                $media = self::$app->material_temporary->uploadImage($path);

                $user->poi_media_id = $media->media_id;
                $user->poi_media_expire_at = time() + 3600 * 24 * 2;

                $user->save();
            }
        }

        $customMessage = "诺! 你的专属二维码\n";
        $customMessage .= "通过此二维码邀请一人关注\n";
        $customMessage .= "你赢一张「棒棒糖/龙蛋代点 3天卡」\n";
        $customMessage .= "送代点卡张数上不封顶~\n";
        $customMessage .= "任意有球球玩家的地方, 都是安利的好地方";

        self::sendMsg($customMessage);
        self::sendImage($user->poi_media_id);

        $customMessage = "又要送代点卡出去了, 香菇\n";
        $customMessage .= "\nQQ群: 592566652";
        self::sendMsg($customMessage);

        return '';
    }

    public function clickGetReward()
    {
        $keys = Key::findByRecommend(self::$openid);
        $number = $keys->count();

        $message = "已邀请「{$number}」人关注\n";
        $message .= "成功获取「{$number}」张代点卡\n";

        if ($number > 0) {
            $url = env('front_url') . 'coupon';
            $message .= "\n<a href='$url'>点击查看</a>";
            $message .= "\n\nQQ群: 592566652";
        } else {
            $message .= "\n点击「专属二维码」邀请小伙伴来抢代点卡吧~\n";
            $message .= "\nQQ群: 592566652";
        }

        return $message;
    }

    public function clickGetQuery()
    {
        /**
         * @var User $user
         */
        if ($user = User::findByOpenid(self::$openid)) {
            $today = $user->pu_today * PopClickJob::POP_RATIO;
            $week = $user->pu_tswk * PopClickJob::POP_RATIO;
            $dayLimit = $today >= 5 ? '(上限)' : '';
            $weekLimit = $week >= 20 ? '(上限)' : '';

            $eggToday = $user->pu_egg_today * EggClickJob::EGG_RATIO;
            $eggWeek = $user->pu_egg_tswk * EggClickJob::EGG_RATIO;
            $eggDayLimit = $today >= 30 ? '(上限)' : '';
            $eggWeekLimit = $week >= 210 ? '(上限)' : '';

            $userType = '自己撸';
            if ($user->isVip()) {
                $userType = $user->isForever() ? '刷棒棒糖永久VIP' : '刷棒棒糖黄金VIP';
            } elseif ($user->pu_vip_expire_at) {
                $userType = 'VIP过期老司机';
            }

            $userEggType = '自己撸';
            if ($user->isEggVip()) {
                $userEggType = $user->isEggForever() ? '刷龙蛋永久VIP' : '刷龙蛋黄金VIP';
            } elseif ($user->pu_egg_vip_expire_at) {
                $userEggType = 'VIP过期老司机';
            }

            $customMessage = "------玩家账号------\n";
            $customMessage .= "玩家名: $user->pu_username\n";
            $customMessage .= "------棒棒糖------\n";
            $customMessage .= "今日已刷: {$today}个{$dayLimit}\n";
            $customMessage .= "本周已刷: {$week}个{$weekLimit}\n";
            $customMessage .= "代刷总数: {$user->pu_touch_total}次\n";
            $customMessage .= "刷糖总数: {$user->pu_pop_total}个\n";
            $customMessage .= "账户类型: {$userType}\n";
            if ($user->isVip()) {
                $customMessage .= $user->isForever() ? "VIP到期时间: 永久\n" : "VIP到期时间: \n{$user->pu_vip_expire_at}\n";
            }
            $customMessage .= "------龙蛋------\n";
            $customMessage .= "今日已刷: {$eggToday}个{$eggDayLimit}\n";
            $customMessage .= "本周已刷: {$eggWeek}个{$eggWeekLimit}\n";
            $customMessage .= "代刷总数: {$user->pu_egg_touch_total}次\n";
            $customMessage .= "刷蛋总数: {$user->pu_egg_total}个\n";
            $customMessage .= "账户类型: {$userEggType}\n";
            if ($user->isEggVip()) {
                $customMessage .= $user->isForever() ? "VIP到期时间: 永久\n" : "VIP到期时间: \n{$user->pu_vip_expire_at}\n";
            }

            $customMessage .= "\nQQ群: 592566652";

            return $customMessage;
        }

        $customMessage = "暂未绑定微信号\n";
        $customMessage .= "<a href='http://pop.gmcloud.io'>点击这里</a>进行绑定!";
        $customMessage .= "\n\nQQ群: 592566652";

        return $customMessage;
    }

    public function clickGetExchange()
    {
        $customMessage = "兑换方法:\n";
        $customMessage .= "1. 输入你的专用邀请链接\n";
        $customMessage .= "2. 输入你的代点卡号\n";
        $customMessage .= "3. 点击提交确认, 充值成功!\n\n";
        $customMessage .= "<a href='http://pop.gmcloud.io/?has_key=true'>点击这里</a>去兑换";
        $customMessage .= "\n\nQQ群: 592566652";

        return $customMessage;
    }

    public static function sendMsg($content)
    {
        return self::$app->staff->message(new Text(['content' => $content]))->to(self::$openid)->send();
    }

    public static function sendImage($media_id)
    {
        return self::$app->staff->message(new Image(['media_id' => $media_id]))->to(self::$openid)->send();
    }
}