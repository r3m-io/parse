<?php
namespace Package\R3m\Io\Parse\Service;

use R3m\Io\App;

use R3m\Io\Module\Cli;
use R3m\Io\Module\Data;

use Plugin;
use Exception;

use R3m\Io\Module\Dir;
use R3m\Io\Module\File;

class Parse
{
    use Plugin\Basic;

    public function __construct(App $object, Data $data, $flags, $options){
        $object->config('package.r3m_io/parse.time.start', microtime(true));
        $this->object($object);
        $this->data($data);
        $this->flags($flags);
        $this->options($options);
    }

    /**
     * @throws Exception
     */
    public function compile($input, $data=null){
        if(is_array($data)){
            $data = new Data($data);
            $this->data($data);
        }
        elseif(
            is_object($data) &&
            !($data instanceof Data)
        ){
            $data = new Data($data);
            $this->data($data);
        } else {
            $data = $this->data();
        }
        $object = $this->object();
        $flags = $this->flags();
        $options = $this->options();
        $token = Token::tokenize($object, $flags, $options, $input);
        $document = Build::create($object, $flags, $options, $token);

        $dir = $object->config('project.dir.data') .
            'Test' .
            $object->config('ds') .
            'Parse' .
            $object->config('ds');
        Dir::create($dir, Dir::CHMOD);
        $url = $dir .
            'Main.php'
        ;
        File::write($url, implode(PHP_EOL, $document));
        File::permission(
            $object,
            [
                'dir' => $dir,
                'url' => $url
            ]
        );


        require_once $url;
        echo PHP_EOL . str_repeat('-', Cli::tput('columns')) . PHP_EOL;

        $main = new \Package\R3m\Io\Parse\Main($object, $this, $data, $flags, $options);
        return $main->run();

        /*
        // Step 2: Define the placeholder values
        $placeholders = [
            'name' => 'John Doe',
            'age' => '30',
            // Add more placeholders and their replacements as needed
        ];
        // Step 3: Replace placeholders with actual values
        foreach ($placeholders as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }
        // Step 4: Output the processed template
        dd($template);
        */
    }

}