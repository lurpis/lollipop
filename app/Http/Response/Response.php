<?php
/**
 * @author lurrpis
 * @date 16/8/15 下午4:17
 * @blog http://lurrpis.com
 */

namespace App\Http\Response;

use Illuminate\Http\Response as BaseResponse;

class Response extends BaseResponse
{
    protected function morphToJson($content)
    {
        if ($content instanceof Jsonable) {
            return $content->toJson();
        }

        return json_encode($content, JSON_UNESCAPED_UNICODE);
    }
}