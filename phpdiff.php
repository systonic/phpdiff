<?php

require __DIR__.'/vendor/autoload.php';

/*
 * Run PHPdiff
 */
try {
    $phpdiff = new Systonic\PHPdiff\PHPdiff();
} catch (Exception $e) {
    echo "\r\nError: {$e->getMessage()}\r\n";
    exit(1);
}
