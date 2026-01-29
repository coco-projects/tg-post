<?php

    require_once __DIR__ . '/../../common.php';

    $key = $app->makeTelegramUpdateLockKey();

    if ($managerProxy->postManager->tgMedia->getRedisClient()->get($key))
    {
        return;
    }

    if (is_callable($config['telegramUpdateCallback']))
    {
        $managerProxy->getTelegramPost()->setContentsAdv($config['telegramUpdateCallback']);
    }

    $managerProxy->getTelegramPost()->updateTelegramPost();

    $managerProxy->postManager->tgMedia->getRedisClient()->del($key);