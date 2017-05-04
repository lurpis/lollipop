<?php
/**
 * Create by lurrpis
 * Date 16/11/1 上午12:09
 * Blog lurrpis.com
 */

namespace App;

class Taobao extends GMCloud
{
    protected $table = 'taobao';
    protected $primaryKey = 'ptb_tid';

    protected $guarded = [];

    protected $casts = [

    ];

    protected $maps = [

    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    const YES = 1;
    const NO = 0;

    public function isDeliver()
    {
        return $this->ptb_is_deliver == self::YES;
    }

    public function isWrong()
    {
        return $this->ptb_is_wrong == self::YES;
    }

    public function setWrong()
    {
        $this->ptb_is_wrong = self::YES;

        return $this->save();
    }
}