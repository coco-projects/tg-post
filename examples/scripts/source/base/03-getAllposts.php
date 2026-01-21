<?php

    require_once __DIR__ . '/common.php';

    $data = $postManager->tgMedia->getPostsByCondition();
//    $data = $postManager->tgMedia->getPostTable()->getCount();
    print_r($data);
