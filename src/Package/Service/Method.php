<?php
namespace Package\R3m\Io\Parse\Service;

use R3m\Io\App;

use R3m\Io\Module\Core;
use R3m\Io\Module\File;

use Exception;
class Method
{
    public static function define(App $object, $flags, $options, $input=[]): array
    {
        if(!is_array($input)){
            return $input;
        }
        if(array_key_exists('array', $input) === false){
            return $input;
        }
        $has_name = false;
        $name = false;
        $is_method = false;
        $set_depth = 0;
        $is_single_quote = false;
        $is_double_quote = false;
        $argument = '';
        $argument_array = [];
        $argument_list = [];
        foreach($input['array'] as $nr => $char){
            $previous_nr = $nr - 1;
            if($previous_nr < 0){
                $previous = null;
            } else {
                $previous = $input['array'][$previous_nr];
                if(is_array($previous)){
                    if(array_key_exists('execute', $previous)){
                        $previous = $previous['execute'];
                    }
                    elseif(array_key_exists('value', $previous)){
                        $previous = $previous['value'];
                    } else {
                        $previous = null;
                    }
                }
            }
            $next_nr = $nr + 1;
            $next = $input['array'][$next_nr] ?? null;
            if(is_array($next)){
                if(array_key_exists('execute', $next)){
                    $next = $next['execute'];
                }
                elseif(array_key_exists('value', $next)){
                    $next = $next['value'];
                } else {
                    $next = null;
                }
            }
            if(
                is_array($char) &&
                array_key_exists('value', $char) &&
                $char['value'] === '(' &&
                $is_method === false
            ){
                $name = '';
                $is_method = $nr;
                for($i = $nr - 1; $i >= 0; $i--){
                    if($input['array'][$i] !== null){
                        if(is_array($input['array'][$i])){
                            if(
                                array_key_exists('value', $input['array'][$i]) &&
                                in_array(
                                    $input['array'][$i]['value'],
                                    [
                                        '.',
                                        '_',
                                        ':'
                                    ]
                                )
                            ){
                                $name .= $input['array'][$i]['value'];
                            } else {
                                break;
                            }
                        } else {
                            if(
                                in_array(
                                    $input['array'][$i],
                                    [
                                        null,
                                        ' ',
                                        "\n",
                                        "\r",
                                        "\t"
                                    ]
                                ) &&
                                $is_single_quote === false &&
                                $is_double_quote === false
                            ){
                                break;
                            } else {
                                $name .= $input['array'][$i];
                            }
                        }
                    }
                }
                if($name && $has_name === false){
                    if(substr($name, 0, 1) === ':'){
                        //modifier with argument set
                        $name = '';
                        $is_method = false;
                    } else {
                        $name = strrev($name);
                        $has_name = true;
                    }
                }
            }
            if(
                $is_method !== false &&
                $name &&
                $has_name === true
            ){
                if(
                    is_array($char) &&
                    array_key_exists('value', $char) &&
                    $char['value'] === '('
                ) {
                    $set_depth++;
                    if($set_depth !== 1){
                        $argument_array[] = $char;
                        $argument .= $char['value'];
                    }
                }
                elseif(
                    is_array($char) &&
                    array_key_exists('value', $char) &&
                    $char['value'] === ')'
                ){
                    $set_depth--;
                    if($set_depth !== 0){
                        $argument_array[] = $char;
                        $argument .= $char['value'];
                    } else{
                        if(array_key_exists(0, $argument_array)){
                            $argument_value = Cast::define(
                                $object,
                                $flags,
                                $options,
                                [
                                    'string' => $argument,
                                    'array' => $argument_array
                                ]
                            );
                            $argument_value = Token::value(
                                $object,
                                $flags,
                                $options,
                                $argument_value
                            );
                            $argument_list[] = $argument_value;
                            $argument_array = [];
                            $argument = '';
                        }
                        $input['array'][$is_method]['method'] = [
                            'name' => $name,
                            'argument' => $argument_list
                        ];
                        $input['array'][$is_method]['type'] = 'method';
                        unset($input['array'][$is_method]['value']);
                        $argument_list = [];
                        $argument_array = [];
                        $argument = '';
                        for($i = $is_method - 1; $i >= 0; $i--){
                            if(
                                !is_array($input['array'][$i]) &&
                                in_array(
                                    $input['array'][$i],
                                    [
                                        null,
                                        ' ',
                                        "\n",
                                        "\r",
                                        "\t",
                                    ]
                                ) &&
                                $is_single_quote === false &&
                                $is_double_quote === false
                            ){
                                break;
                            }
                            elseif(is_array($input['array'][$i])){
                                if(
                                    array_key_exists('value', $input['array'][$i]) &&
                                    in_array(
                                        $input['array'][$i]['value'],
                                        [
                                            '.',
                                            '_',
                                            ':',
                                        ]
                                    )
                                ){
                                    $input['array'][$i] = null;
                                }
                                elseif(
                                    $input['array'][$i]['value'] === '|' &&
                                    $previous !== '|' &&
                                    $next !== '|' &&
                                    $is_single_quote === false &&
                                    $is_double_quote === false
                                ){
                                    break;
                                }
                            } else {
                                $input['array'][$i] = null;
                            }
                        }
                        for($i = $is_method + 1; $i <= $nr; $i++){
                            $input['array'][$i] = null;
                        }
                        $is_method = false;
                        $has_name = false;
                    }
                }
                elseif($set_depth > 0){
                    if(
                        is_array($char) &&
                        array_key_exists('value', $char) &&
                        $char['value'] === '\'' &&
                        $previous !== '\\' &&
                        $is_single_quote === false &&
                        $is_double_quote === false
                    ){
                        $is_single_quote = true;
                        $argument_array[] = $char;
                        $argument .= $char['value'];
                    }
                    elseif(
                        is_array($char) &&
                        array_key_exists('value', $char) &&
                        $char['value'] === '\'' &&
                        $previous !== '\\' &&
                        $is_single_quote === true &&
                        $is_double_quote === false
                    ){
                        $is_single_quote = false;
                        $argument_array[] = $char;
                        $argument .= $char['value'];
                    }
                    elseif(
                        is_array($char) &&
                        array_key_exists('value', $char) &&
                        $char['value'] === '"' &&
                        $previous !== '\\' &&
                        $is_single_quote === false &&
                        $is_double_quote === false
                    ){
                        $is_double_quote = true;
                        $argument_array[] = $char;
                        $argument .= $char['value'];
                    }
                    elseif(
                        is_array($char) &&
                        array_key_exists('value', $char) &&
                        $char['value'] === '"' &&
                        $previous !== '\\' &&
                        $is_single_quote === false &&
                        $is_double_quote === true
                    ){
                        $is_double_quote = false;
                        $argument_array[] = $char;
                        $argument .= $char['value'];
                    }
                    elseif(
                        is_array($char) &&
                        array_key_exists('value', $char) &&
                        $char['value'] === ',' &&
                        $set_depth === 1 &&
                        $is_single_quote === false &&
                        $is_double_quote === false
                    ){
                        if(array_key_exists(0, $argument_array)){
                            $argument_value = Cast::define(
                                $object,
                                $flags,
                                $options,
                                [
                                    'string' => $argument,
                                    'array' => $argument_array
                                ]
                            );
                            $argument_value = Token::value(
                                $object,
                                $flags,
                                $options,
                                $argument_value,
                            );
                            $argument_list[] = $argument_value;
                            $argument_array = [];
                            $argument = '';
                        }
                    } else {
                        if(
                            is_string($char) &&
                            in_array(
                                $char,
                                [
                                    ' ',
                                    "\n",
                                    "\r",
                                    "\t"
                                ],
                                true
                            ) &&
                            $is_single_quote === false &&
                            $is_double_quote === false
                        ){
                            //nothing
                        } else {
                            $argument_array[] = $char;
                            if(is_array($char) && array_key_exists('value', $char)){
                                $argument .= $char['value'];
                            } else {
                                $argument .= $char;
                            }
                        }
                    }
                }
            }
        }
        return $input;
    }

    public static function block(App $object, $flags, $options, $input=[]): array
    {
        if (!is_array($input)) {
            return $input;
        }
        if (array_key_exists('array', $input) === false) {
            return $input;
        }
        $method_name = false;
        $is_block = false;
        $block_functions = [
            'if',
            'block.html',
            'block.code',
            'block.data',
            'foreach',
            'for.each',
            'for',
            'while',
            'switch'
        ];
        d($input['array']);

        foreach($block_functions as $block_function){
            foreach ($input['array'] as $nr => $char) {
                if(
                    is_array($char) &&
                    array_key_exists('type', $char) &&
                    $char['type'] === 'method'
                ){
                    $method_name = $char['method']['name'];
                    if($method_name === $block_function){
                        if($is_block === false){
                            $is_block = $nr;
                        }
                        $block_depth++;
                    }
                }
                elseif(
                    is_array($char)
                ){
                    d($char);
                }
            }
        }


        return $input;
    }
}