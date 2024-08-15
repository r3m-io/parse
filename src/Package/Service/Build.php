<?php
namespace Package\R3m\Io\Parse\Service;

use R3m\Io\App;

use R3m\Io\Module\Core;
use R3m\Io\Module\File;

use Exception;
class Build
{
    public static function create(App $object, $flags, $options, $input='', $tags=[]): string
    {
        d(round((microtime(true) - $object->config('time.start')) * 1000, 2) . 'ms');
        d($tags);
        ddd($input);
    }
}