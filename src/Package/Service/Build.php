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

        foreach($tags as $row_nr => $list){
            foreach($list as $nr => $record){
                $variable_assign = Build::variable_assign($object, $flags, $options, $record);
                if($variable_assign){
                    $data[] = $variable_assign;
                    //remove return from next whitespace
                }
            }
        }
        ddd($data);
    }


    public static function variable_assign(App $object, $flags, $options, $record): bool | string
    {
        if(
            array_key_exists('variable', $record) &&
            array_key_exists('is_assign', $record['variable']) &&
            $record['variable']['is_assign'] !== true
        ) {
            return false;
        }
        $variable_name = str_replace('.', '_', $record['variable']['name']);
        $operator = $record['operator'];
        $value = Build::variable_value($object, $flags, $options, $record['variable']['value']);
        d($variable_name);
        d($operator);
        d($value);
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