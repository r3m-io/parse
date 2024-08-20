<?php
/**
 * @package Plugin\Modifier
 * @author Remco van der Velde
 * @since 2024-08-19
 * @license MIT
 * @version 1.0
 * @changeLog
 *    - all
 */
namespace Plugin;

trait Plugin_default {

    function plugin_default($value, $default=null){
        if(empty($value)){
            return $default;
        }
        return $value;
    }

}