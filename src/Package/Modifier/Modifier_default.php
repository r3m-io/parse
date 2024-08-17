<?php
namespace Package\R3m\Io\Parse\Modifier;

use R3m\Io\App;

use R3m\Io\Module\Data;

trait Modifier_default {

    function modifier_default($value, $default=null){
        if(empty($value)){
            return $default;
        }
        return $value;
    }

}