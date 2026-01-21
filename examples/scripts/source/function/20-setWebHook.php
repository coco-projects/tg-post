<?php

    require_once __DIR__ . '/../../common.php';

    //设置webhook
    $info = $manager->updateWebHook();

    print_r($info);
