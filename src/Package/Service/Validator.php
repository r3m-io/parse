<?php
namespace Package\R3m\Io\Parse\Service;

class Validator
{

    public static function validate($code): bool | string
    {
        ob_start();
        // Create a temporary file and write the PHP code into it
        $tempFile = tempnam(sys_get_temp_dir(), 'PHP');
        file_put_contents($tempFile, "<?php\n" . $code . "\n");

        // Use PHP's built-in syntax checker
        $output = shell_exec("php -l " . escapeshellarg($tempFile));

        // Delete the temporary file
        unlink($tempFile);
        ob_end_clean();
        // Check the output to see if any syntax errors were found
        if (strpos($output, 'No syntax errors detected') !== false) {
            return true;
        } else {
            return $output;
        }
    }
}