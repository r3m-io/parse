<?php
namespace Package\R3m\Io\Parse\Service;

use R3m\Io\App;

use R3m\Io\Module\Core;
use R3m\Io\Module\File;

use Exception;
class Value
{
    public static function define(App $object, $flags, $options, $input=[]): array
    {
        if(!is_array($input)){
            return $input;
        }
        if(array_key_exists('array', $input) === false){
            return $input;
        }
        $value = '';
        $is_double_quoted = false;
        $is_double_quoted_backslash = false;
        $value_nr = false;
        $array_depth = 0;
        $array_nr = false;
        $array_string = '';
        $array = [];
        foreach($input['array'] as $nr => $char){
            $previous = Token::item($input, $nr - 1);
            if(
                !is_array($char) &&
                in_array(
                    $char,
                    [
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
                    $value = Value::basic($object, $flags, $options, $value);
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
                        $value = Value::basic($object, $flags, $options, $value);
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
                    $char['value'] === '"' &&
                    $previous === '\\' &&
                    $is_double_quoted_backslash === false
                ){
                    $is_double_quoted_backslash = true;
                }
                elseif(
                    $char['value'] === '"' &&
                    $previous === '\\' &&
                    $is_double_quoted_backslash === true
                ){
                    $is_double_quoted_backslash = false;
                    if(
                        $value === 0 ||
                        $value === '0' ||
                        $value
                    ){
                        $length = strlen($value);
                        $value = Value::basic($object, $flags, $options, $value);
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
                    $value = Value::basic($object, $flags, $options, $value);
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
                    $value = Value::basic($object, $flags, $options, $value);
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
            $input['array'][$value_nr] = Value::basic($object, $flags, $options, $value);
            for($i = $value_nr; $i < $value_nr + $length; $i++){
                if($i === $value_nr){
                    continue;
                }
                $input['array'][$i] = null;
            }
        }
        return $input;
    }

    public static function basic(App $object, $flags, $options, $input=''){
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
                    $is_int = false;
                    $is_hex = false;
                    $is_hex_nr = false;
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
                            $is_int = true;
                            if(
                                strlen($collect) > 3 &&
                                strtoupper(substr($collect, 0, 2)) === '0X' &&
                                Core::is_hex($collect)
                            ){
                                $is_hex = true;
                            }
                        }
                        elseif(
                            (
                            in_array(
                                strtoupper($data[$i]),
                                [
                                    'X',
                                    'A',
                                    'B',
                                    'C',
                                    'D',
                                    'E',
                                    'F',
                                ]
                            )
                            )
                        ){
                            $collect .= $data[$i];
                            if(
                                strlen($collect) > 3 &&
                                strtoupper(substr($collect, 0, 2)) === '0X' &&
                                Core::is_hex($collect)
                            ){
                                $is_hex = true;
                            }
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
                        elseif($data[$i] === '.'){
                            $collect .= $data[$i];
                            $is_float = true;
                        }
                    }
                    if($is_hex){
                        return [
                            'type' => 'integer',
                            'value' => $input,
                            'is_hex' => true,
                            'execute' => hexdec(substr($collect, 2)),
                        ];
                    }
                    elseif($is_float){
                        return [
                            'type' => 'float',
                            'value' => $input,
                            'execute' => $collect + 0,
                        ];
                    }
                    elseif($is_int) {
                        return [
                            'type' => 'integer',
                            'value' => $input,
                            'execute' => $collect + 0,
                        ];
                    } else {
                        return [
                            'type' => 'string',
                            'value' => $input,
                            'execute' => '\'' . $input . '\''
                        ];
                    }
                } else {
                    return [
                        'type' => 'string',
                        'value' => $input,
                        'execute' => '\'' . $input . '\''
                    ];
                }
        }
    }

    public static function array(App $object, $flags, $options, $input=[]): array
    {
        if(!is_array($input)){
            return $input;
        }
        if(array_key_exists('array', $input) === false){
            return $input;
        }
        $is_single_quote = false;
        $is_double_quote = false;
        $array_depth = 0;
        $array = [];
        $array_nr = false;
        $array_string = '';
        foreach($input['array'] as $nr => $char){
            $previous = Token::item($input, $nr - 1);
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
                $array[] = $char;
                $array_string .= $char['value'];
                if($array_nr === false){
                    $array_nr = $nr;
                }
            }
            elseif(
                $is_single_quote === false &&
                $is_double_quote === false &&
                is_array($char) &&
                array_key_exists('value', $char) &&
                $char['value'] === ']'
            ) {
                $array_depth--;
                $array[] = $char;
                $array_string .= $char['value'];
                if($array_depth === 0){
                    $input['array'][$array_nr] = [
                        'type' => 'array',
                        'string' => $array_string,
                        'array' => $array
                    ];
                    $input['array'][$array_nr] = Token::cleanup($object, $flags, $options, $input['array'][$array_nr]);
                    for($i = $array_nr + 1; $i <= $nr; $i++){
                        $input['array'][$i] = null;
                    }
                    $array_nr = false;
                    $array_string = '';
                    $array = [];
                }
            }
            elseif($array_depth > 0){
                $array[] = $char;
                if(
                    is_array($char) &&
                    array_key_exists('execute', $char)
                ){
                    $array_string .= $char['execute'];
                }
                elseif(
                    is_array($char) &&
                    array_key_exists('tag', $char)
                ){
                    $array_string .= $char['tag'];
                    if(
                        array_key_exists('modifier', $char) &&
                        is_array($char['modifier'])
                    ){
                        foreach($char['modifier'] as $modifier){
                            if(array_key_exists('string', $modifier)){
                                $array_string .= $modifier['string'];
                            }
                        }
                    }
                }
                elseif(
                    is_array($char) &&
                    array_key_exists('value', $char)
                ){
                    $array_string .= $char['value'];
                }
            }
        }
        return $input;
    }

