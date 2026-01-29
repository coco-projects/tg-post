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
        public function getBotToken(): string
        {
            return $this->proxy->postManager->tgMedia->getBootToken();
        }

        public function restartTelegramBotApiAndUpdateWebHook(): void
        {
            $this->restartTelegramBotApi();
            sleep(2);
            $this->updateTelegramWebhook();
        }

        public function restartTelegramBotApi(): void
        {
            $this->proxy->postManager->tgMedia->restartTelegramBotApi();
        }

        public function stopTelegramBotApi(): void
        {
            $this->proxy->postManager->tgMedia->stopTelegramBotApi();
        }

        public function create4MediasTable(bool $forceCreateTable = false): void
        {
            $this->proxy->postManager->tgMedia->getMysqlClient()->createAllTable($forceCreateTable);
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


        public function startTelegramMediaDownloadDaemons(): void
        {
            $this->telegramLaunchers[static::scanAndDownload]->launch();
            $this->telegramLaunchers[static::scanAndMoveFile]->launch();
            $this->telegramLaunchers[static::scanAndMirgrateMediaToDb]->launch();
        }

        public function stopTelegramMediaDownloadDaemons(): void
        {
            $this->proxy->postManager->tgMedia->stopDownloadMedia();
            $this->proxy->postManager->tgMedia->stopFileMove();
            $this->proxy->postManager->tgMedia->stopMigration();
        }


        public function launchTelegramMediaProcessDaemons(): void
        {
            $this->launchListenConvertM3u8Daemon();
            $this->launchListenMakeVideoCoverDaemon();
        }

        public function stopTelegramMediaProcessDaemons(): void
        {
            $this->stopListenConvertM3u8Daemon();
            $this->stopListenMakeVideoCoverDaemon();
        }

        public function resetTelegramMediaProcessDaemons(): void
        {
            $this->resetListenConvertM3u8Daemon();
            $this->resetListenMakeVideoCoverDaemon();
        }

        public function restoreTelegramMediaProcessDaemons(): void
        {
            $this->restoreListenConvertM3u8Daemon();
            $this->restoreListenMakeVideoCoverDaemon();
        }


        public function launchListenConvertM3u8Daemon(): void
        {
            $this->proxy->postManager->tgMedia->convertM3u8Queue->setEnable(true);
            $this->telegramLaunchers[static::listenConvertM3u8]->launch();
        }

        public function stopListenConvertM3u8Daemon(): void
        {
            $this->proxy->postManager->tgMedia->convertM3u8Queue->setEnable(false);
        }

        public function resetListenConvertM3u8Daemon(): void
        {
            $this->proxy->postManager->tgMedia->convertM3u8Queue->reset();
        }

        public function restoreListenConvertM3u8Daemon(): void
        {
            $this->proxy->postManager->tgMedia->convertM3u8Queue->restoreErrorMission();
            $this->proxy->postManager->tgMedia->convertM3u8Queue->restoreTimesReachedMission();
        }

        public function makeListenConvertM3u8Mission(): void
        {
            $fileTab = $this->proxy->postManager->tgMedia->getFileTable();
            $files   = $fileTab->tableIns()->where($fileTab->getMimeTypeField(), 'like', 'video%')->select();

            foreach ($files as $k => $videoFileInfo)
            {
                $videoPath     = $videoFileInfo[$fileTab->getPathField()];
                $saveCoverPath = strtr($videoPath, ["videos" => "photos"]);
                $saveCoverPath = preg_replace('/[^.]+$/im', 'jpg', $saveCoverPath);

                //先查一下，这个图片生成过没有，生成过就跳过
                $isExists = $fileTab->tableIns()->where($fileTab->getPathField(), '=', $saveCoverPath)
                    ->where($fileTab->getFileNameField(), '=', '--cover--')->find();

                if (!$isExists)
                {
                    $this->proxy->postManager->tgMedia->makeVideoCoverToQueue($videoFileInfo, function($path) {
                        return implode('', [
                            $this->proxy->postManager->tgMedia->telegramMediaStorePath,
                            '/',
                            $path,
                        ]);
                    });
                }
            }
        }


        public function launchListenMakeVideoCoverDaemon(): void
        {
            $this->proxy->postManager->tgMedia->makeVideoCoverQueue->setEnable(true);
            $this->telegramLaunchers[static::listenMakeVideoCover]->launch();
        }

        public function stopListenMakeVideoCoverDaemon(): void
        {
            $this->proxy->postManager->tgMedia->makeVideoCoverQueue->setEnable(false);
        }

        public function resetListenMakeVideoCoverDaemon(): void
        {
            $this->proxy->postManager->tgMedia->makeVideoCoverQueue->reset();
        }

        public function restoreListenMakeVideoCoverDaemon(): void
        {
            $this->proxy->postManager->tgMedia->makeVideoCoverQueue->restoreErrorMission();
            $this->proxy->postManager->tgMedia->makeVideoCoverQueue->restoreTimesReachedMission();
        }

        public function makeListenMakeVideoCoverMission(): void
        {
            $fileTab = $this->proxy->postManager->tgMedia->getFileTable();
            $files   = $fileTab->tableIns()->where($fileTab->getMimeTypeField(), 'like', 'video%')->select();

            foreach ($files as $k => $videoFileInfo)
            {
                $this->proxy->postManager->tgMedia->convertM3u8ToQueue($videoFileInfo, function($path) {
                    return implode('', [
                        $this->proxy->postManager->tgMedia->telegramMediaStorePath,
                        '/',
                        $path,
                    ]);
                });
            }
        }

        public function delType(int $groupId): array
        {
            $this->proxy->postManager->tgMedia->delType($groupId);

            $array = [
                "code" => 1,
                "msg"  => '删除成功',
            ];

            return $array;
        }

        public function addType(string $name, int $groupId): array
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

            return $array;
        }


        public function getServerStatus(): array
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
                    case $this->proxy->postManager->tgMedia::MAKE_VIDEO_COVER_QUEUE :
                        $v['zh_name'] = '视频抽图';
                        break;
                    case $this->proxy->postManager->tgMedia::CONVERT_M3U8_QUEUE :
                        $v['zh_name'] = '视频转码';
                        break;
                }
            }

            return $result;
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
            $isWPConneted = $this->proxy->getWpPost()->wpManager->getMysqlClient()->testDbConnect();

            if ($isWPConneted)
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

        public function deleteWpPostByKeyword(string $keyword, bool $isFullMatch = false): array
        {
            $isFullMatch = trim($isFullMatch);
            $keyword     = trim($keyword);

            if (!$keyword)
            {
                $array = [
                    "code" => 1,
                    "msg"  => '关键词不能为空',
                ];

                return $array;
            }

            $isWPConneted = $this->proxy->getWpPost()->wpManager->getMysqlClient()->testDbConnect();
            if ($isWPConneted)
            {
                $array = [
                    "code" => 1,
                    "msg"  => '执行成功',
                ];

                $this->proxy->getWpPost()->wpManager->deletePostByKeyword($keyword, false, $isFullMatch);
            }
            else
            {
                $array = [
                    "code" => 1,
                    "msg"  => 'wp 未连接上',
                ];
            }

            return $array;
        }

        public function deleteMediaPostByKeyword(string $keyword, bool $isFullMatch = false): array
        {
            $isFullMatch = trim($isFullMatch);
            $keyword     = trim($keyword);

            if (!$keyword)
            {
                $array = [
                    "code" => 1,
                    "msg"  => '关键词不能为空',
                ];

                return $array;
            }

            $array = [
                "code" => 1,
                "msg"  => '执行成功',
            ];

            $this->proxy->postManager->tgMedia->deletePostByKeyword($keyword, $isFullMatch);

            return $array;

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

        public function wpReplace(string $wpInitString): bool
        {
            $isWPConneted = $this->proxy->getWpPost()->wpManager->getMysqlClient()->testDbConnect();

            if ($isWPConneted)
            {
                $array = static::parseStringToAssociativeArray($wpInitString);

                $this->proxy->getWpPost()->wpManager->replaceAll($array);

                return true;
            }
            else
            {
                return false;
            }
        }

        protected static function parseStringToAssociativeArray($input): array
        {
            $lines  = preg_split("#[\r\n]+#iu", $input, -1, PREG_SPLIT_NO_EMPTY);
            $result = [];

            foreach ($lines as $line)
            {
                $line = trim($line); // 去掉行首尾的空白字符

                $parts = explode("|", $line);
                if (count($parts) == 2)
                {
                    // 将两部分添加到结果数组
                    $result[trim($parts[0])] = trim($parts[1]);
                }
            }

            return $result;
        }

        public function fullySyncWpPost(): void
        {
            (new PhpLauncher($this->runtimeDir . 'func/01-fullySyncWpPost.php'))->launch();
        }

        public function insertOnlyWpPost(): void
        {
            (new PhpLauncher($this->runtimeDir . 'func/02-insertOnlyWpPost.php'))->launch();
        }

        public function fullySyncTelegramPost(): void
        {
            (new PhpLauncher($this->runtimeDir . 'func/11-fullySyncTelegramPost.php'))->launch();
        }

        public function makeWpUpdateLockKey(): string
        {
            return 'update_lock_' . $this->config['webId'] . '_wp';
        }

        public function makeTelegramUpdateLockKey(): string
        {
            return 'update_lock_' . $this->config['webId'] . '_telegram';
        }
    }
