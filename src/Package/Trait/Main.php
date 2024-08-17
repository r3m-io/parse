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
    public function compile($flags, $options): mixed {
        if(!property_exists($options, 'source')){
            throw new Exception('Source not found');
        }
        if(File::exist($options->source) === false){
            throw new Exception('Source not found');
        }
        $object = $this->object();
        $input = File::read($options->source);
        $parse = new Parse($object, new Data(), $flags, $options);
        $result = $parse->compile($input);
        return $result;
    }
}