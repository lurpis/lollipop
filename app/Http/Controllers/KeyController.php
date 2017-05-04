<?php
/**
 * Create by lurrpis
 * Date 16/9/7 下午10:13
 * Blog lurrpis.com
 */

namespace App\Http\Controllers;

use App\Http\Response\Status;
use App\Key;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;

class KeyController extends Controller
{
    public function index(Request $request)
    {
        /**
         * @var Collection $keys
         */
        $type = $request->input('type');
        $time = $request->input('time') ?: 1;
        $batch = $request->input('batch') ?: 0;

        if (!in_array($type, Key::TYPE)) {
            return self::response(self::chooseIn(Key::TYPE), Status::WARING_PARAM);
        }

        $keys = Key::getByDay(Key::typeToDay($type, $time));

        if (!$keys->isEmpty()) {
            $keys = $batch ? $keys->maps() : collect($keys->random()->maps())->merge(['sku' => $keys->count()]);

            return self::response($keys);
        }

        return self::response();
    }

    public function store(Request $request)
    {
        $type = $request->input('type');
        $time = $request->input('time') ?: 1;
        $number = $request->input('number') ?: 1;

        if (!in_array($type, Key::TYPE)) {
            return self::response(self::chooseIn(Key::TYPE), Status::WARING_PARAM);
        }

        $keys = [];
        for ($i = 0; $i < $number; $i++) {
            $keys[] = [
                'pk_key'     => Key::generateKey(),
                'pk_day'     => Key::typeToDay($type, $time),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
        }

        if (Key::insert($keys)) {
            return self::response($keys, Status::KEY_CREATE_SUCCESS);
        }

        return self::responseCode(Status::KEY_CREATE_FAILED);
    }

    public function create(Request $request)
    {
        $type = $request->input('type');
        $time = $request->input('time') ?: 1;
        $number = $request->input('number') ?: 1;

        if (!in_array($type, Key::TYPE)) {
            return self::response(self::chooseIn(Key::TYPE), Status::WARING_PARAM);
        }

        $keys = [];
        for ($i = 0; $i < $number; $i++) {
            $keys[] = [
                'pk_key'     => Key::generateKey(),
                'pk_day'     => Key::typeToDay($type, $time),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
        }

        if (Key::insert($keys)) {
            $format = '';
            foreach ($keys as $key => $value) {
                $format .= $value['pk_key'] . "<br />";
            }

            echo $format;exit;
        }

        return self::responseCode(Status::KEY_CREATE_FAILED);
    }

    public function show(Request $request)
    {
        $key = $request->input('key');

        $key = Key::findOrFail($key)->maps();

        return self::response($key);
    }
}