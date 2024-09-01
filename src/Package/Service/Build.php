<?php
namespace Package\R3m\Io\Parse\Service;

use R3m\Io\App;

use R3m\Io\Module\Core;
use R3m\Io\Module\File;

use Exception;

class Build
{
    /**
     * @throws Exception
     */
    public static function create(App $object, $flags, $options, $tags=[]): array
    {
        $options->class = 'Main';
        Build::document_default($object, $flags, $options);
        $data = Build::document_tag($object, $flags, $options, $tags);
        $document = Build::document_header($object, $flags, $options);
        $document = Build::document_use($object, $flags, $options, $document, 'package.r3m_io/parse.build.use.class');
        $document[] = '';
        $document[] = 'class '. $options->class .' {';
        $document[] = '';
        $object->config('package.r3m_io/parse.build.state.indent', $object->config('package.r3m_io/parse.build.state.indent') + 1);
        //indent++
        $document = Build::document_use($object, $flags, $options, $document, 'package.r3m_io/parse.build.use.trait');
        $document[] = '';
        $document = Build::document_construct($object, $flags, $options, $document);
        $document[] = '';
        $document = Build::document_run($object, $flags, $options, $document, $data);
        $document[] = '}';
        d($document);
        return $document;
    }

    /**
     * @throws Exception
     */
    public static function document_header(App $object, $flags, $options): array
    {
        $document[] = '<?php';
        $document[] = '/**';
        $document[] = ' * @package Package\R3m\Io\Parse';
        $document[] = ' * @license MIT';
        $document[] = ' * @version ' . $object->config('framework.version');
        $document[] = ' * @author ' . 'Remco van der Velde (remco@universeorange.com)';
        $document[] = ' * @compile-date ' . date('Y-m-d H:i:s');
        $document[] = ' * @compile-time ' . round((microtime(true) - $object->config('package.r3m_io/parse.time.start')) * 1000, 3) . ' ms';
        $document[] = ' * @note compiled by ' . $object->config('framework.name') . ' ' . $object->config('framework.version');
        $document[] = ' * @url ' . $object->config('framework.url');
        $document[] = ' */';
        $document[] = '';
        $document[] = 'namespace Package\R3m\Io\Parse;';
        $document[] = '';
        return $document;
    }

    public static function document_tag(App $object, $flags, $options, $tags = []): array
    {
        $data = [];
        $variable_assign_next_tag = false;
        foreach($tags as $row_nr => $list){
            foreach($list as $nr => &$record){
                $text = Build::text($object, $flags, $options, $record, $variable_assign_next_tag);
                if($text){
                    $text = explode(PHP_EOL, $text);
                    foreach($text as $text_nr => $line) {
                        $data[] = $line;
                    }
                }
                $variable_assign_next_tag = false; //Build::text is taking care of this
                $variable_assign = Build::variable_assign($object, $flags, $options, $record);
                if($variable_assign){
                    $data[] = $variable_assign;
                    $next = $list[$nr + 1] ?? false;
                    if($next !== false){
                        $tags[$row_nr][$nr + 1] = Build::variable_assign_next($object, $flags, $options, $record, $next);
                        $list[$nr + 1] = $tags[$row_nr][$nr + 1];
                    } else {
                        $variable_assign_next_tag = true;
                    }
                }
                $variable_define = Build::variable_define($object, $flags, $options, $record);
                if($variable_define){
                    foreach($variable_define as $variable_define_nr => $line){
                        $data[] = $line;
                    }
                }
                $method = Build::method($object, $flags, $options, $record);
                if($method){
                    $data[] = $method;
                }
            }
        }
        return $data;
    }

    public static function document_construct(App $object, $flags, $options, $document = []): array
    {
        $document[] = '    public function __construct(App $object, Parse $parse, Data $data, $flags, $options){';
        $document[] = '        $this->object($object);';
        $document[] = '        $this->parse($parse);';
        $document[] = '        $this->data($data);';
        $document[] = '        $this->flags($flags);';
        $document[] = '        $this->options($options);';
        $document[] = '    }';
        return $document;
    }

    public static function document_run(App $object, $flags, $options, $document = [], $data = []): array
    {
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
        return $document;
    }


