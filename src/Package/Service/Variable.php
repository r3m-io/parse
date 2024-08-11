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
        $has_name = false;
        $name = '';
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
                $name = '$';
                for($i = $is_variable + 1; $i < $count; $i++){
                    if(
                        array_key_exists($i, $input['array']) &&
                        is_array($input['array'][$i])
                    ){
                        if(array_key_exists('execute', $input['array'][$i])){
                            $current = $input['array'][$i]['execute'] ?? null;
                        }
                        if(array_key_exists('tag', $input['array'][$i])){
                            $current = $input['array'][$i]['tag'] ?? null;
                        }
                        elseif(array_key_exists('value', $input['array'][$i])){
                            $current = $input['array'][$i]['value'] ?? null;
                        } else {
                            $current = null;
                        }
                    } else {
                        $current = $input['array'][$i] ?? null;
                    }
                    if(
                        in_array(
                            $current,
                            [
                                ' ',
                                "\t",
                                "\n",
                                "\r"
                            ],
                            true
                        ) ||
                        (
                            is_array($input['array'][$i]) &&
                            array_key_exists('type', $input['array'][$i]) &&
                            $input['array'][$i]['type'] === 'symbol' &&
                            !in_array(
                                $current,
                                [
                                    '.',
                                    ':',
                                    '_',
                                ],
                                true
                            )
                        )
                    ){
                        if($name !== ''){
                            $has_name = true;
                            $is_reference = false;
                            if ($previous === '&') {
                                $is_reference = true;
                                $input['array'][$is_variable - 1] = null;
                            }
                            $input['array'][$is_variable] = [
                                'type' => 'variable',
                                'tag' => $name,
                                'name' => substr($name, 1),
                                'is_reference' => $is_reference
                            ];
                            $name = '';
                            $has_name = false;
                            for($j = $is_variable + 1; $j < $i; $j++){
                                $input['array'][$j] = null;
                            }
                            $is_variable = false;
                            break;
                        }
                    }
                    elseif($has_name === false){
                        $name .= $current;
                    }
                }
            }
        }
        return $input;
    }

    public static function modifier(App $object, $input, $flags, $options): array
    {
        $count = count($input['array']);
        $set_depth = 0;
        foreach($input['array'] as $nr => $char) {
            $previous = Parse::item($input, $nr - 1);
            $next = Parse::item($input, $nr + 1);
            $current = Parse::item($input, $nr);
            if($current === '('){
                $set_depth++;
            }
            elseif($current === ')'){
                $set_depth--;
            }
            elseif(
                $current !== null &&
                is_array($char) &&
                $char['type'] === 'variable'
            ){
                for($i = $nr + 1; $i < $count; $i++){
                    $previous = Parse::item($input, $i - 1);
                    $next = Parse::item($input, $i + 1);
                    $current = Parse::item($input, $i);
                    if(
                        $current === '|' &&
                        $previous !== '|' &&
                        $next !== '|'
                    ){
                        $is_modifier = $i;
                        $modifier_name = '';
                        $is_double_quote = false;
                        $is_single_quote = false;
                        for($i = $is_modifier + 1; $i < $count; $i++){
                            $previous = Parse::item($input, $i - 1);
                            $next = Parse::item($input, $i + 1);
                            $current = Parse::item($input, $i);
                            if(
                                $current === '"' &&
                                $previous !== '\\' &&
                                $is_double_quote === false
                            ){
                                $is_double_quote = true;
                            }
                            elseif(
                                $current === '"' &&
                                $previous !== '\\' &&
                                $is_double_quote === true
                            ){
                                $is_double_quote = false;
                            }
                            elseif(
                                $current === '\'' &&
                                $previous !== '\\' &&
                                $is_single_quote === false
                            ){
                                $is_single_quote = true;
                            }
                            elseif(
                                $current === '\'' &&
                                $previous !== '\\' &&
                                $is_single_quote === true
                            ){
                                $is_single_quote = false;
                            }
                            if(
                                $current === '(' &&
                                $is_single_quote === false &&
                                $is_double_quote === false
                            ){
                                $set_depth++;
                            }
                            elseif(
                                $current === ')' &&
                                $is_single_quote === false &&
                                $is_double_quote === false
                            ){
                                $set_depth--;
                            }
                            d($current);
                            if(
                                $is_single_quote === false &&
                                $is_double_quote === false &&
                                (
                                    in_array(
                                        $current,
                                        [
                                            ' ',
                                            "\t",
                                            "\n",
                                            "\r",
                                            "}}"
                                        ],
                                        true
                                    ) ||
                                    (
                                        $current === '|' &&
                                        $previous !== '|' &&
                                        $next !== '|'
                                    ) ||
                                    (
                                        $current === ':' &&
                                        $previous !== ':' &&
                                        $next !== ':'
                                    )
                                )
                            ){
                                break;
                            }
                            elseif($set_depth <= 0){
                                break;
                            }

                            else {
                                $modifier_name .= $current;
                            }
                        }
                        if($modifier_name){
                            $argument_list = [];
                            $argument_array = [];
                            $argument = '';
                            $argument_nr = 0;
                            $is_double_quote = false;
                            $is_single_quote = false;
                            $current = Parse::item($input, $i);
                            d($current);
                            for($j = $i + 1; $j < $count; $j++){
                                $previous = Parse::item($input, $j - 1);
                                $next = Parse::item($input, $j + 1);
                                $current = Parse::item($input, $j);
                                if(
                                    $current === '"' &&
                                    $previous !== '\\' &&
                                    $is_double_quote === false
                                ){
                                    $is_double_quote = true;
                                }
                                elseif(
                                    $current === '"' &&
                                    $previous !== '\\' &&
                                    $is_double_quote === true
                                ){
                                    $is_double_quote = false;
                                }
                                elseif(
                                    $current === '\'' &&
                                    $previous !== '\\' &&
                                    $is_single_quote === false
                                ){
                                    $is_single_quote = true;
                                }
                                elseif(
                                    $current === '\'' &&
                                    $previous !== '\\' &&
                                    $is_single_quote === true
                                ){
                                    $is_single_quote = false;
                                }
                                if(
                                    $current === '(' &&
                                    $is_single_quote === false &&
                                    $is_double_quote === false
                                ){
                                    $set_depth++;
                                }
                                elseif(
                                    $current === ')' &&
                                    $is_single_quote === false &&
                                    $is_double_quote === false
                                ){
                                    $set_depth--;
                                }
                                if(
                                    $current === ':' &&
                                    $is_double_quote === false &&
                                    $is_single_quote === false
                                ){
                                    $argument_list[$argument_nr]['string'] = $argument;
                                    $argument_list[$argument_nr]['array'] = $argument_array;
                                    $argument_array = [];
                                    $argument = '';
                                    $argument_nr++;
                                }
                                elseif(
                                    $current === '|' &&
                                    $is_single_quote === false &&
                                    $is_double_quote === false &&
                                    $previous !== '|' &&
                                    $next !== '|'
                                ){
                                    break;
                                }
                                elseif(
                                    $current === ')' &&
                                    $is_single_quote === false &&
                                    $is_double_quote === false &&
                                    $set_depth <= 0
                                ){
                                    break;
                                }
                                else {
                                    $argument .= $current;
                                    $argument_array[] = $input['array'][$j];
                                }
                            }
                            if(array_key_exists(0, $argument_array)){
                                $argument_list[$argument_nr]['string'] = $argument;
                                $argument_list[$argument_nr]['array'] = $argument_array;
                            }
                            if(!array_key_exists('modifier', $input['array'][$nr])){
                                $input['array'][$nr]['modifier'] = [];
                            }
                            $input['array'][$nr]['modifier'][] = [
                                'name' => $modifier_name,
                                'arguments' => $argument_list
                            ];
                            $modifier_name = '';
                            $argument = '';
                            $argument_list = [];
                            $argument_array = [];
                            for($k = $nr + 1; $k < $j; $k++){
                                $input['array'][$k] = null;
                            }
                            $is_modifier = false;
                        }
                    }
                }

                d($input['array']);
                ddd($char);
            }
        }


        return $input;
    }

}