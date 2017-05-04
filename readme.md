# Lumen PHP Framework

[![Build Status](https://travis-ci.org/laravel/lumen-framework.svg)](https://travis-ci.org/laravel/lumen-framework)
[![Total Downloads](https://poser.pugx.org/laravel/lumen-framework/d/total.svg)](https://packagist.org/packages/laravel/lumen-framework)
[![Latest Stable Version](https://poser.pugx.org/laravel/lumen-framework/v/stable.svg)](https://packagist.org/packages/laravel/lumen-framework)
[![Latest Unstable Version](https://poser.pugx.org/laravel/lumen-framework/v/unstable.svg)](https://packagist.org/packages/laravel/lumen-framework)
[![License](https://poser.pugx.org/laravel/lumen-framework/license.svg)](https://packagist.org/packages/laravel/lumen-framework)

Laravel Lumen is a stunningly fast PHP micro-framework for building web applications with expressive, elegant syntax. We believe development must be an enjoyable, creative experience to be truly fulfilling. Lumen attempts to take the pain out of development by easing common tasks used in the majority of web projects, such as routing, database abstraction, queueing, and caching.

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell at taylor@laravel.com. All security vulnerabilities will be promptly addressed.

## License

The Lumen framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)

> 管理员 API TOKEN `*******`

### 添加刷分用户 (会主动触发刷糖并创建用户)

#### 参数解释
| 参数 | 必要 | 说明 |
| ------------- | ------------- | ----- |
| url | 是 | 球球大作战中的分享链接 `string` |
| token | 是 | 提交 Key 时, Token 可为空 `string` |
| key | 否 | Key `string` |
| open_id | 是 | Open ID `string` |

##### 请求
> POST https://pop.gmcloud.io/api/user
``` json
{
    "url":"http://t.cn/RtE6YdU",
    "key":"Q51VXCRNUR76S4E7",
    "open_id":"oT8ucwVxRcvAYvbmFew4jQNEunL8"
}
```

##### 返回 - 无 key
``` json
{
    "state": "success",
    "message": "加入队列成功",
    "time": "551ms",
    "usage": "9.00Mb"
}
```

##### 返回 - 有 key
``` json
{
    "state": "success",
    "message": "Key 使用成功",
    "time": "33ms",
    "usage": "6.50Mb"
}
```
---

### 查询刷分用户

##### 请求
> GET https://pop.gmcloud.io/api/user?id={:id} // ID 方式
> GET https://pop.gmcloud.io/api/user?url={:url} // URL 方式

#### 参数解释
| 参数 | 必要 | 说明 |
| ------------- | ------------- | ----- |
| id | 是 | 球球大作战 用户 ID `int` |

#### 返回解释
| 参数 | 说明 |
| ------------- | ----- |
| id | 球球大作战 用户 ID |
| username | 球球大作战 用户名 |
| today | 本日获得棒棒糖数 (系统限制上限5, 每日清零) |
| tswk | 本周获得棒棒糖数 (系统限制上限20, 每周一清零) |
| touch_total | 总刷分触发数 |
| pop_total | 获得棒棒糖总数 |
| is_vip | 是否为 VIP |
| vip_expire_at | VIP 到期时间 |

##### 返回
``` json
{
    "data": {
        "created_at": "2016-09-08 11:29:29",
        "updated_at": "2016-09-08 11:39:45",
        "id": 164768734,
        "username": "专吃你个傻x",
        "url": "http://t.cn/RtE6YdU",
        "full_url": "http://www.battleofballs.com/?id=164768734&Account=%E4%B8%93%E5%90%83%E4%BD%A0%E4%B8%AA%E5%82%BBx",
        "today": 5,
        "tswk": 5,
        "touch_total": 11,
        "pop_total": 5,
        "is_vip": false,
        "vip_expire_at": null
    },
    "state": "success",
    "time": "37ms",
    "usage": "7.25Mb"
}
```
---

### 查询刷分用户列表
> 权限: `管理员`

##### 请求
> GET https://pop.gmcloud.io/admin/api/user/list

##### 返回
``` json
{
    "data": [
        {
            "pu_id": 164768734,
            "pu_username": "专吃你个傻x",
            "pu_url": "http://t.cn/RtE6YdU",
            "pu_full_url": "http://www.battleofballs.com/?id=164768734&Account=%E4%B8%93%E5%90%83%E4%BD%A0%E4%B8%AA%E5%82%BBx",
            "created_at": "2016-09-05 18:12:44",
            "updated_at": "2016-09-05 18:12:44"
        }
    ],
    "state": "success",
    "time": "20ms",
    "usage": "5.50Mb"
}
```
---

### 批量创建 Key
> 权限: `管理员`

#### 参数解释
| 参数 | 必要 | 说明 |
| ------------- | ------------- | ----- |
| type | 是 | 周期类型 选项 [day, week, month, year] `string` |
| time | 否 | 周期次数 默认 1 `int` |
| number | 否 | 生成数量 默认 1 `int` |

> 说明: type = 'week', time = 1, number = 3 表示生成3张周期为1周(7天)的 Key
数据库以 `day` 为单位, 即 `type = 'week', time = 1` 和 `type = 'day', time = 7` 相同

##### 请求
> POST https://pop.gmcloud.io/admin/api/key
```json
{
  "type":"week",
  "time":"1",
  "number":10
}
```

##### 返回
``` json
{
    "data": [
        {
            "pk_key": "EJHNQARV3DIG8GUOWB",
            "pk_day": 7,
            "created_at": "2016-09-08 15:47:32",
            "updated_at": "2016-09-08 15:47:32"
        },
        {
            "pk_key": "3UXGOHH7WBPXC9GSGS",
            "pk_day": 7,
            "created_at": "2016-09-08 15:47:32",
            "updated_at": "2016-09-08 15:47:32"
        },
        {
            "pk_key": "BHQNR5BJHINZWCHVSA",
            "pk_day": 7,
            "created_at": "2016-09-08 15:47:32",
            "updated_at": "2016-09-08 15:47:32"
        }
    ],
    "state": "success",
    "message": "Key 创建成功",
    "time": "39ms",
    "usage": "6.50Mb"
}
```
---

### 获取 Key
> 权限: `管理员`

#### 参数解释
| 参数 | 必要 | 说明 |
| ------------- | ------------- | ----- |
| type | 是 | 周期类型 选项 [day, week, month, year] `string` |
| time | 否 | 周期次数 默认 1 `int` |
| batch | 否 | 是否批量查询 默认 0 (随机抽取1条) `boolean` |


##### 请求 - 单个随机
> GET https://pop.gmcloud.io/admin/api/key/list?type=week&time=1

##### 返回
``` json
{
    "data": {
        "key": "EJHNQARV3DIG8GUO",
        "day": 7,
        "is_used": false,
        "used_at": null
    },
    "state": "success",
    "time": "23ms",
    "usage": "6.75Mb"
}
```

##### 请求 - 批量获取
> GET https://pop.gmcloud.io/api/key?type=week&time=1&batch=1

##### 返回
``` json
{
    "data": [
        {
            "key": "3UXGOHH7WBPXC9GS",
            "day": 7,
            "is_used": false,
            "used_at": null
        },
        {
            "key": "5U93ZMJYPBHZ0SUC",
            "day": 7,
            "is_used": false,
            "used_at": null
        },
        {
            "key": "BHQNR5BJHINZWCHV",
            "day": 7,
            "is_used": false,
            "used_at": null
        },
        {
            "key": "EJHNQARV3DIG8GUO",
            "day": 7,
            "is_used": false,
            "used_at": null
        },
        {
            "key": "EPROVCZZKJJAAIT2",
            "day": 7,
            "is_used": false,
            "used_at": null
        },
        {
            "key": "GG0DHVN8VOTJTAO0",
            "day": 7,
            "is_used": false,
            "used_at": null
        },
        {
            "key": "SALI61GP6CWUUQ89",
            "day": 7,
            "is_used": false,
            "used_at": null
        },
        {
            "key": "SLUT8MRG9U0NA6KW",
            "day": 7,
            "is_used": false,
            "used_at": null
        },
        {
            "key": "X6Q27MKFUZKP4W34",
            "day": 7,
            "is_used": false,
            "used_at": null
        },
        {
            "key": "ZNGTUNL7J2PENN8I",
            "day": 7,
            "is_used": false,
            "used_at": null
        }
    ],
    "state": "success",
    "time": "81ms",
    "usage": "6.75Mb"
}
```
---

### 查询 Key

#### 参数解释
| 参数 | 必要 | 说明 |
| ------------- | ------------- | ----- |
| key | 是 | key `string` |

##### 请求
> GET https://pop.gmcloud.io/api/key?key=BHQNR5BJHINZWCHV

##### 返回
``` json
{
    "data": {
        "key": "BHQNR5BJHINZWCHV",
        "day": 7,
        "is_used": false,
        "used_at": null
    },
    "state": "success",
    "time": "33ms",
    "usage": "6.75Mb"
}
```
---

### 静默获取 OpenID

##### 请求
> GET https://pop.gmcloud.io/api/login/wx_base?from=https://pop.gmcloud.io/abc/efg

##### 回调地址
``` json
https://pop.gmcloud.io/abc/efg?open_id=oT8ucwVxRcvAYvbmFew4jQNEunL8
```
---

### 购买激活码支付接口

#### 参数解释
| 参数 | 必要 | 说明 |
| ------------- | ------------- | ----- |
| channel | 是 | 支付渠道, 选项 `wx_pub_qr, wx_pub, alipay_qr` |
| type | 是 | 周期类型 选项 [day, week, month, year] `string` |
| time | 否 | 周期次数 默认 1 `int` |

##### Channel 对应第三参数
| 渠道 | 参数 | 说明 |
| ------------- | ------------- | ----- |
| wx_pub | open_id | 微信 OpenID |


##### 请求
> GET https://pop.gmcloud.io/api/buy?channel=wx_pub&type=day&time=3&open_id=oT8ucwVxRcvAYvbmFew4jQNEunL8

##### 返回
``` json
{
    "data": {
        "id": "ch_G0WbfLXTiDaHzzTmz1j9ijHS",
        "object": "charge",
        "created": 1474018193,
        "livemode": false,
        "paid": false,
        "refunded": false,
        "app": "app_XPyrXL0afDe1b588",
        "channel": "wx_pub",
        "order_no": "201609161730212539",
        "client_ip": "114.88.227.138",
        "amount": 6,
        "amount_settle": 6,
        "currency": "cny",
        "subject": "GMCloud 棒棒糖贩卖机 VIP 3 日卡激活码",
        "body": "GMCloud 棒棒糖贩卖机 VIP 3 日卡激活码",
        "extra": {
            "open_id": "oT8ucwVxRcvAYvbmFew4jQNEunL8"
        },
        "time_paid": null,
        "time_expire": 1474025393,
        "time_settle": null,
        "transaction_no": null,
        "refunds": {
            "object": "list",
            "url": "/v1/charges/ch_G0WbfLXTiDaHzzTmz1j9ijHS/refunds",
            "has_more": false,
            "data": [
            ]
        },
        "amount_refunded": 0,
        "failure_code": null,
        "failure_msg": null,
        "metadata": {
        },
        "credential": {
            "object": "credential",
            "wx_pub": {
                "appId": "wxs8sgocvd0cupcs8m",
                "timeStamp": 1474018193,
                "nonceStr": "fccfb3170fda9760a0f4a0e31b391730",
                "package": "prepay_id=11010000001609160idswdvtosa9hyfh",
                "signType": "MD5",
                "paySign": "CA6BBBDC48216B0805B4F88CD0397719"
            }
        },
        "description": null
    },
    "state": "success",
    "time": "221ms",
    "usage": "6.50Mb"
}
```
---

### 查询订单状态

#### 参数解释
| 参数 | 必要 | 说明 |
| ------------- | ------------- | ----- |
| charge_id | 是 | ChargeID |
| force | 否 | 主动查询 默认 0 (防止未接收到 Webhook 而主动查询接口, 建议轮询每五次, 触发一次主动查询) `int` |

##### 请求
> GET https://pop.gmcloud.io/api/retrieve/{:charge_id}?force=1

##### 返回 - 订单支付成功, 未消费(返回 Key, 状态变更未已消费)
``` json
{
    "data": "HJVDHUJJWKQX2ZBV",
    "state": "success",
    "message": "订单支付成功",
    "time": "52ms",
    "usage": "6.25Mb"
}
```

##### 返回 - 订单支付成功, 已消费(不返回 Key)
``` json
{
    "state": "info",
    "message": "订单已被消费",
    "time": "65ms",
    "usage": "6.00Mb"
}
```

##### 返回 - 订单未支付
``` json
{
    "state": "info",
    "message": "订单未支付",
    "time": "33ms",
    "usage": "6.25Mb"
}
```

##### 返回 - 订单支付超时
``` json
{
    "state": "info",
    "message": "订单支付超时",
    "time": "38ms",
    "usage": "6.25Mb"
}
```
---

### 统计数据

##### 请求
> GET https://pop.gmcloud.io/api/stats

#### 返回解释
| 参数 | 说明 |
| ------------- | ----- |
| user | 用户总数 |
| pop | 棒棒糖获取总数 |
| touch | 任务执行总数 |

##### 返回
``` json
{
    "data": {
        "user": 101,
        "pop": 1210,
        "touch": 636
    },
    "state": "success",
    "time": "29ms",
    "usage": "7.00Mb"
}
```
---

### 直接充值接口

#### 参数解释
| 参数 | 必要 | 说明 |
| ------------- | ------------- | ----- |
| channel | 是 | 支付渠道, 选项 `wx_pub_qr, wx_pub, alipay_qr` |
| type | 是 | 周期类型 选项 [day, week, month, year] `string` |
| time | 否 | 周期次数 默认 1 `int` |
| url | 是 | 用户邀请链接 `string` |
| open_id | 否 | 微信中必要 `string` |

##### 请求
> GET https://pop.gmcloud.io/api/buy/url?channel=wx_pub&type=day&time=3&url=http://t.cn/RtE6YdU&open_id=oT8ucwVxRcvAYvbmFew4jQNEunL8

##### 返回
``` json
{
    "data": {
        "charge": {
            "id": "ch_WnTCO4qDe5mHKOC8qPO4GafH",
            "object": "charge",
            "created": 1476695111,
            "livemode": true,
            "paid": false,
            "refunded": false,
            "app": "app_XPyrXL0afDe1b588",
            "channel": "wx_pub_qr",
            "order_no": "201610171706359754",
            "client_ip": "180.168.5.158",
            "amount": 13,
            "amount_settle": 13,
            "currency": "cny",
            "subject": "GMCloud 棒棒糖贩卖机 VIP 充值 1 周",
            "body": "GMCloud 棒棒糖贩卖机 VIP 充值 1 周",
            "extra": {
                "product_id": "6lnhSExr3p"
            },
            "time_paid": null,
            "time_expire": 1476702311,
            "time_settle": null,
            "transaction_no": null,
            "refunds": {
                "object": "list",
                "url": "/v1/charges/ch_WnTCO4qDe5mHKOC8qPO4GafH/refunds",
                "has_more": false,
                "data": [
                ]
            },
            "amount_refunded": 0,
            "failure_code": null,
            "failure_msg": null,
            "metadata": {
                "type": "week",
                "time": 1,
                "url": "http://t.cn/RtE6YdU",
                "open_id": null,
                "productType": "url"
            },
            "credential": {
                "object": "credential",
                "wx_pub_qr": "weixin://wxpay/bizpayurl?pr=kW7d8uK"
            },
            "description": null
        },
        "qr": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAM4AAADOAQMAAABM0a+HAAAABlBMVEX///8AAABVwtN+AAAACXBIWXMAAA7EAAAOxAGVKw4bAAACQ0lEQVRYhc2YPY7rMAyEabhQqSPoJvbFjNiBLybfREdQqUIwd4byw8v2C0UKksJfCoY/w1FE/voExZFwhENmfeOleXF8lvoihgIke968GhIXRVxn5LQCb6H6w/MLq97uUh0NBU27HjIh9AnBfwnNeqY5i38T+DgYalVWhL4h+HdeZPndAH0Q+3tOWzjRbO/88tfvceiE7LREcfRuH338lIEBkFO22oYMznnXWxbM5lV6o+CSpjntiXHeIp75Q4xlHEQNreHUyngBbn812BUhFGu5hMd5Rxb5GO1W+iPGaPEd/vY3lUoje34QJGFOFWKumEqIuTKDQK4vMn2a9b+YL/i8NBYZBjnrdqior8jgAXHQshZh8B0R3iglIJCw2VhMkVU7I03s+VNPpQd4Icro7pEQ5rGaOai0c1i+dCm3i52RiYN1VG4vkQmFtHEYA1nHM3yqwwsqGv1VprZueiJsPZydo0ffm9c8FfiU0hmho0wBYHAZoyzcx2WVYRAOhXyjlDOH5ip5demNUEzIQqJHUbNzVKiBEA0nslh5ocvcd8oqy9p6vh9CDnELOAI19CWT3VP0WTf9ED0KpRyRnpaqydZvbCo6AmIO0fOHVZWTSSHXx1z1Q3Z4C6AHYA4h5maXhkFP8Fw4yCF6nnbz+vxdfZBYw6GIWp8bAO1SdJ1R+ztCwuFPxHdQAVjKpvPDINq5M8+ZHuXxvfIVdGLotmbnPsztGKhVuc0kzeYKn7I0Me+JmLG29c52vQVay/pvHL6P/vr8AEGDF47AeyyaAAAAAElFTkSuQmCC"
    },
    "state": "success",
    "time": "489ms",
    "usage": "7.50Mb"
}
```
---

### 查询邀请代点卡列表

##### 请求
> GET pop.gmcloud.io/api/reward?open_id=oT8ucwTj_T1Px4Nyky8-8-J1QEQI

#### 返回解释
| 参数 | 说明 |
| ------------- | ----- |
| open_id | 用户 Open Id |

##### 返回
```json
{
    "data": [
        {
            "key": "YGBD4Q1DASMFDAHC",
            "day": 31,
            "is_used": false,
            "used_at": null
        },
        {
            "key": "UGMV6DIWJ7Y5SWW5",
            "day": 31,
            "is_used": false,
            "used_at": null
        }
    ],
    "state": "success",
    "time": "21ms",
    "usage": "6.00Mb"
}
```
---

### 查询邀请二维码链接

##### 请求
> GET https://pop.gmcloud.io/api/invite?code=c2hswW

#### 返回解释
| 参数 | 说明 |
| ------------- | ----- |
| code | 用户邀请码 |

##### 返回
```json
{
    "data": "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQHx7joAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL01EcHZmY1BsdFJ2TWpOVFBqeGFMAAIEf04HWAMEAAAAAA==",
    "state": "success",
    "time": "26ms",
    "usage": "6.75Mb"
}
```
---