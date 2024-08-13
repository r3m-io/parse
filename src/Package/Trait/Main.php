<?php
namespace Package\R3m\Io\Parse\Trait;

use R3m\Io\App;
use R3m\Io\Config;

use Package\R3m\Io\Parse\Service\Parse;
use Package\R3m\Io\Parse\Service\Token;

use Exception;

trait Main {

    /**
     * @throws Exception
     */
    public function compile($flags, $options){
        $object = $this->object();
        $token = Token::tokenize($object, $flags, $options);
        ddd($token);


        Parse::compile($object, $flags, $options);
        d($flags);
        ddd($options);
    }
}