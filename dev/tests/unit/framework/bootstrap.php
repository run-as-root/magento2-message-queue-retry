<?php

declare(strict_types=1);

use Magento\Framework\Phrase;
use Magento\Framework\Phrase\Renderer\Placeholder;
use PHPUnit\Framework\Exception;

if (!defined('TESTS_TEMP_DIR')) {
    define('TESTS_TEMP_DIR', dirname(__DIR__) . '/tmp');
}

require_once __DIR__ . '/autoload.php';

setCustomErrorHandler();

Phrase::setRenderer(new Placeholder());

error_reporting(E_ALL);
ini_set('display_errors', 1);

/*  For data consistency between displaying (printing) and serialization a float number */
ini_set('precision', 14);
ini_set('serialize_precision', 14);

function setCustomErrorHandler(): void
{
    set_error_handler(
        callback: function ($errNo, $errStr, $errFile, $errLine) {
            $errLevel = error_reporting();
            if (($errLevel & $errNo) !== 0) {
                $errorNames = [
                    E_ERROR => 'Error',
                    E_WARNING => 'Warning',
                    E_PARSE => 'Parse',
                    E_NOTICE => 'Notice',
                    E_CORE_ERROR => 'Core Error',
                    E_CORE_WARNING => 'Core Warning',
                    E_COMPILE_ERROR => 'Compile Error',
                    E_COMPILE_WARNING => 'Compile Warning',
                    E_USER_ERROR => 'User Error',
                    E_USER_WARNING => 'User Warning',
                    E_USER_NOTICE => 'User Notice',
                    E_STRICT => 'Strict',
                    E_RECOVERABLE_ERROR => 'Recoverable Error',
                    E_DEPRECATED => 'Deprecated',
                    E_USER_DEPRECATED => 'User Deprecated',
                ];

                $errName = $errorNames[$errNo] ?? "";

                $message = "$errName: $errStr in $errFile:$errLine.";
                throw new Exception($message, $errNo);
            }
        }
    );
}
