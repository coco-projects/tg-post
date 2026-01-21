<?php

    namespace Coco\tgPost\posts;

    use Coco\queue\MissionManager;
    use Coco\queue\missionProcessors\GuzzleMissionProcessor;
    use Coco\queue\Queue;
    use Coco\queue\resultProcessor\CustomResultProcessor;

    use Coco\tableManager\TableRegistry;
    use Coco\telegraph\dom\E;

    use Coco\tgPost\missions\TelegraphMission;
    use Coco\tgPost\PostManager;
    use Coco\tgPost\styles\StyleAbstract;
    use Coco\tgPost\tables\Account;
    use Coco\tgPost\tables\Pages;

    use DI\Container;
    use Symfony\Component\Cache\Adapter\RedisAdapter;
    use Symfony\Component\Cache\Marshaller\DefaultMarshaller;
    use Symfony\Component\Cache\Marshaller\DeflateMarshaller;

    class TelegraphPost extends PostAbstract
    {
        const MYSQL_CLIENT = 'mysqlClient';

        const PAGE_INDEX  = 1;
        const PAGE_TYPE   = 2;
        const PAGE_DETAIL = 3;

        const CREATE_ACCOUNT_QUEUE         = 'CREATE_ACCOUNT';
        const CREATE_INDEX_PAGE_QUEUE      = 'CREATE_INDEX_PAGE';
        const CREATE_FIRST_TYPE_PAGE_QUEUE = 'CREATE_FIRST_TYPE_PAGE';
        const CREATE_DETAIL_PAGE_QUEUE     = 'CREATE_DETAIL_PAGE';
        const CREATE_TYPE_ALL_PAGE_QUEUE   = 'CREATE_TYPE_ALL_PAGE';
        const UPDATE_TYPE_ALL_PAGE_QUEUE   = 'UPDATE_TYPE_ALL_PAGE';
        const UPDATE_DETAIL_PAGE_QUEUE     = 'UPDATE_DETAIL_PAGE';
        const UPDATE_INDEX_PAGE_QUEUE      = 'UPDATE_INDEX_PAGE';

        public MissionManager $telegraphQueueMissionManager;
        public Queue          $createAccountQueue;
        public Queue          $createIndexPageQueue;
        public Queue          $createFirstTypePageQueue;
        public Queue          $createDetailPageQueue;
        public Queue          $createTypeAllPageQueue;
        public Queue          $updateTypeAllPageQueue;
        public Queue          $updateDetailPageQueue;
        public Queue          $updateIndexPageQueue;

        protected array $tables = [];

        protected string $mysqlDb;
        protected string $mysqlHost     = '127.0.0.1';
        protected string $mysqlUsername = 'root';
        protected string $mysqlPassword = 'root';
        protected int    $mysqlPort     = 3306;

        protected string $cacheAccountTokens;
        protected string $cacheIndexPage;
        protected string $cacheFirstTypePage;
        protected string $cacheRandDetailPage;
        protected string $cacheTypes;
        protected string $cacheTypePageIdentifications;
        protected string $cacheDetailPageIdentifications;

        protected ?string $accountTableName = null;
        protected ?string $pagesTableName   = null;


        protected ?string $logNamespace;
        protected ?string $cacheNamespace;


        protected ?StyleAbstract $telegraphPageStyle      = null;
        protected int            $telegraphPageRow        = 50;
        protected int            $telegraphTimeout        = 30;
        protected int            $telegraphQueueMaxTimes  = 10;
        protected ?string        $telegraphPageBrandTitle = 'telegraph-pages';
        protected int            $telegraphQueueDelayMs   = 0;
        protected ?string        $telegraphPageShortName  = 'bob';
        protected ?string        $telegraphPageAuthorName = 'tily';
        protected ?string        $telegraphPageAuthorUrl  = '';
        protected ?string        $telegraphProxy          = null;

        public function __construct(PostManager $postManager, protected string $redisNamespace = 'post-log')
        {
            parent::__construct($postManager);

            $this->logNamespace   = $this->redisNamespace . '-log:';
            $this->cacheNamespace = $this->redisNamespace . '-cache';

            $this->cacheAccountTokens             = 'telegraph_account_tokens';
            $this->cacheIndexPage                 = 'telegraph_index_page';
            $this->cacheFirstTypePage             = 'telegraph_first_type_page';
            $this->cacheRandDetailPage            = 'telegraph_rand_detail_page';
            $this->cacheTypes                     = 'telegraph_types';
            $this->cacheTypePageIdentifications   = 'telegraph_type_page_identifications';
            $this->cacheDetailPageIdentifications = 'telegraph_detail_page_identifications';

        }

        public function update()
        {
            echo 'update';
        }

        public function initHandler(?callable $callback = null): static
        {
            call_user_func($callback, $this);

            return $this;
        }

        /**********************/

        public function getContainer(): Container
        {
            return $this->postManager->getContainer();
        }

        /**********************/
        public function initAccountTable(string $name, callable $callback): static
        {
            $this->accountTableName = $name;

            $this->getMysqlClient()->initTable($name, Account::class, $callback);

            return $this;
        }

        public function getAccountTable(): Account
        {
            return $this->getMysqlClient()->getTable($this->accountTableName);
        }

        /**********************/
        public function initPagesTable(string $name, callable $callback): static
        {
            $this->pagesTableName = $name;

            $this->getMysqlClient()->initTable($name, Pages::class, $callback);

            return $this;
        }

        public function getPagesTable(): Pages
        {
            return $this->getMysqlClient()->getTable($this->pagesTableName);
        }

        /**********************/

        public function getTypeTable(): \Coco\tgMedia\tables\Type
        {
            return $this->postManager->tgMedia->getTypeTable();
        }

        public function getPostTable(): \Coco\tgMedia\tables\Post
        {
            return $this->postManager->tgMedia->getPostTable();
        }

        public function getFileTable(): \Coco\tgMedia\tables\File
        {
            return $this->postManager->tgMedia->getFileTable();
        }

        public function getMessageTable(): \Coco\tgMedia\tables\Message
        {
            return $this->postManager->tgMedia->getMessageTable();
        }

        /**********************/

        protected function initMysql(): static
        {
            $this->getContainer()->set(static::MYSQL_CLIENT, function(Container $container) {

                $registry = new TableRegistry($this->mysqlDb, $this->mysqlHost, $this->mysqlUsername, $this->mysqlPassword, $this->mysqlPort,);

                $logName = 'te-mysql';
                $registry->setStandardLogger($logName);

                if ($this->postManager->enableRedisLog)
                {
                    $registry->addRedisHandler(redisHost: $this->postManager->redisHost, redisPort: $this->postManager->redisPort, password: $this->postManager->redisPassword, db: $this->postManager->redisDb, logName: $this->logNamespace . $logName, callback: $this->telegraphQueueMissionManager::getStandardFormatter());
                }

                if ($this->postManager->enableEchoLog)
                {
                    $registry->addStdoutHandler($this->telegraphQueueMissionManager::getStandardFormatter());
                }

                return $registry;
            });

            return $this;
        }

        public function getMysqlClient(): TableRegistry
        {
            return $this->getContainer()->get(static::MYSQL_CLIENT);
        }


        /**********************/

        public function createAccountToQueue($number): void
        {
            for ($i = 1; $i <= $number; $i++)
            {
                $mission = new TelegraphMission();
                $mission->setTimeout($this->telegraphTimeout);
                $mission->index = $i;

                if (!is_null($this->telegraphProxy))
                {
                    $mission->setProxy($this->telegraphProxy);
                }

                $mission->createAccount($this->telegraphPageShortName, $this->telegraphPageAuthorName, $this->telegraphPageAuthorUrl);

                $this->createAccountQueue->addNewMission($mission);
            }
        }

        public function listenCreateAccount(): void
        {
            $queue = $this->createAccountQueue;

            $queue->setContinuousRetry(true);
            $queue->setDelayMs($this->telegraphQueueDelayMs);
            $queue->setEnable(true);
            $queue->setMaxTimes($this->telegraphQueueMaxTimes);
            $queue->setIsRetryOnError(true);
            $queue->setMissionProcessor(new GuzzleMissionProcessor());

            $success = function(TelegraphMission $mission) {
                $response = $mission->getResult();
                $json     = $response->getBody()->getContents();
                $result   = json_decode($json, true);

                if ($result['ok'])
                {
                    $accountTab = $this->getAccountTable();

                    $data = [
                        $accountTab->getShortNameField()   => $result['result']['short_name'],
                        $accountTab->getAuthorUrlField()   => $result['result']['author_url'],
                        $accountTab->getAuthorNameField()  => $result['result']['author_name'],
                        $accountTab->getAuthUrlField()     => $result['result']['auth_url'],
                        $accountTab->getAccessTokenField() => $result['result']['access_token'],
                        $accountTab->getTimeField()        => time(),
                    ];

                    if (!$accountTab->isPkAutoInc())
                    {
                        $data[$accountTab->getPkField()] = $accountTab->calcPk();
                    }

                    $res = $accountTab->tableIns()->insert($data);

                    if ($res)
                    {
                        $this->telegraphQueueMissionManager->logInfo('创建成功: ' . $mission->index);
                    }
                    else
                    {
                        $this->telegraphQueueMissionManager->logError('写入错误: ' . $mission->index);
                    }

                }
                else
                {
                    $this->telegraphQueueMissionManager->logError($mission->index . ' -- ' . $result['error']);
                }
            };

            $catch = function(TelegraphMission $mission, \Exception $exception) {
                $this->telegraphQueueMissionManager->logError($exception->getMessage());
            };

            $queue->addResultProcessor(new CustomResultProcessor($success, $catch));

            $queue->listen();
        }

        /*-------------------------------------------------------------------*/
        public function createIndexPageToQueue(): void
        {
            if ($this->isIndexPageCreated())
            {
                $this->telegraphQueueMissionManager->logInfo('index 页面已经创建，此任务被忽略');

                return;
            }

            $token   = $this->getRandToken();
            $mission = new TelegraphMission();
            $mission->setTimeout($this->telegraphTimeout);
            $mission->token = $token;

            if (!is_null($this->telegraphProxy))
            {
                $mission->setProxy($this->telegraphProxy);
            }

            $json = $this->telegraphPageStyle->placeHolder('index 建设中...');
            $mission->setAccessToken($token);
            $mission->createPage($this->telegraphPageBrandTitle, $json, true);

            $this->createIndexPageQueue->addNewMission($mission);
        }

        public function listenCreateIndexPage(): void
        {
            $queue = $this->createIndexPageQueue;

            $queue->setContinuousRetry(true);
            $queue->setDelayMs($this->telegraphQueueDelayMs);
            $queue->setEnable(true);
            $queue->setMaxTimes($this->telegraphQueueMaxTimes);
            $queue->setIsRetryOnError(true);
            $queue->setMissionProcessor(new GuzzleMissionProcessor());

            $success = function(TelegraphMission $mission) {
                $response = $mission->getResult();
                $json     = $response->getBody()->getContents();
                $result   = json_decode($json, true);

                if ($result['ok'])
                {
                    $pageTab = $this->getPagesTable();
                    $data    = [
                        $pageTab->getPathField()            => $result['result']['path'],
                        $pageTab->getUrlField()             => $result['result']['url'],
                        $pageTab->getTitleField()           => $result['result']['title'],
                        $pageTab->getDescriptionField()     => $result['result']['description'],
                        $pageTab->getContentField()         => json_encode($result['result']['content'], 256),
                        $pageTab->getViewsField()           => $result['result']['views'],
                        $pageTab->getCanEditField()         => (int)$result['result']['can_edit'],
                        $pageTab->getTokenField()           => $mission->token,
                        $pageTab->getPageTypeField()        => static::PAGE_INDEX,
                        $pageTab->getIsFirstTypePageField() => 0,
                        $pageTab->getPageNumField()         => 0,
                        $pageTab->getPostTypeIdField()      => 0,
                        $pageTab->getPostIdField()          => 0,
                        $pageTab->getPageNumListField()     => '',
                        $pageTab->getParamsField()          => json_encode([], 256),
                        $pageTab->getIdentificationField()  => $this->makeIndexPageId(),
                        $pageTab->getUpdateTimeField()      => time(),
                        $pageTab->getTimeField()            => time(),
                    ];

                    if (!$pageTab->isPkAutoInc())
                    {
                        $data[$pageTab->getPkField()] = $pageTab->calcPk();
                    }

                    $re = $pageTab->tableIns()->insert($data);

                    if ($re)
                    {
                        $this->telegraphQueueMissionManager->logInfo('index 创建 ok');
                    }
                    else
                    {
                        $this->telegraphQueueMissionManager->logError($json);
                    }
                }
                else
                {
                    $this->telegraphQueueMissionManager->logError($result['error']);
                }

            };

            $catch = function(TelegraphMission $mission, \Exception $exception) {
                $this->telegraphQueueMissionManager->logError($exception->getMessage());
            };

            $queue->addResultProcessor(new CustomResultProcessor($success, $catch));

            $queue->listen();
        }

        /*-------------------------------------------------------------------*/
        public function createFirstTypePageToQueue(): void
        {
            $typeTab = $this->getTypeTable();

            $types = $this->getTypes();

            foreach ($types as $k => $type)
            {
                if ($this->isTypePageCreated($type[$typeTab->getPkField()], 1))
                {
                    $this->telegraphQueueMissionManager->logInfo('id:' . $type[$typeTab->getPkField()] . ' 分类页面已经创建，此任务被忽略');

                    continue;
                }

                $token    = $this->getRandToken();
                $typeName = $type[$typeTab->getNameField()];

                $mission = new TelegraphMission();
                $mission->setTimeout($this->telegraphTimeout);
                $mission->token    = $token;
                $mission->typeInfo = $type;

                if (!is_null($this->telegraphProxy))
                {
                    $mission->setProxy($this->telegraphProxy);
                }

                $title = $typeName;
                $json  = $this->telegraphPageStyle->placeHolder($title . ' 建设中...');
                $mission->setAccessToken($token);
                $mission->createPage($title, $json, true);

                $this->telegraphQueueMissionManager->logInfo('createFirstTypePage: ' . $title);

                $this->createFirstTypePageQueue->addNewMission($mission);
            }
        }

        public function listenCreateFirstTypePage(): void
        {
            $queue = $this->createFirstTypePageQueue;

            $queue->setContinuousRetry(true);
            $queue->setDelayMs($this->telegraphQueueDelayMs);
            $queue->setEnable(true);
            $queue->setMaxTimes($this->telegraphQueueMaxTimes);
            $queue->setIsRetryOnError(true);
            $queue->setMissionProcessor(new GuzzleMissionProcessor());

            $success = function(TelegraphMission $mission) {
                $response = $mission->getResult();
                $json     = $response->getBody()->getContents();
                $result   = json_decode($json, true);

                $token = $mission->token;
                if ($result['ok'])
                {
                    $pageTab = $this->getPagesTable();
                    $typeTab = $this->getTypeTable();

                    $data = [
                        $pageTab->getPathField()            => $result['result']['path'],
                        $pageTab->getUrlField()             => $result['result']['url'],
                        $pageTab->getTitleField()           => $result['result']['title'],
                        $pageTab->getDescriptionField()     => $result['result']['description'],
                        $pageTab->getContentField()         => json_encode($result['result']['content'], 256),
                        $pageTab->getViewsField()           => $result['result']['views'],
                        $pageTab->getCanEditField()         => (int)$result['result']['can_edit'],
                        $pageTab->getTokenField()           => $token,
                        $pageTab->getPageTypeField()        => static::PAGE_TYPE,
                        $pageTab->getIsFirstTypePageField() => 1,
                        $pageTab->getPageNumField()         => 1,
                        $pageTab->getPostTypeIdField()      => $mission->typeInfo[$typeTab->getPkField()],
                        $pageTab->getPostIdField()          => 0,
                        $pageTab->getPageNumListField()     => '',
                        $pageTab->getParamsField()          => json_encode(["type" => $mission->typeInfo,], 256),
                        $pageTab->getIdentificationField()  => $this->makeTypePageId($mission->typeInfo[$typeTab->getPkField()], 1),
                        $pageTab->getUpdateTimeField()      => time(),
                        $pageTab->getTimeField()            => time(),
                    ];

                    if (!$pageTab->isPkAutoInc())
                    {
                        $data[$pageTab->getPkField()] = $pageTab->calcPk();
                    }

                    $re = $pageTab->tableIns()->insert($data);

                    if ($re)
                    {
                        $this->telegraphQueueMissionManager->logInfo('ok-' . $mission->typeInfo[$this->getTypeTable()
                                ->getNameField()]);
                    }
                    else
                    {
                        $this->telegraphQueueMissionManager->logError($json);
                    }
                }
                else
                {
                    $this->telegraphQueueMissionManager->logError($result['error']);
                }

            };

            $catch = function(TelegraphMission $mission, \Exception $exception) {
                $this->telegraphQueueMissionManager->logError($exception->getMessage());
            };

            $queue->addResultProcessor(new CustomResultProcessor($success, $catch));

            $queue->listen();
        }

        /*-------------------------------------------------------------------*/
        public function createDetailPageToQueue(): void
        {
            $typeTab = $this->getTypeTable();
            $postTab = $this->getPostTable();

            $func = function($posts) use ($postTab, $typeTab) {
                foreach ($posts as $post)
                {
                    if ($this->isDetailPageCreated($post[$postTab->getPkField()]))
                    {
                        $this->telegraphQueueMissionManager->logInfo('id:' . $post[$postTab->getPkField()] . ' 详情页面已经创建，此任务被忽略');

                        continue;
                    }
                    $token = $this->getRandToken();

                    $post[$postTab->getContentsField()] = static::cleanText($post[$postTab->getContentsField()]);

                    $title = static::inlineText($post[$postTab->getContentsField()]);
                    $title = static::truncateUtf8String($title, 50);

                    if (!$title)
                    {
                        $title = '无标题-' . hrtime(true);
                    }

                    $mission = new TelegraphMission();
                    $mission->setTimeout($this->telegraphTimeout);
                    $mission->token = $token;
                    $mission->post  = $post;
                    $mission->title = $title;

                    if (!is_null($this->telegraphProxy))
                    {
                        $mission->setProxy($this->telegraphProxy);
                    }

                    $json = $this->telegraphPageStyle->placeHolder($title . ' 建设中...');
                    $mission->setAccessToken($token);
                    $mission->createPage($title, $json, true);

                    $this->telegraphQueueMissionManager->logInfo('createDetailPage: ' . $post['id'] . ' - ' . $title);

                    $this->createDetailPageQueue->addNewMission($mission);
                }
            };

            $postTab->tableIns()->alias('post')->field(implode(',', [
                'post.*',
                'type.' . $typeTab->getNameField() . ' as type_name',
            ]))
                ->join($typeTab->getName() . ' type', 'post.' . $postTab->getTypeIdField() . ' = type.' . $typeTab->getPkField(), 'left')
                ->chunk(500, $func, 'post.' . $postTab->getPkField());
        }

        public function listenCreateDetailPage(): void
        {
            $queue = $this->createDetailPageQueue;

            $queue->setContinuousRetry(true);
            $queue->setDelayMs($this->telegraphQueueDelayMs);
            $queue->setEnable(true);
            $queue->setMaxTimes($this->telegraphQueueMaxTimes);
            $queue->setIsRetryOnError(true);
            $queue->setMissionProcessor(new GuzzleMissionProcessor());

            $success = function(TelegraphMission $mission) {
                $response = $mission->getResult();
                $json     = $response->getBody()->getContents();
                $result   = json_decode($json, true);

                $token = $mission->token;
                if ($result['ok'])
                {
                    $pageTab = $this->getPagesTable();
                    $typeTab = $this->getTypeTable();
                    $postTab = $this->getPostTable();

                    $data = [
                        $pageTab->getPathField()            => $result['result']['path'],
                        $pageTab->getUrlField()             => $result['result']['url'],
                        $pageTab->getTitleField()           => $result['result']['title'],
                        $pageTab->getDescriptionField()     => $result['result']['description'],
                        $pageTab->getContentField()         => json_encode($result['result']['content'], 256),
                        $pageTab->getViewsField()           => $result['result']['views'],
                        $pageTab->getCanEditField()         => (int)$result['result']['can_edit'],
                        $pageTab->getTokenField()           => $token,
                        $pageTab->getPageTypeField()        => static::PAGE_DETAIL,
                        $pageTab->getIsFirstTypePageField() => 0,
                        $pageTab->getPageNumField()         => 0,
                        $pageTab->getFirstUpadtedField()    => 0,
                        $pageTab->getPostTypeIdField()      => $mission->post[$postTab->getTypeIdField()],
                        $pageTab->getPostIdField()          => $mission->post[$postTab->getPkField()],
                        $pageTab->getPageNumListField()     => '',
                        $pageTab->getParamsField()          => json_encode([
                            "post"  => $mission->post,
                            "token" => $mission->token,
                        ], 256),
                        $pageTab->getIdentificationField()  => $this->makeDetailPageId($mission->post[$postTab->getPkField()]),
                        $pageTab->getUpdateTimeField()      => time(),
                        $pageTab->getTimeField()            => time(),
                    ];

                    if (!$pageTab->isPkAutoInc())
                    {
                        $data[$pageTab->getPkField()] = $pageTab->calcPk();
                    }

                    $re = $pageTab->tableIns()->insert($data);

                    if ($re)
                    {
                        $this->telegraphQueueMissionManager->logInfo('ok-' . $result['result']['title']);
                    }
                    else
                    {
                        $this->telegraphQueueMissionManager->logError($json);
                    }
                }
                else
                {
                    $this->telegraphQueueMissionManager->logError($result['error']);
                }

            };

            $catch = function(TelegraphMission $mission, \Exception $exception) {
                $this->telegraphQueueMissionManager->logError($exception->getMessage());
            };

            $queue->addResultProcessor(new CustomResultProcessor($success, $catch));

            $queue->listen();
        }

        /*-------------------------------------------------------------------*/
        public function createTypeAllPageToQueue(): void
        {
            $typeTab = $this->getTypeTable();
            $postTab = $this->getPostTable();
            $pageTab = $this->getPagesTable();

            //所有分类页面
            $wherePageType = [
                [
                    $pageTab->getPageTypeField(),
                    '=',
                    static::PAGE_TYPE,
                ],
            ];

            //所有详情页面
            $wherePageDetail = [
                [
                    $pageTab->getPageTypeField(),
                    '=',
                    static::PAGE_DETAIL,
                ],
            ];

            //所有涉及到的分类
            $typeIds = $pageTab->tableIns()->where($wherePageDetail)->group($pageTab->getPostTypeIdField())
                ->column($pageTab->getPostTypeIdField());

            //遍历分类，生成分页信息
            foreach ($typeIds as $k => $typeId)
            {
                //查出分类详细信息
                $type = $typeTab->tableIns()->where([$typeTab->getPkField() => $typeId])->find();

                $typeName = $type[$typeTab->getNameField()];

                //当前分类总页面记录数
                $count = $pageTab->tableIns()->where($wherePageDetail)
                    ->where([$pageTab->getPostTypeIdField() => $typeId])->count();

                //折合总页数
                $totalPages = (int)ceil($count / $this->telegraphPageRow);

                //生成当前分类页数信息，为每页构造列表页面
                //分类第1页已经生成过，从第2页开始
                for ($pageNow = 2; $pageNow <= $totalPages; $pageNow++)
                {
                    $currentTypeId = $type[$typeTab->getPkField()];

                    if ($this->isTypePageCreated($currentTypeId, $pageNow))
                    {
                        $this->telegraphQueueMissionManager->logInfo($currentTypeId . '-' . $pageNow . ' 分类页面已经创建，此任务被忽略');

                        continue;
                    }

                    $token = $this->getRandToken();

                    $mission = new TelegraphMission();
                    $mission->setTimeout($this->telegraphTimeout);
                    $mission->token    = $token;
                    $mission->typeInfo = $type;
                    $mission->pageNow  = $pageNow;

                    if (!is_null($this->telegraphProxy))
                    {
                        $mission->setProxy($this->telegraphProxy);
                    }

                    $title = $typeName;
                    $json  = $this->telegraphPageStyle->placeHolder($title . ' 建设中...');
                    $mission->setAccessToken($token);
                    $mission->createPage($title, $json, true);

                    $this->telegraphQueueMissionManager->logInfo('createTypeAllPageToQueue: ' . $typeName . ':' . $currentTypeId . '-' . $pageNow);

                    $this->createTypeAllPageQueue->addNewMission($mission);
                }
            }

        }

        public function listenCreateTypeAllPage(): void
        {
            $queue = $this->createTypeAllPageQueue;

            $queue->setContinuousRetry(true);
            $queue->setDelayMs($this->telegraphQueueDelayMs);
            $queue->setEnable(true);
            $queue->setMaxTimes($this->telegraphQueueMaxTimes);
            $queue->setIsRetryOnError(true);
            $queue->setMissionProcessor(new GuzzleMissionProcessor());

            $success = function(TelegraphMission $mission) {
                $response = $mission->getResult();
                $json     = $response->getBody()->getContents();
                $result   = json_decode($json, true);

                $token = $mission->token;
                if ($result['ok'])
                {
                    $pageTab = $this->getPagesTable();
                    $typeTab = $this->getTypeTable();
                    $postTab = $this->getPostTable();

                    $data = [
                        $pageTab->getPathField()            => $result['result']['path'],
                        $pageTab->getUrlField()             => $result['result']['url'],
                        $pageTab->getTitleField()           => $result['result']['title'],
                        $pageTab->getDescriptionField()     => $result['result']['description'],
                        $pageTab->getContentField()         => json_encode($result['result']['content'], 256),
                        $pageTab->getViewsField()           => $result['result']['views'],
                        $pageTab->getCanEditField()         => (int)$result['result']['can_edit'],
                        $pageTab->getTokenField()           => $token,
                        $pageTab->getPageTypeField()        => static::PAGE_TYPE,
                        $pageTab->getIsFirstTypePageField() => 0,
                        $pageTab->getPageNumField()         => $mission->pageNow,
                        $pageTab->getPostTypeIdField()      => $mission->typeInfo[$typeTab->getPkField()],
                        $pageTab->getPostIdField()          => 0,
                        $pageTab->getPageNumListField()     => '',
                        $pageTab->getParamsField()          => json_encode([
                            "type" => $mission->typeInfo,
                        ], 256),
                        $pageTab->getIdentificationField()  => $this->makeTypePageId($mission->typeInfo[$typeTab->getPkField()], $mission->pageNow),
                        $pageTab->getUpdateTimeField()      => time(),
                        $pageTab->getTimeField()            => time(),
                    ];

                    if (!$pageTab->isPkAutoInc())
                    {
                        $data[$pageTab->getPkField()] = $pageTab->calcPk();
                    }

                    $re = $pageTab->tableIns()->insert($data);

                    if ($re)
                    {
                        $this->telegraphQueueMissionManager->logInfo('ok-' . $mission->typeInfo[$typeTab->getNameField()] . '--页码:' . $mission->pageNow);
                    }
                    else
                    {
                        $this->telegraphQueueMissionManager->logError($json);
                    }
                }
                else
                {
                    $this->telegraphQueueMissionManager->logError($result['error']);
                }

            };

            $catch = function(TelegraphMission $mission, \Exception $exception) {
                $this->telegraphQueueMissionManager->logError($exception->getMessage());
            };

            $queue->addResultProcessor(new CustomResultProcessor($success, $catch));

            $queue->listen();
        }

        /*-------------------------------------------------------------------*/
        public function updateTypeAllPageToQueue(): void
        {
            $typeTab = $this->getTypeTable();
            $postTab = $this->getPostTable();
            $pageTab = $this->getPagesTable();

            $this->syncType();

            //所有分类页面
            $wherePageType = [
                [
                    $pageTab->getPageTypeField(),
                    '=',
                    static::PAGE_TYPE,
                ],
            ];

            //所有详情页面
            $wherePageDetail = [
                [
                    $pageTab->getPageTypeField(),
                    '=',
                    static::PAGE_DETAIL,
                ],
            ];

            //所有涉及到的分类
            $typeIds = $pageTab->tableIns()->where($wherePageDetail)->group($pageTab->getPostTypeIdField())
                ->column($pageTab->getPostTypeIdField());

            //遍历分类，生成分页信息
            foreach ($typeIds as $k => $typeId)
            {
                //查出分类详细信息
                $type = $typeTab->tableIns()->where([$typeTab->getPkField() => $typeId])->find();

                $typeName = $type[$typeTab->getNameField()];

                //当前分类总记录数
                $count = $pageTab->tableIns()->where($wherePageDetail)
                    ->where([$pageTab->getPostTypeIdField() => $typeId])->count();

                //折合总页数
                $totalPages = (int)ceil($count / $this->telegraphPageRow);

                //生成当前分类页数信息，为每页构造列表页面
                for ($pageNow = 1; $pageNow <= $totalPages; $pageNow++)
                {
                    $results = $pageTab->tableIns()->where($wherePageDetail)
                        ->where([$pageTab->getPostTypeIdField() => $typeId,])->order($pageTab->getPostIdField(), 'asc')
                        ->paginate([
                            'list_rows' => $this->telegraphPageRow,
                            'page'      => $pageNow,
                        ]);

                    preg_match_all('%\d+(?=</a>|</span>)%im', (string)$results->render(), $result, PREG_PATTERN_ORDER);
                    $pagesNum = $result[0];

                    sort($pagesNum);

                    $pageTab->tableIns()->where([
                        $pageTab->getIdentificationField() => $this->makeTypePageId($typeId, $pageNow),
                    ])->update([
                        $pageTab->getPageNumListField() => implode(',', $pagesNum),
                    ]);

                    $this->telegraphQueueMissionManager->logInfo(implode([
                        '更新分页列表: ',
                        $this->makeTypePageId($typeId, $pageNow),
                        ' => ',
                        '[' . implode(',', $pagesNum) . ']',
                    ]));

                }
            }

            //查询遍历所有的详情页面
            $typePages = $pageTab->tableIns()->where($wherePageType)->cursor();

            foreach ($typePages as $k => $typePage)
            {
                $params   = json_decode($typePage[$pageTab->getParamsField()], true);
                $typeInfo = $params['type'];
                $title    = $typeInfo[$this->getTypeTable()->getNameField()];

                //分页按钮
                $pageUrls = $pageTab->tableIns()->where($wherePageType)->where([
                    [
                        $pageTab->getPageNumField(),
                        'in',
                        explode(',', $typePage[$pageTab->getPageNumListField()]),
                    ],
                    [
                        $pageTab->getPostTypeIdField(),
                        '=',
                        $typePage[$pageTab->getPostTypeIdField()],
                    ],
                ])->order($pageTab->getPageNumField(), 'asc')->field(implode(',', [
                    $pageTab->getUrlField(),
                    $pageTab->getPageNumField(),
                ]))->select();

                $pageButtons = [];

                foreach ($pageUrls as $urls)
                {
                    $pageButtons[] = [
                        "href"    => $urls[$pageTab->getUrlField()],
                        "caption" => ($urls[$pageTab->getPageNumField()] !== $typePage[$pageTab->getPageNumField()]) ? $urls[$pageTab->getPageNumField()] : "<{$urls[$pageTab->getPageNumField()]}>",
                    ];
                }

                //中间条目列表
                $contentsList = [];

                $detailPages = $pageTab->tableIns()->where($wherePageDetail)->where([
                    [
                        $pageTab->getPostTypeIdField(),
                        '=',
                        $typePage[$pageTab->getPostTypeIdField()],
                    ],
                ])->field(implode(',', [
                    $pageTab->getUrlField(),
                    $pageTab->getTitleField(),
                ]))->order($pageTab->getPostIdField(), 'desc')->paginate([
                    'list_rows' => $this->telegraphPageRow,
                    'page'      => $typePage[$pageTab->getPageNumField()],
                ]);

                foreach ($detailPages as $detailPage)
                {
                    $contentsList[] = [
                        "href"    => $detailPage[$pageTab->getUrlField()],
                        "caption" => $detailPage[$pageTab->getTitleField()],
                    ];
                }

                $this->telegraphPageStyle->updateTypePage($typeInfo, $pageButtons, $contentsList);

                $token   = $typePage[$pageTab->getTokenField()];
                $mission = new TelegraphMission();
                $mission->setTimeout($this->telegraphTimeout);
                $mission->typePage = $typePage;

                if (!is_null($this->telegraphProxy))
                {
                    $mission->setProxy($this->telegraphProxy);
                }

                $mission->setAccessToken($token);
                $mission->editPage($typePage[$pageTab->getPathField()], $title, $this->telegraphPageStyle->toJson(), true);

                $this->telegraphQueueMissionManager->logInfo(implode([
                    'updateTypeAllPageToQueue，',
                    '[' . $this->makeTypePageId($typePage[$pageTab->getPostTypeIdField()], $typePage[$pageTab->getPostTypeIdField()]) . ']，',
                    'url: ' . $typePage[$pageTab->getUrlField()],
                    $title,
                ]));

                $this->updateTypeAllPageQueue->addNewMission($mission);
            }

        }

        public function listenUpdateTypeAllPage(): void
        {
            $queue = $this->updateTypeAllPageQueue;

            $queue->setContinuousRetry(true);
            $queue->setDelayMs($this->telegraphQueueDelayMs);
            $queue->setEnable(true);
            $queue->setMaxTimes($this->telegraphQueueMaxTimes);
            $queue->setIsRetryOnError(true);
            $queue->setMissionProcessor(new GuzzleMissionProcessor());

            $success = function(TelegraphMission $mission) {
                $response = $mission->getResult();
                $json     = $response->getBody()->getContents();
                $result   = json_decode($json, true);

                $typeTab = $this->getTypeTable();
                $postTab = $this->getPostTable();
                $pageTab = $this->getPagesTable();

                if ($result['ok'])
                {
                    $this->telegraphQueueMissionManager->logInfo(implode([
                        '更新成功，',
                        '[' . $this->makeTypePageId($mission->typePage[$pageTab->getPostTypeIdField()], $mission->typePage[$pageTab->getPostTypeIdField()]) . ']，',
                        'url: ' . $mission->typePage[$pageTab->getUrlField()],
                    ]));
                }
                else
                {
                    $this->telegraphQueueMissionManager->logError($result['error']);
                }
            };

            $catch = function(TelegraphMission $mission, \Exception $exception) {
                $this->telegraphQueueMissionManager->logError($exception->getMessage());
            };

            $queue->addResultProcessor(new CustomResultProcessor($success, $catch));

            $queue->listen();
        }

        /*-------------------------------------------------------------------*/
        public function updateDetailPageToQueue(bool $isNewPageUpdateOnly = false): void
        {
            $pageTab = $this->getPagesTable();
            $typeTab = $this->getTypeTable();
            $postTab = $this->getPostTable();
            $fileTab = $this->getFileTable();

            $wherePageDetail = [
                [
                    $pageTab->getPageTypeField(),
                    '=',
                    static::PAGE_DETAIL,
                ],
            ];

            $temp = $pageTab->tableIns()->alias('webpages')
                ->join($typeTab->getName() . ' type', 'webpages.' . $pageTab->getPostTypeIdField() . ' = type.' . $typeTab->getPkField(), 'left')
                ->field(implode(',', [
                    'webpages.*',
                    'type.' . $typeTab->getNameField() . ' as type_name',
                ]))->where($wherePageDetail);

            if ($isNewPageUpdateOnly)
            {
                $temp->where([
                    [
                        'webpages.' . $pageTab->getFirstUpadtedField(),
                        '=',
                        0,
                    ],
                ]);
            }

            $temp->chunk(100, function($pages) use ($wherePageDetail, $postTab, $fileTab, $pageTab, $typeTab) {
                foreach ($pages as $page)
                {
                    $title = $page[$pageTab->getTitleField()];

                    $params = json_decode($page[$pageTab->getParamsField()], true);
                    $token  = $params['token'];
                    $post   = $params['post'];

                    $post['title'] = $title;

                    $files = $fileTab->tableIns()->where([
                        [
                            $fileTab->getPostIdField(),
                            '=',
                            $page[$pageTab->getPostIdField()],
                        ],
                    ])->order($fileTab->getPkField(), 'asc')->select();

                    $prve_next_item = [];

                    $prve = $pageTab->tableIns()->where($wherePageDetail)->where([
                        [
                            $pageTab->getPostTypeIdField(),
                            '=',
                            $page[$pageTab->getPostTypeIdField()],
                        ],
                        [
                            $pageTab->getPostIdField(),
                            '<',
                            $page[$pageTab->getPostIdField()],
                        ],

                    ])->order($pageTab->getPostIdField(), 'desc')->find();
                    if ($prve)
                    {
                        $prve_next_item[] = [
                            "href"    => $prve[$pageTab->getUrlField()],
                            "caption" => [
                                E::strong("[上一篇]: "),
                                $prve[$pageTab->getTitleField()],
                            ],
                        ];
                    }

                    $next = $pageTab->tableIns()->where($wherePageDetail)->where([
                        [
                            $pageTab->getPostTypeIdField(),
                            '=',
                            $page[$pageTab->getPostTypeIdField()],
                        ],
                        [
                            $pageTab->getPostIdField(),
                            '>',
                            $page[$pageTab->getPostIdField()],
                        ],

                    ])->order($pageTab->getPostIdField(), 'asc')->find();
                    if ($next)
                    {
                        $prve_next_item[] = [
                            "href"    => $next[$pageTab->getUrlField()],
                            "caption" => [
                                E::strong("[下一篇]: "),
                                $next[$pageTab->getTitleField()],
                            ],
                        ];
                    }

                    $prve_next = E::AListWithCaption1($prve_next_item, false);

                    $this->telegraphPageStyle->updateDetailPage($post, $page, $prve_next, $files);

                    $mission = new TelegraphMission();
                    $mission->setTimeout($this->telegraphTimeout);
                    $mission->token      = $token;
                    $mission->detailPage = $page;
                    $mission->title      = $title;

                    if (!is_null($this->telegraphProxy))
                    {
                        $mission->setProxy($this->telegraphProxy);
                    }

                    $mission->setAccessToken($token);
                    $mission->editPage($page['path'], $title, $this->telegraphPageStyle->toJson(), true);

                    $this->telegraphQueueMissionManager->logInfo('updateDetailPageToQueue: ' . $page['id'] . ' - ' . $title);
                    $this->telegraphQueueMissionManager->logInfo(implode([
                        'updateDetailPageToQueue，',
                        '分类:' . $mission->detailPage[$pageTab->getPostTypeIdField()] . '，',
                        '标题:' . $mission->title . '，',
                        'url: ' . $mission->detailPage[$pageTab->getUrlField()],
                    ]));
                    $this->updateDetailPageQueue->addNewMission($mission);
                }

            }, 'webpages.' . $pageTab->getPkField());

        }

        public function listenUpdateDetailPage(): void
        {
            $queue = $this->updateDetailPageQueue;

            $queue->setContinuousRetry(true);
            $queue->setDelayMs($this->telegraphQueueDelayMs);
            $queue->setEnable(true);
            $queue->setMaxTimes($this->telegraphQueueMaxTimes);
            $queue->setIsRetryOnError(true);
            $queue->setMissionProcessor(new GuzzleMissionProcessor());

            $success = function(TelegraphMission $mission) {
                $response = $mission->getResult();
                $json     = $response->getBody()->getContents();
                $result   = json_decode($json, true);

                $typeTab = $this->getTypeTable();
                $postTab = $this->getPostTable();
                $pageTab = $this->getPagesTable();

                if ($result['ok'])
                {
                    $re = $pageTab->tableIns()->where([
                        [
                            $pageTab->getPkField(),
                            '=',
                            $mission->detailPage[$pageTab->getPkField()],
                        ],
                    ])->update([
                        $pageTab->getFirstUpadtedField() => 1,
                    ]);

                    if ($re)
                    {
                        $this->telegraphQueueMissionManager->logInfo(implode([
                            '更新成功，',
                            '分类:' . $mission->detailPage[$pageTab->getPostTypeIdField()] . '，',
                            '标题:' . $mission->title . '，',
                            'url: ' . $mission->detailPage[$pageTab->getUrlField()],
                        ]));
                    }
                    else
                    {
                        $this->telegraphQueueMissionManager->logError($json);
                    }

                }
                else
                {
                    $this->telegraphQueueMissionManager->logError($result['error']);
                }
            };

            $catch = function(TelegraphMission $mission, \Exception $exception) {
                $this->telegraphQueueMissionManager->logError($exception->getMessage());
            };

            $queue->addResultProcessor(new CustomResultProcessor($success, $catch));

            $queue->listen();
        }

        /*-------------------------------------------------------------------*/
        public function updateIndexPageToQueue(): void
        {
            $pageTab = $this->getPagesTable();
            $typeTab = $this->getTypeTable();
            $postTab = $this->getPostTable();
            $fileTab = $this->getFileTable();

            $indexPageInfo = $this->getIndexPageInfo();
            $token         = $indexPageInfo[$pageTab->getTokenField()];

            $this->telegraphPageStyle->updateIndexPage();

            $mission = new TelegraphMission();
            $mission->setTimeout($this->telegraphTimeout);
            $mission->token         = $token;
            $mission->indexPageInfo = $indexPageInfo;

            if (!is_null($this->telegraphProxy))
            {
                $mission->setProxy($this->telegraphProxy);
            }

            $mission->setAccessToken($token);
            $mission->editPage($indexPageInfo['path'], $this->telegraphPageBrandTitle, $this->telegraphPageStyle->toJson(), true);

            $this->telegraphQueueMissionManager->logInfo(implode([
                'updateIndexPageToQueue，',
                'url: ' . $indexPageInfo[$pageTab->getUrlField()],
            ]));
            $this->updateIndexPageQueue->addNewMission($mission);
        }

        public function listenUpdateIndexPage(): void
        {
            $queue = $this->updateIndexPageQueue;

            $queue->setContinuousRetry(true);
            $queue->setDelayMs($this->telegraphQueueDelayMs);
            $queue->setEnable(true);
            $queue->setMaxTimes($this->telegraphQueueMaxTimes);
            $queue->setIsRetryOnError(true);
            $queue->setMissionProcessor(new GuzzleMissionProcessor());

            $success = function(TelegraphMission $mission) {
                $response = $mission->getResult();
                $json     = $response->getBody()->getContents();
                $result   = json_decode($json, true);

                $typeTab = $this->getTypeTable();
                $postTab = $this->getPostTable();
                $pageTab = $this->getPagesTable();

                if ($result['ok'])
                {
                    $this->telegraphQueueMissionManager->logInfo(implode([
                        'updateIndexPageToQueue，',
                        'url: ' . $mission->indexPageInfo[$pageTab->getUrlField()],
                    ]));
                }
                else
                {
                    $this->telegraphQueueMissionManager->logError($result['error']);
                }
            };

            $catch = function(TelegraphMission $mission, \Exception $exception) {
                $this->telegraphQueueMissionManager->logError($exception->getMessage());
            };

            $queue->addResultProcessor(new CustomResultProcessor($success, $catch));

            $queue->listen();
        }

        /*-------------------------------------------------------------------*/

        public function queueMonitor(): void
        {
            $this->telegraphQueueMissionManager->getAllQueueInfoTable();
        }

        public function getQueueStatus(): array
        {
            return $this->telegraphQueueMissionManager->getAllQueueInfo();
        }

        public function restoreFailureMission(): void
        {
            $this->createAccountQueue->restoreErrorMission();
            $this->createIndexPageQueue->restoreErrorMission();
            $this->createFirstTypePageQueue->restoreErrorMission();
            $this->createDetailPageQueue->restoreErrorMission();
            $this->createTypeAllPageQueue->restoreErrorMission();
            $this->updateTypeAllPageQueue->restoreErrorMission();
            $this->updateDetailPageQueue->restoreErrorMission();
            $this->updateIndexPageQueue->restoreErrorMission();

            $this->createAccountQueue->restoreTimesReachedMission();
            $this->createIndexPageQueue->restoreTimesReachedMission();
            $this->createFirstTypePageQueue->restoreTimesReachedMission();
            $this->createDetailPageQueue->restoreTimesReachedMission();
            $this->createTypeAllPageQueue->restoreTimesReachedMission();
            $this->updateTypeAllPageQueue->restoreTimesReachedMission();
            $this->updateDetailPageQueue->restoreTimesReachedMission();
            $this->updateIndexPageQueue->restoreTimesReachedMission();
        }

        public function setTelegraphQueueDelayMs(int $telegraphQueueDelayMs): static
        {
            $this->telegraphQueueDelayMs = $telegraphQueueDelayMs;

            return $this;
        }

        public function setTelegraphTimeout(int $telegraphTimeout): static
        {
            $this->telegraphTimeout = $telegraphTimeout;

            return $this;
        }

        public function setTelegraphPageBrandTitle(?string $telegraphPageBrandTitle): static
        {
            $this->telegraphPageBrandTitle = $telegraphPageBrandTitle;

            return $this;
        }

        public function setTelegraphQueueMaxTimes(int $telegraphQueueMaxTimes): static
        {
            $this->telegraphQueueMaxTimes = $telegraphQueueMaxTimes;

            return $this;
        }

        public function setTelegraphProxy(?string $telegraphProxy): static
        {
            $this->telegraphProxy = $telegraphProxy;

            return $this;
        }

        public function setTelegraphPageStyle(?StyleAbstract $telegraphPageStyle): static
        {
            $this->telegraphPageStyle = $telegraphPageStyle;

            $this->telegraphPageStyle->setManager($this);

            return $this;
        }

        public function getRandToken(): string
        {
            $tokens = $this->postManager->getCacheManager()->get($this->cacheAccountTokens, function($item) {
                $item->expiresAfter(30);
                $tab = $this->getAccountTable();

                $tokens = $tab->tableIns()->column($tab->getAccessTokenField());

                return $tokens;
            });

            return $tokens[rand(0, count($tokens) - 1)];
        }

        public function getIndexPageInfo(): array
        {
            return $this->postManager->getCacheManager()->get($this->cacheIndexPage, function($item) {
                $item->expiresAfter(30);

                $webPageTab = $this->getPagesTable();

                //index 页面信息
                return $webPageTab->tableIns()->where([
                    [
                        $webPageTab->getPageTypeField(),
                        '=',
                        static::PAGE_INDEX,
                    ],
                ])->findOrEmpty();
            });
        }

        public function getTypeFirstPage(): array
        {
            return $this->postManager->getCacheManager()->get($this->cacheFirstTypePage, function($item) {
                $item->expiresAfter(30);

                $pageTab = $this->getPagesTable();
                $typeTab = $this->getTypeTable();

                //获取所有分类第一页的记录
                $typeFirstPage = $pageTab->tableIns()->where([
                    [
                        $pageTab->getIsFirstTypePageField(),
                        '=',
                        1,
                    ],
                    [
                        $pageTab->getPageTypeField(),
                        '=',
                        static::PAGE_TYPE,
                    ],
                ])->order($pageTab->getPkField(), 'asc')->select()->toArray();

                $typeFirstPageArr = [];
                foreach ($typeFirstPage as $k => $v)
                {
                    $params = json_decode($v[$pageTab->getParamsField()], true);

                    $typeFirstPageArr[] = [
                        "href"    => $v[$pageTab->getUrlField()],
                        "caption" => $params['type'][$typeTab->getNameField()],
                    ];
                }

                return $typeFirstPageArr;
            });
        }

        public function getLatestDetailPages($count = 10): array
        {
            $pageTab = $this->getPagesTable();

            $items = $pageTab->tableIns()->where([
                [
                    $pageTab->getPageTypeField(),
                    '=',
                    static::PAGE_DETAIL,
                ],
            ])->field(implode(',', [
                $pageTab->getUrlField(),
                $pageTab->getTitleField(),
            ]))->order($pageTab->getPkField(), 'desc')->limit(0, $count)->select();

            $pagesList = [];
            foreach ($items as $item)
            {
                $pagesList[] = [
                    "href"    => $item[$pageTab->getUrlField()],
                    "caption" => $item[$pageTab->getTitleField()],
                ];
            }

            return $pagesList;
        }

        public function getRandDetailPages($count = 10): array
        {
            $detailPages = $this->postManager->getCacheManager()->get($this->cacheRandDetailPage, function($item) {
                $item->expiresAfter(30);

                $pageTab = $this->getPagesTable();

                $items = $pageTab->tableIns()->where([
                    [
                        $pageTab->getPageTypeField(),
                        '=',
                        static::PAGE_DETAIL,
                    ],
                ])->field(implode(',', [
                    $pageTab->getUrlField(),
                    $pageTab->getTitleField(),
                ]))->select();

                $pagesList = [];
                foreach ($items as $item)
                {
                    $pagesList[] = [
                        "href"    => $item[$pageTab->getUrlField()],
                        "caption" => $item[$pageTab->getTitleField()],
                    ];
                }

                return $pagesList;
            });

            $count = min($count, count($detailPages));

            // 根据随机键提取元素
            $randomItems = [];

            if (count($detailPages))
            {
                // 获取随机键
                $randomKeys = array_rand($detailPages, $count);

                foreach ($randomKeys as $key)
                {
                    $randomItems[] = $detailPages[$key];
                }
            }

            return $randomItems;
        }


        /**
         * -2 表都没创建
         * -1 已经存在
         * 0 写入失败
         * 1 写入成功
         *
         * @param string $name
         * @param int    $groupId
         *
         * @return int
         */
        public function addType(string $name, int $groupId): int
        {
            $typeTab = $this->getTypeTable();

            if (!$typeTab->isTableCerated())
            {
                return -2;
            }

            if ($this->isTypeGroupIdExists($groupId))
            {
                return -1;
            }

            return (int)!!$typeTab->tableIns()->insert([
                $typeTab->getGroupIdField() => $groupId,
                $typeTab->getNameField()    => $name,
            ]);
        }

        public function delType(int $groupId): bool
        {
            $typeTab = $this->getTypeTable();

            if (!$typeTab->isTableCerated())
            {
                return false;
            }
            $typeTab->tableIns()->where($typeTab->getGroupIdField(), '=', $groupId)->delete();

            return true;
        }

        public function isTypeGroupIdExists(int $groupId): bool
        {
            $typeTab = $this->getTypeTable();

            if (!$typeTab->isTableCerated())
            {
                return false;
            }

            return !!$typeTab->tableIns()->where($typeTab->getGroupIdField(), '=', $groupId)->findOrEmpty();
        }

        public function getTypeList()
        {
            $typeTab = $this->getTypeTable();

            if (!$typeTab->isTableCerated())
            {
                return [];
            }

            return $typeTab->tableIns()->field(implode(',', [
                $typeTab->getPkField(),
                $typeTab->getNameField(),
                $typeTab->getGroupIdField(),
            ]))->select();
        }

        public function getTypes()
        {
            $types = $this->postManager->getCacheManager()->get($this->cacheTypes, function($item) {
                $item->expiresAfter(30);

                $typeTab = $this->getTypeTable();

                return $typeTab->tableIns()->field(implode(',', [
                    $typeTab->getPkField(),
                    $typeTab->getNameField(),
                    $typeTab->getGroupIdField(),
                ]))->select();
            });

            return $types;
        }


        protected function makeIndexPageId(): string
        {
            return '-';
        }

        protected function makeTypePageId(string|int $typePkId, string|int $pageNum): string
        {
            return $typePkId . '-' . $pageNum;

        }

        protected function makeDetailPageId(string|int $postPkId): string
        {
            return (string)$postPkId;
        }

        protected function initQueue(): static
        {
            $this->createAccountQueue       = $this->telegraphQueueMissionManager->initQueue(static::CREATE_ACCOUNT_QUEUE);
            $this->createIndexPageQueue     = $this->telegraphQueueMissionManager->initQueue(static::CREATE_INDEX_PAGE_QUEUE);
            $this->createFirstTypePageQueue = $this->telegraphQueueMissionManager->initQueue(static::CREATE_FIRST_TYPE_PAGE_QUEUE);
            $this->createDetailPageQueue    = $this->telegraphQueueMissionManager->initQueue(static::CREATE_DETAIL_PAGE_QUEUE);
            $this->createTypeAllPageQueue   = $this->telegraphQueueMissionManager->initQueue(static::CREATE_TYPE_ALL_PAGE_QUEUE);
            $this->updateTypeAllPageQueue   = $this->telegraphQueueMissionManager->initQueue(static::UPDATE_TYPE_ALL_PAGE_QUEUE);
            $this->updateDetailPageQueue    = $this->telegraphQueueMissionManager->initQueue(static::UPDATE_DETAIL_PAGE_QUEUE);
            $this->updateIndexPageQueue     = $this->telegraphQueueMissionManager->initQueue(static::UPDATE_INDEX_PAGE_QUEUE);
            $this->cdnPrefetchQueue         = $this->telegraphQueueMissionManager->initQueue(static::CDN_PREFETCH_QUEUE);
            $this->makeVideoCoverQueue      = $this->telegraphQueueMissionManager->initQueue(static::MAKE_VIDEO_COVER_QUEUE);
            $this->convertM3u8Queue         = $this->telegraphQueueMissionManager->initQueue(static::CONVERT_M3U8_QUEUE);

            return $this;
        }

        public function isIndexPageCreated(): bool
        {
            $pagesTable = $this->getPagesTable();

            if (!$pagesTable->isTableCerated())
            {
                return false;
            }

            return !!$pagesTable->tableIns()
                ->where($pagesTable->getIdentificationField(), '=', $this->makeIndexPageId())
                ->where($pagesTable->getPageTypeField(), '=', static::PAGE_INDEX)->find();
        }

        protected function isTypePageCreated(string|int $typePkId, string|int $pageNum): bool
        {
            $detailIdentifications = $this->postManager->getCacheManager()
                ->get($this->cacheTypePageIdentifications, function($item) {
                    $item->expiresAfter(5);
                    $pagesTable = $this->getPagesTable();

                    $ids = $pagesTable->tableIns()->where($pagesTable->getPageTypeField(), '=', static::PAGE_TYPE)
                        ->column($pagesTable->getIdentificationField());

                    return $ids;
                });

            $typeIdentification = $this->makeTypePageId($typePkId, $pageNum);

            return in_array($typeIdentification, $detailIdentifications);
        }

        protected function isDetailPageCreated(string|int $postPkId): bool
        {
            $detailIdentifications = $this->postManager->getCacheManager()
                ->get($this->cacheDetailPageIdentifications, function($item) {
                    $item->expiresAfter(5);
                    $pagesTable = $this->getPagesTable();

                    $ids = $pagesTable->tableIns()->where($pagesTable->getPageTypeField(), '=', static::PAGE_DETAIL)
                        ->column($pagesTable->getIdentificationField());

                    return $ids;
                });

            return in_array($this->makeDetailPageId($postPkId), $detailIdentifications);
        }


        //把type表中的type同步到post表中分类为 type的 pararm字段中
        public function syncType(): void
        {
            $pageTab = $this->getPagesTable();

            //所有分类页面
            $wherePageType = [
                [
                    $pageTab->getPageTypeField(),
                    '=',
                    static::PAGE_TYPE,
                ],
            ];

            $typePages = $pageTab->tableIns()->where($wherePageType)->field(implode(',', [
                $pageTab->getPkField(),
                $pageTab->getParamsField(),
            ]))->select();

            $types = $this->getTypeList();

            $typeArr = [];

            array_map(function($type) use (&$typeArr) {
                $typeArr[$type['id']] = $type;
            }, $types->toArray());

            foreach ($typePages as $k => $pageInfo)
            {
                $pageTypeParam = json_decode($pageInfo['params'], true);
                if (isset($pageTypeParam['type']))
                {
                    //pages 表中的param属性
                    $paramTypeInfo = $pageTypeParam['type'];

                    if (isset($typeArr[$paramTypeInfo['id']]))
                    {
                        //type表的数据
                        $originalTypeInfo = $typeArr[$paramTypeInfo['id']];

                        //如果不想等，就把type表的数据更新到 pages 表中的param属性
                        if ($originalTypeInfo['name'] != $paramTypeInfo['name'])
                        {
                            $pageTab->tableIns()->where($pageTab->getPkField(), '=', $pageInfo[$pageTab->getPkField()])
                                ->update([
                                    $pageTab->getParamsField() => json_encode([
                                        "type" => $originalTypeInfo,
                                    ], 256),
                                ]);

                        }
                    }

                }
            }

        }


        public function getAllTableStatus(): array
        {
            $data = [];

            $e                                         = $this->getAccountTable()->isTableCerated();
            $data[$this->getAccountTable()->getName()] = [
                'is_created' => (int)$e,
                'count'      => $e ? (int)$this->getAccountTable()->getCount() : 0,
            ];

            $f                                       = $this->getPagesTable()->isTableCerated();
            $data[$this->getPagesTable()->getName()] = [
                'is_created' => (int)$f,
                'count'      => $f ? (int)$this->getPagesTable()->getCount() : 0,
            ];

            return $data;
        }

        protected function initMissionManager(): static
        {
            $this->telegraphQueueMissionManager = new MissionManager($this->container);
            $this->telegraphQueueMissionManager->setPrefix($this->redisNamespace);

            $logName = 'te-queue-manager';
            $this->telegraphQueueMissionManager->setStandardLogger($logName);
            if ($this->enableRedisLog)
            {
                $this->telegraphQueueMissionManager->addRedisHandler(redisHost: $this->redisHost, redisPort: $this->redisPort, password: $this->redisPassword, db: $this->redisDb, logName: $this->logNamespace . $logName, callback: $this->telegraphQueueMissionManager::getStandardFormatter());
            }

            if ($this->enableEchoLog)
            {
                $this->telegraphQueueMissionManager->addStdoutHandler($this->telegraphQueueMissionManager::getStandardFormatter());
            }

            return $this;
        }


    }
