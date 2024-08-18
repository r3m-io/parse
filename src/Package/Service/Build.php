<?php
namespace Package\R3m\Io\Parse\Service;

use R3m\Io\App;

use R3m\Io\Module\Core;
use R3m\Io\Module\File;

use Exception;
class Build
{
    public static function create(App $object, $flags, $options, $tags=[]): array
    {
        $options->class = 'Main';
        $data = [];
        d($tags);
        foreach($tags as $row_nr => $list){
            foreach($list as $nr => &$record){
                $text = Build::text($object, $flags, $options, $record);
                if($text){
                    $text = explode(PHP_EOL, $text);
                    foreach($text as $text_nr => $line) {
                        $data[] = $line;
                    }
                }
                $variable_assign = Build::variable_assign($object, $flags, $options, $record);
                if($variable_assign){
                    $data[] = $variable_assign;
                    $next = $list[$nr + 1] ?? false;
                    if($next !== false){
                        $tags[$row_nr][$nr + 1] = Build::variable_assign_next($object, $flags, $options, $record, $next);
                        $list[$nr + 1] = $tags[$row_nr][$nr + 1];
                    }
                }
                $variable_define = Build::variable_define($object, $flags, $options, $record);
                if($variable_define){
                    foreach($variable_define as $variable_define_nr => $line){
                        $data[] = $line;
                    }
                }
            }
        }
        $document = [];
        $document[] = '<?php';
        $document[] = 'namespace Package\R3m\Io\Parse;';
        $document[] = '';
        $document[] = 'use R3m\Io\App;';
        $document[] = '';
        $document[] = 'use R3m\Io\Module\Data;';
        $document[] = '';
        $document[] = 'use \Package\R3m\Io\Parse\Service\Parse;';
        $document[] = 'use \Package\R3m\Io\Parse\Trait\Basic;';
        $document[] = 'use \Package\R3m\Io\Parse\Trait\Parser;';
        $document[] = 'use \Package\R3m\Io\Parse\Trait\Value;';
        $document[] = 'use \Package\R3m\Io\Parse\Modifier;';
        $document[] = '';
        $document[] = 'use Exception;';
        $document[] = '';
        $document[] = 'class '. $options->class .' {';
        $document[] = '';
        $document[] = '    use Basic;';
        $document[] = '    use Parser;';
        $document[] = '    use Value;';
        $document[] = '    use Modifier\Modifier_default;';
        $document[] = '';
        $document[] = '    public function __construct(App $object, Parse $parse, Data $data, $flags, $options){';
        $document[] = '        $this->object($object);';
        $document[] = '        $this->parse($parse);';
        $document[] = '        $this->data($data);';
        $document[] = '        $this->flags($flags);';
        $document[] = '        $this->options($options);';
        $document[] = '    }';
        $document[] = '';
        $document[] = '    /**';
        $document[] = '     * @throws Exception';
        $document[] = '     */';
        $document[] = '    public function run(): mixed';
        $document[] = '    {';
        $document[] = '        ob_start();';
        $document[] = '        $object = $this->object();';
        $document[] = '        $parse = $this->parse();';
        $document[] = '        $data = $this->data();';
        $document[] = '        $flags = $this->flags();';
        $document[] = '        $options = $this->options();';
        $document[] = '        $options->debug = true;';
        $document[] = '        if (!($object instanceof App)) {';
        $document[] = '            throw new Exception(\'$object is not an instance of R3m\Io\App\');';
        $document[] = '        }';
        $document[] = '        if (!($parse instanceof Parse)) {';
        $document[] = '            throw new Exception(\'$parse is not an instance of Package\R3m\Io\Parse\Service\Parse\');';
        $document[] = '        }';
        $document[] = '        if (!($data instanceof Data)) {';
        $document[] = '            throw new Exception(\'$data is not an instance of R3m\Io\Module\Data\');';
        $document[] = '        }';
        $document[] = '        if (!is_object($flags)) {';
        $document[] = '            throw new Exception(\'$flags is not an object\');';
        $document[] = '        }';
        $document[] = '        if (!is_object($options)) {';
        $document[] = '            throw new Exception(\'$options is not an object\');';
        $document[] = '        }';
        foreach($data as $nr => $line){
            $document[] = '        ' . $line;
        }
        $document[] = '        return ob_get_clean();';
        $document[] = '    }';
        $document[] = '}';
        d($document);
        return $document;
    }

