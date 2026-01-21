<?php

    require_once __DIR__ . '/common.php';

    $telegramPost->setContentsAdv(function($content) {
        $content .= implode(PHP_EOL, [
            PHP_EOL . '-----------------------',
            'adv test',
            \Coco\wp\Tag::a('http://baidu.com', 'Baidu adv'),
        ]);

        return $content;
    });

    $telegramPost->updateTelegramPost();