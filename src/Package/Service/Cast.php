<?php
namespace Package\R3m\Io\Parse\Service;

use R3m\Io\App;

use R3m\Io\Module\Core;
use R3m\Io\Module\File;

use Exception;
class Cast
{
    public static function define(App $object, $input, $flags, $options): array
    {
        $is_collect = false;
        $define = '';
        foreach($input['array'] as $nr => $char){
            if(
                is_array($char) &&
                array_key_exists('value', $char) &&
                $char['value'] === '('
            ){
                $is_collect = $nr;
            }
            elseif(
                is_array($char) &&
                array_key_exists('value', $char) &&
                $char['value'] === ')'
            ){
                if(strlen($define) > 0){
                    $is_define = false;
                    switch(strtolower($define)){
                        case 'int':
                        case 'integer':
                            $input['array'][$is_collect + 1] = [
                                'value' => $define,
                                'type' => 'cast',
                                'cast' => 'integer'
                            ];
                            $is_define = true;
                        break;
                        case 'float':
                        case 'double':
                            $input['array'][$is_collect + 1] = [
                                'value' => $define,
                                'type' => 'cast',
                                'cast' => 'float'
                            ];
                            $is_define = true;
                        break;
                        case 'boolean':
                        case 'bool':
                            $input['array'][$is_collect + 1] = [
                                'value' => $define,
                                'type' => 'cast',
                                'cast' => 'boolean'
                            ];
                            $is_define = true;
                        break;
                        case 'string':
                            $input['array'][$is_collect + 1] = [
                                'value' => $define,
                                'type' => 'cast',
                                'cast' => 'string'
                            ];
                            $is_define = true;
                        break;
                        case 'array':
                            $input['array'][$is_collect + 1] = [
                                'value' => $define,
                                'type' => 'cast',
                                'cast' => 'array'
                            ];
                            $is_define = true;
                        break;
                        case 'object':
                            $input['array'][$is_collect + 1] = [
                                'value' => $define,
                                'type' => 'cast',
                                'cast' => 'object'
                            ];
                            $is_define = true;
                        break;
                        case 'clone':
                            $input['array'][$is_collect + 1] = [
                                'value' => $define,
                                'type' => 'cast',
                                'cast' => 'clone'
                            ];
                            $is_define = true;
                        break;
                    }
                    if($is_define){
                        for($i = $is_collect + 2; $i < $nr; $i++){
                            $input['array'][$i] = null;
                        }
                    }
                    $define = '';
                }
                $is_collect = false;
            }
            elseif(
                $is_collect !== false &&
                !is_array($char)
            ){
                if(
                    in_array(
                        $char,
                        [
                            ' ',
                            "\t",
                            "\n",
                            "\r",
                        ],
                        true
                    )
                ){
                    continue;
                }
                $define .= $char;
            }
            elseif(
                $is_collect !== false &&
                is_array($char)
            ){
                d($define);
                d($char);
            }
        }
        return $input;
    }
}