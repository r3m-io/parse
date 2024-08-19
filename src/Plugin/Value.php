<?php
namespace Plugin;

trait Value {
    const STRING = 'string';
    const BOOLEAN = 'boolean';
    const INTEGER = 'integer';
    const FLOAT = 'double';
    const ARRAY = 'array';
    const OBJECT = 'object';
    const NULL = 'NULL';
    const UNKNOWN = 'unknown';
    const RESOURCE = 'resource';
    const RESOURCE_CLOSED = 'resource (closed)';


    protected function value_concatenate($variable1=null, $variable2=null) : string
    {
        return (string) $variable1 . (string) $variable2;
    }

    protected function value_plus_plus($variable=0): int | float
    {
        $variable += 0;
        $variable++;
        return $variable;
    }

    protected function value_minus_minus($variable=0): int |  float
    {
        $variable += 0;
        $variable--;
        return $variable;
    }

    protected function value_plus($variable1=null, $variable2=null) : int |  float | string
    {
        $type1 = getType($variable1);
        $type2 = getType($variable2);
        if(
            $type1 == self::STRING ||
            $type2 == self::STRING
        ){
            return (string) $variable1 . (string) $variable2;
        } else {
            $variable1 += 0;
            $variable2 += 0;
            return $variable1 + $variable2;
        }
    }

    protected function value_minus($variable1=null, $variable2=null): int |  float
    {
        $variable1 += 0;
        $variable2 += 0;
        return $variable1 - $variable2;
    }

    protected function value_multiply($variable1=null, $variable2=null): int |  float
    {
        $variable1 += 0;
        $variable2 += 0;
        return $variable1 * $variable2;
    }

    protected function value_modulo($variable1=null, $variable2=null): int |  float
    {
        return $variable1 % $variable2;
    }

    protected function value_divide($variable1=null, $variable2=null): int |  float
    {
        $variable1 += 0;
        $variable2 += 0;
        if($variable2 != 0){
            return $variable1 / $variable2;
        } else {
            return INF;
        }
    }

    protected function value_smaller($variable1=null, $variable2=null): bool
    {
        return $variable1 < $variable2;
    }

    protected function value_smaller_equal($variable1=null, $variable2=null): bool
    {
        return $variable1 <= $variable2;
    }

    protected function value_smaller_smaller($variable1=null, $variable2=null): bool | int
    {
        return $variable1 << $variable2;
    }

    protected function value_greater($variable1=null, $variable2=null): bool
    {
        return $variable1 > $variable2;
    }

    protected function value_greater_equal($variable1=null, $variable2=null): bool
    {
        return $variable1 >= $variable2;
    }

    protected function value_greater_greater($variable1=null, $variable2=null): bool | int
    {
        return $variable1 >> $variable2;
    }

    protected function value_not_equal($variable1=null, $variable2=null): bool
    {
        return $variable1 != $variable2;
    }

    protected function value_not_identical($variable1=null, $variable2=null): bool
    {
        return $variable1 !== $variable2;
    }

    protected function value_equal($variable1=null, $variable2=null): bool
    {
        return $variable1 == $variable2;
    }

    protected function value_identical($variable1=null, $variable2=null): bool
    {
        return $variable1 === $variable2;
    }

    protected function value_and($variable1=null, $variable2=null): bool
    {
        return $variable1 && $variable2;
    }

    protected function value_or($variable1=null, $variable2=null): bool
    {
        return $variable1 || $variable2;
    }

    protected function value_xor($variable1=null, $variable2=null): bool
    {
        return $variable1 xor $variable2;
    }

    protected function value_null_coalescing($variable1=null, $variable2=null): bool
    {
        return $variable1 ?? $variable2;
    }



}