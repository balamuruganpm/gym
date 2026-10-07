<?php
/**
 * Full Project Syntax & Lint Verifier
 */

$phpBinary = 'D:\Bala\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe';
$rootDir = dirname(__DIR__);

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($rootDir, RecursiveDirectoryIterator::SKIP_DOTS)
);

$filesChecked = 0;
$errors = [];

foreach ($iterator as $path => $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $filesChecked++;
        $escapedPath = escapeshellarg($path);
        $output = [];
        $returnCode = 0;
        exec("\"{$phpBinary}\" -l {$escapedPath}", $output, $returnCode);

        if ($returnCode !== 0) {
            $errors[] = [
                'file' => $path,
                'output' => implode("\n", $output)
            ];
            echo "  [FAIL] " . str_replace($rootDir, '', $path) . "\n";
        }
    }
}

echo "\n============================================\n";
echo "          PROJECT LINT SUMMARY               \n";
echo "============================================\n";
echo "Files Checked  : {$filesChecked}\n";
echo "Syntax Errors  : " . count($errors) . "\n";
echo "============================================\n\n";

if (count($errors) === 0) {
    echo ">>> ALL {$filesChecked} PHP FILES COMPILED WITH 0 SYNTAX ERRORS! <<<\n\n";
    exit(0);
} else {
    echo ">>> ERRORS FOUND IN " . count($errors) . " FILES! <<<\n";
    foreach ($errors as $err) {
        echo "\nFile: {$err['file']}\n{$err['output']}\n";
    }
    exit(1);
}
