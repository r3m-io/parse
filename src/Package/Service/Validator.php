<?php
namespace Package\R3m\Io\Parse\Service;

use R3m\Io\App;

use R3m\Io\Module\Core;

class Validator
{

    public static function validate(App $object, $string, &$code=null): bool | string
    {
        ob_start();
        // Create a temporary file and write the PHP code into it
        $tempFile = tempnam(sys_get_temp_dir(), 'PHP');
        file_put_contents($tempFile, "<?php\n" . $string . "\n");
        // Use PHP's built-in syntax checker
        Core::execute($object, 'php -l ' . escapeshellarg($tempFile), $output, $notification);
//        exec("php -l " . escapeshellarg($tempFile), $output, $code);
        // Delete the temporary file
        unlink($tempFile);
        ob_end_clean();
        d($notification);
        d($output);
//        $output = implode(PHP_EOL, $output);
        // Check the output to see if any syntax errors were found
        if (strpos($output, 'No syntax errors detected') !== false) {
            return true;
        } else {
            return $output;
        }
    }
}