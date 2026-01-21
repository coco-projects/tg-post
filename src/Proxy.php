<?php

    namespace Coco\tgPost;

    use Coco\tgPost\posts\TelegramPost;
    use Coco\tgPost\posts\TelegraphPost;
    use Coco\tgPost\posts\WpPost;
    use Coco\tgMedia\Manager;

    class Proxy
    {
        public PostManager $postManager;

        public ?WpPost        $wpPostHandler        = null;
        public ?TelegramPost  $telegramPostHandler  = null;
        public ?TelegraphPost $telegraphPostHandler = null;

        public function __construct(public $config)
        {

        }

        public function getAllConfig()
        {
            return $this->config;
        }

        public function getConfigItem(string $key)
        {
            if (isset($this->config[$key]))
            {
                return $this->config[$key];
            }

            return null;
        }

        public function init(): void
        {
            $postManager = new PostManager();
            $postManager->setDebug($this->config['debug']);
            $postManager->setEnableEchoLog($this->config['enableEchoLog']);
            $postManager->setEnableRedisLog($this->config['enableRedisLog']);
            $postManager->setRedisConfig($this->config['redisHost'], $this->config['redisPassword'], $this->config['redisPort'], $this->config['redisDb']);

            $postManager->setTeMessageTableName('te_message');
            $postManager->setTePostTableName('te_post');
            $postManager->setTeFileTableName('te_file');
            $postManager->setTeTypeTableName('te_type');

            $postManager->setFileCdnUrl($this->config['fileCdnUrl']);

            $postManager->initMediaManager(function(PostManager $postManager) {

                $tgMediaManager = new Manager($this->config['bootToken'], $this->config['apiId'], $this->config['apiHash'], $this->config['basePath'], $this->config['redisNamespace']);

                if (isset($this->config['mediaStorePath']) && $this->config['mediaStorePath'])
                {
                    $tgMediaManager->setTelegramMediaStorePath($this->config['mediaStorePath']);
                }

                $tgMediaManager->setMysqlConfig($this->config['mysqlDb'], $this->config['mysqlHost'], $this->config['mysqlUsername'], $this->config['mysqlPassword'], $this->config['mysqlPort']);

                $tgMediaManager->setTelegramMediaMaxDownloading($this->config['maxDownloading']);
                $tgMediaManager->setTelegramMediaDownloadDelayInSecond($this->config['downloadDelayInSecond']);
                $tgMediaManager->setTelegramMediaMaxDownloadTimeout($this->config['maxDownloadTimeout']);
                $tgMediaManager->setMediaOwner($this->config['mediaOwner']);
                $tgMediaManager->setTelegramMediaMaxFileSize($this->config['mediaMaxFileSize']);

                $tgMediaManager->setTelegramWebHookUrl($this->config['webHookUrl']);
                $tgMediaManager->setLocalServerPort($this->config['localServerPort']);
                $tgMediaManager->setStatisticsPort($this->config['statisticsPort']);

                $tgMediaManager->setBeforePostFilesInsert(function($files) use ($tgMediaManager) {

                    $fileTab = $tgMediaManager->getFileTable();

                    foreach ($files as $k => $videoFileInfo)
                    {
                        if (str_starts_with($videoFileInfo[$fileTab->getMimeTypeField()], 'video'))
                        {
                            //如果是视频，就抽个封面图
                            $tgMediaManager->makeVideoCoverToQueue($videoFileInfo, function($path) use ($tgMediaManager) {
                                $path = $tgMediaManager->telegramMediaStorePath . '/' . $path;

                                return $path;
                            });

                            //转码为m3u8
                            $tgMediaManager->convertM3u8ToQueue($videoFileInfo, function($path) use ($tgMediaManager) {
                                $path = $tgMediaManager->telegramMediaStorePath . '/' . $path;

                                return $path;
                            });
                        }

                        /*
                        //所有文件cdn预热
                        $tgMediaManager->cdnPrefetchToQueue($videoFileInfo, function($path) {
                            return implode('', [
                                $this->cdnUrl,
                                $path,
                            ]);

                        }, $this->referer);
                        */
                    }
                });

                return $tgMediaManager;
            });

            $this->postManager = $postManager;
        }

        public function getWpPost(): WpPost
        {
            if (is_null($this->wpPostHandler))
            {
                $wpPost = new WpPost($this->postManager);
                $wpPost->initHandler(function(WpPost $wpPostManager) {
                    $wpManager = new \Coco\wp\Manager($wpPostManager->postManager->redisNamespace);
                    $wpManager->setMysqlConfig($this->config['mysqlWpDb'], $this->config['mysqlWpHost'], $this->config['mysqlWpUsername'], $this->config['mysqlWpPassword'], $this->config['mysqlWpPort']);

                    return $wpManager;
                });

                $this->wpPostHandler = $wpPost;
            }

            return $this->wpPostHandler;
        }

        public function getTelegramPost(): TelegramPost
        {
            if (is_null($this->telegramPostHandler))
            {
                $telegramPost = new TelegramPost($this->postManager);
                $telegramPost->initHandler(function(TelegramPost $telegramPostManager) {

                    if (isset($this->config['telegramProxy']) && $this->config['telegramProxy'])
                    {
                        $telegramPostManager->setProxy($this->config['telegramProxy']);
                    }

                    $telegramPostManager->setRetryTimes($this->config['retryTimes']);
                    $telegramPostManager->setMessageDelay($this->config['messageDelay']);
                    $telegramPostManager->setTypeMap($this->config['typeMap']);
                });

                $this->telegramPostHandler = $telegramPost;
            }

            return $this->telegramPostHandler;
        }

        public function getTelegraphPost(): TelegraphPost
        {
            if (is_null($this->telegraphPostHandler))
            {
                $telegraphPost = new TelegraphPost($this->postManager);

                $telegraphPost->initHandler(function(TelegraphPost $wpManager) {

                });

                $this->telegraphPostHandler = $telegraphPost;
            }

            return $this->telegraphPostHandler;
        }
    }
