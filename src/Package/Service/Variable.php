<?php
namespace Package\R3m\Io\Parse\Service;

use R3m\Io\App;

use R3m\Io\Module\Core;
use R3m\Io\Module\File;

use Exception;
class Variable
{
    public static function define(App $object, $flags, $options, $input=[]){
        if(!is_array($input)){
            return $input;
        }
        if(array_key_exists('array', $input) === false){
            return $input;
        }
//        trace();
//        d($input['array']);
        $count = count($input['array']);
        $is_variable = false;
        $has_name = false;
        $name = '';
        foreach($input['array'] as $nr => $char){
            $previous = Token::item($input, $nr - 1);
            $next = Token::item($input, $nr + 1);
            $current = Token::item($input, $nr);
            if($current === '$'){
                $is_variable = $nr;
                $name = '$';
                for($i = $is_variable + 1; $i < $count; $i++){
                    $current = Token::item($input, $i);
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
                        if($name !== '$'){
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
                if(
                    !in_array(
                        $name,
                        [
                            '',
                            '$'
                        ],
                    true
                    )
                ){
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
                    for($j = $is_variable + 1; $j < $i; $j++){
                        $input['array'][$j] = null;
                    }
                    break;
                }
            }
        }
        return $input;
    }

    public static function modifier(App $object, $flags, $options, $input=[]): array
    {
        if(!is_array($input)){
            return $input;
        }
        if(array_key_exists('array', $input) === false){
            return $input;
        }
        $count = count($input['array']);
        $set_depth = 0;
        $set_depth_modifier = false;
        $outer_curly_depth = 0;
        $modifier_string = '';
        $modifier_name = '';
        $is_variable = false;
        $is_modifier = false;
        $is_argument = false;
        $is_single_quote = false;
        $is_double_quote = false;
        $is_double_quote_backslash = false;
        $argument_nr = -1;
        $argument = [];
        $argument_array = [];
        $nr = $count - 1;
        foreach($input['array'] as $nr => $char) {
            $previous = Token::item($input, $nr - 1);
            $next = Token::item($input, $nr + 1);
            $current = Token::item($input, $nr);
            d($current);
            d($set_depth);
            d($set_depth_modifier);
            d($is_double_quote_backslash);
            d($is_double_quote);
            if($current === '('){
                $set_depth++;
            }
            elseif($current === ')'){
                $set_depth--;
                if(
                    $is_modifier &&
                    $set_depth === $set_depth_modifier
                ){
                    foreach($argument_array as $argument_nr => $array){
                        $argument_value = Cast::define(
                            $object,
                            $flags,
                            $options,
                            [
                                'string' => $argument[$argument_nr],
                                'array' => $array
                            ]
                        );
                        $argument_value = Token::value(
                            $object,
                            $flags,
                            $options,
                            $argument_value,
                        );
                        $argument_array[$argument_nr] = $argument_value;
                    }
                    $input['array'][$is_variable]['modifier'][] = [
                        'string' => $modifier_string,
                        'name' => $modifier_name,
                        'argument' => $argument_array
                    ];
                    for($index = $is_variable + 1; $index < $nr; $index++){
                        $input['array'][$index] = null;
                    }
                    $modifier_name = '';
                    $modifier_string = '';
                    $is_argument = false;
                    $is_variable = false;
                    $is_modifier = false;
                    $argument_array = [];
                    $argument = [];
                    $argument_nr = -1;
                }
            }
            elseif($current === '{{'){
                $outer_curly_depth++;
            }
            elseif($current === '}}'){
                $outer_curly_depth--;
            }
            elseif(
                $current === '\'' &&
                $previous !== '\\' &&
                $is_single_quote === false &&
                $is_double_quote === false
            ){
                $is_single_quote = true;
            }
            elseif(
                $current === '\'' &&
                $previous !== '\\' &&
                $is_single_quote === true &&
                $is_double_quote === false
            ){
                $is_single_quote = false;
            }
            elseif(
                $current === '"' &&
                $previous !== '\\' &&
                $is_single_quote === false &&
                $is_double_quote === false
            ){
                $is_double_quote = true;
            }
            elseif(
                $current === '"' &&
                $previous !== '\\' &&
                $is_single_quote === false &&
                $is_double_quote === true
            ){
                $is_double_quote = false;
            }
            elseif(
                $current === '"' &&
                $previous === '\\' &&
                $is_single_quote === false &&
                $is_double_quote_backslash === false
            ){
                $is_double_quote_backslash = true;
            }
            elseif(
                $current === '"' &&
                $previous === '\\' &&
                $is_single_quote === false &&
                $is_double_quote_backslash === true
            ){
                $is_double_quote_backslash = false;
            }
            elseif(
                $current === '|' &&
                $previous !== '|' &&
                $next !== '|' &&
                $is_single_quote === false &&
                $is_double_quote === false &&
                $is_double_quote_backslash === false &&
                (
                    $set_depth === $set_depth_modifier ||
                    $set_depth_modifier === false
                )
            ){
                d($is_argument);
                if($is_argument !== false){
                    //add set_depth
                    foreach($argument_array as $argument_nr => $array){
                        $argument_value = Cast::define(
                            $object,
                            $flags,
                            $options,
                            [
                                'string' => $argument[$argument_nr],
                                'array' => $array
                            ]
                        );
                        $argument_value = Token::value(
                            $object,
                            $flags,
                            $options,
                            $argument_value,
                        );
                        $argument_array[$argument_nr] = $argument_value;
                    }
                    $input['array'][$is_variable]['modifier'][] = [
                        'string' => $modifier_string,
                        'name' => $modifier_name,
                        'argument' => $argument_array
                    ];
                    for($index = $is_variable + 1; $index < $nr; $index++){
                        $input['array'][$index] = null;
                    }
                    $modifier_name = '';
                    $modifier_string = '';
                    $is_argument = false;
                    $argument_array = [];
                    $argument = [];
                    $argument_nr = -1;
                }
                elseif($is_modifier !== false){
                    $input['array'][$is_variable]['modifier'][] = [
                        'string' => $modifier_string,
                        'name' => $modifier_name,
                        'argument' => []
                    ];
                    for($index = $is_variable + 1; $index < $nr; $index++){
                        $input['array'][$index] = null;
                    }
                    $modifier_name = '';
                    $modifier_string = '';
                    $is_argument = false;
                    $argument_array = [];
                    $argument = [];
                    $argument_nr = -1;
                }
                elseif($is_variable !== false){
                    $is_modifier = true;
                }
            }
            elseif(
                $current === ':' &&
                $previous !== ':' &&
                $next !== ':' &&
                $is_single_quote === false &&
                $is_double_quote === false &&
                $is_double_quote_backslash === false
            ){
                if($is_modifier !== false){
                    $is_argument = true;
                }
                $argument_nr++;
                d('yes1');
                d($is_modifier);
            }
            elseif(
                $current === ',' &&
                $is_single_quote === false &&
                $is_double_quote === false &&
                $is_double_quote_backslash === false
            ){
                if(
                    $is_variable !== false &&
                    $is_modifier !== false
                ){
                    if($is_argument !== false){
                        foreach($argument_array as $argument_nr => $array){
                            $argument_value = Cast::define(
                                $object,
                                $flags,
                                $options,
                                [
                                    'string' => $argument[$argument_nr],
                                    'array' => $array
                                ]
                            );
                            $argument_value = Token::value(
                                $object,
                                $flags,
                                $options,
                                $argument_value,
                            );
                            $argument_array[$argument_nr] = $argument_value;
                        }
                        $input['array'][$is_variable]['modifier'][] = [
                            'string' => $modifier_string,
                            'name' => $modifier_name,
                            'argument' => $argument_array
                        ];
                        for($index = $is_variable + 1; $index < $nr; $index++){
                            $input['array'][$index] = null;
                        }
                    }
                    elseif($is_modifier !== false){
                        $input['array'][$is_variable]['modifier'][] = [
                            'string' => $modifier_string,
                            'name' => $modifier_name,
                            'argument' => []
                        ];
                        for($index = $is_variable + 1; $index < $nr; $index++){
                            $input['array'][$index] = null;
                        }
                    }
                }
            }
            elseif(
                $current !== null &&
                is_array($char) &&
                $char['type'] === 'variable' &&
                $is_single_quote === false &&
                $is_double_quote === false &&
                $is_variable === false
            ){
                $is_variable = $nr;
                d($is_variable);
            }
            if($is_modifier === true){
                $modifier_string .= $current;
                d($modifier_string);
            }
            if(
                $is_modifier === true &&
                $is_argument === false
            ){
                if(
                    !in_array(
                        $current,
                        [
                            ' ',
                            "\t",
                            "\n",
                            "\r",
                            ':',
                            '|',
                        ],
                        true
                    )
                ){
                    $modifier_name .= $current;
                    d($modifier_name);
                    if($set_depth_modifier === false){
                        $set_depth_modifier = $set_depth - 1;
                    }
                }
            }
            elseif(
                $is_argument
            ){
                if(
                    $current === ':' &&
                    $previous !== ':' &&
                    $next !== ':' &&
                    $is_single_quote === false &&
                    $is_double_quote === false &&
                    $is_double_quote_backslash === false
                ){
                    d('found here');
                } else {
                    if(!array_key_exists($argument_nr, $argument_array)){
                        $argument_array[$argument_nr] = [];
                        $argument[$argument_nr] = '';
                    }
                    $argument[$argument_nr] .= $current;
                    $argument_array[$argument_nr][] = $char;
                }
            }
        }
        d($is_modifier);
        if(
            $is_variable !== false &&
            $is_modifier !== false
        ){
            d($is_variable);
            d($is_modifier);
            if($is_argument !== false){
                foreach($argument_array as $argument_nr => $array){
                    $argument_value = Cast::define(
                        $object,
                        $flags,
                        $options,
                        [
                            'string' => $argument[$argument_nr],
                            'array' => $array
                        ]
                    );
                    $argument_value = Token::value(
                        $object,
                        $flags,
                        $options,
                        $argument_value,
                    );
                    $argument_array[$argument_nr] = $argument_value;
                }
                $input['array'][$is_variable]['modifier'][] = [
                    'string' => $modifier_string,
                    'name' => $modifier_name,
                    'argument' => $argument_array
                ];
                for($index = $is_variable + 1; $index < $nr; $index++){
                    $input['array'][$index] = null;
                }
            }
            elseif($is_modifier !== false){
                $input['array'][$is_variable]['modifier'][] = [
                    'string' => $modifier_string,
                    'name' => $modifier_name,
                    'argument' => []
                ];
                for($index = $is_variable + 1; $index < $nr; $index++){
                    $input['array'][$index] = null;
                }
            }
        }
        d($input['array']);
        return $input;
    }

}