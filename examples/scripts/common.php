<?php

    require __DIR__ . '/../../vendor/autoload.php';
    require __DIR__ . '/../../config.php';

    $app = new \Coco\tgPost\App($config, __DIR__ . '/source');

    $managerProxy = $app->proxy;
    $postManager  = $managerProxy->postManager;
    $manager      = $postManager->tgMedia;