    /**
     * @throws Exception
     */
    public static function document_default(App $object, $flags, $options): void
    {
        $use_class = $object->config('package.r3m_io/parse.build.use.class');
        if(empty($use_class)){
            $use_class = [];
            $use_class[] = 'R3m\Io\App';
            $use_class[] = 'R3m\Io\Module\Data';
            $use_class[] = 'Package\R3m\Io\Parse\Service\Parse';
            $use_class[] = 'Plugin';
            $use_class[] = 'Exception';
        }
        $object->config('package.r3m_io/parse.build.use.class', $use_class);
        $use_trait = $object->config('package.r3m_io/parse.build.use.trait');
        if(empty($use_trait)){
            $use_trait = [];
            $use_trait[] = 'Plugin\Basic';
            $use_trait[] = 'Plugin\Parse';
            $use_trait[] = 'Plugin\Value';
        }
        $object->config('package.r3m_io/parse.build.use.trait', $use_trait);
        $object->config('package.r3m_io/parse.build.state.echo', true);
        $object->config('package.r3m_io/parse.build.state.indent', 0);
    }

    /**
     * @throws Exception
     */
    public static function document_use(App $object, $flags, $options, $document = [], $attribute=''): array
    {
        $use_class = $object->config($attribute);
        $indent = $object->config('package.r3m_io/parse.build.state.indent');
        if($use_class){
            foreach($use_class as $nr => $use){
                if(empty($use)){
                    $document[] = '';
                } else {
                    $document[] = str_repeat(' ', $indent * 4) . 'use ' . $use . ';';
                }
            }
        }
        return $document;
    }

