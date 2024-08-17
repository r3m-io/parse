<?php
namespace Package\R3m\Io\Parse\Trait;

use R3m\Io\App;
use R3m\Io\Config;

use R3m\Io\Module\Data;
use R3m\Io\Module\Dir;
use R3m\Io\Module\File;
use R3m\Io\Module\Cli;

use Package\R3m\Io\Parse\Service\Parse;
use Package\R3m\Io\Parse\Service\Token;
use Package\R3m\Io\Parse\Service\Build;

use Exception;


trait Main {

    /**
     * @throws Exception
     */
    public function compile($flags, $options){
        if(!property_exists($options, 'source')){
            throw new Exception('Source not found');
        }
        if(File::exist($options->source) === false){
            throw new Exception('Source not found');
        }
        $object = $this->object();
        $input = File::read($options->source);
        $token = Token::tokenize($object, $flags, $options, $input);
        $document = Build::create($object, $flags, $options, $token);

        $dir = $object->config('project.dir.data') .
            'Test' .
            $object->config('ds') .
            'Parse' .
            $object->config('ds');
        Dir::create($dir, Dir::CHMOD);
        $url = $dir .
            'Main.php'
        ;
        File::write($url, implode(PHP_EOL, $document));

        require_once $url;
        d($url);
        echo str_repeat('-', Cli::tput('columns')) . PHP_EOL;
        $main = new \Package\R3m\Io\Parse\Main($object, new Parse(), new Data(), $flags, $options);
        return $main->run();
    }
}