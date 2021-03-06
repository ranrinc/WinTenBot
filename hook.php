<?php
/**
 * Created by PhpStorm.
 * User: Azhe
 * Date: 04/08/2018
 * Time: 22.47
 */

// Load composer
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Resources/kosakata.php';

// Load all in /app dir
foreach (glob('app/*.php') as $files) {
    include_once $files;
}

// load all under folder src
foreach (glob('src/*/*.php') as $files) {
    include_once $files;
}

$commands_paths = [
    __DIR__ . '/Commands/',
];

try {
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram(bot_token, bot_username);

    // Set custom Upload and Download paths
    $telegram->setDownloadPath(__DIR__ . '/Download');
    $telegram->setUploadPath(__DIR__ . '/Upload');

    // Handle telegram webhook request
    $telegram->addCommandsPaths($commands_paths);

//     Logging (Error, Debug and Raw Updates)
//     Longman\TelegramBot\TelegramLog::initDebugLog(__DIR__ . '/{bot_username}_debug.log');
//     Longman\TelegramBot\TelegramLog::initErrorLog(__DIR__ . '/{bot_username}_error.log');
//     Longman\TelegramBot\TelegramLog::initUpdateLog(__DIR__ . '/{bot_username}_update.log');

    // Handle Webhook Request
    $telegram->handle();

    // Enable Limiter
    $telegram->enableLimiter();

} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    // Silence is golden!
    \Longman\TelegramBot\Request::sendMessage([
            'chat_id' => '-1001390529198',
            'text'    => $e->getMessage()
        ]);
}

echo 'wik';
