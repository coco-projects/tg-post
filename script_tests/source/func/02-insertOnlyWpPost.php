<?php

    require_once __DIR__ . '/../../common.php';

    $key = $app->makeUpdateLockKey();

    if ($managerProxy->postManager->tgMedia->getRedisClient()->get($key))
    {
        return;
    }

    $isWPConneted = $managerProxy->getWpPost()->wpManager->getMysqlClient()->testDbConnect();

    if ($isWPConneted)
    {
        //完全同步 media post 到wp中，增删改
        $managerProxy->getWpPost()->updateWpPost(true);
    }

    $managerProxy->postManager->tgMedia->getRedisClient()->del($key);