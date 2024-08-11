<?php
namespace Package\R3m\Io\Parse\Service;

use R3m\Io\App;

use R3m\Io\Module\Core;
use R3m\Io\Module\File;

use Exception;
class Variable
{
    public static function define(App $object, $input, $flags, $options){
        $count = count($input['array']);
        $is_variable = false;
        $outer_set_depth = 0;
        $set_depth = 0;
        $curly_depth = 0;
        $array_depth = 0;
        $previous = null;
        $is_single_quoted = false;
        $is_double_quoted = false;
//        trace();
        d($input['array']);
        foreach($input['array'] as $nr => $char){
            if(
                array_key_exists($nr - 1, $input['array']) &&
                is_array($input['array'][$nr - 1])
            ){
                if(array_key_exists('execute', $input['array'][$nr - 1])){
                    $previous = $input['array'][$nr - 1]['execute'] ?? null;
                }
                if(array_key_exists('tag', $input['array'][$nr - 1])){
                    $previous = $input['array'][$nr - 1]['tag'] ?? null;
                }
                elseif(array_key_exists('value', $input['array'][$nr - 1])){
                    $previous = $input['array'][$nr - 1]['value'] ?? null;
                } else {
                    $previous = null;
                }
            } else {
                $previous = $input['array'][$nr - 1] ?? null;
            }
            if(
                array_key_exists($nr + 1, $input['array']) &&
                is_array($input['array'][$nr + 1])
            ){
                if(array_key_exists('execute', $input['array'][$nr + 1])){
                    $next = $input['array'][$nr + 1]['execute'] ?? null;
                }
                if(array_key_exists('tag', $input['array'][$nr + 1])){
                    $next = $input['array'][$nr + 1]['tag'] ?? null;
                }
                elseif(array_key_exists('value', $input['array'][$nr + 1])){
                    $next = $input['array'][$nr + 1]['value'] ?? null;
                } else {
                    $next = null;
                }
            } else {
                $next = $input['array'][$nr + 1] ?? null;
            }
            if(
                array_key_exists($nr, $input['array']) &&
                is_array($input['array'][$nr])
            ){
                if(array_key_exists('execute', $input['array'][$nr])){
                    $current = $input['array'][$nr]['execute'] ?? null;
                }
                if(array_key_exists('tag', $input['array'][$nr])){
                    $current = $input['array'][$nr]['tag'] ?? null;
                }
                elseif(array_key_exists('value', $input['array'][$nr])){
                    $current = $input['array'][$nr]['value'] ?? null;
                } else {
                    $current = null;
                }
            } else {
                $current = $input['array'][$nr] ?? null;
            }
            if($current === '$'){
                $is_variable = $nr;
                ddd($is_variable);
            }
        }
        return $input;
    }
}