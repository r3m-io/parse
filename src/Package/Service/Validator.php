<?php
namespace Package\R3m\Io\Parse\Service;

use R3m\Io\App;

use R3m\Io\Module\Core;
use R3m\Io\Module\Dir;
use R3m\Io\Module\File;

use Exception;

use R3m\Io\Exception\ObjectException;
use R3m\Io\Exception\DirectoryCreateException;

class Validator
{

    /**
     * @throws DirectoryCreateException
     * @throws Exception
     */
    private static function dir_ramdisk(App $object): string
    {
        $posix_id = $object->config('posix.id');
        $dir_ramdisk = $object->config('ramdisk.url');
        $dir_ramdisk_user = $dir_ramdisk .
            $posix_id .
            $object->config('ds')
        ;
        $dir_ramdisk_parse = $dir_ramdisk_user .
            'Parse' .
            $object->config('ds')
        ;
        if(!Dir::is($dir_ramdisk_user)){
            Dir::create($dir_ramdisk_user,  Dir::CHMOD);
        }
        if(!Dir::is($dir_ramdisk_parse)){
            Dir::create($dir_ramdisk_parse,  Dir::CHMOD);
        }
        if($posix_id !== 0){
            File::permission($object, [
                'url' => $dir_ramdisk_user,
                'parse' => $dir_ramdisk_parse
            ]);
        }
        return $dir_ramdisk_parse;
    }

    /**
     * @throws ObjectException
     * @throws Exception
     */
    public static function validate(App $object, $string): bool | string
    {
        $dir = Validator::dir_ramdisk($object);
        $url = $dir . 'Validate-' . hash('sha256', $string) . $object->config('extension.php');
        if(File::exist($url) === false){
            File::write($url, '<?php ' . PHP_EOL . $string . PHP_EOL);
        }
        // Use PHP's built-in syntax checker
        Core::execute($object, 'php -l ' . escapeshellarg($url), $output, $notification);
        // Check the output to see if any syntax errors were found
        if (strpos($output, 'No syntax errors detected') !== false) {
            return true;
        } else {
            if($notification !== ''){
                //don't need $output
                throw new Exception($notification);
            }
            throw new Exception($output);
        }
    }
}