    public static function text(App $object, $flags, $options,$record = []){
        if(
            array_key_exists('text', $record) &&
            $record['text'] !== ''
        ){
            $text = explode("\n", $record['text']);
            $result = [];
            foreach($text as $nr => $line) {
                if(
                    !in_array(
                        $line,
                        [
                            '',
                            "\r",
                        ],
                    true
                    )
                ){
                    $result[] = 'echo \'' . $line . '\';' . PHP_EOL;
                }
                elseif(
                    in_array(
                        $line,
                        [
                            '',
                            "\r",
                        ],
                        true
                    )
                ){
                    $result[] = '';
                }
            }
            if(array_key_exists(1, $result)){
                return implode('echo "\n";' . PHP_EOL, $result);
            }
            return $result[0] ?? false;

        }
        return false;
    }
    
    public static function variable_assign_next(App $object, $flags, $options,$record = [], $next=[]){
        if(!array_key_exists('variable', $record)){
            return $next;
        }
        elseif(
            !array_key_exists('is_assign', $record['variable']) ||
            $record['variable']['is_assign'] !== true
        ) {
            return $next;
        }
        if(
            array_key_exists('text', $next) &&
            array_key_exists('is_multiline', $next) &&
            $next['is_multiline'] === true
        ){
            $text = explode("\n", $next['text'], 2);
            $test = trim($text[0]);
            if($test === ''){
                $next['text'] = $text[1];
            }
        }
        return $next;
    }

    public static function variable_define(App $object, $flags, $options, $record = []): bool | array
    {
        if (!array_key_exists('variable', $record)) {
            return false;
        }
        elseif (
            !array_key_exists('is_define', $record['variable']) ||
            $record['variable']['is_define'] !== true
        ) {
            return false;
        }
        $variable_name = $record['variable']['name'];
        $variable_uuid = Core::uuid_variable();
        if(array_key_exists('modifier', $record['variable'])){
            $previous_modifier = '$data->get(\'' . $variable_name . '\')';
            foreach($record['variable']['modifier'] as $nr => $modifier){
                //load modifier through reflection ?
                $modifier_value = '$this->modifier_' . str_replace('.', '_', $modifier['name']) . '(' . PHP_EOL;
                $modifier_value .= '            ' . $previous_modifier .', ' . PHP_EOL;
                if(array_key_exists('argument', $modifier)){
                    foreach($modifier['argument'] as $argument_nr => $argument){
                        $modifier_value .= '            ' . Build::value($object, $flags, $options, $argument) . ',' . PHP_EOL;
                    }
                    $modifier_value = substr($modifier_value, 0, -2) . PHP_EOL;
                }
                $modifier_value .= '        )';
                $previous_modifier = $modifier_value;
            }
            $value = $modifier_value;
            $data = [
                $variable_uuid . ' = ' . $value . ';',
            ];
            $data[] = 'if(' . $variable_uuid .' === null){';
            $data[] = '    throw new Exception(\'Null-pointer exception: "$' . $variable_name . '" on line: ' . $record['line']  . ' you can use modifier "default" to surpress it \');';
            $data[] = '}';
            $data[] = 'if(!is_scalar('. $variable_uuid. ')){';
            $data[] = '    //array or object';
            $data[] = '    ob_get_clean();';
            $data[] = '    return ' . $variable_uuid .';';
            $data[] = '} else {';
            $data[] = '    echo '. $variable_uuid .';';
            $data[] = '}';
            return $data;
        } else {
            $data = [
                $variable_uuid . ' = $data->get(\'' . $variable_name . '\');' ,
            ];
            $data[] = 'if(' . $variable_uuid .' === null){';
            $data[] = '    throw new Exception(\'Null-pointer exception: "$' . $variable_name . '" on line: ' . $record['line']  . ' you can use modifier "default" to surpress it \');';
            $data[] = '}';
            $data[] = 'if(!is_scalar('. $variable_uuid. ')){';
            $data[] = '    //array or object';
            $data[] = '    ob_get_clean();';
            $data[] = '    return ' . $variable_uuid .';';
            $data[] = '} else {';
            $data[] = '    echo '. $variable_uuid .';';
            $data[] = '}';
            return $data;;
        }

        /*
        $modifier_value = '';
        $modifier_list = [];
        if(array_key_exists('modifier', $record['variable'])){
            foreach($record['variable']['modifier'] as $nr => $modifier){
                //load modifier through reflection ?
                $modifier_value = '$variable = $this->modifier_' . str_replace('.', '_', $modifier['name']) . '(' . PHP_EOL;
                $modifier_value .= '            $variable, ' . PHP_EOL;
                if(array_key_exists('argument', $modifier)){
                    foreach($modifier['argument'] as $argument_nr => $argument){
                        $modifier_value .= '            ' . Build::value($object, $flags, $options, $argument) . ',' . PHP_EOL;
                    }
                    $modifier_value = substr($modifier_value, 0, -2) . PHP_EOL;
                }
                $modifier_value .= '        );';
                $modifier_list[] = $modifier_value;
            }
        }
        if(array_key_exists(0, $modifier_list)){
            $data = [
                '$variable = $data->get(\'' . $variable_name . '\');',
            ];
            foreach($modifier_list as $modifier_nr => $modifier){
                $data[] = $modifier;
            }
            $data[] = 'if($variable === null){';
            $data[] = '    throw new Exception(\'Null-pointer exception: "$' . $variable_name . '" on line: ' . $record['line']  . ' you can use modifier "default" to surpress it \');';
            $data[] = '}';
            $data[] = 'if(!is_scalar($variable)){';
            $data[] = '    //array or object';
            $data[] = '    return $variable;';
            $data[] = '} else {';
            $data[] = '    echo $variable;';
            $data[] = '}';
            return $data;
        } else {
            return [
                '$variable = $data->get(\'' . $variable_name . '\');',
                'if($variable === null){',
                '    throw new Exception(\'Null-pointer exception: "$' . $variable_name . '" on line: ' . $record['line']  . ' you can use modifier "default" to surpress it \');',
                '}',
                'if(!is_scalar($variable)){',
                '    //array or object',
                '    return $variable;',
                '} else {',
                '    echo $variable;',
                '}'
            ];
        }
        */
    }

