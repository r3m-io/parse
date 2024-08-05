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
        d($input['string']);
        $value = '';
        $is_double_quoted = false;
        $value_nr = false;
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
                if($value){
                    $value = Value::basic($object, $value, $flags, $options);
                    $input['array'][$value_nr] = $value;
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
                    if($value){
                        $value = Value::basic($object, $value, $flags, $options);
                        $input['array'][$value_nr] = $value;
                    }
                }
                elseif($value){
                    $value = Value::basic($object, $value, $flags, $options);
                    $input['array'][$value_nr] = $value;
                }
                $value = '';
                $value_nr = false;
            }
            elseif(
                is_array($char) &&
                array_key_exists('is_method', $char)
            ){
                if($value){
                    $value = Value::basic($object, $value, $flags, $options);
                    $input['array'][$value_nr] = $value;
                }
                $value = '';
                $value_nr = false;
            }
            else {
                if(is_array($char)){
                    d($char);
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
        if($value_$nr !== false){
            $input['array'][$value_nr] = Value::basic($object, $value, $flags, $options);
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
                if(is_numeric($input)){
                    $length = strlen($input);
                    $data = mb_str_split($input, 1);
                    $is_float = false;
                    $collect = '';
                    for($i=0; $i<$length; $i++){
                        if(
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
                                    ',',
                                    '_'
                                ]
                            )
                        ){
                            $collect .= $data[$i];
                        }
                        elseif($data[$i] === '.'){
                            $collect .= $data[$i];
                            $is_float = true;
                        } else {
                            return [
                                'type' => 'string',
                                'value' => $input,
                                'execute' => $input
                            ];
                        }
                    }
                    if($is_float){
                        return [
                            'type' => 'float',
                            'value' => $input,
                            'execute' => $collect + 0
                        ];
                    } else {
                        return [
                            'type' => 'integer',
                            'value' => $input,
                            'execute' => $collect + 0
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
}