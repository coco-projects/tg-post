<?php

    require_once './scripts/common.php';

    $a = $app->proxy->postManager->tgMedia->getBootToken();
    var_export($a);

