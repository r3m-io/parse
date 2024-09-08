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

trait Plugin_constant {

    protected function plugin_constant(string $constant, mixed $value=null): mixed
    {
        $constant = strtoupper($constant);
        if($value === null){
            return constant($constant);
        } else {
            define($constant, $value);
        }
        return null;
    }
}