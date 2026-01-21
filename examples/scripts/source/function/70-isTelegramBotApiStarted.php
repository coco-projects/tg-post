<?php

    require_once __DIR__ . '/../../common.php';

    $info = $manager->isTelegramBotApiStarted();
    var_dump($info);
