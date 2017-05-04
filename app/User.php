<?php

namespace App;

use App\Jobs\PopClickJob;
use Carbon\Carbon;
use Faker\Factory;
use GuzzleHttp\Client;
use \stdClass;

class User extends GMCloud
{
    protected $table = 'user';
    protected $primaryKey = 'pu_id';

    protected $guarded = [];

    protected $maps = [
        'pu_id'                => 'id',
        'pu_username'          => 'username',
        'pu_url'               => 'url',
        'pu_full_url'          => 'full_url',
        'pu_today'             => 'today',
        'pu_tswk'              => 'tswk',
        'pu_touch_total'       => 'touch_total',
        'pu_pop_total'         => 'pop_total',
        'pu_is_vip'            => 'is_vip',
        'pu_vip_expire_at'     => 'vip_expire_at',
        'pu_egg_url'           => 'egg_url',
        'pu_egg_full_url'      => 'egg_full_url',
        'pu_egg_today'         => 'egg_today',
        'pu_egg_tswk'          => 'egg_tswk',
        'pu_egg_touch_total'   => 'egg_touch_total',
        'pu_egg_total'         => 'egg_total',
        'pu_egg_is_vip'        => 'egg_is_vip',
        'pu_egg_vip_expire_at' => 'egg_vip_expire_at',
    ];

    protected $casts = [
        'id'            => 'int',
        'today'         => 'int',
        'tswk'          => 'int',
        'touch_total'   => 'int',
        'pop_total'     => 'int',
        'is_vip'        => 'boolean',
        'vip_expire_at' => 'date',
    ];

    const ID_TYPE = 2;
    const URI_TYPE = 4;

    const VIP = 1;
    const NOT_VIP = 0;

    static $sockets = [
        '127.0.0.1:8111',
        '127.0.0.1:8112',
        '127.0.0.1:8114',
        '127.0.0.1:8115',
        '127.0.0.1:8116',
        '127.0.0.1:8117'
    ];
    static $base_url = 'http://www.battleofballs.com/';
    static $timeout = 5;

    public static function getVip()
    {
        return self::where('pu_is_vip', self::VIP)->where('pu_vip_expire_at', '>=', Carbon::now())->get();
    }

    public function isVip()
    {
        return $this->pu_is_vip == self::VIP && $this->pu_vip_expire_at >= Carbon::now();
    }

    public function isForever()
    {
        return $this->pu_vip_expire_at > '2030-01-01 00:00:00';
    }

    public static function getEggVip()
    {
        return self::where('pu_egg_is_vip', self::VIP)->where('pu_egg_vip_expire_at', '>=', Carbon::now())->get();
    }

    public function isEggVip()
    {
        return $this->pu_egg_is_vip == self::VIP && $this->pu_egg_vip_expire_at >= Carbon::now();
    }

    public function isEggForever()
    {
        return $this->pu_egg_vip_expire_at > '2030-01-01 00:00:00';
    }

    public function setOpenid($openid)
    {
        if ($this->pu_open_id != $openid) {
            $users = self::findByOpenid($openid);

            if ($users) {
                $users->pu_open_id = '';
                $users->save();
            }

            $this->pu_open_id = $openid;

            return $this->save();
        }
    }

    public static function findByUrl($url)
    {
        return self::where('pu_url', $url)->first();
    }

    public static function findByEggUrl($url)
    {
        return self::where('pu_egg_url', $url)->first();
    }

    public static function findByOpenid($openid)
    {
        return self::where('pu_open_id', $openid)->first();
    }

    public static function findOrCreateByUrl($url)
    {
        if (!$user = self::findByUrl($url)) {
            $fullUrl = self::urlQuery($url);

            parse_str(parse_url($fullUrl, PHP_URL_QUERY), $originUser);

            $originUser = array_change_key_case($originUser, CASE_LOWER);

            if (empty($originUser['id']) || empty($originUser['account'])) {
                return false;
            }

            if ($user = self::find($originUser['id'])) {
                $user->pu_username = $originUser['account'];
                $user->pu_url = $url;
                $user->pu_full_url = $fullUrl;

                $user->save();
            } else {
                $user = self::create([
                    'pu_id'            => $originUser['id'],
                    'pu_username'      => $originUser['account'],
                    'pu_url'           => $url,
                    'pu_full_url'      => $fullUrl,
                    'pu_today'         => 0,
                    'pu_tswk'          => 0,
                    'pu_touch_total'   => 0,
                    'pu_is_vip'        => 0,
                    'pu_vip_expire_at' => null,
                    'updated_at'       => null
                ]);
            }
        }

        return $user;
    }