    public static function double_quoted_string(App $object, $flags, $options, $input=[], $with_backslash=false): array
    {
        if(!is_array($input)){
            return $input;
        }
        if(array_key_exists('array', $input) === false){
            return $input;
        }
        $is_double_quote = false;
        $tag = '';
        $tag_array = [];
        $tag_nr = false;
        $curly_depth = 0;
        $string_depth = 0;
        foreach($input['array'] as $nr => $char){
            $previous = Token::item($input, $nr - 1);
            $next = Token::item($input, $nr + 1);
            $current = Token::item($input, $nr);
            if($with_backslash){
                if(
                    $current === '"' &&
                    $previous === '\\' &&
                    $is_double_quote === false
                ){
                    $is_double_quote = true;
                    $string_depth++;
                }
                elseif(
                    $current === '"' &&
                    $previous === '\\' &&
                    $is_double_quote === true
                ){
                    $string_depth--;
                    if($string_depth === 0){
                        $is_double_quote = false;
                    }
                }
            } else {
                if(
                    $current === '"' &&
                    $previous !== '\\' &&
                    $is_double_quote === false
                ){
                    $is_double_quote = true;
                    $string_depth++;
                }
                elseif(
                    $current === '"' &&
                    $previous !== '\\' &&
                    $is_double_quote === true
                ){
                    $string_depth--;
                    if($string_depth === 0){
                        $is_double_quote = false;
                    }
                }
            }
            if($is_double_quote === true){
                if($current === '{{'){
                    $curly_depth++;
                    if($tag_nr === false){
                        $tag_nr = $nr;
                    }
                }
                elseif($current === '}}'){
                    $curly_depth--;
                    if($curly_depth <= 0){
                        $tag .= $current;
                        $tag_array[] = $char;
                        $tag_value = Cast::define(
                            $object,
                            $flags,
                            $options,
                            [
                                'string' => $tag,
                                'array' => $tag_array
                            ]
                        );
                        $tag_value = Token::value(
                            $object,
                            $flags,
                            $options,
                            $tag_value,
                        );
                        for($i = $tag_nr + 1; $i < $nr; $i++){
                            $input['array'][$i] = array_shift($tag_value['array']);
                        }
                        $tag_nr = false;
                        $tag = '';
                        $tag_array = [];
                    }
                }
                if($curly_depth > 0){
                    $tag .= $current;
                    $tag_array[] = $char;
                }
            }
        }
        return $input;
    }
}