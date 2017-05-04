<?php
/**
 * Create by lurrpis
 * Date 16/9/11 下午3:05
 * Blog lurrpis.com
 */

if (!function_exists('route_parameter')) {
    /**
     * Get a given parameter from the route.
     *
     * @param $name
     * @param null $default
     * @return mixed
     */
    function route_parameter($param, $default = null)
    {
        $route = app('request')->route();

        return array_get($route[2], $param, $default);
    }
}