<?php
/**
 * Create by lurrpis
 * Date 16/10/31 下午10:25
 * Blog lurrpis.com
 */

namespace App\Http\Controllers;

use App\Http\Response\Status;
use App\Key;
use App\Taobao;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TaobaoController extends Controller
{
    const appId = 100899360150;
    const appSecret = '4zf932i3sj422q2n8a2mvmxvb5';
    const accessToken = 'aldse7u2mfsambnnbtpaptrrycuecbfht8buz69m44pwhzrvv';

    const agisoApi = 'http://gw.api.agiso.com';
    const deliverStatus = 'WAIT_SELLER_SEND_GOODS';
    const headers = [
        'Authorization' => 'Bearer ' . self::accessToken,
        'ApiVersion'    => 1
    ];

    const goodsId = 541040806647;

    public function webHook(Request $request)
    {
        $request = $request->all();

        Log::info(collect($request)->toJson());

        $sign = $request['sign'];

        $params = [
            'json'      => $request['json'],
            'timestamp' => $request['timestamp']
        ];

        if(self::tbSign($params, self::appSecret) != strtolower($sign)) {
            Log::info('验签错误中止!');

            return self::responseCode(Status::API_FORBIDDEN);
        }

        $order = array_change_key_case(json_decode($request['json'], true), CASE_LOWER);

        if (current($order['orders'])['NumIid'] != self::goodsId) {
            return 'OK';
        }

        if (!$trade = Taobao::find($order['tid'])) {
            $trade = Taobao::create([
                'ptb_tid'           => $order['tid'],
                'ptb_seller_nick'   => $order['sellernick'],
                'ptb_buyer_nick'    => $order['buyernick'],
                'ptb_buyer_message' => $order['buyermessage'],
                'ptb_price'         => $order['price'],
                'ptb_num'           => $order['num'],
                'ptb_total_fee'     => $order['totalfee'],
                'ptb_payment'       => $order['payment'],
                'ptb_orders'        => collect($order['orders'])->toJson(),
                'ptb_pay_time'      => $order['paytime'],
                'ptb_created_time'  => $order['created']
            ]);
        }

        if (!$trade->isDeliver() && !$trade->isWrong() && $order['status'] == self::deliverStatus) {
            if (preg_match('/(http\:\/\/)?t\.cn\/[a-zA-Z0-9]+/', $trade->ptb_buyer_message, $match)) {
                if ($key = Key::createKey('day', 6000)) {
                    if (UserController::booleanProgress(current($match), $key->pk_key)) {
                        Log::info('充值: ' . current($match) . ' Key: ' . $key->pk_key);
                        if (self::deliver($trade->ptb_tid) && self::receipt($trade->ptb_tid)) {
                            Log::info('发货并推送消息成功');
                            $trade->ptb_key = $key->pk_key;
                            $trade->ptb_url = current($match);
                            $trade->ptb_is_deliver = Taobao::YES;

                            if ($trade->save()) {
                                return "OK";
                            }
                        }
                    } else {
                        $trade->setWrong();

                        return 'OK';
                    }
                }
            } else {
                $trade->setWrong();

                return 'OK';
            }
        }
    }

    protected static function deliver($tid)
    {
        $client = User::getClient(self::agisoApi, self::headers);

        $uri = '/api/Trade/LogisticsDummySend';

        $params = [
            'tids'      => $tid,
            'timestamp' => time()
        ];

        $params['sign'] = self::tbSign($params, self::appSecret);

        $response = $client->post($uri, ['form_params' => $params, 'verify' => false]);
        $response = json_decode($response->getBody()->getContents());

        Log::info(collect([
            'params'   => $params,
            'response' => $response
        ])->toJson(JSON_UNESCAPED_UNICODE));

        if ($response->IsSuccess == 1) {
            return true;
        }

        return false;
    }

    protected static function receipt($tid)
    {
        $client = User::getClient(self::agisoApi, self::headers);

        $uri = '/api/Trade/AldsProcessTrades';

        $params = [
            'tids'      => $tid,
            'timestamp' => time()
        ];

        $params['sign'] = self::tbSign($params, self::appSecret);

        $response = $client->post($uri, ['form_params' => $params, 'verify' => false]);
        $response = json_decode($response->getBody()->getContents());

        Log::info(collect([
            'params'   => $params,
            'response' => $response
        ])->toJson(JSON_UNESCAPED_UNICODE));

        if ($response->IsSuccess == 1) {
            return true;
        }

        return false;
    }

    protected static function tbSign($params, $secret)
    {
        ksort($params);
        $str = '';
        foreach ($params as $key => $value) {
            $str .= ($key . $value);
        }

        $str = $secret . $str . $secret;
        $encodeStr = strtolower(md5($str));

        return $encodeStr;
    }
}