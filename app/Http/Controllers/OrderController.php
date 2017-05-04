<?php
/**
 * Create by lurrpis
 * Date 16/9/11 下午3:07
 * Blog lurrpis.com
 */

namespace App\Http\Controllers;

use App\Jobs\PopClickJob;
use App\Key;
use App\Openid;
use App\User;
use stdClass;
use App\Order;
use Carbon\Carbon;
use Pingpp\Charge;
use App\Http\Response\Status;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    const PRODUCT_KEY = 'key';
    const PRODUCT_URL = 'url';
    const CHANNEL = ['wx_pub', 'wx_pub_qr', 'alipay_qr'];

    public function webHook(Request $request)
    {
        /**
         * @var Order $order
         */
        $rawData = file_get_contents('php://input');
        $signature = $request->header('X-Pingplusplus-Signature') ?: NULL;
        $keyPath = base_path() . '/resources/cert/pingxx.pem';

        if (self::verifySignature($rawData, $signature, $keyPath) === 1) {
            $event = json_decode($rawData);

            switch ($event->type) {
                case 'charge.succeeded':
                    $charge = $event->data->object;
                    if ($charge->paid == Order::ORDER_PAID) {
                        $order = Order::findByCharge($charge->id);

                        if ($order && $order->setPaid()) {
                            return self::responseCode();
                        }
                    }
                    break;
            }
        }

        return self::responseCode(Status::API_FORBIDDEN);
    }

    public function retrieve($chargeId, Request $request)
    {
        /**
         * @var Key $key
         * @var Order $order
         */
        if ($order = Order::findByCharge($chargeId)) {
            if ($request->input('force') && $charge = Charge::retrieve($chargeId)) {
                if ($charge->paid == Order::ORDER_PAID) {
                    $order->setPaid();
                }
            }

            if ($order->po_paid == Order::ORDER_PAID) {
                if ($order->po_consume == Order::ORDER_NOT_CONSUME) {
                    $metadata = json_decode($order->po_metadata);

                    if ($key = Key::createKey($metadata->type, $metadata->time)) {
                        $order->po_consume = Order::ORDER_CONSUME;
                        $order->po_key = $key->pk_key;

                        switch ($metadata->productType) {
                            case self::PRODUCT_KEY:
                                if ($order->save()) {
                                    return self::response($key->pk_key, Status::PAID_SUCCESS);
                                }
                                break;
                            case self::PRODUCT_URL:
                                if ($order->save()) {
                                    if (UserController::handleProgress($metadata->url, $key->pk_key, $metadata->open_id)) {
                                        return self::response($key->pk_key, Status::PAID_SUCCESS);
                                    }
                                }
                                break;
                        }
                    }
                }

                return self::responseCode(Status::PAID_CONSUME);
            }

            if (Carbon::now() > $order->po_expired_time) {
                return self::responseCode(Status::PAID_OVERDUE);
            }

            return self::responseCode(Status::UNPAID);
        }
    }

    public function buy(Request $request)
    {
        $channel = $request->input('channel');
        $type = $request->input('type');
        $time = $request->input('time') ?: 1;

        if (!in_array($type, Key::TYPE)) {
            return self::response(self::chooseIn(Key::TYPE), Status::WARING_PARAM);
        }

        if (!in_array($channel, self::CHANNEL)) {
            return self::response(self::chooseIn(self::CHANNEL), Status::WARING_PARAM);
        }

        $openid = $request->input('open_id');

        if ($channel == 'wx_pub') {
            if (empty($openid)) {
                return self::responseCode(Status::OPENID_NOT_FOUND);
            }
        }

        $product = self::getProduct($type, $time, self::PRODUCT_KEY);
        $product->channel = $channel;
        $product->clientIp = $request->getClientIp();
        $product->openid = $openid;

        $order = new Order;
        if ($charge = $order->createCharge($product)) {
            $response = new stdClass;
            $response->charge = $charge;
            strpos($channel, '_qr') !== false
            && $response->qr = self::createBase64Image(self::createQrCode($charge->credential->$channel));

            return self::response($response);
        }

        return self::responseCode(Status::CREATE_ORDER_FAILED);
    }

    public function buyUrl(Request $request)
    {
        $channel = $request->input('channel');
        $url = $request->input('url');
        $type = $request->input('type');
        $time = $request->input('time') ?: 1;

        if (!in_array($type, Key::TYPE)) {
            return self::response(self::chooseIn(Key::TYPE), Status::WARING_PARAM);
        }

        if (!in_array($channel, self::CHANNEL)) {
            return self::response(self::chooseIn(self::CHANNEL), Status::WARING_PARAM);
        }

        if (!isset($url) || !User::findOrCreateByUrl($url)) {
            return self::responseCode(Status::WARING_PARAM);
        }

        $openid = $request->input('open_id');

        if ($channel == 'wx_pub') {
            if (empty($openid)) {
                return self::responseCode(Status::OPENID_NOT_FOUND);
            }
        }

        $product = self::getProduct($type, $time, self::PRODUCT_URL);
        $product->channel = $channel;
        $product->url = $url;
        $product->clientIp = $request->getClientIp();
        $product->openid = $openid;

        $order = new Order;
        if ($charge = $order->createCharge($product)) {
            $response = new stdClass;
            $response->charge = $charge;
            strpos($channel, '_qr') !== false
            && $response->qr = self::createBase64Image(self::createQrCode($charge->credential->$channel));

            return self::response($response);
        }

        return self::responseCode(Status::CREATE_ORDER_FAILED);
    }

    protected static function getProduct($type, $time, $productType = self::PRODUCT_KEY)
    {
        $product = new stdClass;

        switch ($productType) {
            case self::PRODUCT_KEY:
                $product->body = $type == 'year'
                    ? "GMCloud 棒棒糖贩卖机 VIP " . Key::$typeCn[$type] . "代点卡"
                    : "GMCloud 棒棒糖贩卖机 VIP {$time} " . Key::$typeCn[$type] . "代点卡";
                break;
            case self::PRODUCT_URL:
                $product->body = $type == 'year'
                    ? "GMCloud 棒棒糖贩卖机 VIP 充值 " . Key::$typeCn[$type]
                    : "GMCloud 棒棒糖贩卖机 VIP 充值 {$time} " . Key::$typeCn[$type];
                break;
        }

        $product->type = $type;
        $product->time = $time;
        $product->subject = $product->body;
        $product->amount = Key::$amount[$type] * $time;
        $product->productType = $productType;

        return $product;
    }

    protected static function verifySignature($rawData, $signature, $pubKeyPath)
    {
        $keyContents = file_get_contents($pubKeyPath);

        return openssl_verify($rawData, base64_decode($signature), $keyContents, OPENSSL_ALGO_SHA256);
    }
}