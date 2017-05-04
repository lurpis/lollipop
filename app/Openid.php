<?php
/**
 * Create by lurrpis
 * Date 2016/10/19 上午11:49
 * Blog lurrpis.com
 */

namespace App;

class Openid extends GMCloud
{
    public $incrementing = true;

    protected $table = 'open_id';
    protected $primaryKey = 'poi_id';

    protected $guarded = [];

    protected $casts = [
        'is_subscribe' => 'boolean',
    ];

    protected $maps = [
        'poi_open_id'      => 'open_id',
        'poi_rec_open_id'  => 'rec_open_id',
        'poi_is_subscribe' => 'is_subscribe',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    const SUBSCRIBE = 1;
    const NOT_SUBSCRIBE = 0;

    public function setSubscribe()
    {
        $this->poi_is_subscribe = self::SUBSCRIBE;

        return $this->save();
    }

    public function setRecommendOpenid($openid)
    {
        $this->poi_rec_open_id = $openid;

        return $this->save();
    }

    public static function find($openid)
    {
        return self::where('poi_open_id', $openid)->first();
    }

    public static function findById($id)
    {
        return self::where('poi_id', $id)->first();
    }

    public static function findByInviteCode($inviteCode)
    {
        return self::where('poi_invite_code', $inviteCode)->first();
    }

    public static function shareBy($openid)
    {
        return self::where('poi_rec_open_id', $openid)->get();
    }

    public static function subscribeBy($openid)
    {
        return self::where('poi_rec_open_id', $openid)->where('poi_is_subscribe', self::SUBSCRIBE)->get();
    }

    public static function handleInvite($openid, $inviteId = false)
    {
        if (!$user = self::find($openid)) {
            $user = self::create([
                'poi_open_id' => $openid
            ]);
        }

        if ($inviteId && $openid != $inviteId && self::find($inviteId) && !$user->poi_rec_open_id) {
            $user->setRecommendOpenid($inviteId);
        }

        return $user;
    }
}