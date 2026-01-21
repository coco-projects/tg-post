<?php

    namespace Coco\tgPost;

    use Coco\tgMedia\Manager;
    use DI\Container;
    use Symfony\Component\Cache\Adapter\RedisAdapter;
    use Symfony\Component\Cache\Marshaller\DefaultMarshaller;
    use Symfony\Component\Cache\Marshaller\DeflateMarshaller;

    class PostManager
    {
        const CACHE_MANAGER = 'cacheManager';
        const REDIS_CLIENT  = 'redisClient';

        public bool $debug = false;

        public string $redisHost     = '127.0.0.1';
        public string $redisPassword = '';
        public int    $redisPort     = 6379;
        public int    $redisDb       = 9;

        public bool $enableRedisLog = false;
        public bool $enableEchoLog  = false;

        /**
         * @var Manager $tgMedia 操作源消息的对象
         */
        public Manager    $tgMedia;
        public ?Container $container = null;

        public string $teMessageTableName = 'te_message';
        public string $tePostTableName    = 'te_post';
        public string $teFileTableName    = 'te_file';
        public string $teTypeTableName    = 'te_type';

        public string $fileCdnUrl = '';

        /*
         * ---------------------------------------------------------
         * */
        public function __construct(public string $redisNamespace = 'tg-post-log')
        {
            $this->container = new Container();
        }

        public function initLaunchers()
        {

        }

        public function initMediaManager(callable $callback): static
        {
            $this->initRedis();
            $this->initCache();

            $this->tgMedia = call_user_func($callback, $this);

            $this->tgMedia->setEnableRedisLog($this->enableRedisLog);
            $this->tgMedia->setEnableEchoLog($this->enableEchoLog);
            $this->tgMedia->setDebug($this->debug);
            $this->tgMedia->setRedisConfig($this->redisHost, $this->redisPassword, $this->redisPort, $this->redisDb,);

            $this->tgMedia->initServer();

            /*
             * 初始化公用表
             * -------------------------------------------------------------------------------
             * */

            $this->tgMedia->initMessageTable($this->teMessageTableName, function(\Coco\tgMedia\tables\Message $table) {
                $registry = $table->getTableRegistry();

                $table->setPkField('id');
                $table->setIsPkAutoInc(false);
                $table->setPkValueCallable($registry::snowflakePKCallback());
            });

            $this->tgMedia->initPostTable($this->tePostTableName, function(\Coco\tgMedia\tables\Post $table) {
                $registry = $table->getTableRegistry();

                $table->setPkField('id');
                $table->setIsPkAutoInc(false);
                $table->setPkValueCallable($registry::snowflakePKCallback());
            });

            $this->tgMedia->initFileTable($this->teFileTableName, function(\Coco\tgMedia\tables\File $table) {
                $registry = $table->getTableRegistry();

                $table->setPkField('id');
                $table->setIsPkAutoInc(false);
                $table->setPkValueCallable($registry::snowflakePKCallback());
            });

            $this->tgMedia->initTypeTable($this->teTypeTableName, function(\Coco\tgMedia\tables\Type $table) {
                $registry = $table->getTableRegistry();

                $table->setPkField('id');
                $table->setIsPkAutoInc(true);
            });

            $this->tgMedia->initCommonProperty();

            return $this;
        }

        public function setFileCdnUrl(string $fileCdnUrl): static
        {
            $this->fileCdnUrl = $fileCdnUrl;

            return $this;
        }

        public function setDebug(bool $debug): static
        {
            $this->debug = $debug;

            return $this;
        }

        public function setEnableEchoLog(bool $enableEchoLog): static
        {
            $this->enableEchoLog = $enableEchoLog;

            return $this;
        }

        public function setEnableRedisLog(bool $enableRedisLog): static
        {
            $this->enableRedisLog = $enableRedisLog;

            return $this;
        }

        public function setRedisConfig(string $host = '127.0.0.1', string $password = '', int $port = 6379, int $db = 9): static
        {
            $this->redisHost     = $host;
            $this->redisPassword = $password;
            $this->redisPort     = $port;
            $this->redisDb       = $db;

            return $this;
        }

        /*
         * ---------------------------------------------------------
         * */
        public function setTeMessageTableName(string $teMessageTableName): static
        {
            $this->teMessageTableName = $teMessageTableName;

            return $this;
        }

        public function setTePostTableName(string $tePostTableName): static
        {
            $this->tePostTableName = $tePostTableName;

            return $this;
        }

        public function setTeFileTableName(string $teFileTableName): static
        {
            $this->teFileTableName = $teFileTableName;

            return $this;
        }

        public function setTeTypeTableName(string $teTypeTableName): static
        {
            $this->teTypeTableName = $teTypeTableName;

            return $this;
        }

        /*
         * ---------------------------------------------------------
         * */
        protected function initRedis(): static
        {
            $this->container->set(static::REDIS_CLIENT, function(Container $container) {
                $redis = new \Redis();
                $redis->connect($this->redisHost, $this->redisPort);
                $this->redisPassword && $redis->auth($this->redisPassword);
                $redis->select($this->redisDb);

                return $redis;
            });

            return $this;
        }

        public function getRedisClient(): \Redis
        {
            return $this->container->get(static::REDIS_CLIENT);
        }

        /*
         * ---------------------------------------------------------
         * */
        protected function initCache(): static
        {
            $this->container->set(static::CACHE_MANAGER, function(Container $container) {
                $marshaller   = new DeflateMarshaller(new DefaultMarshaller());
                $cacheManager = new RedisAdapter($container->get(static::REDIS_CLIENT), $this->redisNamespace . "-cache", 0, $marshaller);

                return $cacheManager;
            });

            return $this;
        }

        public function getCacheManager(): RedisAdapter
        {
            return $this->container->get(static::CACHE_MANAGER);
        }

        /*
         * ---------------------------------------------------------
         * */
        public function makeMediaUrl(string $mediaPath): string
        {
            $uri = rtrim($this->fileCdnUrl, '/') . '/' . ltrim($mediaPath, '/');

            return $uri;
        }
    }
