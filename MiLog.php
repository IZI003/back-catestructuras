<?php
function writeLog($message, $logFile = __DIR__ . '/logs/app.log')
{
    if (!is_dir(dirname($logFile))) {
        mkdir(dirname($logFile), 0755, true);
    }

    $formattedMessage = "[" . date('Y-m-d H:i:s') . "] " . $message . PHP_EOL;

    error_log($formattedMessage, 3, $logFile);
}
?>