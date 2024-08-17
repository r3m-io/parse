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

        foreach($tags as $row_nr => $list){
            foreach($list as $nr => $record){
                ddd($record);
            }
        }


        ddd($tags);
    }
}