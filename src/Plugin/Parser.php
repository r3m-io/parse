<?php
namespace Plugin;

use Package\R3m\Io\Parse\Service\Parse;

trait Parser {

    protected Parse $parse;

    public function parse(Parse $parse=null): ?Parse
    {
        if($parse !== null){
            $this->setParse($parse);
        }
        return $this->getParse();
    }

    private function setParse(Parse $parse): void
    {
        $this->parse = $parse;
    }

    private function getParse(): ?Parse
    {
        return $this->parse;
    }

}