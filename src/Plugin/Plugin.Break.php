<?php
/**
 * @package Plugin\Modifier
 * @author Remco van der Velde
 * @since 2024-08-19
 * @license MIT
 * @version 1.0
 * @changeLog
 *    - all
 */
namespace Plugin;

trait Plugin_break {
    protected $break = false;
    protected $break_level = 0;

    protected function plugin_break($level=1): void
    {
        $this->break(true);
        $this->break_level = $level;
    }

    public function break($break = null): bool
    {
        if($break !== null){
            $this->setBreak($break);

        }
        return $this->getBreak();
    }

    private function setBreak($break = false): void
    {
        $this->break = $break;
    }

    private function getBreak(): bool
    {
        return $this->break;
    }


    public function break_level($level = null): int
    {
        if($level !== null){
            $this->set_break_level($level);

        }
        return $this->get_break_level();
    }

    private function set_break_level($level = 0): void
    {
        $this->break_level = $level;
    }

    private function get_break_level(): int
    {
        return $this->break_level;
    }

}