    public function xzfuliQuery()
    {
        $client = self::getClient('http://www.xzfuli.cn/');

        $uri = '/index.php?a=api_qiuqiu';
        $params = [
            'type' => self::ID_TYPE,
            'id'   => $this->pu_id,
            'key'  => 'f660bc98551ee03de7cf498e2ada1a25'
        ];

        $response = $client->post($uri, ['form_params' => $params]);

        $contents = json_decode($response->getBody()->getContents());

        $contents->success = $response->getStatusCode() == 200;

        return $contents;
    }

    public function socketQuery()
    {
        $client = self::getClient();

        $uri = "/index_PC.html?id={$this->pu_id}&Account={$this->pu_username}";

        $count = 0;
        $stdClass = new stdClass;
        foreach (self::$sockets as $socket) {
            $response = $client->get($uri, [
                'proxy' => [
                    'http' => 'http://' . $socket,
                ]
            ]);

            if ($response->getStatusCode() == 200) {
                $count ++;
                $stdClass->count["代理[$count]"] = $socket . '执行成功';
            } else {
                $stdClass->count["代理[$count]"] = $socket . '执行失败';
            }

            if ($count >= 5) {
                $stdClass->success = true;

                return $stdClass;
            }
        }

        return false;
    }

    public function cnwolfQuery()
    {
        $client = self::getClient('http://api.cn-wolf.cn/', [
            'Origin'  => 'http://www.yjsmsg.com',
            'Referer' => 'http://www.yjsmsg.com'
        ]);

        $uri = "/Edition/BattleOfBalls/API/?url={$this->pu_url}";

        $response = $client->get($uri);

        $contents = json_decode($response->getBody()->getContents());

        $contents->success = $response->getStatusCode() == 200;

        return $contents;
    }

    public function cnwolfEggQuery()
    {
        $client = self::getClient('http://api.cn-wolf.cn/', [
            'Origin'  => 'http://www.yjsmsg.com',
            'Referer' => 'http://www.yjsmsg.com'
        ]);

        $uri = "/Edition/BattleOfBalls/API/?url={$this->pu_egg_url}";

        $response = $client->get($uri);

        $contents = json_decode($response->getBody()->getContents());

        $contents->success = $response->getStatusCode() == 200;

        return $contents;
    }

    public static function urlQuery($url)
    {
        $client = new Client();

        $response = $client->request('GET', $url, ['allow_redirects' => false]);

        if ($response->getStatusCode() == 302) {
            return $response->getHeader('location')[0];
        }

        return false;
    }

    //    public function idQuery()
    //    {
    //        $client = self::getClient();
    //
    //        $uri = '/index.php?a=api_qiuqiu';
    //        $params = [
    //            'type' => self::ID_TYPE,
    //            'id'   => $this->pu_id,
    //            'key'  => 'sd5sdf6d4fs4dfsd1'
    //        ];
    //
    //        $response = $client->post($uri, ['form_params' => $params, 'verify' => FALSE]);
    //
    //        return json_decode($response->getBody()->getContents());
    //    }

    //    public static function urlQuery($url)
    //    {
    //        $client = self::getClient();
    //
    //        $uri = '/index.php?a=api_qiuqiu';
    //        $params = [
    //            'type' => self::URI_TYPE,
    //            'url'  => $url
    //        ];
    //
    //        $response = $client->post($uri, ['form_params' => $params, 'verify' => FALSE]);
    //        $response = json_decode($response->getBody()->getContents());
    //
    //        if ($response->code == 0) {
    //            return $response->url;
    //        }
    //
    //        return FALSE;
    //    }

    public static function getClient($baseUrl = false, $headers = [])
    {
        $faker = Factory::create();

        $fakeIp = $faker->ipv4;

        $headers += [
            'Content-Type'    => 'multipart/form-data',
            'User-Agent'      => $faker->userAgent,
            'Referer'         => $baseUrl ?: self::$base_url,
            'X-FORWARDED-FOR' => $fakeIp,
            'CLIENT-IP'       => $fakeIp
        ];

        $client = new Client([
            'base_uri' => $baseUrl ?: self::$base_url,
            'timeout'  => self::$timeout,
            'headers'  => $headers
        ]);

        return $client;
    }

    public function getTodayAttribute()
    {
        return $this->attributes['today'] * PopClickJob::POP_RATIO;
    }

    public function getTswkAttribute()
    {
        return $this->attributes['tswk'] * PopClickJob::POP_RATIO;
    }

    public function getIsVipAttribute()
    {
        return $this->attributes['is_vip'] == self::VIP && $this->attributes['vip_expire_at'] >= Carbon::now();
    }
}
