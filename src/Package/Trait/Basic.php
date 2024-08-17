<?php
namespace Package\R3m\Io\Parse\Trait;

use R3m\Io\App;

use R3m\Io\Module\Data;

trait Basic {

    protected App $object;
    protected Data $data;
    protected object $flags;
    protected object $options;

    public function object(App $object=null): ?App
    {
        if($object !== null){
            $this->setObject($object);
        }
        return $this->getObject();
    }

    private function setObject(App $object): void
    {
        $this->object = $object;
    }

    private function getObject(): ?App
    {
        return $this->object;
    }

    public function data(Data $data=null): ?Data
    {
        if($data !== null){
            $this->setData($data);
        }
        return $this->getData();
    }

    private function setData(Data $data): void
    {
        $this->data = $data;
    }

    private function getData(): ?Data
    {
        return $this->data;
    }

    public function options(object $options=null): ?object
    {
        if($options !== null){
            $this->setOptions($options);
        }
        return $this->getOptions();
    }

    private function setOptions(object $options): void
    {
        $this->options = $options;
    }

    private function getOptions(): ?object
    {
        return $this->options;
    }

    public function flags(object $flags=null): ?object
    {
        if($flags !== null){
            $this->setFlags($flags);
        }
        return $this->getFlags();
    }

    private function setFlags(object $flags): void
    {
        $this->flags = $flags;
    }

    private function getFlags(): ?object
    {
        return $this->flags;
    }

}