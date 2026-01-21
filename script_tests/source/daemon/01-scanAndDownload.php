<?php

    require_once __DIR__ . '/../../common.php';

    //扫描数据库中通过 webhook 写入的数据
    //通过 file_id 发送下载请求
    $manager->scanAndDownload();
