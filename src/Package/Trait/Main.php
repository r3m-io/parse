<?php
namespace Package\R3m\Io\Parse\Trait;

use R3m\Io\App;
use R3m\Io\Config;

use R3m\Io\Module\File;

use Package\R3m\Io\Parse\Service\Parse;
use Package\R3m\Io\Parse\Service\Token;

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
        ddd($token);


        Parse::compile($object, $flags, $options);
        d($flags);
        ddd($options);
    }
}