    public static function variable_assign(App $object, $flags, $options, $record = []): bool | string
    {
        if(!array_key_exists('variable', $record)){
            return false;
        }
        elseif(
            !array_key_exists('is_assign', $record['variable']) ||
            $record['variable']['is_assign'] !== true
        ) {
            return false;
        }
        $variable_name = $record['variable']['name'];
        $operator = $record['variable']['operator'];
        $value = Build::value($object, $flags, $options, $record['variable']['value']);
        if(array_key_exists('modifier', $record['variable'])){
            $previous_modifier = '$data->get(\'' . $record['variable']['name'] . '\')';
            foreach($record['variable']['modifier'] as $nr => $modifier){
                //load modifier through reflection ?
                $modifier_value = '$this->modifier_' . str_replace('.', '_', $modifier['name']) . '(' . PHP_EOL;
                $modifier_value .= '            ' . $previous_modifier .', ' . PHP_EOL;
                if(array_key_exists('argument', $modifier)){
                    foreach($modifier['argument'] as $argument_nr => $argument){
                        $modifier_value .= '            ' . Build::value($object, $flags, $options, $argument) . ',' . PHP_EOL;
                    }
                    $modifier_value = substr($modifier_value, 0, -2) . PHP_EOL;
                }
                $modifier_value .= '        )';
                $previous_modifier = $modifier_value;
            }
            $value = $modifier_value;
        }
        if(
            $variable_name !== '' &&
            $operator !== '' &&
            $value !== ''
        ){
            switch($operator){
                case '=' :
                    return '$data->set(\'' . $variable_name . '\', ' . $value . ');';
                case '.=' :
                    return '$data->set(\'' . $variable_name . '\', ' .  '$this->value_plus_concatenate($data->get(\'' . $variable_name . '\'), ' . $value . '));';
                case '+=' :
                    return '$data->set(\'' . $variable_name . '\', ' .  '$this->value_plus($data->get(\'' . $variable_name . '\'), ' . $value . '));';
                case '-=' :
                    return '$data->set(\'' . $variable_name . '\', ' .  '$this->value_minus($data->get(\'' . $variable_name . '\'), ' . $value . '));';
                case '*=' :
                    return '$data->set(\'' . $variable_name . '\', ' .  '$this->value_multiply($data->get(\'' . $variable_name . '\'), ' . $value . '));';
            }
        }
        elseif(
            $variable_name !== '' &&
            $operator !== '' &&
            $value === ''
        ){
            switch($operator){
                case '++' :
                    return '$data->set(\'' . $variable_name . '\', ' .  '$this->value_plus_plus($data->get(\'' . $variable_name . '\')));';
                case '--' :
                    return '$data->set(\'' . $variable_name . '\', ' .  '$this->value_minus_minus($data->get(\'' . $variable_name . '\')));';
            }
        }
        return false;
    }

