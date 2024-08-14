<?php
namespace Package\R3m\Io\Parse\Service;

use R3m\Io\App;

use R3m\Io\Module\Core;
use R3m\Io\Module\File;

use Exception;
class Tag
{
    public static function define(App $object, $flags, $options, $input=''): array
    {
        if(!is_string($input)){
            return [];
        }
        $length = mb_strlen($input);
        $start = microtime(true);
        $split = mb_str_split($input, 1);
        $curly_count = 0;
        $line = 1;
        $column = [];
        $column[$line] = 1;
        $tag = false;
        $tag_list = [];
        $is_literal = false;
        $is_single_quoted = false;
        $is_double_quoted = false;
        $is_tag_in_double_quoted = false;
        $is_curly_open = false;
        $is_curly_close = false;
        $next = false;
        $chunk = 64;
        $previous = false;
        for($i = 0; $i < $length; $i+=$chunk){
            $char_list = [];
            for($j = 0; $j < $chunk; $j++){
                $char_list[] = $split[$i + $j] ?? null;
            }

            foreach($char_list as $nr => $char){
                if(array_key_exists($nr - 1, $char_list)){
                    $previous = $char_list[$nr - 1];
                }
                if($char === null){
                    break;
                }
                elseif($char === "\n"){
                    $line++;
                    $column[$line] = 1;
                }
                if(
                    $char === '\'' &&
                    $is_single_quoted === false &&
                    $previous !== '\\'
                ){
                    $is_single_quoted = true;
                }
                elseif(
                    $char === '\'' &&
                    $is_single_quoted === true &&
                    $previous !== '\\'
                ){
                    $is_single_quoted = false;
                }
                elseif(
                    $char === '"' &&
                    $is_double_quoted === false &&
                    $previous !== '\\'
                ){
                    $is_double_quoted = true;
                }
                elseif(
                    $char === '"' &&
                    $is_double_quoted === true &&
                    $previous !== '\\'
                ){
                    $is_double_quoted = false;
                }
                elseif(
                    $char === '{' &&
                    $is_curly_open === false &&
                    $is_single_quoted === false &&
                    $is_double_quoted === false
                ){
                    $is_curly_open = true;
                }
                elseif(
                    $char === '}' &&
                    $is_curly_close === false &&
                    $is_single_quoted === false &&
                    $is_double_quoted === false
                ){
                    $is_curly_close = true;
                }
                elseif(
                    $char === '{' &&
                    $is_curly_open === true &&
                    $is_single_quoted === false &&
                    $is_double_quoted === false
                ){
                    $curly_count++;
                }
                elseif(
                    $char === '}' &&
                    $is_curly_close === true &&
                    $is_single_quoted === false &&
                    $is_double_quoted === false
                ){
                    $curly_count--;
                    $is_curly_open = false;
                    $is_curly_close = false;
                }
                elseif(
                    $char === '{' &&
                    $is_curly_open === true &&
                    $is_single_quoted === false &&
                    $is_double_quoted === true &&
                    $curly_count === 0
                ){
                    $is_tag_in_double_quoted = true;
                    $curly_count++;
                }
                elseif(
                    $char === '}' &&
                    $is_curly_close === true &&
                    $is_single_quoted === false &&
                    $is_double_quoted === true &&
                    $is_tag_in_double_quoted === true
                ){
                    $curly_count--;
                    $is_tag_in_double_quoted = false;
                    $is_curly_open = false;
                    $is_curly_close = false;
                }
                if(
                    $curly_count === 1 &&
                    $tag === false
                ){
                    $tag = '{{';
                }
                elseif($curly_count === 0){
                    if($tag){
                        $tag .= $char;
                        $column[$line]++;
                        $explode = explode("\n", $tag);
                        $count = count($explode);
                        if($count > 1){
                            $content = trim(substr($tag, 2, -2));
                            $length_start = strlen($explode[0]);
                            $record = [
                                'tag' => $tag,
                                'is_multiline' => true,
                                'line' => [
                                    'start' => $line - $count + 1,
                                    'end' => $line
                                ],
                                'length' => [
                                    'start' => $length_start,
                                    'end' => strlen($explode[$count - 1])
                                ],
                                'column' => [
                                    ($line - $count + 1) => [
                                        'start' => $column[$line - $count + 1] - $length_start,
                                        'end' => $column[$line - $count + 1]
                                    ],
                                    $line => [
                                        'start' => $column[$line] - strlen($explode[$count - 1]),
                                        'end' => $column[$line]
                                    ]
                                ]
                            ];
                            if(empty($tag_list[$line - $count + 1])){
                                $tag_list[$line - $count + 1] = [];
                            }
                            $tag_list[$line - $count + 1][] = $record;
                        } else {
                            $length_start = strlen($explode[0]);
                            $record = [
                                'tag' => $tag,
                                'line' => $line,
                                'length' => $length_start,
                                'column' => [
                                    'start' => $column[$line] - $length_start,
                                    'end' => $column[$line]
                                ]
                            ];
                            $content = trim(substr($tag, 2, -2));
                            if(strtoupper(substr($content, 0, 3)) === 'R3M'){
                                $record['is_header'] = true;
                                $record['content'] = $content;
                            }
                            elseif(
                                strtoupper($content) === 'LITERAL' ||
                                $is_literal === true
                            ){
                                $is_literal = true;
                                $record['is_literal'] = true;
                                $record['is_literal_start'] = true;
                            }
                            elseif(
                                strtoupper($content) === '/LITERAL' ||
                                $is_literal === true
                            ){
                                $is_literal = false;
                                $record['is_literal'] = true;
                                $record['is_literal_end'] = true;
                            }
                            if(empty($tag_list[$line])){
                                $tag_list[$line] = [];
                            }
                            $tag_list[$line][] = $record;
                        }
                        $tag = false;
                        $column[$line]--;
                    }
                }
                elseif($tag){
                    $tag .= $char;
                }
                if($char !== "\n") {
                    $column[$line]++;
                }
            }
            $previous = $char_list[$chunk - 1] ?? null;
        }
        return $tag_list;
    }

