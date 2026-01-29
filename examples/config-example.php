<?php

    $config = [

        /**
         * --------------------------------------------------------
         * 基础常用配置
         * --------------------------------------------------------
         */
        'webId' => 10500,
        'wpUrl' => 'http://dev6080',

        'bootToken'        => '6026300000:AAGvMcaxTRBbcPxs_ShGu-G4CffyCyI_6Ek',
        'apiId'            => 23130000,
        'apiHash'          => '73a4b06c6458e3252dc8818811e10000',
        'webHookUrl'       => 'http://127.0.0.1:30001/tg/scripts/tgMedias/endpoint/endPoint.php',
        'fileCdnUrl'       => 'http://dev6025/new/coco-tgPost/data/media',
        'mediaMaxFileSize' => 3000 * 1024 * 1024,
        'debug'            => true,

        /**
         * --------------------------------------------------------
         * media 源数据 mysql 配置
         * --------------------------------------------------------
         */
        'mysqlHost'        => '127.0.0.1',
        'mysqlUsername'    => 'root',
        'mysqlPassword'    => '',
        'mysqlDb'          => 'tg_media',
        'mysqlPort'        => 3306,

        /**
         * --------------------------------------------------------
         * WpPost 配置
         * --------------------------------------------------------
         */
        'mysqlWpHost'      => '127.0.0.1',
        'mysqlWpUsername'  => 'root',
        'mysqlWpPassword'  => '',
        'mysqlWpDb'        => 'wp_te_10100',
        'mysqlWpPort'      => 3306,

        'wpUpdateCallback' => function(&$contents) {
            $contents[] = \Coco\wp\WpTag::p('✅  🔥 广告', 'red', '28px');
        },

        /**
         * --------------------------------------------------------
         * TelegramPost 配置
         * --------------------------------------------------------
         */
        'retryTimes'       => 10,
        'messageDelay'     => 6,
        'typeMap'          => [
            1 => -1001480630000,
        ],

        'telegramUpdateCallback' => function($content) {
            $content .= implode(PHP_EOL, [
                PHP_EOL . '-----------------------',
                'adv test',
                \Coco\wp\Tag::a('http://baidu.com', 'Baidu adv'),
            ]);

            return $content;
        },

        //始终用不上
        //线上外国服务器不需要代理
        //本地测试自建api服务器无法工作，设置代理也不起作用
        'telegramProxy'    => '',

        /**
         * --------------------------------------------------------
         * --------------------------------------------------------
         * --------------------------------------------------------
         * 不常用配置
         * --------------------------------------------------------
         * --------------------------------------------------------
         * --------------------------------------------------------
         */
        'basePath'         => __DIR__ . '/../data',

        //不用填，默认在 basePath 下,确实有必要改的时候才设置
        //        'mediaStorePath'        => __DIR__ . '/../medias',

        'maxDownloading'        => 10,
        'downloadDelayInSecond' => 5,
        'maxDownloadTimeout'    => 160,
        'mediaOwner'            => 'www',
        'redisNamespace'        => 'tg_media',

        'enableRedisLog' => true,
        'enableEchoLog'  => true,

        'localServerPort' => 8081,
        'statisticsPort'  => 8082,

        'redisHost'     => '127.0.0.1',
        'redisPassword' => '',
        'redisPort'     => 6379,
        'redisDb'       => 12,

    ];


