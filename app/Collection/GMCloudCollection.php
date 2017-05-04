<?php
/**
 * Create by lurrpis
 * Date 16/9/7 下午11:41
 * Blog lurrpis.com
 */

namespace App\Collection;

use Illuminate\Database\Eloquent\Collection;

class GMCloudCollection extends Collection {

    public function setAppends(array $appends) {
        foreach($this->items as &$item) $item->setAppends($appends);

        return $this;
    }

    public function maps() {
        foreach($this->items as &$item) $item->maps();

        return $this;
    }
}