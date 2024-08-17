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
        $document[] = '';
        $document[] = 'use Exception;';
        $document[] = '';
        $document[] = 'class '. $options->class .' {';
        $document[] = '';
        $document[] = '    use Basic;';
        $document[] = '    use Parser;';
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
        $document[] = '        return null;';
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
                d($line);
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
            d($result);
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
        $assign = '$variable = ';
        $variable_name = $record['variable']['name'];
        return [
            '$variable = $data->get(\'' . $variable_name . '\');',
            'if($variable === null){',
            '    throw new Exception(\'Variable: "' . $variable_name . '" not assigned on line: ' . $record['line']  . ' you can use modifier "default" to surpress it \');',
            '}',
            'if(!is_scalar($variable)){',
            '    //array or object',
            '    return $variable;',
            '} else {',
            '    echo $variable;',
            '}'
        ];
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
        $value = Build::variable_value($object, $flags, $options, $record['variable']['value']);
        if(
            $variable_name !== '' &&
            $operator !== '' &&
            $value !== ''
        ){
            switch($operator){
                case '=' :
                    return '$data->set(\'' . $variable_name . '\',' . $value . ');';
                case '.=' :
                    return '$data->set(\'' . $variable_name . '\', ' .  $options->class . '::value_concat($data->get(\'' . $variable_name . '\'),' . $value . '));';
                case '+=' :
                    return '$data->set(\'' . $variable_name . '\', ' .  $options->class . '::value_plus($data->get(\'' . $variable_name . '\'),' . $value . '));';
                case '-=' :
                    return '$data->set(\'' . $variable_name . '\', ' .  $options->class . '::value_min($data->get(\'' . $variable_name . '\'),' . $value . '));';
                case '*=' :
                    return '$data->set(\'' . $variable_name . '\', ' .  $options->class . '::value_multiply($data->get(\'' . $variable_name . '\'),' . $value . '));';
            }

        }
        return false;
    }

    public static function variable_value(App $object, $flags, $options, $input): string
    {
        $value = '';
        $count = count($input['array']);
        foreach($input['array'] as $nr => $record){
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
                in_array(
                    $current,
                    [
                        '\\',
                        '"'
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
            else {
                switch($current){
                    case '+':
                        $right = '';
                        switch($next){
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
                                        break;
                                    }
                                    $right .= $item;
                                }
                                break;
                            case '"':
                                for($i = $nr + 1; $i < $count; $i++){
                                    $previous = Token::item($input, $i - 1);
                                    $item = Token::item($input, $i);
                                    if(
                                        $item === '"' &&
                                        $previous !== '\\' &&
                                        $i > ($nr + 1)
                                    ){
                                        $right .= $item;
                                        break;
                                    }
                                    $right .= $item;
                                }
                                break;
                            default:
                                d($current);
                                ddd($next);
                        }
                        $value = 'value_plus(' . $value . ',' . $right . ')';
                        $right = '';
                }
                d($next);
                ddd($value);
            }
        }
        return $value;
    }

}