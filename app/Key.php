<?php
/**
 * Create by lurrpis
 * Date 16/9/7 下午10:57
 * Blog lurrpis.com
 */

namespace App;

use Carbon\Carbon;

class Key extends GMCloud
{
    protected $table = 'key';
    protected $primaryKey = 'pk_key';

    protected $guarded = [];

    protected $casts = [
        'key'     => 'string',
        'day'     => 'int',
        'is_used' => 'boolean',
        'used_at' => 'date',
    ];

    protected $maps = [
        'pk_key'     => 'key',
        'pk_day'     => 'day',
        'pk_is_used' => 'is_used',
        'pk_used_at' => 'used_at',
    ];

    protected $hidden = [
        'pk_send_open_id',
        'pk_type',
        'created_at',
        'updated_at'
    ];

    static $amount = [
        'day'   => 0.02,
        'week'  => 0.3,
        'month' => 0.9,
        'year'  => 2.3
    ];

    static $typeCn = [
        'day'   => '日',
        'week'  => '周',
        'month' => '月',
        'year'  => '永久'
    ];

    static $typeDay = 1;
    static $typeWeek = 7;
    static $typeMonth = 31;
    static $typeYear = 6000;

    const TYPE = [
        'day',
        'week',
        'month',
        'year'
    ];

    const USED = 1;
    const NOT_USED = 0;
    const TYPE_DEFAULT = 0;
    const TYPE_SUBSCRIBE = 1;
    const TYPE_RECOMMEND = 2;

    public function isUsed()
    {
        return $this->pk_is_used == self::USED;
    }

    public function setUsed()
    {
        $this->pk_is_used = self::USED;
        $this->pk_used_at = Carbon::now();

        return $this->save();
    }

    public function setSubscribe($openid)
    {
        $this->pk_type = self::TYPE_SUBSCRIBE;
        $this->pk_send_open_id = $openid;

        return $this->save();
    }

    public function setRecommend($openid)
    {
        $this->pk_type = self::TYPE_RECOMMEND;
        $this->pk_send_open_id = $openid;

        return $this->save();
    }

    public static function findBySubscribe($openid)
    {
        return self::where('pk_send_open_id', $openid)->where('pk_type', self::TYPE_SUBSCRIBE)->first();
    }

    public static function findByRecommend($openid)
    {
        return self::where('pk_send_open_id', $openid)->where('pk_type', self::TYPE_RECOMMEND)->get();
    }

    public static function typeToDay($type, $time = 1)
    {
        return self::${'type' . ucfirst($type)} * $time;
    }

    public static function getByDay($day)
    {
        return self::where('pk_day', $day)->where('pk_is_used', self::NOT_USED)->get();
    }

    public static function createKey($type, $time)
    {
        return self::create([
            'pk_key'     => Key::generateKey(),
            'pk_day'     => Key::typeToDay($type, $time),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    public static function generateKey()
    {
        return strtoupper(str_random(16));
    }
}
