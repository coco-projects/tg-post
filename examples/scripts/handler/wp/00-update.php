<?php

    use Coco\wp\WpTag;

    require_once __DIR__ . '/common.php';
/*
    $wpPost->wpManager->deleteAllPost();

    $wpPost->setContentsAdv(function(&$contents) {
        $contents[] = WpTag::p('✅  🔥 广告', 'red', '28px');
    });
    $wpPost->updateWpPost();


    $wpPost->wpManager->updateAllPostView(1, 298);

    $begin = '2025-7-5';
    $end   = date('Y-m-d');
    $times = 15;
    $wpPost->wpManager->updateAllPostPublishTime($begin, $end, $times,);
*/

        echo $wpPost->wpManager->getOptionsTable()->getCount();

