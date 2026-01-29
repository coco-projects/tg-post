<?php

    require_once __DIR__ . '/../../common.php';

    $key = $app->makeWpUpdateLockKey();

    if ($managerProxy->postManager->tgMedia->getRedisClient()->get($key))
    {
        return;
    }

    $isWPConneted = $managerProxy->getWpPost()->wpManager->getMysqlClient()->testDbConnect();

    if ($isWPConneted)
    {
        if (is_callable($config['wpUpdateCallback']))
        {
            $managerProxy->getWpPost()->setContentsAdv($config['wpUpdateCallback']);
        }

        //完全同步 media post 到wp中，增删改
        $managerProxy->getWpPost()->updateWpPost(true);
    }

    $managerProxy->postManager->tgMedia->getRedisClient()->del($key);