    /**
     * @throws Exception
     */
    public static function value(App $object, $flags, $options, $input): string
    {
        $value = '';
        $skip = 0;
        foreach($input['array'] as $nr => $record){
            if($skip > 0){
                $skip--;
                continue;
            }
            $current = Token::item($input, $nr);
            $next = Token::item($input, $nr + 1);
            if(
                array_key_exists('is_single_quoted', $record) &&
                array_key_exists('execute', $record) &&
                $record['is_single_quoted'] === true
            ){
                $value .= '\'' . $record['execute'] . '\'';
            }
            elseif(
                array_key_exists('is_null', $record) &&
                $record['is_null'] === true
            ){
                $value .= 'NULL';
            }
            elseif(
                in_array(
                    $current,
                    [
                        '\\',
                        '"',
                        '(',
                        ')',
                        '\'',
                    ],
                    true
                )
            ){
                $value .= $current;
            }
            elseif(
                array_key_exists('type', $record) &&
                $record['type'] === 'string'
            ){
                $value .=  $record['execute'];
            }
            elseif(
                array_key_exists('type', $record) &&
                $record['type'] === 'variable'
            ){
                $modifier_value = '';
                if(array_key_exists('modifier', $record)){
                    $previous_modifier = '$data->get(\'' . $record['name'] . '\')';
                    foreach($record['modifier'] as $modifier_nr => $modifier){
                        //load modifier through reflection ?
                        $modifier_value = '$this->modifier_' . str_replace('.', '_', $modifier['name']) . '(' . PHP_EOL;
                        $modifier_value .= '            '. $previous_modifier .', ' . PHP_EOL;
                        if(array_key_exists('argument', $modifier)){
                            foreach($modifier['argument'] as $argument_nr => $argument){
                                $modifier_value .= '            ' . Build::value($object, $flags, $options, $argument) . ',' . PHP_EOL;
                            }
                            $modifier_value = substr($modifier_value, 0, -2) . PHP_EOL;
                        }
                        $modifier_value .= '        )';
                        $previous_modifier = $modifier_value;
                    }
                    $value .= $modifier_value;
                } else {
                    $value .= '$data->get(\'' . $record['name'] . '\')';
                }
            }
            elseif(
                array_key_exists('is_hex', $record) &&
                $record['is_hex'] === true
            ) {
                if($value === ''){
                    $value .= $record['execute'];
                } else {
                    //no hex if we have value
                    $value .= $record['value'];
                }
            }
            elseif(
                array_key_exists('type', $record) &&
                $record['type'] === 'integer'
            ){
                $value .=  $record['execute'];
            }
            else {
                $right = Build::value_right(
                    $object,
                    $flags,
                    $options,
                    $input,
                    $nr,
                    $next,
                    $skip
                );
                $right = Build::value($object, $flags, $options, $right);
                switch($current){
                    case '+':
                        $value = '$this->value_plus(' . $value . ', ' . $right . ')';
                    break;
                    case '-':
                        $value = '$this->value_minus(' . $value . ', ' . $right . ')';
                    break;
                    case '*':
                        $value = '$this->value_multiply(' . $value . ', ' . $right . ')';
                    break;
                    case '%':
                        $value = '$this->value_modulo(' . $value . ', ' . $right . ')';
                    break;
                    case '/':
                        $value = '$this->value_divide(' . $value . ', ' . $right . ')';
                    break;
                    case '<':
                        $value = '$this->value_smaller(' . $value . ', ' . $right . ')';
                    break;
                    case '<=':
                        $value = '$this->value_smaller_equal(' . $value . ', ' . $right . ')';
                    break;
                    case '<<':
                        $value = '$this->value_smaller_smaller(' . $value . ', ' . $right . ')';
                    break;
                    case '>':
                        $value = '$this->value_greater(' . $value . ', ' . $right . ')';
                    break;
                    case '>=':
                        $value = '$this->value_greater_equal(' . $value . ', ' . $right . ')';
                    break;
                    case '>>':
                        $value = '$this->value_greater_greater(' . $value . ', ' . $right . ')';
                    break;
                    case '==':
                        $value = '$this->value_equal(' . $value . ', ' . $right . ')';
                    break;
                    case '===':
                        $value = '$this->value_identical(' . $value . ', ' . $right . ')';
                    break;
                    case '!=':
                        $value = '$this->value_not_equal(' . $value . ', ' . $right . ')';
                    break;
                    case '!==':
                        $value = '$this->value_not_identical(' . $value . ', ' . $right . ')';
                    break;
                    case '??':
                        $value = $value . ' ?? ' . $right;
                    break;
                    case '&&':
                        $value = $value . ' && ' . $right;
                    break;
                    case '||':
                        $value = $value . ' || ' . $right;
                    break;
                }
            }
        }
        return $value;
    }

