<?php

    namespace Coco\tgPost;

    use Coco\commandRunner\PhpLauncher;

    class App
    {
        const scanAndDownload          = 'scanAndDownload';
        const scanAndMoveFile          = 'scanAndMoveFile';
        const scanAndMirgrateMediaToDb = 'scanAndMirgrateMediaToDb';

        const listenMakeVideoCover = 'listenMakeVideoCover';
        const listenConvertM3u8    = 'listenConvertM3u8';

        public array $telegramLaunchers;
        public Proxy $proxy;

        public function __construct(public $config, protected string $runtimeDir = '.')
        {
            $this->runtimeDir = rtrim($this->runtimeDir, '/\\') . DIRECTORY_SEPARATOR;

            $this->telegramLaunchers[static::scanAndDownload]          = new PhpLauncher($this->runtimeDir . 'daemon/01-scanAndDownload.php');
            $this->telegramLaunchers[static::scanAndMoveFile]          = new PhpLauncher($this->runtimeDir . 'daemon/02-scanAndMoveFile.php');
            $this->telegramLaunchers[static::scanAndMirgrateMediaToDb] = new PhpLauncher($this->runtimeDir . 'daemon/03-scanAndMirgrateMediaToDb.php');

            $this->telegramLaunchers[static::listenMakeVideoCover] = (new PhpLauncher($this->runtimeDir . 'queue/listen/01-listenMakeVideoCover.php'))->setAllowMultiLaunch(true);
            $this->telegramLaunchers[static::listenConvertM3u8]    = (new PhpLauncher($this->runtimeDir . 'queue/listen/02-listenConvertM3u8.php'))->setAllowMultiLaunch(true);

            $this->proxy = new Proxy($config);
            $this->proxy->init();
        }

        /*
         * -----------------------------------------------------
         * 通用基础方法
         * -----------------------------------------------------
         *
         */

        public function create4MediasTable(): void
        {
            $this->proxy->postManager->tgMedia->getMysqlClient()->createAllTable(true);
        }

        public function deleteMediasCache(): void
        {
            $this->proxy->postManager->tgMedia->deleteCache();
        }

        public function updateTelegramWebhook(): void
        {
            $this->proxy->postManager->tgMedia->updateWebHook();
        }

        public function deleteTelegramWebhook(): void
        {
            $this->proxy->postManager->tgMedia->deleteWebHook();
        }

        public function deleteNoneMediaPost(): void
        {
            $this->proxy->postManager->tgMedia->deleteEmptyPost();
        }

        public function getBootToken(): string
        {
            return $this->proxy->postManager->tgMedia->getBootToken();
        }

        public function restartTelegramBotApi()
        {
            $this->proxy->postManager->tgMedia->restartTelegramBotApi();
            sleep(2);
            $this->proxy->postManager->tgMedia->updateWebHook();

            return true;
        }

        public function stopTelegramBotApi()
        {
            $this->proxy->postManager->tgMedia->stopTelegramBotApi();

            return true;
        }


        public function startTelegramMediaDownloadDaemons()
        {
            $this->telegramLaunchers[static::scanAndDownload]->launch();
            $this->telegramLaunchers[static::scanAndMoveFile]->launch();
            $this->telegramLaunchers[static::scanAndMirgrateMediaToDb]->launch();

            return true;
        }

        public function stopTelegramMediaDownloadDaemons()
        {
            $this->proxy->postManager->tgMedia->stopDownloadMedia();
            $this->proxy->postManager->tgMedia->stopFileMove();
            $this->proxy->postManager->tgMedia->stopMigration();

            return true;
        }

        public function startTelegramMediaProcessDaemons()
        {
            $this->proxy->postManager->tgMedia->convertM3u8Queue->setEnable(true);
            $this->telegramLaunchers[static::listenConvertM3u8]->launch();

            $this->proxy->postManager->tgMedia->makeVideoCoverQueue->setEnable(true);
            $this->telegramLaunchers[static::listenMakeVideoCover]->launch();

            return true;
        }

        public function stopTelegramMediaProcessDaemons()
        {
            $this->proxy->postManager->tgMedia->convertM3u8Queue->setEnable(false);
            $this->proxy->postManager->tgMedia->makeVideoCoverQueue->setEnable(false);

            return true;
        }

        public function resetTelegramMediaProcessDaemons()
        {
            $this->proxy->postManager->tgMedia->convertM3u8Queue->reset();
            $this->proxy->postManager->tgMedia->makeVideoCoverQueue->reset();

            return true;
        }

        public function getServerStatus()
        {
            $telegram_server_status = (int)$this->proxy->postManager->tgMedia->isTelegramBotApiStarted();

            $type_list = $this->proxy->postManager->tgMedia->getTypeList();

            $table_status = $this->proxy->postManager->tgMedia->getAllTableStatus();

            $is_all_table_created = true;
            foreach ($table_status as $k => $v)
            {
                if (!$v['is_created'])
                {
                    $is_all_table_created = false;
                    break;
                }
            }

            $isWPConneted = $this->proxy->getWpPost()->wpManager->getMysqlClient()->testDbConnect();

            $result['wp_connected']            = $isWPConneted;
            $result['type_list']               = $type_list;
            $result['is_all_table_created']    = $is_all_table_created;
            $result['table_status']            = $table_status;
            $result['need_update_pages_count'] = $this->needCreateDetailPageCount();
            $result['telegram_server_status']  = $telegram_server_status;

            $result['telegram_server_info'] = $telegram_server_status ? $this->proxy->postManager->tgMedia->getTelegramApiInfo() : [];
            $result['get_me']               = $telegram_server_status ? $this->proxy->postManager->tgMedia->getMe() : [];

            $result['download_status'] = [
                "download_status_0" => $this->proxy->postManager->tgMedia->getFileStatus0Count(),
                "download_status_1" => $this->proxy->postManager->tgMedia->getFileStatus1Count(),
                "download_status_2" => $this->proxy->postManager->tgMedia->getFileStatus2Count(),
                "download_status_3" => $this->proxy->postManager->tgMedia->getFileStatus3Count(),
            ];

            $result['telegram_launchers_status'] = [
                static::scanAndDownload          => $this->telegramLaunchers[static::scanAndDownload]->getCount(),
                static::scanAndMoveFile          => $this->telegramLaunchers[static::scanAndMoveFile]->getCount(),
                static::scanAndMirgrateMediaToDb => $this->telegramLaunchers[static::scanAndMirgrateMediaToDb]->getCount(),
            ];

            $result['telegraph_launchers_status'] = [
                static::listenMakeVideoCover => $this->telegramLaunchers[static::listenMakeVideoCover]->getCount(),
                static::listenConvertM3u8    => $this->telegramLaunchers[static::listenConvertM3u8]->getCount(),
            ];

            $result['queue_status'] = $this->proxy->postManager->tgMedia->getQueueStatus();
            foreach ($result['queue_status'] as $k => &$v)
            {
                switch ($v['name'])
                {
                    case static::listenMakeVideoCover:
                        $v['zh_name'] = '视频抽图';
                        break;
                    case static::listenConvertM3u8:
                        $v['zh_name'] = '视频转码';
                        break;
                }
            }

            echo json_encode($result, 256);
        }

        public function delType(int $groupId): bool
        {
            $this->proxy->postManager->tgMedia->delType($groupId);

            $array = [
                "code" => 1,
                "msg"  => '删除成功',
            ];

            echo json_encode($array, 1);
        }

        public function addType(string $name, int $groupId): int
        {
            $res = $this->proxy->postManager->tgMedia->addType($name, $groupId);

            $array = [];
            if ($res == -2)
            {
                $array = [
                    "code" => 0,
                    "msg"  => '表都没创建',
                ];
            }
            if ($res == -1)
            {
                $array = [
                    "code" => 0,
                    "msg"  => 'group_id 已经存在',
                ];
            }
            if ($res == 0)
            {
                $array = [
                    "code" => 0,
                    "msg"  => '写入失败',
                ];
            }

            if ($res == 1)
            {
                $array = [
                    "code" => 1,
                    "msg"  => '写入成功',
                ];
            }
            echo json_encode($array, 1);
        }


        /*
         * -----------------------------------------------------
         * wp 相关
         * -----------------------------------------------------
         *
         */

        public function deleteMediasRedisLog(): void
        {
            $this->proxy->postManager->tgMedia->deleteRedisLog();
            if ($this->proxy->getWpPost()->wpManager->getMysqlClient()->testDbConnect())
            {
                $this->proxy->getWpPost()->wpManager->deleteRedisLog();
                $this->proxy->getWpPost()->wpManager->deleteTransient();
            }
        }

        public function deleteWpAllPost(): void
        {
            $this->proxy->getWpPost()->wpManager->deleteTransient();
            $this->proxy->getWpPost()->wpManager->deleteAllPost();
        }

        public function updateAllPostView(): void
        {
            $this->proxy->getWpPost()->wpManager->updateAllPostView(1, 50);
        }

        public function updateAllPostPublishTime(): void
        {
            $this->proxy->getWpPost()->wpManager->updateAllPostPublishTime('2021-5-5', date('Y-m-d'), 300);
        }

        public function needCreateDetailPageCount(): int
        {
            $postTab   = $this->proxy->postManager->tgMedia->getPostTable();
            $a         = $postTab->isTableCerated();
            $postCount = $a ? (int)$postTab->getCount() : 0;

            $postWpTab       = $this->proxy->getWpPost()->wpManager->getPostsTable();
            $f               = $postWpTab->isTableCerated();
            $detailPageCount = $f ? (int)$postWpTab->tableIns()->where([
                [
                    $postWpTab->getGuidField(),
                    'regexp',
                    '^[0-9]{18,20}$',
                ],
            ])->count() : 0;

            return $postCount - $detailPageCount;
        }

        public function fullySyncWpPost(): void
        {
            (new PhpLauncher($this->runtimeDir . 'daemon/01-fullySyncWpPost.php'))->launch();
        }

        public function insertOnlyWpPost(): void
        {
            (new PhpLauncher($this->runtimeDir . 'daemon/02-insertOnlyWpPost.php'))->launch();
        }

        public function makeUpdateLockKey(): string
        {
            return 'wordpress' . $this->config['webId'] . '_update_lock';
        }



        public function form_wp_init(): void
        {
            $isWPConneted = $this->proxy->wpManager->getMysqlClient()->testDbConnect();

            if ($isWPConneted)
            {
                $data = [];
                foreach ($_POST as $k => $v)
                {
                    $data[$k] = trim($v);
                }

                $array = static::parseStringToAssociativeArray($data['wp_init']);

                $jsonConfig = $this->getConfigFileJson();
                $this->initProxy($jsonConfig);

                $this->proxy->wpManager->replaceAll($array);

                $array = [
                    "code" => 1,
                    "msg"  => '执行成功',
                ];
            }
            else
            {
                $array = [
                    "code" => 1,
                    "msg"  => 'wp数据库未连接',
                ];
            }
            echo json_encode($array, 1);
        }

        public function form_delete_post(): void
        {
            $jsonConfig = $this->getConfigFileJson();
            $this->initProxy($jsonConfig);
            $data = [];

            foreach ($_POST as $k => $v)
            {
                $data[$k] = trim($v);
            }

            $isFullMatch = $data['isFullMatch'] ?? false;
            $keyword     = $data['keyword'] ?? '';

            if (!$keyword)
            {
                $array = [
                    "code" => 1,
                    "msg"  => '关键词不能为空',
                ];
                echo json_encode($array, 1);
                exit();
            }

            $array = [
                "code" => 1,
                "msg"  => '执行成功',
            ];

            $this->proxy->tgDownloaderManager->deletePostByKeyword($keyword, $isFullMatch);

            $isWPConneted = $this->proxy->wpManager->getMysqlClient()->testDbConnect();
            if ($isWPConneted)
            {
                $this->proxy->wpManager->deletePostByKeyword($keyword, false, false);
            }

            echo json_encode($array, 1);
        }

    }
