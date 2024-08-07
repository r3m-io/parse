<?php
namespace Package\R3m\Io\Parse\Service;

use R3m\Io\App;

use R3m\Io\Module\Core;
use R3m\Io\Module\File;

use Exception;
class Value
{
    public static function define(App $object, $input, $flags, $options): array
    {
        $value = '';
        $is_double_quoted = false;
        $value_nr = false;
        $array_depth = 0;
        $array_nr = false;
        $array_string = '';
        $array = [];

        d($input['array']);
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
            if(
                in_array(
                    $char, [
                        null,
                        " ",
                        "\t",
                        "\n",
                        "\r"
                    ],
                    true
                )
            ){
                if(
                    $value === 0 ||
                    $value === '0' ||
                    $value
                ){
                    $length = strlen($value);
                    $value = Value::basic($object, $value, $flags, $options);
                    $input['array'][$value_nr] = $value;
                    for($i = $value_nr; $i < $value_nr + $length; $i++){
                        if($i === $value_nr){
                            continue;
                        }
                        $input['array'][$i] = null;
                    }
                }
                if($char !== null){
                    $input['array'][$nr] = [
                        'type' => 'whitespace',
                        'value' => $char
                    ];
                }
                $value = '';
                $value_nr = false;
            }
            elseif(
                is_array($char) &&
                array_key_exists('value', $char)
            ){
                if(
                    $char['value'] === '"' &&
                    $previous !== '\\' &&
                    $is_double_quoted === false
                ){
                    $is_double_quoted = true;
                }
                elseif(
                    $char['value'] === '"' &&
                    $previous !== '\\' &&
                    $is_double_quoted === true
                ){
                    $is_double_quoted = false;
                    if(
                        $value === 0 ||
                        $value === '0' ||
                        $value
                    ){
                        $length = strlen($value);
                        $value = Value::basic($object, $value, $flags, $options);
                        $input['array'][$value_nr] = $value;
                        for($i = $value_nr; $i < $value_nr + $length; $i++){
                            if($i === $value_nr){
                                continue;
                            }
                            $input['array'][$i] = null;
                        }
                    }
                }
                elseif(
                    $value === 0 ||
                    $value === '0' ||
                    $value
                ){
                    $length = strlen($value);
                    $value = Value::basic($object, $value, $flags, $options);
                    $input['array'][$value_nr] = $value;
                    for($i = $value_nr; $i < $value_nr + $length; $i++){
                        if($i === $value_nr){
                            continue;
                        }
                        $input['array'][$i] = null;
                    }
                }
                $value = '';
                $value_nr = false;
            }
            elseif(
                is_array($char) &&
                array_key_exists('type', $char) &&
                $char['type'] === 'method'
            ){
                if(
                    $value === 0 ||
                    $value === '0' ||
                    $value
                ){
                    $length = strlen($value);
                    $value = Value::basic($object, $value, $flags, $options);
                    $input['array'][$value_nr] = $value;
                    for($i = $value_nr; $i < $value_nr + $length; $i++){
                        if($i === $value_nr){
                            continue;
                        }
                        $input['array'][$i] = null;
                    }
                }
                $value = '';
                $value_nr = false;
            }
            else {
                if(is_array($char)){
                    if(array_key_exists('execute', $char)){
                        $char = $char['execute'];
                    }
                    elseif(array_key_exists('value', $char)){
                        $char = $char['value'];
                    } else {
                        $char = null;
                    }
                }
                $value .= $char;
                if($value_nr === false){
                    $value_nr = $nr;
                }
            }
        }
        if($value_nr !== false){
            $length = strlen($value);
            $input['array'][$value_nr] = Value::basic($object, $value, $flags, $options);
            for($i = $value_nr; $i < $value_nr + $length; $i++){
                if($i === $value_nr){
                    continue;
                }
                $input['array'][$i] = null;
            }
        }
        return $input;
    }

    public static function basic(App $object, $input, $flags, $options){
        switch($input){
            case 'true':
                return [
                    'type' => 'boolean',
                    'value' => $input,
                    'execute' => true
                ];
            case 'false':
                return [
                    'type' => 'boolean',
                    'value' => $input,
                    'execute' => false
                ];
            case 'null':
                return [
                    'type' => 'null',
                    'value' => $input,
                    'execute' => null
                ];
            case '[]':
                return [
                    'type' => 'array',
                    'value' => $input,
                    'execute' => []
                ];
            case '{}':
                return [
                    'type' => 'object',
                    'value' => $input,
                    'execute' => (object) []
                ];
            default:
                $trim_input = trim($input);
                if(
                    $trim_input === '' &&
                    $trim_input !== $input
                ){
                    return [
                        'type' => 'whitespace',
                        'value' => $input,
                    ];
                }
                elseif(
                    is_numeric($input) ||
                    Core::is_hex($input)
                ){
                    $length = strlen($input);
                    $data = mb_str_split($input, 1);
                    $is_float = false;
                    $is_hex = false;
                    $collect = '';
                    for($i=0; $i<$length; $i++){
                        if(
                            (
                                in_array(
                                    $data[$i],
                                    [
                                        '0',
                                        '1',
                                        '2',
                                        '3',
                                        '4',
                                        '5',
                                        '6',
                                        '7',
                                        '8',
                                        '9',
                                    ]
                                )
                            )
                        ){
                            $collect .= $data[$i];
                        }
                        elseif(
                            (
                            in_array(
                                $data[$i],
                                [
                                    ',',
                                    '_'
                                ]
                            )
                            )
                        ){
                            //nothing
                        }
                        elseif(
                            in_array(
                                strtolower($data[$i]),
                                [
                                    'x',
                                    'a',
                                    'b',
                                    'c',
                                    'd',
                                    'e',
                                    'f'
                                ]
                            )
                        ){
                            $collect .= $data[$i];
                            $is_hex = true;
                        }
                        elseif($data[$i] === '.'){
                            $collect .= $data[$i];
                            $is_float = true;
                        } else {
                            return [
                                'type' => 'string',
                                'value' => $input,
                                'execute' => $input,
                            ];
                        }
                    }
                    if($is_hex){
                        return [
                            'type' => 'integer',
                            'value' => $input,
                            'execute' => hexdec($collect),
                        ];
                    }
                    elseif($is_float){
                        return [
                            'type' => 'float',
                            'value' => $input,
                            'execute' => $collect + 0,
                        ];
                    } else {
                        return [
                            'type' => 'integer',
                            'value' => $input,
                            'execute' => $collect + 0,
                        ];
                    }
                }
        }
        return [
            'type' => 'string',
            'value' => $input,
            'execute' => $input
        ];
    }

    public static function array(App $object, $input, $flags, $options): array
    {
        $is_single_quote = false;
        $is_double_quote = false;
        $array_depth = 0;
        $array = [];
        $array_string = '';
        $is_collect = false;
        d($input);
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
            if(
                is_array($char) &&
                array_key_exists('value', $char) &&
                $char['value'] === '\'' &&
                $is_single_quote === false &&
                $is_double_quote === false &&
                $previous !== '\\'
            ){
                $is_single_quote = true;
            }
            elseif(
                is_array($char) &&
                array_key_exists('value', $char) &&
                $char['value'] === '\'' &&
                $is_single_quote === true &&
                $is_double_quote === false &&
                $previous !== '\\'
            ){
                $is_single_quote = false;
            }
            elseif(
                is_array($char) &&
                array_key_exists('value', $char) &&
                $char['value'] === '"' &&
                $is_single_quote === false &&
                $is_double_quote === false &&
                $previous !== '\\'
            ){
                $is_double_quote = true;
            }
            elseif(
                is_array($char) &&
                array_key_exists('value', $char) &&
                $char['value'] === '"' &&
                $is_single_quote === false &&
                $is_double_quote === true &&
                $previous !== '\\'
            ){
                $is_double_quote = false;
            }
            elseif(
                $is_single_quote === false &&
                $is_double_quote === false &&
                is_array($char) &&
                array_key_exists('value', $char) &&
                $char['value'] === '['
            ){
                $array_depth++;
            }
            elseif(
                $is_single_quote === false &&
                $is_double_quote === false &&
                is_array($char) &&
                array_key_exists('value', $char) &&
                $char['value'] === ']'
            ) {
                $array_depth--;
                if($array_depth === 0){
                    d($array_string);
                    ddd($array);
                }
            }
            if($array_depth > 0){
                $array[] = $char;
            }

            /*
            elseif(
                $is_single_quote === false &&
                $is_double_quote === false &&
                is_array($char) &&
                array_key_exists('value', $char) &&
                $char['value'] === '['
            ){
                $array_depth++;
                $is_collect = true;
//                $array_string .= $char['value'];
                continue;
            }
            elseif(
                $is_single_quote === false &&
                $is_double_quote === false &&
                is_array($char) &&
                array_key_exists('value', $char) &&
                $char['value'] === ']'
            ){
                $array_depth--;
//                $array_string .= $char['value'];
                if($array_depth === 0){
                    d($array_string);
                    ddd($array);
                    $is_collect = false;
                }

            }
            if($is_collect){
                $array[] = $char;
                if(is_array($char)){
                    if(array_key_exists('execute', $char)){
                        $array_string .= $char['execute'];
                    }
                    elseif(array_key_exists('tag', $char)){
                        $array_string .= $char['tag'];
                        if(array_key_exists('modifier', $char)){
                            foreach($char['modifier'] as $modifier){
                                if(array_key_exists('string', $modifier)){
                                    $array_string .= $modifier['string'];
                                }
                            }
                        }
                    }
                    elseif(array_key_exists('value', $char)){
                        $array_string .= $char['value'];
                    }
                } else {
                    $array_string .= $char;
                }
            }
            */
        }
        return $input;
    }
}