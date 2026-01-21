<?php

    require_once __DIR__ . '/../../common.php';

    $info = $manager->deleteWebHook();
    print_r($info);
