<?php
/**
 * @author lurrpis
 * @date 16/7/5 下午5:12
 * @blog http://lurrpis.com
 */

namespace App\Http\Response;

interface Status
{
    const CODE = 0;
    const STATE = 1;
    const MESSAGE = 2;

    const success = 'success';
    const info = 'info';
    const error = 'error';

    // System
    const SUCCESS = [Response::HTTP_OK, self::success];
    const NOT_FOUND = [Response::HTTP_NOT_FOUND, self::error];
    const API_FORBIDDEN = [Response::HTTP_FORBIDDEN, self::error, '非法请求, 接口合作请联系 QQ:28568090'];
    const API_LOSE_TOKEN = [Response::HTTP_UNAUTHORIZED, self::error, '缺少 Authenticate Token'];
    const API_OVERDUE_TOKEN = [Response::HTTP_UNAUTHORIZED, self::error, '过期 Authenticate Token'];
    const API_FAULT_TOKEN = [Response::HTTP_UNAUTHORIZED, self::error, '错误 Authenticate Token'];
    const API_UNAUTHORIZED = [Response::HTTP_UNAUTHORIZED, self::error, '没有接口访问权限'];
    const MISSING_PARAM = [Response::HTTP_NOT_FOUND, self::info, '缺少参数'];
    const WARING_PARAM = [Response::HTTP_NOT_FOUND, self::info, '错误参数'];
    const TOO_MANY_REQUEST = [Response::HTTP_TOO_MANY_REQUESTS, self::info, '您的操作过于频繁, 请稍后再试'];
    const REDIRECT_URL_NOT_FOUND = [Response::HTTP_NOT_FOUND, self::info, '缺少 redirect_url'];

    // Client
    const USER_CREATE_SUCCESS = [Response::HTTP_OK, self::success, '用户创建成功'];
    const USER_ALREADY_EXIST = [Response::HTTP_FORBIDDEN, self::info, '用户已存在'];
    const USER_CREATE_FAILED = [Response::HTTP_FORBIDDEN, self::info, '用户创建失败'];
    const KEY_CREATE_SUCCESS = [Response::HTTP_OK, self::success, 'Key 创建成功'];
    const KEY_CREATE_FAILED = [Response::HTTP_FORBIDDEN, self::info, 'Key 创建失败'];
    const QUEUE_SUCCESS = [Response::HTTP_OK, self::success, '加入队列成功'];
    const QUEUE_FAILED = [Response::HTTP_FORBIDDEN, self::info, '加入队列失败'];
    const USER_BANED = [Response::HTTP_FORBIDDEN, self::info, '用户被封禁'];
    const USER_NOT_FOUND = [Response::HTTP_NOT_FOUND, self::info, '用户不存在'];
    const GROUP_NOT_FOUND = [Response::HTTP_NOT_FOUND, self::info, '用户组不存在'];
    const KEY_USED_SUCCESS = [Response::HTTP_OK, self::success, 'Key 使用成功'];
    const KEY_USED_FAILED = [Response::HTTP_FORBIDDEN, self::info, 'Key 使用失败'];
    const KEY_IS_USED = [Response::HTTP_FORBIDDEN, self::info, 'Key 已被使用'];
    const KEY_NOT_FOUND = [Response::HTTP_NOT_FOUND, self::info, '无效的 Key'];
    const OPENID_NOT_FOUND = [Response::HTTP_NOT_FOUND, self::info, '缺少 OpenID'];

    const CREATE_ORDER_FAILED = [Response::HTTP_BAD_REQUEST, self::error, '创建订单失败, 请联系管理员'];

    const UNPAID = [Response::HTTP_OK, self::info, '订单未支付'];
    const PAID_SUCCESS = [Response::HTTP_OK, self::success, '订单支付成功'];
    const PAID_CONSUME = [Response::HTTP_OK, self::info, '订单已被消费'];
    const PAID_OVERDUE = [Response::HTTP_OK, self::info, '订单支付超时'];
}