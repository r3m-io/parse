<?php
namespace Package\R3m\Io\Parse\Service;

use R3m\Io\App;

use R3m\Io\Module\Core;
use R3m\Io\Module\File;

use Exception;
class Build
{
    public static function create(App $object, $flags, $options, $tags=[]): string
    {
        d(round((microtime(true) - $object->config('time.start')) * 1000, 2) . 'ms');
//        d($tags);

        $data = [];

        $tags = [
            1 => [
                0 => 'test1',
                1 => "\n test2"
            ]
        ];
        foreach($tags as $row_nr => $list){
            foreach($list as $nr => $record){
                if($record === 'test1'){
                    $next = $list[$nr + 1] ?? false;
                    if($next !== false){
                        $tags[$row_nr][$nr + 1] = 'test2';
                        $list[$nr + 1] = $tags[$row_nr][$nr + 1];
                        var_dump($list);
                    }
                }
                var_dump($record);
            }
        }
        die;


        /*
        foreach($tags as $row_nr => $list){
            foreach($list as $nr => $record){
                $variable_assign = Build::variable_assign($object, $flags, $options, $record);
                if($variable_assign){
                    $data[] = $variable_assign . ';';
                    $next = $list[$nr + 1] ?? false;
                    if($next !== false){
                        $tags[$row_nr][$nr + 1] = Build::variable_assign_next($object, $flags, $options, $next);
                        $list[$nr + 1] = $tags[$row_nr][$nr + 1];
                        d($list);
                    }
                }
                d($record);
            }
        }
        */
        ddd($data);
    }

    public static function variable_assign_next(App $object, $flags, $options,$record = []){
        d($record);
        if(
            array_key_exists('text', $record) &&
            array_key_exists('is_multiline', $record) &&
            $record['is_multiline'] === true
        ){
            $text = explode("\n", $record['text'], 2);
            $test = trim($text[0]);
            if($test === ''){
                d($text[1]);
                $record['text'] = $text[1];
            }
        }
        return $record;
    }


    public static function variable_assign(App $object, $flags, $options, $record = []): bool | string
    {
        if(!array_key_exists('variable', $record)){
            return false;
        }
        elseif(
            !array_key_exists('is_assign', $record['variable']) ||
            $record['variable']['is_assign'] !== true
        ) {
            return false;
        }
        $variable_name = str_replace('.', '_', $record['variable']['name']);
        $operator = $record['variable']['operator'];
        $value = Build::variable_value($object, $flags, $options, $record['variable']['value']);
        if(
            $variable_name !== '' &&
            $operator !== '' &&
            $value !== ''
        ){
            return '$' . $variable_name . ' ' . $operator . ' ' . $value;
        }
        return false;
    }

    public static function variable_value(App $object, $flags, $options, $input): string
    {
        $value = '';
        foreach($input['array'] as $nr => $record){
            if(
                array_key_exists('is_single_quoted', $record) &&
                array_key_exists('execute', $record) &&
                $record['is_single_quoted'] === true
            ){
                $value .= '\'' . $record['execute'] . '\'';
            } else {
                ddd($record);
            }
        }
        return $value;
    }

}