<?php

    use Coco\tableManager\TableRegistry;

    require_once __DIR__ . '/../common.php';

    $method = TableRegistry::makeMethod($manager->getMessageTable()->getFieldsSqlMap());

    print_r($method);
