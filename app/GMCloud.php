<?php
/**
 * Create by lurrpis
 * Date 16/9/7 下午11:42
 * Blog lurrpis.com
 */

namespace App;

use App\Collection\GMCloudCollection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class GMCloud
 * @package App
 *
 * @method static static|null findOrFail($primaryKey)
 * @method static static|null find($primaryKey)
 */
abstract class GMCloud extends Model
{
    public $incrementing = false;

    public function primaryKey()
    {
        return $this->primaryKey;
    }

    public function newCollection(array $models = [])
    {
        return new GMCloudCollection($models);
    }

    public function maps()
    {
        if (isset($this->maps)) {
            foreach ($this->maps as $key => $value) {
                $this->$value = $this->$key;
                unset($this->$key);
            }
        }

        return $this;
    }
}