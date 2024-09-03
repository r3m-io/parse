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

        $url = $dir . 'validate-' . hash('sha256', $string) . $object->config('extension.php');

        ddd($url);



        // Create a temporary file and write the PHP code into it
        $tempFile = tempnam(sys_get_temp_dir(), 'PHP');
        file_put_contents($tempFile, "<?php\n" . $string . "\n");
        // Use PHP's built-in syntax checker
        Core::execute($object, 'php -l ' . escapeshellarg($tempFile), $output, $notification);
//        exec("php -l " . escapeshellarg($tempFile), $output, $code);
        // Delete the temporary file
        unlink($tempFile);
//        $output = implode(PHP_EOL, $output);
        // Check the output to see if any syntax errors were found
        if (strpos($output, 'No syntax errors detected') !== false) {
            return true;
        } else {
            if($notification !== ''){
                throw new Exception($output . PHP_EOL . $notification);
            }
            throw new Exception($output);
        }
    }
}