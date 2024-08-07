<?php
namespace Package\R3m\Io\Parse\Service;

use R3m\Io\App;

use R3m\Io\Module\Core;
use R3m\Io\Module\File;

use Exception;
class Variable
{
    public static function define(App $object, $input, $flags, $options){
        $count = strlen($input['string']);
        $is_variable = false;
        $set_depth = 0;
        $curly_depth = 0;
        $array_depth = 0;
        $previous = null;
        foreach($input['array'] as $nr => $char){
            if(
                array_key_exists($nr - 1, $input['array']) &&
                is_array($input['array'][$nr - 1])
            ){
                if(array_key_exists('execute', $input['array'][$nr - 1])){
                    $previous = $input['array'][$nr - 1]['execute'] ?? null;
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
                is_array($char) &&
                array_key_exists('value', $char)
            ){
                if(
                    $char['value'] === '$' &&
                    $input['array'][$nr] !== null // null check needed
                ){
                    $is_variable = $nr;
                    $name = '$';
                    ddd($input['array']);
                    for($i = $nr + 1; $i < $count; $i++){
                        if(
                            is_array($input['array'][$i]) &&
                            array_key_exists('value', $input['array'][$i])
                        ){
                            if(
                                in_array(
                                    $input['array'][$i]['value'],
                                    [
                                        '_',
                                        '.'
                                    ]
                                )
                            ){
                                $name .= $input['array'][$i]['value'];
                            }
                            elseif(
                                $input['array'][$i]['value'] === '('
                            ){
                                $set_depth++;
                            }
                            elseif(
                                $input['array'][$i]['value'] === ')'
                            ) {
                                $set_depth--;
                                if ($set_depth < 0) {
                                    break;
                                }
                            }
                            elseif(
                                $input['array'][$i]['value'] === '['
                            ){
                                $array_depth++;
                            }
                            elseif(
                                $input['array'][$i]['value'] === ']'
                            ) {
                                $array_depth--;
                                if ($array_depth < 0) {
                                    break;
                                }
                            }
                            else {
                                break;
                            }
                        } else {
                            if(
                                !in_array(
                                    $input['array'][$i],
                                    [
                                        ' ',
                                        "\n",
                                        "\r",
                                        "\t"
                                    ]
                                )
                            ){
                                $name .= $input['array'][$i];
                            }

                        }
                    }
                    if($name){
                        $is_reference = false;
                        if($previous === '&'){
                            $is_reference = true;
                            $input['array'][$nr - 1] = null;
                        }
                        $input['array'][$is_variable] = [
                            'type' => 'variable',
                            'tag' => $name,
                            'name' => substr($name, 1),
                            'is_reference' => $is_reference
                        ];
                        $has_modifier = false;
                        $has_name = false;
                        $argument = '';
                        $argument_array = [];
                        $argument_list = [];
                        $modifier_name = '';
                        for($i = $is_variable + 1; $i < $count; $i++){
                            if(
                                array_key_exists($i - 1, $input['array']) &&
                                is_array($input['array'][$i - 1]) &&
                                array_key_exists('execute', $input['array'][$i - 1])
                            ){
                                $previous = $input['array'][$i - 1]['execute'];
                            }
                            elseif(
                                array_key_exists($i - 1, $input['array']) &&
                                is_array($input['array'][$i - 1]) &&
                                array_key_exists('value', $input['array'][$i - 1])
                            ){
                                $previous = $input['array'][$i - 1]['value'];
                            }
                            elseif(
                                array_key_exists($i - 1, $input['array']) &&
                                !is_array($input['array'][$i - 1])
                            ){
                                $previous = $char;
                            } else {
                                $previous = null;
                            }
                            if(
                                array_key_exists($i + 1, $input['array']) &&
                                is_array($input['array'][$i + 1]) &&
                                array_key_exists('execute', $input['array'][$i + 1])
                            ){
                                $next = $input['array'][$i + 1]['execute'];
                            }
                            elseif(
                                array_key_exists($i + 1, $input['array']) &&
                                is_array($input['array'][$i + 1]) &&
                                array_key_exists('value', $input['array'][$i + 1])
                            ){
                                $next = $input['array'][$i + 1]['value'];
                            }
                            elseif(
                                array_key_exists($i + 1, $input['array']) &&
                                !is_array($input['array'][$i + 1])
                            ){
                                $next = $char;
                            } else {
                                $next = null;
                            }
                            if(
                                is_array($input['array'][$i]) &&
                                array_key_exists('value', $input['array'][$i])
                            ){
                                if(
                                    in_array(
                                        $input['array'][$i]['value'],
                                        [
                                            '_',
                                            '.'
                                        ]
                                    ) &&
                                    $has_modifier === false
                                ){
                                    $input['array'][$i] = null;
                                }
                                elseif($input['array'][$i]['value'] === '('){
                                    $set_depth++;
                                    d($set_depth);
                                }
                                elseif($input['array'][$i]['value'] === ')'){
                                    $set_depth--;
                                    d($set_depth);
                                }
                                elseif($input['array'][$i]['value'] === '{{'){
                                    $curly_depth++;
                                }
                                elseif($input['array'][$i]['value'] === '}}'){
                                    $curly_depth--;
                                }
                                elseif($input['array'][$i]['value'] === '['){
                                    $array_depth++;
                                }
                                elseif($input['array'][$i]['value'] === ']'){
                                    $array_depth--;
                                }
                                elseif(
                                    $input['array'][$i]['value'] === '|' &&
                                    $previous !== '|' &&
                                    $next !== '|' &&
                                    $has_modifier === false
                                ){
                                    /**
                                     * needs:
                                     * set-depth
                                     * array-depth
                                     * curly-depth
                                     */
                                    d($argument_array);
                                    d($name);
                                    d($array_depth);
                                    d($curly_depth);
                                    ddd($set_depth);
                                    $has_modifier = true;
                                    $input['array'][$i] = null;
                                }
                                elseif($has_modifier === false) {
                                    if($input['array'][$i]['value'] === '=>'){
                                        break;
                                    }
                                    d($input['array'][$i]['value']);
                                    d($modifier_name);
                                    d($array_depth);
                                    d($curly_depth);
                                    ddd($set_depth);
                                    break;
                                }
                                elseif(
                                    $input['array'][$i]['value'] === ':' &&
                                    $previous !== ':' &&
                                    $next !== ':' &&
                                    $modifier_name && $has_name === false
                                ) {
                                    $has_name = true;
                                    $input['array'][$i] = null;
                                }
                                elseif($modifier_name){
                                    if(is_array($input['array'][$i])){
                                        if(array_key_exists('execute', $input['array'][$i])){
                                            $argument .= $input['array'][$i]['execute'];
                                            $argument_array[] = $input['array'][$i];
                                        }
                                        elseif(array_key_exists('value', $input['array'][$i])){
                                            /*
                                            if($input['array'][$i]['value'] === ')'){
                                                if($set_depth < 0){
                                                    break;
                                                }
                                            }
                                            */
                                            d($set_depth);
                                            if($set_depth >= 0){
                                                $argument .= $input['array'][$i]['value'];
                                                $argument_array[] = $input['array'][$i];
                                            }
                                        }
                                    } else {
                                        $argument .= $input['array'][$i];
                                        $argument_array[] = $input['array'][$i];
                                    }
                                    $input['array'][$i] = null;
                                }
                            }
                            elseif($has_modifier === false) {
                                $input['array'][$i] = null;
                            }
                            elseif($has_modifier === true){
                                if($has_name === false) {
                                    if(is_array($input['array'][$i])){
                                        if(array_key_exists('execute', $input['array'][$i])){
                                            $modifier_name .= $input['array'][$i]['execute'];
                                        }
                                        elseif(array_key_exists('value', $input['array'][$i])){
                                            $modifier_name .= $input['array'][$i]['value'];
                                        }
                                    }
                                    elseif(
                                        !in_array(
                                            $input['array'][$i],
                                            [
                                                ' ',
                                                "\n",
                                                "\r",
                                                "\t"
                                            ]
                                        )
                                    ){
                                        $modifier_name .= $input['array'][$i];
                                    }
                                    $input['array'][$i] = null;
                                } else {
                                    if(is_array($input['array'][$i])){
                                        if(array_key_exists('execute', $input['array'][$i])){
                                            $argument .= $input['array'][$i]['execute'];
                                        }
                                        elseif(array_key_exists('value', $input['array'][$i])){
                                            $argument .= $input['array'][$i]['value'];
                                        }
                                    } else {
                                        $argument .= $input['array'][$i];
                                    }
                                    $argument_array[] = $input['array'][$i];
                                    $input['array'][$i] = null;
                                }
                            }
                        }
                        if(array_key_exists(0, $argument_array)) {
                            $argument_value = Cast::define(
                                $object, [
                                'string' => $argument,
                                'array' => $argument_array
                            ],
                                $flags,
                                $options
                            );
                            d($argument_value);
                            $argument_value = Parse::value(
                                $object,
                                $argument_value,
                                $flags,
                                $options
                            );
                            $argument_list[] = $argument_value;
                        }
                        if($modifier_name){
                            $input['array'][$is_variable]['modifier'][] = [
                                'name' => $modifier_name,
                                'argument' => $argument_list
                            ];
                        }
                    }
                }
                elseif(
                    $input['array'][$nr]['value'] === '('
                ){
                    $set_depth++;
                }
                elseif(
                    $input['array'][$nr]['value'] === ')'
                ) {
                    $set_depth--;
                }
                elseif(
                    $input['array'][$nr]['value'] === '['
                ){
                    $array_depth++;
                }
                elseif(
                    $input['array'][$nr]['value'] === ']'
                ) {
                    $array_depth--;
                }
            }
        }
        return $input;
    }
}