    /**
     * @throws Exception
     */
    public static function value_right(App $object, $flags, $options, $input, $nr, $next, &$skip=0): array
    {
        $count = count($input['array']);
        $right = '';
        $right_array = [];
        switch($next){
            case '(':
                $set_depth = 1;
                for($i = $nr + 1; $i < $count; $i++){
                    $previous = Token::item($input, $i - 1);
                    $item = Token::item($input, $i);
                    if($item === '('){
                        $set_depth++;
                    }
                    elseif($item === ')'){
                        $set_depth--;
                    }
                    if(
                        $item === ')' &&
                        $set_depth === 0 &&
                        $i > ($nr + 1)
                    ){
                        $right .= $item;
                        $right_array[] = $input['array'][$i];
                        $skip++;
                        break;
                    }
                    $right .= $item;
                    if(!array_key_exists($i, $input['array'])){
                        d($i);
                        d($item);
                        ddd($input);
                    }
                    $right_array[] = $input['array'][$i];
                    $skip++;
                }
                break;
            case '\'':
                for($i = $nr + 1; $i < $count; $i++){
                    $previous = Token::item($input, $i - 1);
                    $item = Token::item($input, $i);
                    if(
                        $item === '\'' &&
                        $previous !== '\\' &&
                        $i > ($nr + 1)
                    ){
                        $right .= $item;
                        $right_array[] = $input['array'][$i];
                        $skip++;
                        break;
                    }
                    $right .= $item;
                    $right_array[] = $input['array'][$i];
                    $skip++;
                }
                break;
            case '"':
                d($input);
                for($i = $nr + 1; $i < $count; $i++){
                    $previous = Token::item($input, $i - 1);
                    $item = Token::item($input, $i);
                    if(
                        $item === '"' &&
                        $previous !== '\\' &&
                        $i > ($nr + 1)
                    ){
                        $right .= $item;
                        $right_array[] = $input['array'][$i];
                        $skip++;
                        break;
                    }
                    $right .= $item;
                    $right_array[] = $input['array'][$i];
                    $skip++;
                }
                break;
            case NULL:
                $right = 'NULL';
                $right_array[] = [
                    'value' => $right,
                    'execute' => NULL,
                    'is_null' => true
                ];
                $skip++;
            break;
            default:
                throw new Exception('Not implemented: ' . $next . ' on line ' . __LINE__ . ' in ' . __FILE__);
        }
        return [
            'string' => $right,
            'array' => $right_array
        ];
    }

}