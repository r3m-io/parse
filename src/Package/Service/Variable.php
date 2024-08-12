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
        foreach($input['array'] as $nr => $char){
            $previous = Parse::item($input, $nr - 1);
            $next = Parse::item($input, $nr + 1);
            $current = Parse::item($input, $nr);
            if($current === '$'){
                $is_variable = $nr;
                $name = '$';
                for($i = $is_variable + 1; $i < $count; $i++){
                    $current = Parse::item($input, $i);
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
        $curly_depth = 0;
        $outer_curly_depth = 0;
        $modifier_string = '';
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
            elseif($current === '{{'){
                $outer_curly_depth++;
            }
            elseif($current === '}}'){
                $outer_curly_depth--;
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
                    $modifier_string .= $current;
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
                            if(
                                $is_single_quote === false &&
                                $is_double_quote === false &&
                                $modifier_name !== '' &&
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
                                        $current === ':' &&
                                        $previous !== ':' &&
                                        $next !== ':'
                                    )
                                )
                            ){
                                break;
                            }
                            elseif(
                                $current === '|' &&
                                $previous !== '|' &&
                                $next !== '|' &&
                                $is_single_quote === false &&
                                $is_double_quote === false
                            ){
                                d('one');
                                break;
                            }
                            elseif($set_depth < 0) {
                                break;
                            }
                            elseif(
                                !in_array(
                                    $current,
                                    [
                                        ' ',
                                        "\t",
                                        "\n",
                                        "\r",
                                    ],
                                    true
                                )
                            ) {
                                $modifier_name .= $current;
                            }
                            $modifier_string .= $current;
                        }
                        if($modifier_name){
                            $argument_list = [];
                            $argument_array = [];
                            $argument = '';
                            $is_double_quote = false;
                            $is_double_quote_backslash = false;
                            $is_single_quote = false;
                            while(true){
                                $current = Parse::item($input, $i);
                                $modifier_string .= $current;
                                if(
                                    in_array(
                                        $current,
                                        [
                                            ' ',
                                            "\t",
                                            "\n",
                                            "\r",
                                        ],
                                        true
                                    )
                                ){
                                    $i++;
                                } else {
                                    break;
                                }
                            }
                            if($current === ':'){
                                for($j = $i + 1; $j < $count; $j++){
                                    $previous = Parse::item($input, $j - 1);
                                    $next = Parse::item($input, $j + 1);
                                    $current = Parse::item($input, $j);
                                    if(
                                        $current === '"' &&
                                        $previous !== '\\' &&
                                        $is_double_quote === false &&
                                        $is_single_quote === false &&
                                        $is_double_quote_backslash === false
                                    ){
                                        $is_double_quote = true;
                                    }
                                    elseif(
                                        $current === '"' &&
                                        $previous !== '\\' &&
                                        $is_double_quote === true &&
                                        $is_single_quote === false &&
                                        $is_double_quote_backslash === false
                                    ){
                                        $is_double_quote = false;
                                    }
                                    elseif(
                                        $current === '"' &&
                                        $previous === '\\' &&
                                        $is_single_quote === false &&
                                        $is_double_quote === false &&
                                        $is_double_quote_backslash === false
                                    ){
                                        $is_double_quote_backslash = true;
                                    }
                                    elseif(
                                        $current === '"' &&
                                        $previous === '\\' &&
                                        $is_single_quote === false &&
                                        $is_double_quote === false &&
                                        $is_double_quote_backslash === true
                                    ){
                                        $is_double_quote_backslash = false;
                                    }
                                    elseif(
                                        $current === '\'' &&
                                        $previous !== '\\' &&
                                        $is_single_quote === false &&
                                        $is_double_quote === false &&
                                        $is_double_quote_backslash === false
                                    ){
                                        $is_single_quote = true;
                                    }
                                    elseif(
                                        $current === '\'' &&
                                        $previous !== '\\' &&
                                        $is_single_quote === true &&
                                        $is_double_quote === false &&
                                        $is_double_quote_backslash === false
                                    ){
                                        $is_single_quote = false;
                                    }
                                    if(
                                        $current === '(' &&
                                        $is_single_quote === false &&
                                        $is_double_quote === false &&
                                        $is_double_quote_backslash === false
                                    ){
                                        $set_depth++;
                                    }
                                    elseif(
                                        $current === ')' &&
                                        $is_single_quote === false &&
                                        $is_double_quote === false &&
                                        $is_double_quote_backslash === false
                                    ){
                                        $set_depth--;
                                    }
                                    if(
                                        $current === '{{' &&
                                        $is_single_quote === false &&
                                        $is_double_quote === false &&
                                        $is_double_quote_backslash === false
                                    ){
                                        $curly_depth++;
                                    }
                                    elseif(
                                        $current === '}}' &&
                                        $is_single_quote === false &&
                                        $is_double_quote === false &&
                                        $is_double_quote_backslash === false
                                    ){
                                        if($curly_depth > 0){
                                            $curly_depth--;
                                        }
                                        elseif($outer_curly_depth > 0){
                                            $outer_curly_depth--;
                                            break;
                                        }
                                    }
                                    if(
                                        $current === ':' &&
                                        $is_double_quote === false &&
                                        $is_single_quote === false &&
                                        $is_double_quote_backslash === false
                                    ){
                                        d($argument);
                                        $argument_value = Cast::define(
                                            $object, [
                                            'string' => $argument,
                                            'array' => $argument_array
                                        ],
                                            $flags,
                                            $options
                                        );
                                        $argument_value = Parse::value(
                                            $object,
                                            $argument_value,
                                            $flags,
                                            $options
                                        );
                                        $argument_list[] = $argument_value;
                                        $argument_array = [];
                                        $argument = '';
                                    }
                                    elseif(
                                        $current === '|' &&
                                        $is_single_quote === false &&
                                        $is_double_quote === false &&
                                        $is_double_quote_backslash === false &&
                                        $previous !== '|' &&
                                        $next !== '|'
                                    ){
                                        break;
                                    }
                                    elseif(
                                        $current === ')' &&
                                        $is_single_quote === false &&
                                        $is_double_quote === false &&
                                        $is_double_quote_backslash === false &&
                                        $set_depth <= 0
                                    ){
                                        break;
                                    }
                                    else {
                                        $argument .= $current;
                                        $argument_array[] = $input['array'][$j];
                                    }
                                    $modifier_string .= $current;
                                }
                                if(array_key_exists(0, $argument_array)){
                                    d($argument);
                                    $argument_value = Cast::define(
                                        $object, [
                                        'string' => $argument,
                                        'array' => $argument_array
                                    ],
                                        $flags,
                                        $options
                                    );
                                    $argument_value = Parse::value(
                                        $object,
                                        $argument_value,
                                        $flags,
                                        $options
                                    );
                                    if(str_contains($argument, '\"{{$test3 | default: \'yes\'}}\"')){
                                        d($argument_value);
                                    }
                                    $argument_list[] = $argument_value;
                                }
                                if(!array_key_exists('modifier', $input['array'][$nr])){
                                    $input['array'][$nr]['modifier'] = [];
                                }
                                $input['array'][$nr]['modifier'][] = [
                                    'name' => $modifier_name,
                                    'string' => $modifier_string,
                                    'arguments' => $argument_list
                                ];
                                $modifier_name = '';
                                $modifier_string = '';
                                $argument = '';
                                $argument_list = [];
                                $argument_array = [];
                                for($k = $nr + 1; $k < $j; $k++){
                                    $input['array'][$k] = null;
                                }
                                $is_modifier = false;
                                $i = $j;
                            } elseif(
                                !in_array(
                                    $current,
                                    [
                                        ' ',
                                        "\t",
                                        "\n",
                                        "\r",
                                    ],
                                    true
                                )
                            ){
                                $i++;
                            } else {
                                d('yes1');
                                $input['array'][$nr]['modifier'][] = [
                                    'name' => $modifier_name,
                                    'arguments' => []
                                ];
                                $modifier_name = '';
                                $modifier_string = '';
                                $i--;
                            }
                        }
                    }
                }
            }
        }
        d($input['array']);
        return $input;
    }

}