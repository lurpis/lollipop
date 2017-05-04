<?php
/**
 * Create by lurrpis
 * Date 16/9/11 下午2:51
 * Blog lurrpis.com
 */

namespace App;

use Carbon\Carbon;
use Pingpp\Pingpp;
use Pingpp\Charge;
use stdClass;

class Order extends GMCloud
{
    protected $table = 'order';
    protected $primaryKey = 'po_order_no';

    protected $liveKey;
    protected $testKey;
    protected $appid;
    protected $currency = 'cny';

    protected $guarded = [];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    const ORDER_PAID = 1;
    const ORDER_NOT_PAID = 0;
    const ORDER_CONSUME = 1;
    const ORDER_NOT_CONSUME = 0;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->liveKey = env('PINGPP_LIVE_KEY');
        $this->testKey = env('PINGPP_TEST_KEY');
        $this->appid = env('PINGPP_APP_ID');

        Pingpp::setApiKey($this->liveKey);
    }

    public function users()
    {
        return $this->belongsTo(User::class, 'pu_id', 'po_uid');
    }

    public function setPaid()
    {
        $this->po_paid = Order::ORDER_PAID;
        $this->po_paid_time = date('Y-m-d H:i:s');

        return $this->save();
    }

    public static function findByCharge($chargeId)
    {
        return self::where('po_charge_id', $chargeId)->first();
    }

    public static function createOrderNo()
    {
        return date('YmdHi') . rand(pow(10, (6 - 1)), pow(10, 6) - 1);
    }

    public static function getPaid()
    {
        return self::where('po_paid', self::ORDER_PAID)->get();
    }

    public function createCharge($product)
    {
        $charge = Charge::create([
            'order_no'  => self::createOrderNo(),
            'app'       => [
                'id' => $this->appid
            ],
            'channel'   => $product->channel,
            'amount'    => $product->amount * 100,
            'client_ip' => $product->clientIp,
            'currency'  => $this->currency,
            'subject'   => $product->subject,
            'body'      => $product->body,
            'extra'     => self::_extra($product),
            'metadata'  => [
                'type'        => $product->type,
                'time'        => $product->time,
                'url'         => isset($product->url) ? $product->url : null,
                'open_id'     => $product->openid ?: null,
                'productType' => $product->productType,
            ]
        ]);

        if (self::createOrder($charge)) {
            return $charge;
        }

        return false;
    }

    public static function createOrder($charge)
    {
        return Order::create([
            'po_order_no'     => $charge->order_no,
            'po_open_id'      => $charge->metadata['open_id'],
            'po_charge_id'    => $charge->id,
            'po_paid'         => $charge->paid,
            'po_amount'       => $charge->amount / 100,
            'po_channel'      => $charge->channel,
            'po_url'          => $charge->metadata['url'],
            'po_body'         => $charge->body,
            'po_subject'      => $charge->subject,
            'po_expired_time' => Carbon::createFromTimestamp($charge->time_expire),
            'po_client_ip'    => $charge->client_ip,
            'po_metadata'     => json_encode($charge->metadata),
            'po_description'  => $charge->description
        ]);
    }

    protected static function _extra($product)
    {
        $extra = new stdClass;

        switch ($product->channel) {
            case 'wx_pub':
                $extra->open_id = $product->openid;
                break;
            case 'wx_pub_qr':
                $extra->product_id = str_random(10);
                break;
        }

        return collect($extra)->toArray();
    }
}