    public static function text(App $object, $flags, $options,$record = [], $variable_assign_next_tag = false): bool | string
    {
        $is_echo = $object->config('package.r3m_io/parse.build.state.echo');
        if($is_echo !== true){
            return false;
        }
        if(
            array_key_exists('text', $record) &&
            $record['text'] !== ''
        ){
            if(
                array_key_exists('is_multiline', $record) &&
                $record['is_multiline'] === true
            ){
                $text = explode("\n", $record['text'], 2);
                $test = trim($text[0]);
                if($test === ''){
                    $record['text'] = $text[1];
                }
            }
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

    /**
     * @throws Exception
     */
    public static function plugin(App $object, $flags, $options, $name): string
    {
        if(
            in_array(
                $name,
                [
                    'default',
                    'object',
                    'echo',
                    'parse',
                    'break',
                    'continue'
                ],
                true
            )
        ){
            $plugin = 'plugin_' . $name;
        } else {
            $plugin = $name;
        }
        $plugin = str_replace('.', '_', $plugin);
        $plugin = str_replace('-', '_', $plugin);

        $use_plugin = explode('_', $plugin);
        foreach($use_plugin as $nr => $use){
            $use_plugin[$nr] = ucfirst($use);
        }
        $use_plugin = 'Plugin\\' . implode('_', $use_plugin);

        $use = $object->config('package.r3m_io/parse.build.use.trait');
        if(!$use){
            $use = [];
        }
        if(
            !in_array(
                $use_plugin,
                [
                    'Plugin\\Value_Concatenate',
                    'Plugin\\Value_Plus_Plus',
                    'Plugin\\Value_Minus_Minus',
                    'Plugin\\Value_Plus',
                    'Plugin\\Value_Minus',
                    'Plugin\\Value_Multiply',
                    'Plugin\\Value_Modulo',
                    'Plugin\\Value_Divide',
                    'Plugin\\Value_Smaller',
                    'Plugin\\Value_Smaller_Equal',
                    'Plugin\\Value_Smaller_Smaller',
                    'Plugin\\Value_Greater',
                    'Plugin\\Value_Greater_Equal',
                    'Plugin\\Value_Greater_Greater',
                    'Plugin\\Value_Equal',
                    'Plugin\\Value_Identical',
                    'Plugin\\Value_Not_Equal',
                    'Plugin\\Value_Not_Identical',
                    'Plugin\\Value_And',
                    'Plugin\\Value_Or',
                    'Plugin\\Value_Xor',
                    'Plugin\\Value_Null_Coalescing',
                ],
                true
            )
        ){
            if(!in_array($use_plugin, $use, true)){
                $use[] = $use_plugin;
            }
        }
        $object->config('package.r3m_io/parse.build.use.trait', $use);
        return strtolower($plugin);
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
        if(!array_key_exists('name', $record['variable'])){
            trace();
            ddd($record);
        }
        $variable_name = $record['variable']['name'];
        $variable_uuid = Core::uuid_variable();
        if(array_key_exists('modifier', $record['variable'])){
            $previous_modifier = '$data->get(\'' . $variable_name . '\')';
            foreach($record['variable']['modifier'] as $nr => $modifier){
                $plugin = Build::plugin($object, $flags, $options, str_replace('.', '_', $modifier['name']));
                $modifier_value = '$this->' . $plugin . '(' . PHP_EOL;
                $modifier_value .= '            ' . $previous_modifier .', ' . PHP_EOL;
                $is_argument = false;
                if(array_key_exists('argument', $modifier)){
                    foreach($modifier['argument'] as $argument_nr => $argument){
                        $modifier_value .= '            ' . Build::value($object, $flags, $options, $argument) . ',' . PHP_EOL;
                        $is_argument = true;
                    }
                    if($is_argument === true){
                        $modifier_value = substr($modifier_value, 0, -2) . PHP_EOL;
                    } else {
                        $modifier_value = substr($modifier_value, 0, -1);
                    }
                }
                $modifier_value .= ')';
                $previous_modifier = $modifier_value;
            }
            $value = $modifier_value;
            $data = [
                $variable_uuid . ' = ' . $value . ';',
            ];
            if(
                array_key_exists('is_multiline', $record) &&
                $record['is_multiline'] === true
            ){
                $data[] = 'if(' . $variable_uuid .' === null){';
                $data[] = '    throw new Exception(\'Null-pointer exception: "$' . $variable_name . '" on line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . '. You can use modifier "default" to surpress it \');';
                $data[] = '}';
            } else {
                $data[] = 'if(' . $variable_uuid .' === null){';
                $data[] = '    throw new Exception(\'Null-pointer exception: "$' . $variable_name . '" on line: ' . $record['line']  . ', column: ' . $record['column']['start'] . '. You can use modifier "default" to surpress it \');';
                $data[] = '}';
            }
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
            if(
                array_key_exists('is_multiline', $record) &&
                $record['is_multiline'] === true
            ){
                $data[] = 'if(' . $variable_uuid .' === null){';
                $data[] = '    throw new Exception(\'Null-pointer exception: "$' . $variable_name . '" on line: ' . $record['line']['start']  . ', column: ' . $record['column'][$record['line']['start']]['start'] . '. You can use modifier "default" to surpress it \');';
                $data[] = '}';
            } else {
                $data[] = 'if(' . $variable_uuid .' === null){';
                $data[] = '    throw new Exception(\'Null-pointer exception: "$' . $variable_name . '" on line: ' . $record['line']  . ', column: ' . $record['column']['start'] . '. You can use modifier "default" to surpress it \');';
                $data[] = '}';
            }
            $data[] = 'if(!is_scalar('. $variable_uuid. ')){';
            $data[] = '    //array or object';
            $data[] = '    ob_get_clean();';
            $data[] = '    return ' . $variable_uuid .';';
            $data[] = '} else {';
            $data[] = '    echo '. $variable_uuid .';';
            $data[] = '}';
            return $data;;
        }
    }

    public static function method(App $object, $flags, $options, $record = []): bool | string
    {
        if(!array_key_exists('method', $record)){
            return false;
        }
        $method_name = $record['method']['name'];
        $plugin = Build::plugin($object, $flags, $options, str_replace('.', '_', $method_name));
        $method_value = '$this->' . $plugin . '(' . PHP_EOL;
        $is_argument = false;
        foreach($record['method']['argument'] as $nr => $argument) {
            $method_value .= '            ' . Build::value($object, $flags, $options, $argument) . ',' . PHP_EOL;
            $is_argument = true;
        }
        if($is_argument){
            $method_value = substr($method_value, 0, -2) . PHP_EOL;
        }
        $method_value .= '        );';
        return $method_value;
    }

    public static function variable_assign(App $object, $flags, $options, $record = []): bool | string
    {
        d($record);
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
        d($variable_name);
        $operator = $record['variable']['operator'];
        d($operator);
        $value = Build::value($object, $flags, $options, $record['variable']['value']);
        d($value);
        if(array_key_exists('modifier', $record['variable'])){
            $previous_modifier = '$data->get(\'' . $record['variable']['name'] . '\')';
            foreach($record['variable']['modifier'] as $nr => $modifier){
                $plugin = Build::plugin($object, $flags, $options, str_replace('.', '_', $modifier['name']));
                $modifier_value = '$this->' . $plugin . '(' . PHP_EOL;
                $modifier_value .= '            ' . $previous_modifier .', ' . PHP_EOL;
                if(array_key_exists('argument', $modifier)){
                    $is_argument = false;
                    foreach($modifier['argument'] as $argument_nr => $argument){
                        $modifier_value .= '            ' . Build::value($object, $flags, $options, $argument) . ',' . PHP_EOL;
                        $is_argument = true;
                    }
                    if($is_argument === true){
                        $modifier_value = substr($modifier_value, 0, -2) . PHP_EOL;
                    } else {
                        $modifier_value = substr($modifier_value, 0, -1);
                    }
                }
                $modifier_value .= '            ' . ')';
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

    public static function value_single_quote(App $object, $flags, $options, $input): array
    {
        $is_single_quote = false;
        foreach($input['array'] as $nr => $record){
            $current = Token::item($input, $nr);
            $next = Token::item($input, $nr + 1);
            if(
                $current === '\''  &&
                $is_single_quote === false
            ){
                $is_single_quote = $nr;
            }
            elseif(
                $current === '\''  &&
                $is_single_quote !== false
            ){
                for($i = $is_single_quote + 1; $i <= $nr; $i++){
                    $current = Token::item($input, $i);
                    $input['array'][$is_single_quote]['value'] .= $current;
                    $input['array'][$i] = null;
                }
                $input['array'][$is_single_quote]['type'] = 'string';
                $input['array'][$is_single_quote]['execute'] = $input['array'][$is_single_quote]['value'];
                $input['array'][$is_single_quote]['is_single_quoted'] = true;
                $is_single_quote = false;
            }
        }
        $input = Token::cleanup($object, $flags, $options, $input);
        return $input;
    }

    /**
     * @throws Exception
     */
    public static function value(App $object, $flags, $options, $input): string
    {
        $value = '';
        $skip = 0;
        $input = Build::value_single_quote($object, $flags, $options, $input);
        $is_double_quote = false;
        $double_quote_previous = false;
        $is_array = false;
        if(
            array_key_exists('type', $input) &&
            $input['type'] === 'array'
        ){
            $is_array = true;
        }
        foreach($input['array'] as $nr => $record){
            if($skip > 0){
                $skip--;
                continue;
            }
            $previous = Token::item($input, $nr - 1);
            $current = Token::item($input, $nr);
            $next = Token::item($input, $nr + 1);
            if(
                array_key_exists('is_single_quoted', $record) &&
                array_key_exists('execute', $record) &&
                $record['is_single_quoted'] === true
            ){
                $value .= $record['execute'];
            }
            elseif(
                array_key_exists('type', $record) &&
                $record['type'] === 'integer'
            ){
                $value .= $record['execute'];
            }
            elseif(
                array_key_exists('type', $record) &&
                $record['type'] === 'float'
            ){
                $value .= $record['execute'];
            }
            elseif(
                array_key_exists('is_hex', $record) &&
                $record['is_hex'] === true
            ) {
                $value .= $record['execute'];
            }
            elseif(
                array_key_exists('type', $record) &&
                $record['type'] === 'symbol'
            ){
                if($next === null){
                    $value .= $record['value'] .
                        PHP_EOL .
                        '        ';
                } else {
                    $value .= $record['value'] .
                        PHP_EOL .
                        '            ';
                }
            }
            elseif(
                array_key_exists('value', $record) &&
                in_array(
                    $record['value'],
                    [
                        '{{',
                        '}}'
                    ],
                    true
                )
            ){
                if(
                    $is_double_quote === true &&
                    $record['value'] === '{{'
                ){
                    if($double_quote_previous === '\\'){
                        $value .= '\\" . ';
                    } else {
                        $value .= '" . ';
                    }
                    $double_quote_previous = false;

                }
                elseif(
                    $is_double_quote === true &&
                    $record['value'] === '}}'
                ){
                    if($double_quote_previous === '\\'){
                        $value .= ' . \\"';
                    } else {
                        $value .= ' . "';
                    }
                    $double_quote_previous = false;
                } else {
                    //nothing
                }

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
                if(
                    $current === '"' &&
                    $is_double_quote === false
                ){
                    $is_double_quote = true;
                    $double_quote_previous = $previous;
                }
                elseif(
                    $current === '"' &&
                    $is_double_quote === true
                ){
                    $is_double_quote = false;
                    $double_quote_previous = $previous;
                }
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
                $record['type'] === 'array'
            ){
                $value .= Build::value($object, $flags, $options, $record);
            }
            elseif(
                array_key_exists('type', $record) &&
                $record['type'] === 'method'
            ){
                $plugin = Build::plugin($object, $flags, $options, str_replace('.', '_', $record['method']['name']));
                $method_value = '$this->' . $plugin . '(' . PHP_EOL;
                if(
                    array_key_exists('method', $record) &&
                    array_key_exists('argument', $record['method'])
                ){
                    $is_argument = false;
                    foreach($record['method']['argument'] as $argument_nr => $argument){
                        $method_value .= Build::value($object, $flags, $options, $argument) . ',' . PHP_EOL;
                        $is_argument = true;
                    }
                    if($is_argument === true){
                        $method_value = substr($method_value, 0, -2) . PHP_EOL;
                    } else {
                        $method_value = substr($method_value, 0, -1);
                    }
                }
                $method_value .= ')';
                $value .= $method_value;
            }
            elseif(
                array_key_exists('type', $record) &&
                $record['type'] === 'variable'
            ){
                $modifier_value = '';
                if(array_key_exists('modifier', $record)){
                    $previous_modifier = '$data->get(\'' . $record['name'] . '\')';
                    foreach($record['modifier'] as $modifier_nr => $modifier){
                        $plugin = Build::plugin($object, $flags, $options, str_replace('.', '_', $modifier['name']));
                        $modifier_value = '$this->' . $plugin . '(' . PHP_EOL;
                        $modifier_value .= '            '. $previous_modifier .', ' . PHP_EOL;
                        $is_argument = false;
                        if(array_key_exists('argument', $modifier)){
                            foreach($modifier['argument'] as $argument_nr => $argument){
                                $modifier_value .= '            ' . Build::value($object, $flags, $options, $argument) . ',' . PHP_EOL;
                                $is_argument = true;
                            }
                            if($is_argument === true){
                                $modifier_value = substr($modifier_value, 0, -2) . PHP_EOL;
                            } else {
                                $modifier_value = substr($modifier_value, 0, -1);
                            }
                        }
                        $modifier_value .= '        ' . ')';
                        $previous_modifier = $modifier_value;
                    }
                    $value .= $modifier_value;
                } else {
                    $value .= '$data->get(\'' . $record['name'] . '\')';
                }
            }
            elseif(
                array_key_exists('type', $record) &&
                $record['type'] === 'whitespace' &&
                $is_double_quote === true
            ){
                $value .=  $record['value'];
            }
            elseif(
                array_key_exists('type', $record) &&
                $record['type'] === 'whitespace' &&
                $is_double_quote === false
            ){
                //nothing
            } else {
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
        d($value);
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
                for($i = $nr + 1; $i < $count; $i++){
                    $previous = Token::item($input, $i - 1);
                    $item = Token::item($input, $i);
                    if(
                        in_array(
                            $item,
                            [
                                '.',
                                '+',
                                '-',
                                '*',
                                '%',
                                '/',
                                '<',
                                '<=',
                                '<<',
                                '>',
                                '>=',
                                '>>',
                                '==',
                                '===',
                                '!=',
                                '!==',
                                '??',
                                '&&',
                                '||',
                            ],
                            true
                        )
                    ){
                        break;
                    }
                    $right .= $item;
                    $right_array[] = $input['array'][$i];
                    $skip++;
                }
                break;
        }
        return [
            'string' => $right,
            'array' => $right_array
        ];
    }

}