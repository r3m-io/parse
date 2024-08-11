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
        foreach($input['array'] as $nr => $char) {
            if (
                array_key_exists($nr - 1, $input['array']) &&
                is_array($input['array'][$nr - 1])
            ) {
                if (array_key_exists('execute', $input['array'][$nr - 1])) {
                    $previous = $input['array'][$nr - 1]['execute'] ?? null;
                }
                if (array_key_exists('tag', $input['array'][$nr - 1])) {
                    $previous = $input['array'][$nr - 1]['tag'] ?? null;
                } elseif (array_key_exists('value', $input['array'][$nr - 1])) {
                    $previous = $input['array'][$nr - 1]['value'] ?? null;
                } else {
                    $previous = null;
                }
            } else {
                $previous = $input['array'][$nr - 1] ?? null;
            }
            if (
                array_key_exists($nr + 1, $input['array']) &&
                is_array($input['array'][$nr + 1])
            ) {
                if (array_key_exists('execute', $input['array'][$nr + 1])) {
                    $next = $input['array'][$nr + 1]['execute'] ?? null;
                }
                if (array_key_exists('tag', $input['array'][$nr + 1])) {
                    $next = $input['array'][$nr + 1]['tag'] ?? null;
                } elseif (array_key_exists('value', $input['array'][$nr + 1])) {
                    $next = $input['array'][$nr + 1]['value'] ?? null;
                } else {
                    $next = null;
                }
            } else {
                $next = $input['array'][$nr + 1] ?? null;
            }
            if (
                array_key_exists($nr, $input['array']) &&
                is_array($input['array'][$nr])
            ) {
                if (array_key_exists('execute', $input['array'][$nr])) {
                    $current = $input['array'][$nr]['execute'] ?? null;
                }
                if (array_key_exists('tag', $input['array'][$nr])) {
                    $current = $input['array'][$nr]['tag'] ?? null;
                } elseif (array_key_exists('value', $input['array'][$nr])) {
                    $current = $input['array'][$nr]['value'] ?? null;
                } else {
                    $current = null;
                }
            } else {
                $current = $input['array'][$nr] ?? null;
            }
            if(is_array($char) && $char['type'] === 'variable'){
                for($i = $nr + 1; $i < $count; $i++){
                    if (
                        array_key_exists($i - 1, $input['array']) &&
                        is_array($input['array'][$i - 1])
                    ) {
                        if (array_key_exists('execute', $input['array'][$i - 1])) {
                            $previous = $input['array'][$i - 1]['execute'] ?? null;
                        }
                        if (array_key_exists('tag', $input['array'][$i - 1])) {
                            $previous = $input['array'][$i - 1]['tag'] ?? null;
                        } elseif (array_key_exists('value', $input['array'][$i - 1])) {
                            $previous = $input['array'][$i - 1]['value'] ?? null;
                        } else {
                            $previous = null;
                        }
                    } else {
                        $previous = $input['array'][$i - 1] ?? null;
                    }
                    if (
                        array_key_exists($i + 1, $input['array']) &&
                        is_array($input['array'][$i + 1])
                    ) {
                        if (array_key_exists('execute', $input['array'][$i + 1])) {
                            $next = $input['array'][$i + 1]['execute'] ?? null;
                        }
                        if (array_key_exists('tag', $input['array'][$i + 1])) {
                            $next = $input['array'][$i + 1]['tag'] ?? null;
                        } elseif (array_key_exists('value', $input['array'][$i + 1])) {
                            $next = $input['array'][$i + 1]['value'] ?? null;
                        } else {
                            $next = null;
                        }
                    } else {
                        $next = $input['array'][$i + 1] ?? null;
                    }
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
                        $current === '|' &&
                        $previous !== '|' &&
                        $next !== '|'
                    ){
                        $is_modifier = $i;
                        $modifier_name = '';
                        for($i = $is_modifier + 1; $i < $count; $i++){
                            if (
                                array_key_exists($i - 1, $input['array']) &&
                                is_array($input['array'][$i - 1])
                            ) {
                                if (array_key_exists('execute', $input['array'][$i - 1])) {
                                    $previous = $input['array'][$i - 1]['execute'] ?? null;
                                }
                                if (array_key_exists('tag', $input['array'][$i - 1])) {
                                    $previous = $input['array'][$i - 1]['tag'] ?? null;
                                } elseif (array_key_exists('value', $input['array'][$i - 1])) {
                                    $previous = $input['array'][$i - 1]['value'] ?? null;
                                } else {
                                    $previous = null;
                                }
                            } else {
                                $previous = $input['array'][$i - 1] ?? null;
                            }
                            if (
                                array_key_exists($i + 1, $input['array']) &&
                                is_array($input['array'][$i + 1])
                            ) {
                                if (array_key_exists('execute', $input['array'][$i + 1])) {
                                    $next = $input['array'][$i + 1]['execute'] ?? null;
                                }
                                if (array_key_exists('tag', $input['array'][$i + 1])) {
                                    $next = $input['array'][$i + 1]['tag'] ?? null;
                                } elseif (array_key_exists('value', $input['array'][$i + 1])) {
                                    $next = $input['array'][$i + 1]['value'] ?? null;
                                } else {
                                    $next = null;
                                }
                            } else {
                                $next = $input['array'][$i + 1] ?? null;
                            }
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
                            ){
                                break;
                            } else {
                                $modifier_name .= $current;
                            }
                        }
                        d($i);
                        ddd($modifier_name);



                        /*
                        $input['array'][$nr] = [
                            'type' => 'variable',
                            'tag' => $char['tag'],
                            'name' => $char['name'],
                            'is_reference' => $char['is_reference'],
                            'modifier' => 'function'
                        ];
                        break;
                        */
                    }
                }

                d($input['array']);
                ddd($char);
            }
        }


        return $input;
    }

}