    public static function remove(App $object, $flags, $options, $tags=[]): array
    {
        if(!is_array($tags)){
            return $tags;
        }
        foreach($tags as $line => $tag){
            foreach($tag as $nr => $record){
                if(
                    array_key_exists('is_header', $record) ||
                    array_key_exists('is_literal', $record) &&
                    !array_key_exists('is_literal_start', $record) &&
                    !array_key_exists('is_literal_end', $record)
                ){
                    unset($tags[$line][$nr]);
                    if(empty($tags[$line])){
                        unset($tags[$line]);
                    }
                }
            }
        }
        return $tags;
    }

    public static function block_method(App $object, $flags, $options, $tags=[]): array
    {
        $block_functions = [
            'if',
            'block.*',
            'script',
            'link',
            'foreach',
            'for.each',
            'for',
            'while',
            'switch'
        ];

        $block_depth = 0;
        $is_block = false;
        $method_name = false;
        $block_function = false;
        foreach($tags as $line => $tag){
            foreach($tag as $nr => $record){
                if(array_key_exists('method', $record)){
                    $method_name = $record['method']['name'];
                    foreach($block_functions as $block_function){
                        if($method_name === $block_function){
                            if($is_block === false){
                                $is_block = $nr;
                            }
                            $block_depth++;
                            d($block_depth);
                            break;
                        }
                    }
                }
                if($is_block !== false){
                    d($record);
                    if(array_key_exists('marker', $record)){
                        ddd($record);
                        $marker_name = $record['name'];
                        if($marker_name === $method_name){
                            $block_depth--;
                            d($block_depth);
                            if($block_depth === 0){
                                d($nr);
                                ddd($is_block);

                                $is_block = false;
                            }
                        }
                    }
                }
            }
        }
        return $tags;
    }
}