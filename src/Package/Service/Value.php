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
        foreach($input['array'] as $nr => $char){
            if(
                in_array(
                    $char, [
                        null,
                        " ",
                        "\t",
                        "\n",
                        "\r"
                ],
                    true)
            ){
                if($value){
                    $value = Value::basic($object, $value, $flags, $options);
                    $value = '';

                }
                $value = '';
                continue;
            }
            elseif(is_array($char)){
                $value = '';
            } else {
                $value .= $char;
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