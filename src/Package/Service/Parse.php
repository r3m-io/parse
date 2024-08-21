<?php
namespace Package\R3m\Io\Parse\Service;

use R3m\Io\App;

use R3m\Io\Module\Cli;
use R3m\Io\Module\Data;

use Plugin;

use R3m\Io\Module\Dir;
use R3m\Io\Module\File;

use R3m\Io\Node\Model\Node;

use Exception;

use R3m\Io\Exception\ObjectException;

class Parse
{
    const NODE = 'System.Parse';
    const CONFIG = 'package.r3m_io/parse';


    use Plugin\Basic;

    /**
     * @throws ObjectException
     * @throws Exception
     */
    public function __construct(App $object, Data $data, $flags, $options){
        $this->object($object);
        $this->data($data);
        $this->flags($flags);
        $this->options($options);
        $this->config();
    }

    /**
     * @throws ObjectException
     * @throws Exception
     */
    protected function config(): void
    {
        $object = $this->object();
        $node = new Node($object);
        $parse = $node->record(
            Parse::NODE,
            $node->role_system(),
            [
                'ramdisk' => true
            ]
        );
        if(!$parse){
            $url = $this->object()->config('framework.dir.vendor') .
                'r3m_io' .
                $this->object()->config('ds') .
                'parse' .
                $this->object()->config('ds') .
                'Data' .
                $this->object()->config('ds') .
                Parse::NODE .
                $this->object()->config('extension.json')
            ;
            ddd($url);

        }
        $object->config(Parse::CONFIG . '.time.start', microtime(true));
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

        d($object->config('package'));

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