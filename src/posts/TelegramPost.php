<?php

    namespace Coco\tgPost\posts;

    use Coco\simplePageDownloader\Downloader;
    use Coco\tgMedia\Utils;
    use Coco\tgPost\PostManager;
    use Coco\wp\Tag;
    use GuzzleHttp\Exception\RequestException;
    use Psr\Http\Message\ResponseInterface;

    class TelegramPost extends PostAbstract
    {
        const MEDIA_GROUP_VIDEO     = 'video';
        const MEDIA_GROUP_AUDIO     = 'audio';
        const MEDIA_GROUP_PHOTO     = 'photo';
        const MEDIA_GROUP_DOCUMENT  = 'document';
        const MEDIA_GROUP_ANIMATION = 'animation';

        public array  $typeMap = [];
        public string $proxy   = '';

        public string $cachePath       = '../downloadCache';
        public string $postIdcachePath = '../postIdcache';
        public int    $retryTimes      = 8;
        public int    $messageDelay    = 2;
        public        $contentsAdv;

        public function __construct(PostManager $postManager)
        {
            parent::__construct($postManager);
        }

        public function setContentsAdv(callable $contentsAdv): static
        {
            $this->contentsAdv = $contentsAdv;

            return $this;
        }

        public function initHandler(?callable $callback = null): static
        {
            call_user_func($callback, $this);

            ini_set('memory_limit', '512M');

            $this->setStandardLogger(__CLASS__);
            if ($this->postManager->enableRedisLog)
            {
                $this->addRedisHandler(redisHost: $this->postManager->redisHost, redisPort: $this->postManager->redisPort, password: $this->postManager->redisPassword, db: $this->postManager->redisDb, logName: __CLASS__ . '-log', callback: static::getStandardFormatter());
            }

            if ($this->postManager->enableEchoLog)
            {
                $this->addStdoutHandler(static::getStandardFormatter());
            }

            Downloader::initClientConfig([
                'timeout' => 10.0,
                'verify'  => false,
                'debug'   => false,
                'proxy'   => $this->proxy,
            ]);

            Downloader::initLogger(__CLASS__ . '-downloadLog', $this->postManager->enableEchoLog, $this->postManager->enableRedisLog);

            Downloader::setRedis(redisHost: $this->postManager->redisHost, redisPort: $this->postManager->redisPort, password: $this->postManager->redisPassword, db: $this->postManager->redisDb);

            return $this;
        }

        public function setMessageDelay(int $messageDelay): static
        {
            $this->messageDelay = $messageDelay;

            return $this;
        }

        public function setPostIdcachePath(string $postIdcachePath): static
        {
            $this->postIdcachePath = $postIdcachePath;

            return $this;
        }

        public function setCachePath(string $cachePath): static
        {
            $this->cachePath = $cachePath;

            return $this;
        }

        public function setRetryTimes(int $retryTimes): static
        {
            $this->retryTimes = $retryTimes;

            return $this;
        }

        public function setTypeMap(array $typeMap): static
        {
            $this->typeMap = $typeMap;

            return $this;
        }

        public function setProxy(string $proxy): static
        {
            $this->proxy = $proxy;

            return $this;
        }

        public function updateTelegramPost(): void
        {
            $lastPost  = $this->getContinuePostId() ?? 0;
            $fileTable = $this->postManager->tgMedia->getFileTable();

            $posts = [
                1170736494743127869 => [
                    'post'       => [
                        'id'             => 1170736494743127869,
                        'type_id'        => 2,
                        'contents'       => '🔥 表情测试，✅ 成功了 曝光一个黑台子 ',
                        'media_group_id' => 14088457185706853,
                        'hashtags'       => '',
                        'date'           => 1761057148,
                        'time'           => 1762152531,
                    ],
                    'image'      => [
                        21187819330542610 => [
                            'id'               => 1170736494751516675,
                            'post_id'          => 1170736494743127869,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgQAAx0CcaAwEgACEQJo95l9zB2NcGGzuHggjR9V2XoncAACBL0xG1jv6FNBuiN7bwbbjQEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADBL0xG1jv6FN-',
                            'file_size'        => 74563,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/2/21187819330542610.jpg',
                            'media_group_id'   => 14088457185706853,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152531,
                            'origin_file_path' => '2025-11/03/image/2/21187819330542610.jpg',
                        ],
                        21187819301333343 => [
                            'id'               => 1170736494751516676,
                            'post_id'          => 1170736494743127869,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgQAAx0CcaAwEgACEQNo95l90exGaY59DYwWocV5VbNFzwACBb0xG1jv6FM7stNUC9MngQEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADBb0xG1jv6FN-',
                            'file_size'        => 105726,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/3/21187819301333343.jpg',
                            'media_group_id'   => 14088457185706853,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152531,
                            'origin_file_path' => '2025-11/03/image/3/21187819301333343.jpg',
                        ],
                        21187819327292689 => [
                            'id'               => 1170736494751516677,
                            'post_id'          => 1170736494743127869,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgQAAx0CcaAwEgACEQRo95l9d4_UjR7YmmrnBzPJPgcAAb8AAga9MRtY7-hTeR1QPtDP5h4BAAMCAAN5AAM2BA',
                            'file_unique_id'   => 'AQADBr0xG1jv6FN-',
                            'file_size'        => 48997,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/8/21187819327292689.jpg',
                            'media_group_id'   => 14088457185706853,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152531,
                            'origin_file_path' => '2025-11/03/image/8/21187819327292689.jpg',
                        ],
                        21187819296596869 => [
                            'id'               => 1170736494751516678,
                            'post_id'          => 1170736494743127869,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgQAAx0CcaAwEgACEQVo95l9c2_f2WAc8qNAAokedwYGyAACB70xG1jv6FNxgD0e0VDCogEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADB70xG1jv6FN-',
                            'file_size'        => 55140,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/7/21187819296596869.jpg',
                            'media_group_id'   => 14088457185706853,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152531,
                            'origin_file_path' => '2025-11/03/image/7/21187819296596869.jpg',
                        ],
                        21187819316501148 => [
                            'id'               => 1170736494751516679,
                            'post_id'          => 1170736494743127869,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgQAAx0CcaAwEgACEQZo95l9se8y1ueQFiiNyzQ6aXjKbQACCL0xG1jv6FNpaS26hdymCQEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADCL0xG1jv6FN-',
                            'file_size'        => 48490,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/5/21187819316501148.jpg',
                            'media_group_id'   => 14088457185706853,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152531,
                            'origin_file_path' => '2025-11/03/image/5/21187819316501148.jpg',
                        ],
                        21187819324167523 => [
                            'id'               => 1170736494751516680,
                            'post_id'          => 1170736494743127869,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgQAAx0CcaAwEgACEQdo95l9DxJfmw_uthjZWPi7bHfUQQACCb0xG1jv6FPssHVXdsB8eQEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADCb0xG1jv6FN-',
                            'file_size'        => 52549,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/4/21187819324167523.jpg',
                            'media_group_id'   => 14088457185706853,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152531,
                            'origin_file_path' => '2025-11/03/image/4/21187819324167523.jpg',
                        ],
                        21187819304734093 => [
                            'id'               => 1170736494751516681,
                            'post_id'          => 1170736494743127869,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgQAAx0CcaAwEgACEQho95l97Z37HoDWm54R039WKNUOCgACCr0xG1jv6FPovzBlGcMumAEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADCr0xG1jv6FN-',
                            'file_size'        => 55641,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/2/21187819304734093.jpg',
                            'media_group_id'   => 14088457185706853,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152531,
                            'origin_file_path' => '2025-11/03/image/2/21187819304734093.jpg',
                        ],
                        21187819320735699 => [
                            'id'               => 1170736494751516682,
                            'post_id'          => 1170736494743127869,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgQAAx0CcaAwEgACEQlo95l9D-P-VOABcNQDZVzjOqBROQACC70xG1jv6FMJ3cXr3n_q2AEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADC70xG1jv6FN-',
                            'file_size'        => 69262,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/A/21187819320735699.jpg',
                            'media_group_id'   => 14088457185706853,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152531,
                            'origin_file_path' => '2025-11/03/image/A/21187819320735699.jpg',
                        ],
                        21187819312693400 => [
                            'id'               => 1170736494751516683,
                            'post_id'          => 1170736494743127869,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgQAAx0CcaAwEgACEQpo95l9Y06TvQey08bB6RcXh5PvdQACDL0xG1jv6FNvrEcb9uGLkQEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADDL0xG1jv6FN-',
                            'file_size'        => 65369,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/6/21187819312693400.jpg',
                            'media_group_id'   => 14088457185706853,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152531,
                            'origin_file_path' => '2025-11/03/image/6/21187819312693400.jpg',
                        ],
                        21187819308409714 => [
                            'id'               => 1170736494751516684,
                            'post_id'          => 1170736494743127869,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgQAAx0CcaAwEgACEQto95l9fBUww76smQUrqIcixalmtQACDb0xG1jv6FOkLasGQ7kMKAEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADDb0xG1jv6FN-',
                            'file_size'        => 53490,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/5/21187819308409714.jpg',
                            'media_group_id'   => 14088457185706853,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152531,
                            'origin_file_path' => '2025-11/03/image/5/21187819308409714.jpg',
                        ],
                    ],
                    'tele_files' => [
                        0 => [
                            'file_id'          => 'AgACAgQAAx0CcaAwEgACEQJo95l9zB2NcGGzuHggjR9V2XoncAACBL0xG1jv6FNBuiN7bwbbjQEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADBL0xG1jv6FN-',
                            'file_size'        => 74563,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        1 => [
                            'file_id'          => 'AgACAgQAAx0CcaAwEgACEQNo95l90exGaY59DYwWocV5VbNFzwACBb0xG1jv6FM7stNUC9MngQEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADBb0xG1jv6FN-',
                            'file_size'        => 105726,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        2 => [
                            'file_id'          => 'AgACAgQAAx0CcaAwEgACEQRo95l9d4_UjR7YmmrnBzPJPgcAAb8AAga9MRtY7-hTeR1QPtDP5h4BAAMCAAN5AAM2BA',
                            'file_unique_id'   => 'AQADBr0xG1jv6FN-',
                            'file_size'        => 48997,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        3 => [
                            'file_id'          => 'AgACAgQAAx0CcaAwEgACEQVo95l9c2_f2WAc8qNAAokedwYGyAACB70xG1jv6FNxgD0e0VDCogEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADB70xG1jv6FN-',
                            'file_size'        => 55140,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        4 => [
                            'file_id'          => 'AgACAgQAAx0CcaAwEgACEQZo95l9se8y1ueQFiiNyzQ6aXjKbQACCL0xG1jv6FNpaS26hdymCQEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADCL0xG1jv6FN-',
                            'file_size'        => 48490,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        5 => [
                            'file_id'          => 'AgACAgQAAx0CcaAwEgACEQdo95l9DxJfmw_uthjZWPi7bHfUQQACCb0xG1jv6FPssHVXdsB8eQEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADCb0xG1jv6FN-',
                            'file_size'        => 52549,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        6 => [
                            'file_id'          => 'AgACAgQAAx0CcaAwEgACEQho95l97Z37HoDWm54R039WKNUOCgACCr0xG1jv6FPovzBlGcMumAEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADCr0xG1jv6FN-',
                            'file_size'        => 55641,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        7 => [
                            'file_id'          => 'AgACAgQAAx0CcaAwEgACEQlo95l9D-P-VOABcNQDZVzjOqBROQACC70xG1jv6FMJ3cXr3n_q2AEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADC70xG1jv6FN-',
                            'file_size'        => 69262,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        8 => [
                            'file_id'          => 'AgACAgQAAx0CcaAwEgACEQpo95l9Y06TvQey08bB6RcXh5PvdQACDL0xG1jv6FNvrEcb9uGLkQEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADDL0xG1jv6FN-',
                            'file_size'        => 65369,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        9 => [
                            'file_id'          => 'AgACAgQAAx0CcaAwEgACEQto95l9fBUww76smQUrqIcixalmtQACDb0xG1jv6FOkLasGQ7kMKAEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADDb0xG1jv6FN-',
                            'file_size'        => 53490,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                    ],
                ],
                1170736494923482027 => [
                    'post' => [
                        'id'             => 1170736494923482027,
                        'type_id'        => 2,
                        'contents'       => 'https://youtu.be/h-rh3hicWyU?si=8bbOqoq99GlgJBFY',
                        'media_group_id' => 1170735724996068195,
                        'hashtags'       => '',
                        'date'           => 1761057149,
                        'time'           => 1762152531,
                    ],
                ],
                1170736494940258834 => [
                    'post'       => [
                        'id'             => 1170736494940258834,
                        'type_id'        => 2,
                        'contents'       => 'ps1 test',
                        'media_group_id' => 1170735725025429744,
                        'hashtags'       => '',
                        'date'           => 1761057202,
                        'time'           => 1762152531,
                    ],
                    'document'   => [
                        0 => [
                            'id'               => 1170736494940260420,
                            'post_id'          => 1170736494940258834,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'BQACAgUAAx0CcaAwEgACERZo95myZSPfm8WGnVRbRoMC35QmNgAC5REAAgzIUVSiuO4kG-AB6jYE',
                            'file_unique_id'   => 'AgAD5REAAgzIUVQ',
                            'file_size'        => 34,
                            'file_name'        => 'startup.ps1',
                            'path'             => '2025-11/03/text/4/21187825338037633.txt',
                            'media_group_id'   => 1170735725025429744,
                            'ext'              => 'txt',
                            'mime_type'        => 'text/plain',
                            'media_type'       => 'document',
                            'origin_ext'       => 'txt',
                            'origin_mime_type' => 'text/plain',
                            'time'             => 1762152531,
                            'origin_file_path' => '2025-11/03/text/4/21187825338037633.txt',
                        ],
                    ],
                    'tele_files' => [
                        0 => [
                            'file_id'          => 'BQACAgUAAx0CcaAwEgACERZo95myZSPfm8WGnVRbRoMC35QmNgAC5REAAgzIUVSiuO4kG-AB6jYE',
                            'file_unique_id'   => 'AgAD5REAAgzIUVQ',
                            'file_size'        => 34,
                            'file_name'        => 'startup.ps1',
                            'origin_ext'       => 'txt',
                            'origin_mime_type' => 'text/plain',
                            'media_type'       => 'document',
                        ],
                    ],
                ],
                1170736494973815468 => [
                    'post' => [
                        'id'             => 1170736494973815468,
                        'type_id'        => 2,
                        'contents'       => '油管地址测试  https://www.youtube.com/watch?v=h-rh3hicWyU 油管地址测试
https://www.youtube.com/watch?v=h-rh3hicWyU
https://youtu.be/h-rh3hicWyU?si=8bbOqoq99GlgJBFY 测试测试  https://vimeo.com/340057344
测试测试  https://vimeo.com/340057344   https://twitter.com/elonmusk/status/1815929451256979636
https://twitter.com/elonmusk/status/1815929451256979636',
                        'media_group_id' => 1170735725205783100,
                        'hashtags'       => '',
                        'date'           => 1761057203,
                        'time'           => 1762152531,
                    ],
                ],
                1170736494990592685 => [
                    'post' => [
                        'id'             => 1170736494990592685,
                        'type_id'        => 2,
                        'contents'       => '?{<PG娱乐 十年信誉 百年品牌><https://pgyl.com/?ad=kw49504>}
?{<PG电子壹号娱乐新人注册送②⑧（s8yu.top）逢赌必赢><https://t.me/pg77333?ad=kw54999>}
?{<PG万倍爆奖入口【注册送彩金188-388】新人必红大奖好礼><https://t.me/wyfdc68?ad=kw60498>}
?{<PG电子丨麻将胡了丨赏金女王大水【首充送88-888必赢大奖】><https://t.me/NG6696?ad=kw45583>}
?{<【PG电子新人万倍爆率赏金】新人首充就送彩金><https://t.me/NG6696?ad=kw54249>}
?{<【PG 】此频道逢赌必赢｜此频道新人必红｜PG模拟器｜注册送88><https://t.me/hdhucudusjdududu?ad=kw57299>}
?{<PG投注❽首充送彩金❽问鼎❽巅峰国际❽壹号娱乐❽保时捷❽超凡国际><https://t.me/Pg88668866pg?ad=kw59152>}
?{<PG电子大水彩金活动中-十年老台大额也无忧><https://t.me/ngtycctr?ad=kw58043>}
?{<【PG 】麻将胡了｜此频道新人必红｜PG模拟器｜此频道注册送88><https://t.me/hdhucudusjdududu?ad=kw57346>}
?{<【P G】｜祝您逢赌必赢｜PG大水｜充100送100｜麻将胡了｜><https://t.me/hdhucudusjdududu?ad=kw55560>}
?{<PG电子频道100+100（f32rr.top）新人必红赢钱提款><https://t.me/+6Tp3hBeVw9Y3ZTFk>}
?{<PG电子频道【注册送88扶持彩金】88彩金新人必红赢钱><https://t.me/+EJN_SQIqEwY1YjI0>}
?{<开元棋牌｜招代理/❼⓿%返佣+人头费｜可玩可代｜充100送100><https://t.me/jinshaguoji4808?ad=kw59267>}
?{<小喵的ACG黄油 (RPG/ACT/...><https://t.me/xiaomaogame>} 44k
?{<樱刻春影丨黄油/GAL/RPG/ADV><https://t.me/iGALGAME>} 20k
?{<AI酒馆RPG角色卡~二次元角色~><https://t.me/AIRPG_ACG>} 4k
?{<安卓黄油の领域（转世重生...><https://t.me/thomasaua>} 8k
?{<黄游玩家公社><https://t.me/F95GAMECOM>} 304
热搜：万历烧鸽子 小猿泰山 台北车站 迪卡侬 坏空空 孙淼 野人小孩 齐靖煊 何卫东',
                        'media_group_id' => 1170735725243534715,
                        'hashtags'       => '',
                        'date'           => 1761057225,
                        'time'           => 1762152531,
                    ],
                ],
                1170736495007369526 => [
                    'post' => [
                        'id'             => 1170736495007369526,
                        'type_id'        => 2,
                        'contents'       => '广告:{<体育?电竞?百家乐?注册就送豪礼?全网最强福利?冲1K送1K?大额无忧><https://t.me/+-UMsuN1eJ1lkNDk1>}
?{<//_紫微天府在申，太陽必居巳。和太陽居亥一樣，巳位的太陽也是六合><https://t.me/the_chaser_news/6314>}
?{<大马气象局今午6时，向六州发出持续降雨警报，直至明日（1月25日）。><https://t.me/pocketimes/6741>}
?{<六必居的，淋点酱豆腐汁儿，酱豆腐必须王致和的，配上刚出炉的芝...><https://t.me/DBHR8/148648>}
?[00:46] {<六必居纯芝麻酱二八酱火锅蘸料正宗热干面酱花生黑芝麻烘...><https://t.me/KyDJChannel/22684>}
?{<淘口令:_09_HU7632_UGpX3Yw9B3R_https://m.tb.cn/h.gimGgPH_六必居><https://t.me/KyDJChannel/18832>}
?{<淘口令:_17_CZ8908_yg5o3Y6G2dZ_https://m.tb.cn/h.giMd8Wd_六必居><https://t.me/KyDJChannel/17638>}
?{<六必居纯芝麻酱二八酱火锅蘸料正宗热干面酱芝麻花生烘培拌面麻酱_淘><https://t.me/taobaotmall/8650>}
?{<六必居純芝麻醬火鍋蘸料正宗熱干面醬拌面醬花生黑芝麻烘培二八醬_...><https://t.me/taobaotmall/5367>}
?{<六必居纯芝麻酱二八酱，口感细腻，香气浓郁！_领5到手12.9亓，超...><https://t.me/baguasex/32245>}
?{<6【签到】六必居芝北京正宗麻花生酱300g*2_【签到红包】六必居纯...><https://t.me/qwhyangmao/546>}
热搜：万历烧鸽子 小猿泰山 台北车站 迪卡侬 坏空空 孙淼 野人小孩 齐靖煊 何卫东',
                        'media_group_id' => 1170735725264504976,
                        'hashtags'       => '',
                        'date'           => 1761057225,
                        'time'           => 1762152531,
                    ],
                ],
                1170736495024147295 => [
                    'post' => [
                        'id'             => 1170736495024147295,
                        'type_id'        => 2,
                        'contents'       => '广告:{<南宫 ??问鼎 pg电子/真人/彩票加拿大?新人送88 充值600送288包赢><https://t.me/+iG0d55JD2qoxODNl>}
?{<只有一米五，长相甜美，性格也很可爱，一直稳居我心里的“必约榜”第><https://t.me/SZNMLLL3/2242>}
?{<必理痛，一度被搶購一空。想不到「搶」潮陸續有來，近日雪梨華人...><https://t.me/thepointsglobal/75>}
?{<自热火锅_味必居自热小火锅全素食宽粉素食蔬菜火锅><https://t.me/wenwenbaihiodian/1760>}
?{<内幕消息_做跨境电商必知的34大产业带：_1、家电产业带：-安徽合...><https://t.me/inside1024/57956>}
?[00:44] {<重：50KG_三围：75-68-96_（A杯）_年龄：2003_常居地：广州><https://t.me/qhcdqgz/4377>}
?{<1、28_省份公布去年人口数据：广东、河南、山东出生人口数量居前三，><https://t.me/chigua_007/5928>}
?{<居外IQI:新元首效应下_柔佛产业必掀新章><https://t.me/ShareWorldConsultingTradingIdeas/14437>}
?[02:15] {<台商李孟居：紅魔必受審判_神韻一定能赴中國><https://t.me/mrxwjx/1138>}
?{<必ずしも話したり、書いたり、他の人と交流したりする必要がある...><https://t.me/spipj/8583>}
?{<台中必吃燒肉，餐點好吃份量又多，日本鳥居造景超好拍，還有免費和服><https://t.me/taiwanfood/3673>}
?点击按钮，筛选资源类型【第3页】',
                        'media_group_id' => 1170735725277089702,
                        'hashtags'       => '',
                        'date'           => 1761057225,
                        'time'           => 1762152531,
                    ],
                ],
                1170736495040923926 => [
                    'post' => [
                        'id'             => 1170736495040923926,
                        'type_id'        => 2,
                        'contents'       => '关键词：摄影师
? 赞助商: {<开云体育合营招商.5新增55%佣金.找鲸鱼必赚钱.扶持福利行业顶级待遇.免费加盟 欢迎骚扰><https://t.me/kaiyunxx999>}
? {<#推特  retsamms 摄影师><https://t.me/ntnrth65/188>} ?46秒
? {<#推特  luka 摄影师><https://t.me/ntnrth65/208>} ?15秒
? {<街头摄影师 #扫街><https://t.me/ppxgxcg/9009>} ?50秒
? {<街头摄影师 #扫街><https://t.me/aichiguaba/65572>} ?50秒
? {<被摄影师艹bb><https://t.me/gdian_av/1867>} ?5分钟
? {<摄影师 悲伤的故事><https://t.me/Mugglepc/13354>} ?4秒
? {<好球 摄影师加鸡腿><https://t.me/xxooktv/4425>} ?9秒
? {<做摄影师真好～#摄影 #写真><https://t.me/ccjjyy6866/28389>} ?1分钟
? {<《摄影师牛斗室内人像灯光快速入门系列课程》><https://t.me/xuendj1/135304>}
? {<《自由职业摄影师实用学习教程》><https://t.me/xuendj1/77861>}
? {<『极品小乳猪』酒店和摄影师啪啪><https://t.me/guochan7/133>} ?68分钟
? {<博主： #嫂嫂的摄影师><https://t.me/SM_Mov/74755>} ?30秒
第7/834页
?点击按钮可以切换类型?',
                        'media_group_id' => 1170735725293866069,
                        'hashtags'       => '',
                        'date'           => 1761057246,
                        'time'           => 1762152531,
                    ],
                ],
                1170736495057700329 => [
                    'post' => [
                        'id'             => 1170736495057700329,
                        'type_id'        => 2,
                        'contents'       => '关键词：私密按摩
? 赞助商: {<?注册送777??六合彩一码中特???六合彩一码中特 连中18期 点我查看 日入十万 随便赢?百家乐棋牌电子真人齐全??><https://t.me/tycgf>}
? {<【美食素材】聚餐-海外美食 sucai><https://t.me/meishisucail1>} 11.4 K
? {<糖心露脸女神【小桃酱】内射中出 女性私密按摩终极体验，师精液滋养嫩穴><https://t.me/oe521/94049>} ?25分钟
? {<人设-三方-素材-套图包装-亚洲女@qw_susal><https://t.me/taotu_004>} 11.0 K
? {<私密按摩白虎妺子淫水失控屁眼都7a7721.mp4><https://t.me/jn8888899/596>} ?41分钟
? {<马尼拉修车按摩提供上门服务><https://t.me/pasaix>}{<?><https://t.me/ccav/22>} 5.7 K
? {<TXVLOG_按摩体验_女性私密按摩，精液滋养嫩穴_小桃酱.mp4><https://t.me/AVWUMAYUANPIAN/3776>} ?25分钟
? {<广州•夜之都><https://t.me/guangzhouyzd>} 60.1 K
? {<深圳龙筋Spa><https://t.me/shenzhen77s>} 59.0 K
? {<人设套图 -（欧美男）><https://t.me/taotu_V394>} 10.1 K
? {<女子SPA私密按摩（穿着花内裤的熟女第一次体验全套）体验反馈非常好><https://t.me/sssnnn/23306>} ?55分钟
? {<郑州私密莞式服务><https://t.me/huangyoukiss>} 26.2 K
? {<偷拍探花按摩技师SPA><https://t.me/tsywcos>} 131.0 K
第3/834页
?点击按钮可以切换类型?',
                        'media_group_id' => 1170735725310641821,
                        'hashtags'       => '',
                        'date'           => 1761057247,
                        'time'           => 1762152531,
                    ],
                ],
                1170736495070282906 => [
                    'post' => [
                        'id'             => 1170736495070282906,
                        'type_id'        => 2,
                        'contents'       => '关键词：12baegirl
? 赞助商: {<?六合彩32分封盘！注册送999?百家乐电子齐全 ✔️ 特码④⑨倍⭐️日出亿U?百家乐电子齐全 ?注册送999><https://t.me/xjYLgf>}
? {<#12baegirl @BDSMmg><https://t.me/BDSMmg/1418>} ?25分钟
? {<#12baegirl @BDSMmg><https://t.me/BDSMmg/1419>} ?8分钟
? {<#12baegirl @BDSMmg><https://t.me/BDSMmg/1420>} ?12分钟
? {<#12baegirl><https://t.me/sqvip/18736>} ?17分钟
? {<推特|12baegirl ，订制合集20V新更 @shangche12..><https://t.me/shangche888/224015>} ?47秒
? {<巨乳母狗 #12baegirl推特大奶博主，第19部剧情片 《咸湿的酒..><https://t.me/ntnrth65/25611>} ?2分钟
? {<推特 12baegirl 巨乳肥臀各种啪啪 20部# #12baegi..><https://t.me/Flzspg/297081>} ?20秒
? {<推特|12baegirl ，巨乳网红/路人挑战面试官做爱1V 应求更新..><https://t.me/shangche888/217435>} ?1分钟
? {<#12baegirl @BDSMmg性冷淡闺蜜的yoni spa初体验..><https://t.me/BDSMmg/1421>} ?26分钟
? {<-性冷淡闺蜜的yoni spa初体验 她叫起来可一点都不性冷淡关键词：..><https://t.me/sesexb/15758>} ?26分钟
? {<金貝担保300W上押•?KK国际 ?娱乐全品类 一网打尽 ，新人首充即享100%赠送！><https://t.me/KKGJ1bot?start=444421904>}
?共10条结果，当前第1页，共1页',
                        'media_group_id' => 1170735725331611748,
                        'hashtags'       => '',
                        'date'           => 1761057247,
                        'time'           => 1762152531,
                    ],
                ],
                1170736495091256243 => [
                    'post' => [
                        'id'             => 1170736495091256243,
                        'type_id'        => 2,
                        'contents'       => '关键词：快手主播
? 赞助商: {<?2028体育世界杯官方赞助??千万福利大放送?PG电子轻松爆奖1w倍?开户网址 2028123.com ??><https://t.me/PG0228>}
? {<抖音快手主播走光露点预览><https://t.me/caommbb>} 16.2 K
? {<#全网曝光 #快手主播><https://t.me/baoguang_tougao/3686>}
? {<快手主播 福利><https://t.me/zl866_r2>} 0.52 K
? {<#快手 #主播 #黑玫瑰 #熟女><https://t.me/GTY0010/9413>} ?16秒
? {<快手主播><https://t.me/chiguaqunzhong77/18280>} ?54秒
? {<快手主播 走光露点><https://t.me/mczl18/6089>} ?10秒
? {<#曝光 #快手主播 姓名：袁小敏><https://t.me/baoguang_tougao/4131>}
? {<??‍♀️点我关注soso认证女团个人频道><https://t.me/addlist/V2GBIiE4QJc1OGU1>}
? {<快手主播裸舞 后续（2)><https://t.me/gdian_av/428>} ?1分钟
? {<#快手主播 #软软 #网红资源><https://t.me/jinpinzy/2462>} ?4分钟
? {<快手主播，表演炸逼才艺><https://t.me/chiguasese/18010>} ?30秒
? {<快手主播 裸舞（1)><https://t.me/gdian_av/427>} ?1分钟
? {<#快手主播 大娇姐><https://t.me/zybwg/8017>} ?2分钟
? {<金貝担保 · KK国际上押资金 300W? 电子｜棋牌｜捕鱼｜视讯｜体育? 首充加赠 100%实力见证，安全保障，畅玩无忧！><https://t.me/KKGJ1bot?start=444421904>}
?共10001条结果，当前第1页，共834页
?点击按钮可以切换类型哦~?',
                        'media_group_id' => 1170735725344197445,
                        'hashtags'       => '',
                        'date'           => 1761057247,
                        'time'           => 1762152531,
                    ],
                ],
                1170736495103838519 => [
                    'post' => [
                        'id'             => 1170736495103838519,
                        'type_id'        => 2,
                        'contents'       => '广告:{<?澳门永利国际?充值100送28?日入百万不是梦 出款稳与实力强！充提秒到账><https://t.me/YLGJPD?ad=link3332>}
?[2:10:14] {<无码_黑丝诱惑_极品骨像颜值_高潮_这个不能用美形容反...><https://t.me/TGbiaomei1/1071>}
?[40:39] {<白皙肌膚女大生被調教+自慰！粉鮑插入酒瓶！_無碼好尻影片40><https://t.me/EdMosaicZY/2079>}
?[54:37] {<：10musume_041225_01_标签：_无码av_中文字幕_美乳_后入><https://t.me/orzav1/1993>}
?[26:23] {<（无码高清中字）BrattySis【HD】繼姐的刻薄小穴_演员：...?><https://t.me/tmzxomav/1162>}
?[2:01:09] {<（破解无码）MIMK-253_一个关于一个女孩出去玩并让我使用><https://t.me/tmzxcll/1647>}
?[1:28:08] {<国产_无码_熟女_口交_调教_富二代包养白净母狗萝莉后庭...><https://t.me/FCSBJLS/7515>}
?[48:58] {<国产_无码__口交_深喉_内射_新人_21岁珉珉人生中的第三根...><https://t.me/zhiye11/573>}
?[05:02] {<原神_3d无码动漫同人_甘雨_黑丝_和甘雨共度良宵_作者_MOMO><https://t.me/R18ACG3D/5013>}
?[05:17] {<【深喉小河】ep38.無碼野外暴露_禁慾頂樓爽尻噴射_加入_...><https://t.me/xlgtv/324>}
?[1:58:12] {<[无码破解]JUL-870_出差在外，我意外地和一直很敬佩的女上?><https://t.me/daacy/34912>}
热搜：万历烧鸽子 小猿泰山 台北车站 坏空空 孙淼 迪卡侬 野人小孩 齐靖煊 何卫东',
                        'media_group_id' => 1170735725365168877,
                        'hashtags'       => '',
                        'date'           => 1761057281,
                        'time'           => 1762152531,
                    ],
                ],
                1170736495129003183 => [
                    'post' => [
                        'id'             => 1170736495129003183,
                        'type_id'        => 2,
                        'contents'       => '广告:{<?新加坡娱乐城?官方直营 提款秒到?注册送888 ?老虎机?捕鱼?电子><https://t.me/xjp581?ad=link3327>}
?{<环华包养支持同城同省三天包养相比外围性价比高><https://t.me/baoyang558800?ad=kw59441>}
?[00:15] {<河北廊坊市，这是喝了多少8+1啊！这个车挺倒霉，醉汉不赔他><https://t.me/qiwenbaike/435442>}
?[00:14] {<7月25日，河北，廊坊市。御龙河公园，理想汽车4S店，一群理><https://t.me/juexinggonglue/61483>}
?[06:40] {<式:_证件:131081200202271066_登记地址:河北省廊坊市霸州市><https://t.me/xiaoyi91111111/4320>}
?[00:14] {<7月25日，河北，廊坊市。御龙河公园，理想汽车4S店，一群理><https://t.me/times001/769656>}
?{<情69，不接吻，aiai必须打伞哦；p5米，pp10米_地址：苏州市，吴中区><https://t.me/suzhou66998/290>}
?{<河北_廊坊_价格：800_服务内容：鸳鸯浴_蚂蚁上树_性爱69_冰火两重...><https://t.me/zhanjie678/536>}
?{<一点点中文。_半夜:1500（2小时内两次走）_全夜:3000_商丘市_梁园区><https://t.me/Shangqiuo/274>}
?{<.05_【妹子编号】：慢慢_【妹子花名】：慢慢_【所在位置】：廊坊市区><https://t.me/langfangbg/139>}
?{<:36C_服务内容：bt400/p600_全套莞式服务_地址：苏州市      _相城区><https://t.me/suzhou66998/276>}
?{<haoliying1016_户籍地址:_河北省廊坊市经济技术开发区友谊路18号4排><https://t.me/xiaoyi91111111/512>}
?{<g1016_身份证号:_131002198708244828_户籍地址：河北省廊坊市安次区><https://t.me/xiaoyi91111111/56>}
?[01:13] {<河北省廊坊市三河市燕郊镇学院大街与迎宾路交叉口发生爆炸，?><https://t.me/youtube_hothot/28888>}
?{<此前河北三河市禁用红蓝黑三色招牌一事引发关注。廊坊市政府今日通报><https://t.me/pelosi3/14498>}
?{<出轨_老师_廊坊市_绿帽_投稿爆料_河北廊坊市某高中教师出轨_四十...><https://t.me/HLCGO/989>}
?{<5月26日，河北廊坊市大城县臧屯派出所民警在对涉嫌非法买卖的烟花爆><https://t.me/onlychigua/9498>}
?{<7_【妹子花名】：慢慢_【联系方式】：公开榜_【所在位置】：廊坊市区><https://t.me/langfangbg/137>}
?{<本群是安康唯一修车群_本群有如下频道_安康__群里自提_安康_:  _...><https://t.me/AnKq1/5>}
热搜：万历烧鸽子 小猿泰山 台北车站 坏空空 孙淼 迪卡侬 野人小孩 齐靖煊 何卫东',
                        'media_group_id' => 1170735725381945147,
                        'hashtags'       => '',
                        'date'           => 1761057281,
                        'time'           => 1762152531,
                    ],
                ],
                1170736495149974829 => [
                    'post' => [
                        'id'             => 1170736495149974829,
                        'type_id'        => 2,
                        'contents'       => '广告:{<?开云☄️爱游戏☄️华体会☄️乐鱼☄️九游☄️星空☄️OD☄️米兰☄️MK-?><https://t.me/ky7888?ad=link3076>}
?{<港澳足爺真实素人线下约足/TK/足模介绍（足爺原创足交作品）><https://t.me/pikaaimeizu123?ad=kw60410>}
?{<真实大学生线下舔足/TK/足交推荐（出原创睡足作品）><https://t.me/+Vz3s1kFrojFjM2M1>}
?{<大学生足交 足控 恋足 美足预览（大学生足交射脚）><https://t.me/xuejiezujiaojiao?ad=kw49855>}
?{<足控资源分享><https://t.me/jiojio123456>} 44k
?[02:54] {<黑丝_足交_足疗_同时...><https://t.me/xxlolpi/8934>}
?[02:00] {<足交_肉丝_会所_韩国...><https://t.me/xxlolpi/8930>}
?[01:55] {<足交_丝袜_会所丝袜技...><https://t.me/xxlolpi/8925>}
?[22:02] {<会所黑丝按摩技师足交毒><https://t.me/MRJXTH/1533>}
?[01:25] {<足交_会所丝袜技师足...><https://t.me/xxlolpi/8924>}
?[02:03] {<足交_技师_会所技师足交><https://t.me/zujiao011/23212>}
?[22:39] {<技师足交榨精合集_[18...><https://t.me/fanchaxox/1160>}
?{<肉丝_玉足_足交_高跟_极品肉丝...><https://t.me/xkmdb3/1098>}
?[19:10] {<足浴店技师足交，脚底特><https://t.me/zujiao011/20711>}
?[07:46] {<足交，脚底开洞丝袜，...><https://t.me/zukongL/9039>}
?[15:15] {<技师足交_各种足疗店技师><https://t.me/lovefoot03/3929>}
?[04:23] {<足交_少妇_会所技师足交><https://t.me/zujiao33/438>}
?[18:23] {<自购199RMB香港技师足交_><https://t.me/Huimeizu/11023>}
?[11:45] {<技师298足交服务，肉丝足><https://t.me/zukongL/9769>}
热搜：万历烧鸽子 小猿泰山 台北车站 坏空空 孙淼 迪卡侬 野人小孩 齐靖煊 何卫东',
                        'media_group_id' => 1170735725415500242,
                        'hashtags'       => '',
                        'date'           => 1761057293,
                        'time'           => 1762152531,
                    ],
                ],
                1170736495166754260 => [
                    'post' => [
                        'id'             => 1170736495166754260,
                        'type_id'        => 2,
                        'contents'       => '#频道互推 #群组推荐
-- -- -- -- -- -- -- -- -- -- --
〇 {<LSP游戏目录 Galgame|ADV|拔作|黄油><https://t.me/+sMWUp9xvKtNlN2U0>}
〇 {<零度资源-破解软件/游戏分享><https://t.me/+lEE3WXzVQ1syYjc1>}
〇 {<热门短剧每日分享><https://t.me/+Oy0c-BKjcCIxYjhl>}
〇 {<??电报频道精选推荐><https://t.me/+r9PtTw9QxaI1YjI1>}
〇 {<电报群组&频道><https://t.me/+ok-nIL77MJI0ZjNl>}
〇 {<无损音乐分享频道><https://t.me/+xCRN6lkORC45M2M5>}
〇 {<不是白嫖，这叫借鉴（备用）><https://t.me/+Y_D9uNz4Wdk1MjRk>}
〇 {<小声逼逼· 软件|Emby|抽奖><https://t.me/+wgjDVJwaiLxmN2Rl>}
〇 {<油油の科技软件资源分享><https://t.me/+y1dZb91_iVwwZWFl>}
-- -- -- -- -- -- -- -- -- -- --
♢ {<iOS社区· 软件|资讯|Emby><https://t.me/+sSTEdYKerG05YmI1>}
参加互推：@miaomiao333bot',
                        'media_group_id' => 1170735725486804802,
                        'hashtags'       => '#频道互推,#群组推荐',
                        'date'           => 1761057350,
                        'time'           => 1762152531,
                    ],
                ],
                1170736495183528839 => [
                    'post' => [
                        'id'             => 1170736495183528839,
                        'type_id'        => 2,
                        'contents'       => '关键词：姐姐滑步滑
? 赞助商: {<?送388?百家乐漏洞包赢?电子✅真人?棋牌?协议球⚠️?送388?百家乐漏洞包赢?电子✅真人?棋牌?协议球⚠️><https://t.me/xpjqw>}
? {<纵享丝滑><https://t.me/pantyhoselover>} 12.0 K
? {<起点快手资源/每晚直播/业务供需广告><https://t.me/kuaishou>} 90.1 K
? {<拿个 DJ 在那滑滑滑滑滑滑你喵喵呢><https://t.me/lychee_wood/33407>}
? {<全网十大滑步太空步高手，你觉得谁跳的最丝滑？太空步。欢迎您加入 『』><https://t.me/shixun160/69271>} ?3分钟
? {<中名:黏黏滑滑的女孩们的秘汤福利 1><https://t.me/dmlfse/7222>}
? {<滑滑的好舒服><https://t.me/xiatianasmrs/3469>} ?1分钟
? {<#国产 #嫖娼 #滑套><https://t.me/caoliusheq1/11099>}
? {<嫩嫩滑滑_自慰_20201029><https://t.me/Sihutv/6365>} ?97分钟
? {<最喜欢的上位感：鸡鸡蹭几下，微喷后滑滑的进入><https://t.me/r21vip/8164>} ?2分钟
? {<【半岛烟业】香烟专卖 品质更稳定 口感更丝滑><https://t.me/BDyy_888>} 0.10 K
? {<东南亚大事件><https://t.me/zixun77>} 47.5 K
? {<#娜娜 #糖心湿滑体验，用湿湿滑滑的腿勾引你><https://t.me/taipeinanaav/1190>} ?31分钟
? {<金貝担保上押300W，?KK国际 · 全覆盖电子、棋牌、捕鱼、视讯、体育，首充100%回馈。><https://t.me/KKGJ1bot?start=444421904>}
?共10000条结果，当前第1页，共834页
?点击按钮可以切换类型哦~?',
                        'media_group_id' => 1170735725709102850,
                        'hashtags'       => '',
                        'date'           => 1761057392,
                        'time'           => 1762152531,
                    ],
                ],
                1170736495204502980 => [
                    'post' => [
                        'id'             => 1170736495204502980,
                        'type_id'        => 2,
                        'contents'       => '尤物互推【青铜A频道组】{<尤物互推交流群><https://t.me/zishen_tg>}
1. {<一对一裸聊?【1v1】视频大飞机?️专业带射?><https://t.me/+NvmYfGzntPo4Mzdl>}
2. {<?美女视频?女生照片?><https://t.me/+DMIQl-egbDs4ODBk>}
3. {<探花偷拍外围空降小姐><https://t.me/+5Mzr1P48i2JkZjQ9>}
4. {<AV观影解说 日韩AV解说><https://t.me/+i5L87bZlaVNhZWRl>}
5. {<探花精选 空降外围偷拍><https://t.me/+0oU_TG9lJKwwOTY1>}
6. {<?性感图片?女人都爱看图片?高清图片><https://t.me/+qe0DtAFE0XIzYmI8>}
7. {<学生嫩逼?爆操少女㊙️反差偷拍><https://t.me/+tFY8N7bctBU3ZWU1>}
8. {<视频一对一裸聊?举牌 文爱 语爱 选妃><https://t.me/+2bjYAOQnzhhlYmE1>}
9. {<高清写真?高清图片?美女诱惑><https://t.me/+20dvGj9qQEYyMDE0>}
10. {<福利姬【一对一视频裸聊】激情四射?️><https://t.me/+EwIt_7N5teU3ZmFh>}
11. {<乱伦少女3p美女><https://t.me/+yXeuvOjuBNwxMGU0>}
12. {<偷拍街拍><https://t.me/+EesvOYuDe44yN2I6>}
13. {<芯欣裸聊【1v1视频】选妃 文爱 语爱 原味内衣><https://t.me/+cqumP1evcF5kOWVl>}
14. {<美女图片♈️自拍图片♈️宅男粮食><https://t.me/+81BbzCogQmM1ZDM0>}
15. {<国产电影 麻豆 糖心 精东><https://t.me/+BsJ_wuB3HdUwZDA1>}
16. {<乱伦吧 熟女 绿帽 人妻 综合整理><https://t.me/+pjchRrjQKlk0OTU1>}
17. {<反差母狗学生><https://t.me/+XaNPYoPhAEc3OTZh>}
18. {<女友反差少女万人骑><https://t.me/+aXR7v258yIYyODE1>}
19. {<?高中❤️初中大合集?><https://t.me/+0V8uVTgWs9hhNGRk>}
20. {<裸聊大秀 女主播 网红福利姬><https://t.me/+-nxEvYACgrYyMTM9>}
21. {<大奶子抠逼淫水逼自慰><https://t.me/+Q6Naedkiuz5lODU1>}
22. {<合欢教-性趣宗‖黄游堂><https://t.me/+P7rneX8-cDk4Mjlk>}
23. {<奇趣百科?猎奇?天天吃瓜><https://t.me/+4dga15j6D4VkZjE0>}
24. {<EVERYDAY 反差/綠帽/人妻/主奴/調教><https://t.me/+mRM0fmtXODg5YWE9>}
25. {<可爱小仙女㊙️甜美高中生㊗️小表妹><https://t.me/+cnW6D6bhmHg3ODA1>}
{<?萝莉呦女|学生少女|偷拍自拍|福利姬?全网精品><https://t.me/addlist/pUISyviTo_lkYzA0>}',
                        'media_group_id' => 1170735725910428477,
                        'hashtags'       => '',
                        'date'           => 1761057500,
                        'time'           => 1762152531,
                    ],
                ],
                1170736495225474413 => [
                    'post' => [
                        'id'             => 1170736495225474413,
                        'type_id'        => 2,
                        'contents'       => '?️叮当猫互推-A7车队?
AD: {<问鼎/壹号/爆奖推荐100送100><https://t.me/KK23100>}
●{<EVERYDAY每天瑟瑟 中轉站><https://t.me/+jpL6IhQRyrpmMWY1>}
●{<精品涩 视觉盛宴><https://t.me/+GBBNLBJEm2YyN2Y1>}
●{<美女艺术照/美女必须看/极品美女><https://t.me/+ygHIK8S5fGY2Y2Ix>}
●{<吃瓜群众每日必看图片?妖娆美女><https://t.me/+piawsC09wZYxMWI0>}
●{<Anime hentai | MANGA | MANHWA | eachi | harem | انیمه هنتای | انیمه حارم | انیمه ایچی><https://t.me/+2rbClU6HsqYyMzY8>}
●{<【太原】兼职约炮【全国都有】><https://t.me/+zyo2oBWwhrA3ZWE1>}
●{<门票? 啪啪??娱乐 ?裸聊?><https://t.me/+yMcb5A1mcQQ3MjRl>}
●{<文爱❤️情色?游戏?><https://t.me/+DUOZbWbi5fM3YmNl>}
●{<嫩逼-白虎-小白菜-小萝莉><https://t.me/+t3IeWjlXVAoyODk9>}
●{<品色堂-独家裸贷曝光群><https://t.me/+rcOL_RjUF75lMGI1>}
●{<抖音 精品涩><https://t.me/+64NH9MLrFzM1NmVl>}
●{<ONLYFANS><https://t.me/+G9oNBalWs9lhYjU8>}
●{<大马抖音性感网红美女分享><https://t.me/+WEvRJmxiajhiNjI1>}
●{<口爆与口交><https://t.me/+G_z6uJVfu_I4ZjFh>}
●{<?女人味十足?写真合集?撸撸专用图><https://t.me/+YEe-HBSywkhhOTJl>}
●{<【青岛】兼职约炮【全国都有】><https://t.me/+66CABXaVTJY2NDQ1>}
●{<? VAM国漫精品收藏 | ANIME 3D??><https://t.me/+B4WBXMjFL8FjOTU0>}
●{<ONLYFANS><https://t.me/+R4qKc7SiNfEzODM0>}
●{<大学生酒店偷拍监【免费群】㊙️><https://t.me/+ED9nrQAvlsRlMWE1>}
●{<黄色meme梗图迷因><https://t.me/+uulJi54smLw2Nzg1>}
AD: {<广告位置招商><https://t.me/qf1314521>}
互推交流: @ddmcomm',
                        'media_group_id' => 1170735725931397227,
                        'hashtags'       => '',
                        'date'           => 1761057500,
                        'time'           => 1762152531,
                    ],
                ],
                1170736495242249669 => [
                    'post' => [
                        'id'             => 1170736495242249669,
                        'type_id'        => 2,
                        'contents'       => '{<D一下，福星来喽><https://t.me/yiye_guanggao>}
{<? 全网最牛逼? 各种最新?大戟士><https://t.me/addlist/XDgWPLf_yb45ODRl>}
{<? 点击蓝色字? 一行一组?白马队><https://t.me/addlist/jv_iUo5G3AM0MDE1>}
{<? 手指点一点? 加入分组 ?白耳队><https://t.me/addlist/M83PNfkxM-lmNzY1>}
{<?其他车队><https://t.me/yiyedaohang>}  ?{<导航网站><http://www.yiye98.com/>} ?{<娱乐指南><https://t.me/+1YtJqyzpems5N2I1>} {<#娱乐指南><https://t.me/chuanmeizhiguang>}',
                        'media_group_id' => 1170735725948175320,
                        'hashtags'       => '',
                        'date'           => 1761057511,
                        'time'           => 1762152531,
                    ],
                ],
                1170736495254832674 => [
                    'post' => [
                        'id'             => 1170736495254832674,
                        'type_id'        => 2,
                        'contents'       => '广告:{<?PG官方直营?注册就送888?充提秒到?大额无忧 棋牌 捕鱼 应有尽有><https://t.me/YHGJPD?ad=link2979>}
?{<宁夏银川修车大队><https://t.me/ningxiaxiuche999>} 12k
?{<宁夏的小屋><https://t.me/linger6969>} 5k
?{<西南西北（四川 云南 重庆...><https://t.me/LM_members_xnxb>} 3k
?{<宁夏银川狼友修车品茶交流群><https://t.me/Ycxc123>} 8k
?{<清河十二坊：青海、宁夏...><https://t.me/qingheshierfangdd>} 391
?[00:10] {<宁夏一小区内老太太和小><https://t.me/pingrangTV/52015>}
?{<宁夏_银川_金凤_扫街_有没有银><https://t.me/chujsi/1221>}
?[06:34] {<花椒_______宁夏><https://t.me/FLZsp88/53859>}
?{<雷芳_地区：宁夏_吴忠市_红寺堡区><https://t.me/fcmgtgbg3/5626>}
?{<宁夏_银川_兴庆区_养生_蜀都><https://t.me/chujsi/1215>}
?{<宁夏_银川_足浴_养生_避雷_沧><https://t.me/chujsi/1216>}
?{<宁夏，甘肃，内蒙户籍_60万，...><https://t.me/Aaa999600/10060>}
?[00:37] {<宁夏银川兄弟，根据客户><https://t.me/hgrz888/894>}
?{<海南，宁夏移动老卡_来预约！！！><https://t.me/AtT6969/821>}
?{<宁夏省吴忠市的19岁巨乳美少女【><https://t.me/chiguabaoguangq/4503>}
?{<获刑2年6个月的宁夏固原市法轮功><https://t.me/wqw2010/10095>}
?[01:50] {<2025年8月30日，宁夏平罗><https://t.me/ufocnvideo/8234>}
?{<宁夏有什么好玩的吗><https://t.me/chujsi/1202>}
热搜：林黛玉 江栩麒 万千惠 百丽宫 江祖平 薛凯琪 在下小羊 抖音林黛玉 王腾 二喜 阅兵',
                        'media_group_id' => 1170735725964952544,
                        'hashtags'       => '',
                        'date'           => 1761057524,
                        'time'           => 1762152531,
                    ],
                ],
                1170736495271608396 => [
                    'post' => [
                        'id'             => 1170736495271608396,
                        'type_id'        => 2,
                        'contents'       => '{<AD:㊙性欲至上㊙性瘾分享㊙㊙><https://t.me/+Ava7ISJPx2o0YThi>}
{<AD:美少妇畅聊群?><https://t.me/+YNwhJFY1I4xmMWEx>}
?北冰洋互推B2?
1. {<家庭乱伦?偷情?母子><https://t.me/+Kee3qS73-TBmZjM5>}
2. {<性瘾女树洞秘密><https://t.me/+Ava7ISJPx2o0YThi>}
3. {<㊙性爱姿势大全约炮指导㊙><https://t.me/+Ava7ISJPx2o0YThi>}
4. {<爱妻艺换妻><https://t.me/+uKoIQCwWKhJlZTcx>}
5. {<【海角精品】母子家庭伦理自拍><https://t.me/+8fiqsu2P-kRkMjc5>}
6. {<换妻绿帽><https://t.me/+1-Kx9si94U5kNzA5>}
7. {<高中生萝莉少女><https://t.me/+Pm7fZSxAIFI2YWJh>}
8. {<VAM精品3d动漫国漫><https://t.me/+VKsIKtw-uzkwNWJh>}
9. {<调教 女女 反差><https://t.me/+GVshAhNPZTtiYzM5>}
10. {<哺乳期少妇㊙|人妻熟女|?自拍母乳|自慰露出㊙><https://t.me/+PMbBm3_JXAYxMGM8>}
11. {<?臺灣專車 2區?><https://t.me/+FIeR4Pum74kwZWQ1>}
12. {<推特网黄俱乐部><https://t.me/+soJQmfFn7HpkNTc1>}
13. {<㊙不良少女（大学生少妇肉肠?裸贷）㊙性瘾分享㊙><https://t.me/+paQWMre2Hto2MjI0>}
14. {<E户外露出少妇勾引><https://t.me/+QTij-jJj3okyZmQ1>}
15. {<木心会高颜修车频道><https://t.me/+RHBKVsqXvq9jZWY1>}
16. {<绿奴绿帽少妇多人?萝莉巨乳><https://t.me/jisou?start=a_5896344099>}
17. {<捆仙绳滴蜡?sm（字母圈）><https://t.me/+MtHJg5A0w5k0NTgx>}
18. {<男技师为/富婆/网红做SPA按摩推油㊙️><https://t.me/+3OKgQDcQbmUwODk8>}
19. {<男女炮友内部分享><https://t.me/+YNwhJFY1I4xmMWEx>}
20. {<sm捆绑调教母狗><https://t.me/+pGn9Ae1lSNs3ZGFl>}
21. {<Cos反差萝莉写真><https://t.me/+4TwkojH1yxA5Y2Nl>}
22. {<附近性瘾少妇圈子社-你懂得><https://t.me/+YNwhJFY1I4xmMWEx>}
23. {<反差淫妻母狗绿帽曝光><https://t.me/+8JY649Pa8URkYWFl>}
24. {<㊙?配对母狗在线?直播?><https://t.me/+paQWMre2Hto2MjI0>}
25. {<性瘾泡妹树洞秘密><https://t.me/+Ava7ISJPx2o0YThi>}
26. {<sm调教母狗合集><https://t.me/+RWgcRecXyAQ1NzM1>}
{<BAD:北冰洋互推交流群><https://t.me/bbyjlq>}
{<BAD:TG搜片免费工具><https://t.me/jisou?start=a_5896344099>}
{<BAD:全国sm约炮交友汇><https://t.me/+PMbBm3_JXAYxMGM8>}
{<BAD:私密情感?约会?><https://t.me/+paQWMre2Hto2MjI0>}',
                        'media_group_id' => 1170735725990118662,
                        'hashtags'       => '',
                        'date'           => 1761057539,
                        'time'           => 1762152531,
                    ],
                ],
                1170736495288386065 => [
                    'post' => [
                        'id'             => 1170736495288386065,
                        'type_id'        => 2,
                        'contents'       => '广告:{<?世界杯赞助商 注册送188?><https://t.me/HD1588?ad=link3304>}
?{<【全国包养COS少女】萝莉/高中生/白虎-支持面付-包月3千起><https://t.me/+MM5mvMN4SMljMWI8>}
?{<我现在在给cos妹妹做助理，教教我怎么哄她们来桂林><https://t.me/GL_XCDD/981331>}
?[00:14] {<222_小小天艺[2023.06.27]_桂林阳朔旅拍药指cos视频><https://t.me/bilibili_dance/21171>}
?[1:15:58] {<不动声色:_Host_AliyunTTS_思琪_Outline_00:05_片段一...><https://t.me/chinapodcast/137318>}
?[19:30] {<【小晗猫-003】西施cos-人家也好想被哥哥“疯狂输出”_小...><https://t.me/lisenkuku/639>}
?[10:01] {<自慰_巫女服_骑乘_肥臀_巨乳_潮吹_喷水_cosplay_小瑶?><https://t.me/aimm66/2053>}
?[02:28] {<高中生小母狗可爱二次元学生美少女小萝莉爱cosplay露出嫩穴><https://t.me/dmjy6/1347>}
?[10:05] {<大长腿__cosplay__少女__巨乳_拥有大长腿的福利姬少女_co...><https://t.me/khgdjagsdga/1593>}
?[02:02] {<COS__白虎_COS_王者少司缘><https://t.me/cos308/1174>}
?[02:35] {<cos_福利姬_网红_三丰集团353_长腿高颜值网红御姐，露脸...?><https://t.me/flj663/33199>}
?[12:23] {<集已更新至网盘_会员群更新日志_钛合金TiTi_Cosplay_美乳><https://t.me/AVSHARE_FHD/12104>}
?{<cos_泳池_Machi馬吉_处女的比基尼_39p-评论区自取><https://t.me/Coser_zg/3481>}
?{<茜_嫩穴_自慰_新作_[208门槛]_10分钟_Cos[旗袍+女仆]_福利自慰订阅><https://t.me/zhubojiayouzhan/1729>}
?[00:04] {<(xiaoguiluoli)一线天白虎嫩穴_小草神cos美图自录_真的好嫩><https://t.me/av8db/643>}
?{<铃木美咲_cos_汉服_自慰_白丝_白虎_MisakiSuzuki_MisakiSuzuki–...><https://t.me/jierahuang1/1403>}
?[07:43] {<男娘_手冲_cos_射精_最初只是爱好发女装，然后就陷入了一...?><https://t.me/lgbt365/7926>}
?{<推特极品福利姬_悠宝三岁_最新私拍_Cosplay初音未来2025年10月20...?><https://t.me/TG51CG/14426>}
?[03:06] {<崽崽@Babybaobaoya–新作_[288门槛]_[稀缺]_共12G_顶级Cos?><https://t.me/fsyh3/6084>}
热搜：万历烧鸽子 小猿泰山 台北车站 迪卡侬 坏空空 孙淼 野人小孩 齐靖煊 何卫东',
                        'media_group_id' => 1170735726015285731,
                        'hashtags'       => '',
                        'date'           => 1761057558,
                        'time'           => 1762152531,
                    ],
                ],
                1170736495305164676 => [
                    'post' => [
                        'id'             => 1170736495305164676,
                        'type_id'        => 2,
                        'contents'       => '{<?F03-互推助手 ?加入><https://t.me/hutuiZSbot?start=1>}
A:{<?初中|高中|大学|萝莉><https://t.me/+X8l2cy2_zWsxZWE0>}
1: {<刚刚上高中的小妹学口><https://t.me/+nplZJTBNNGFlNjU5>}
2: {<第一次尝试做疼><https://t.me/+3Ol_b8m3DFk4ZmI5>}
3: {<成人小说精选><https://t.me/+QgqwZUPF_ttlMTRl>}
4: {<来乐（易直播）?丝足?玉足?恋足资源分享><https://t.me/+yl-3wWa3n6YxYzE9>}
5: {<长春旅游攻略【必看/北京/天津><https://t.me/+nR9NgGKqTcEzMGNh>}
6: {<《萝莉爱街拍》偷窥|厕拍|露出|街拍|街...><https://t.me/+6d3HqEsW-YExMzkx>}
7: {<巨乳天堂交流群><https://t.me/+3sSZUoR9o2swZGU9>}
8: {<行走的意淫母狗|偷拍|街拍|裙底|><https://t.me/+JEiiqR74O642ODBh>}
9: {<【小婊妹精选】最爱无码Av><https://t.me/+2wjWFeAJJJ44MTE5>}
10: {<懵懂的小表妹><https://t.me/+P2uN_nZ6W1hmN2Ix>}
11: {<真实良家流出><https://t.me/+XOWbBzpHTO0yZDQx>}
12: {<极品女少妇空姐白领><https://t.me/+IfkILdY_Lis2ZWE5>}
13: {<闷骚女狗打奶子><https://t.me/+2qQm6Fy4SLA3NzFh>}
14: {<抖音 网红主播 私拍 中文群组频道><https://t.me/+PFOLrkwCNollNDM5>}
15: {<? 每日少萝【萝莉曝光】><https://t.me/+0IfZE3IYxjpmOTQ5>}
16: {<教妹妹做爱><https://t.me/+UUwnhI-GG_04Zjc5>}
17: {<? 激情碰撞夜 ?><https://t.me/+6iUKK5QZ7btmZmU1>}
18: {<引诱妹妹玩手机><https://t.me/+zXXxjhUFagdmYmYx>}
19: {<日本好片无码AV高颜值FC2><https://t.me/+t_TzZZgtBF9jNWUx>}
20: {<²免费ai去衣?预览频道><https://t.me/+Aqsjj2-MlAUyNDVl>}
21: {<?白嫩少女大尺度泄密><https://t.me/+_eIHxdvGvYFmNTFl>}
22: {<喝醉了上了妹妹><https://t.me/+lzWq_jHuiJFjNjEx>}
23: {<巨乳天堂?OnlyFans 推特><https://t.me/+oEAEz8sfGzxmNGQ1>}
24: {<极品学生萝莉喷水><https://t.me/+njXraK72-bwwOWYx>}
25: {<全网华人曝光吧AG04><https://t.me/+6acrNQbTephiMGVl>}
26: {<少女露出日记?><https://t.me/+ftIErlm57-plZjQ9>}
27: {<? 偷拍 极品 探花 ?><https://t.me/+UE21b6sytiFjYmE1>}
28: {<?绿帽淫妻（资源社区）经典资源><https://t.me/+X8onwhlX2PRmOTJl>}
29: {<菊花|后庭?肛门?屁眼><https://t.me/+zFLV0Vfw8yc0YTMx>}
30: {<女同拉拉SM调教反差自拍><https://t.me/+qHxbe3QphVEyZDA5>}
31: {<揪着小表妹的小乳头><https://t.me/+O3ZD5USNBe1mOTlh>}
32: {<大屌人妖女王伪娘高潮><https://t.me/+DG38RQJwDAA5NDhh>}
33: {<极品学生萝莉><https://t.me/+kiYaVkNtZRk3MGY5>}
A:{<?萝莉姬|白虎|嫩逼 ?中文资源><https://t.me/+e5F54MoDPIIwZWI1>}
A:{<? 少妇|换妻|堕落|群P|车震><https://t.me/+CI6Kq84g3dgxNzBi>}
互推交流: @htzs_bot',
                        'media_group_id' => 1170735726300496902,
                        'hashtags'       => '',
                        'date'           => 1761057587,
                        'time'           => 1762152531,
                    ],
                ],
                1170736495321940773 => [
                    'post' => [
                        'id'             => 1170736495321940773,
                        'type_id'        => 2,
                        'contents'       => '?【广告】{<?️超级引擎 Telegram华人交流群? 商务洽谈><https://t.me/+47hx7yCPVs9hNGZl>}
?【广告】{<?禁广告：2025狗推交流 海外华人务工吹水群><https://t.me/bc123>}
?【广告】{<⚽️品牌站点??金尊体育⚽️⚽️官方招商频道⚽️><https://t.me/JZTYGF>}
?【广告】{<?品牌站点，2026世界杯推荐投注站点 金尊体育??><https://t.me/JZTYGF>}
?【广告】{<?品牌站点，2026世界杯推荐投注站点 金尊体育??><https://t.me/JZTYGF>}
?【广告】{<???KM包网搭建 DB游戏 9台代理???><https://t.me/snowSKG>}
?【广告】{<问鼎?南宫系列、PG电子、棋牌、加拿大28、百家乐><https://t.me/ng28tty>}
?【广告】{<?高薪诚聘职能岗位?加入我们，成就自己?创造未来?><https://t.me/kaiyun00100>}
?以上是付费定时推送广告，不为其担保！',
                        'media_group_id' => 1170735726325664785,
                        'hashtags'       => '',
                        'date'           => 1761057604,
                        'time'           => 1762152531,
                    ],
                ],
                1170736495342913705 => [
                    'post' => [
                        'id'             => 1170736495342913705,
                        'type_id'        => 2,
                        'contents'       => '您有新订单！(30 CNY)
订单编号：2025032717313576311
原始金额：30 CNY
订单金额：4.1322 USDT
实付金额：4.1322 USDT
付款地址：TLaGjwhvA8XQYSxFAcAXy7Dvuue9eGYitv
收款地址：TLUurg2z2voif7vLNmFMJ3XehfhsegskZ9
创建时间：2025-03-27 17:31:36
支付时间：2025-03-27 17:41:18
交易哈希：3e4e660b99f261c920603b76642c080231c921e73ae89e616e5554bd1161f802  {<查看交易><https://tronscan.org/#/transaction/3e4e660b99f261c920603b76642c080231c921e73ae89e616e5554bd1161f802?lang=zh>}',
                        'media_group_id' => 1170735726636043607,
                        'hashtags'       => '',
                        'date'           => 1761057763,
                        'time'           => 1762152531,
                    ],
                ],
                1170736507971964387 => [
                    'post'       => [
                        'id'             => 1170736507971964387,
                        'type_id'        => 2,
                        'contents'       => '美国青少年日常的娱乐活动',
                        'media_group_id' => 1170735724983484452,
                        'hashtags'       => '',
                        'date'           => 1761057148,
                        'time'           => 1762152534,
                    ],
                    'video'      => [
                        21187828357773130 => [
                            'source' => [
                                'id'               => 1170736507971961749,
                                'post_id'          => 1170736507971964387,
                                'bot_id'           => 6026303590,
                                'file_id'          => 'BAACAgUAAx0CcaAwEgACERNo95l98XqXS5fmOzZWDu6-v7qr3AACFA0AAt8JQFT9G9PKdqAsszYE',
                                'file_unique_id'   => 'AgADFA0AAt8JQFQ',
                                'file_size'        => 7893598,
                                'file_name'        => 'IMG_5195.MP4',
                                'path'             => '2025-11/03/video/D/21187828357773130/hls.m3u8',
                                'media_group_id'   => 1170735724983484452,
                                'ext'              => 'm3u8',
                                'mime_type'        => 'application/x-mpegURL',
                                'media_type'       => 'video',
                                'origin_ext'       => 'mp4',
                                'origin_mime_type' => 'video/mp4',
                                'time'             => 1762152534,
                                'origin_file_path' => '2025-11/03/video/D/21187828357773130.mp4',
                            ],
                            'cover'  => [
                                0 => [
                                    'id'               => 1170741594731581218,
                                    'post_id'          => 1170736507971964387,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 136643,
                                    'file_name'        => 'cover-21187828357773130-1',
                                    'path'             => '2025-11/03/video/D/21187828357773130-1.jpg',
                                    'media_group_id'   => 1170735724983484452,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153747,
                                    'origin_file_path' => '2025-11/03/video/D/21187828357773130-1.jpg',
                                ],
                                1 => [
                                    'id'               => 1170741597185248459,
                                    'post_id'          => 1170736507971964387,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 136790,
                                    'file_name'        => 'cover-21187828357773130-2',
                                    'path'             => '2025-11/03/video/D/21187828357773130-2.jpg',
                                    'media_group_id'   => 1170735724983484452,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153748,
                                    'origin_file_path' => '2025-11/03/video/D/21187828357773130-2.jpg',
                                ],
                                2 => [
                                    'id'               => 1170741603946464153,
                                    'post_id'          => 1170736507971964387,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 154093,
                                    'file_name'        => 'cover-21187828357773130-3',
                                    'path'             => '2025-11/03/video/D/21187828357773130-3.jpg',
                                    'media_group_id'   => 1170735724983484452,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153749,
                                    'origin_file_path' => '2025-11/03/video/D/21187828357773130-3.jpg',
                                ],
                            ],
                        ],
                    ],
                    'tele_files' => [
                        0 => [
                            'file_id'          => 'BAACAgUAAx0CcaAwEgACERNo95l98XqXS5fmOzZWDu6-v7qr3AACFA0AAt8JQFT9G9PKdqAsszYE',
                            'file_unique_id'   => 'AgADFA0AAt8JQFQ',
                            'file_size'        => 7893598,
                            'file_name'        => 'IMG_5195.MP4',
                            'origin_ext'       => 'mp4',
                            'origin_mime_type' => 'video/mp4',
                            'media_type'       => 'video',
                        ],
                    ],
                ],
                1170736508747910890 => [
                    'post'       => [
                        'id'             => 1170736508747910890,
                        'type_id'        => 2,
                        'contents'       => 'zip test',
                        'media_group_id' => 1170735725012844582,
                        'hashtags'       => '',
                        'date'           => 1761057202,
                        'time'           => 1762152534,
                    ],
                    'document'   => [
                        0 => [
                            'id'               => 1170736508747909647,
                            'post_id'          => 1170736508747910890,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'BQACAgUAAx0CcaAwEgACERVo95myu8kHR9gh9D5KhRp_Vhog9QAC5BEAAgzIUVQjurxO6wXeaTYE',
                            'file_unique_id'   => 'AgAD5BEAAgzIUVQ',
                            'file_size'        => 10337590,
                            'file_name'        => 'The Witcher 3.zip',
                            'path'             => '2025-11/03/application/5/21187828361127200.zip',
                            'media_group_id'   => 1170735725012844582,
                            'ext'              => 'zip',
                            'mime_type'        => 'application/zip',
                            'media_type'       => 'document',
                            'origin_ext'       => 'zip',
                            'origin_mime_type' => 'application/zip',
                            'time'             => 1762152534,
                            'origin_file_path' => '2025-11/03/application/5/21187828361127200.zip',
                        ],
                    ],
                    'tele_files' => [
                        0 => [
                            'file_id'          => 'BQACAgUAAx0CcaAwEgACERVo95myu8kHR9gh9D5KhRp_Vhog9QAC5BEAAgzIUVQjurxO6wXeaTYE',
                            'file_unique_id'   => 'AgAD5BEAAgzIUVQ',
                            'file_size'        => 10337590,
                            'file_name'        => 'The Witcher 3.zip',
                            'origin_ext'       => 'zip',
                            'origin_mime_type' => 'application/zip',
                            'media_type'       => 'document',
                        ],
                    ],
                ],
                1170736521389542265 => [
                    'post'       => [
                        'id'             => 1170736521389542265,
                        'type_id'        => 2,
                        'contents'       => 'pdf test',
                        'media_group_id' => 1170735725042207667,
                        'hashtags'       => '',
                        'date'           => 1761057202,
                        'time'           => 1762152537,
                    ],
                    'document'   => [
                        0 => [
                            'id'               => 1170736521389541591,
                            'post_id'          => 1170736521389542265,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'BQACAgUAAx0CcaAwEgACERdo95my0r1OWdCdB0NY4pQLWpHYeQAC5hEAAgzIUVQrthxrh70xUTYE',
                            'file_unique_id'   => 'AgAD5hEAAgzIUVQ',
                            'file_size'        => 818312,
                            'file_name'        => '易媒助手产品介绍+.pdf',
                            'path'             => '2025-11/03/application/D/21187831375226920.pdf',
                            'media_group_id'   => 1170735725042207667,
                            'ext'              => 'pdf',
                            'mime_type'        => 'application/pdf',
                            'media_type'       => 'document',
                            'origin_ext'       => 'pdf',
                            'origin_mime_type' => 'application/pdf',
                            'time'             => 1762152537,
                            'origin_file_path' => '2025-11/03/application/D/21187831375226920.pdf',
                        ],
                    ],
                    'tele_files' => [
                        0 => [
                            'file_id'          => 'BQACAgUAAx0CcaAwEgACERdo95my0r1OWdCdB0NY4pQLWpHYeQAC5hEAAgzIUVQrthxrh70xUTYE',
                            'file_unique_id'   => 'AgAD5hEAAgzIUVQ',
                            'file_size'        => 818312,
                            'file_name'        => '易媒助手产品介绍+.pdf',
                            'origin_ext'       => 'pdf',
                            'origin_mime_type' => 'application/pdf',
                            'media_type'       => 'document',
                        ],
                    ],
                ],
                1170736521427291709 => [
                    'post'       => [
                        'id'             => 1170736521427291709,
                        'type_id'        => 2,
                        'contents'       => 'xls test',
                        'media_group_id' => 1170735725063176978,
                        'hashtags'       => '',
                        'date'           => 1761057202,
                        'time'           => 1762152537,
                    ],
                    'document'   => [
                        0 => [
                            'id'               => 1170736521427288883,
                            'post_id'          => 1170736521427291709,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'BQACAgUAAx0CcaAwEgACERlo95myMVaTTbXlYEcSlKIdETjDLgAC6BEAAgzIUVRhlWyxkWjWfTYE',
                            'file_unique_id'   => 'AgAD6BEAAgzIUVQ',
                            'file_size'        => 101376,
                            'file_name'        => '新媒体平台介绍2022.1.1.xls',
                            'path'             => '2025-11/03/application/C/21187831367615420.xls',
                            'media_group_id'   => 1170735725063176978,
                            'ext'              => 'xls',
                            'mime_type'        => 'application/vnd.ms-excel',
                            'media_type'       => 'document',
                            'origin_ext'       => 'xls',
                            'origin_mime_type' => 'application/vnd.ms-excel',
                            'time'             => 1762152537,
                            'origin_file_path' => '2025-11/03/application/C/21187831367615420.xls',
                        ],
                    ],
                    'tele_files' => [
                        0 => [
                            'file_id'          => 'BQACAgUAAx0CcaAwEgACERlo95myMVaTTbXlYEcSlKIdETjDLgAC6BEAAgzIUVRhlWyxkWjWfTYE',
                            'file_unique_id'   => 'AgAD6BEAAgzIUVQ',
                            'file_size'        => 101376,
                            'file_name'        => '新媒体平台介绍2022.1.1.xls',
                            'origin_ext'       => 'xls',
                            'origin_mime_type' => 'application/vnd.ms-excel',
                            'media_type'       => 'document',
                        ],
                    ],
                ],
                1170736521465038845 => [
                    'post'       => [
                        'id'             => 1170736521465038845,
                        'type_id'        => 2,
                        'contents'       => '',
                        'media_group_id' => 1170735725121898591,
                        'hashtags'       => '',
                        'date'           => 1761057202,
                        'time'           => 1762152537,
                    ],
                    'image'      => [
                        21187831371527103 => [
                            'id'               => 1170736521465039118,
                            'post_id'          => 1170736521465038845,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACER1o95my3igsRs384NJnnmPSz1f2YgACB8QxGwzIUVSB5hQXbVBSggEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADB8QxGwzIUVR-',
                            'file_size'        => 194773,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/D/21187831371527103.jpg',
                            'media_group_id'   => 1170735725121898591,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152537,
                            'origin_file_path' => '2025-11/03/image/D/21187831371527103.jpg',
                        ],
                    ],
                    'tele_files' => [
                        0 => [
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACER1o95my3igsRs384NJnnmPSz1f2YgACB8QxGwzIUVSB5hQXbVBSggEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADB8QxGwzIUVR-',
                            'file_size'        => 194773,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                    ],
                ],
                1170736534098280910 => [
                    'post'       => [
                        'id'             => 1170736534098280910,
                        'type_id'        => 2,
                        'contents'       => 'mp3 test',
                        'media_group_id' => 1170735725092536634,
                        'hashtags'       => '',
                        'date'           => 1761057202,
                        'time'           => 1762152540,
                    ],
                    'music'      => [
                        0 => [
                            'id'               => 1170736534098281951,
                            'post_id'          => 1170736534098280910,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'CQACAgUAAx0CcaAwEgACERto95myphnnAeiL8Se03so3tfZjZgAC6hEAAgzIUVSZPZCH7Mck_zYE',
                            'file_unique_id'   => 'AgAD6hEAAgzIUVQ',
                            'file_size'        => 129193,
                            'file_name'        => '236.mp3',
                            'path'             => '2025-11/03/audio/D/21187834384081166.mp3',
                            'media_group_id'   => 1170735725092536634,
                            'ext'              => 'mp3',
                            'mime_type'        => 'audio/mpeg',
                            'media_type'       => 'audio',
                            'origin_ext'       => 'mp3',
                            'origin_mime_type' => 'audio/mpeg',
                            'time'             => 1762152540,
                            'origin_file_path' => '2025-11/03/audio/D/21187834384081166.mp3',
                        ],
                    ],
                    'tele_files' => [
                        0 => [
                            'file_id'          => 'CQACAgUAAx0CcaAwEgACERto95myphnnAeiL8Se03so3tfZjZgAC6hEAAgzIUVSZPZCH7Mck_zYE',
                            'file_unique_id'   => 'AgAD6hEAAgzIUVQ',
                            'file_size'        => 129193,
                            'file_name'        => '236.mp3',
                            'origin_ext'       => 'mp3',
                            'origin_mime_type' => 'audio/mpeg',
                            'media_type'       => 'audio',
                        ],
                    ],
                ],
                1170736534131836773 => [
                    'post'       => [
                        'id'             => 1170736534131836773,
                        'type_id'        => 2,
                        'contents'       => 'png test',
                        'media_group_id' => 1170735725134483316,
                        'hashtags'       => '',
                        'date'           => 1761057202,
                        'time'           => 1762152540,
                    ],
                    'image'      => [
                        21187834380892195 => [
                            'id'               => 1170736534131837428,
                            'post_id'          => 1170736534131836773,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACER5o95myOFcaXYARIhsQPRYrvEDhkQACCMQxGwzIUVQi9Nsq1fkEXQEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADCMQxGwzIUVR-',
                            'file_size'        => 84023,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/B/21187834380892195.jpg',
                            'media_group_id'   => 1170735725134483316,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152540,
                            'origin_file_path' => '2025-11/03/image/B/21187834380892195.jpg',
                        ],
                    ],
                    'tele_files' => [
                        0 => [
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACER5o95myOFcaXYARIhsQPRYrvEDhkQACCMQxGwzIUVQi9Nsq1fkEXQEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADCMQxGwzIUVR-',
                            'file_size'        => 84023,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                    ],
                ],
                1170736546769275532 => [
                    'post'       => [
                        'id'             => 1170736546769275532,
                        'type_id'        => 2,
                        'contents'       => 'wmv test',
                        'media_group_id' => 1170735725105121813,
                        'hashtags'       => '',
                        'date'           => 1761057202,
                        'time'           => 1762152544,
                    ],
                    'video'      => [
                        21187837398486819 => [
                            'source' => [
                                'id'               => 1170736546769273098,
                                'post_id'          => 1170736546769275532,
                                'bot_id'           => 6026303590,
                                'file_id'          => 'BQACAgUAAx0CcaAwEgACERxo95myHhfAxoNdIa2QcbyAUqZT4wAC6xEAAgzIUVQ1yIZN1-PNcDYE',
                                'file_unique_id'   => 'AgAD6xEAAgzIUVQ',
                                'file_size'        => 23688689,
                                'file_name'        => '介绍.wmv',
                                'path'             => '2025-11/03/video/4/21187837398486819/hls.m3u8',
                                'media_group_id'   => 1170735725105121813,
                                'ext'              => 'm3u8',
                                'mime_type'        => 'application/x-mpegURL',
                                'media_type'       => 'document',
                                'origin_ext'       => 'wmv',
                                'origin_mime_type' => 'video/x-ms-wmv',
                                'time'             => 1762152543,
                                'origin_file_path' => '2025-11/03/video/4/21187837398486819.wmv',
                            ],
                            'cover'  => [
                                0 => [
                                    'id'               => 1170741604596580722,
                                    'post_id'          => 1170736546769275532,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 110492,
                                    'file_name'        => 'cover-21187837398486819-1',
                                    'path'             => '2025-11/03/video/4/21187837398486819-1.jpg',
                                    'media_group_id'   => 1170735725105121813,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153749,
                                    'origin_file_path' => '2025-11/03/video/4/21187837398486819-1.jpg',
                                ],
                                1 => [
                                    'id'               => 1170741613308149833,
                                    'post_id'          => 1170736546769275532,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 123470,
                                    'file_name'        => 'cover-21187837398486819-2',
                                    'path'             => '2025-11/03/video/4/21187837398486819-2.jpg',
                                    'media_group_id'   => 1170735725105121813,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153751,
                                    'origin_file_path' => '2025-11/03/video/4/21187837398486819-2.jpg',
                                ],
                                2 => [
                                    'id'               => 1170741629305225568,
                                    'post_id'          => 1170736546769275532,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 129583,
                                    'file_name'        => 'cover-21187837398486819-3',
                                    'path'             => '2025-11/03/video/4/21187837398486819-3.jpg',
                                    'media_group_id'   => 1170735725105121813,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153755,
                                    'origin_file_path' => '2025-11/03/video/4/21187837398486819-3.jpg',
                                ],
                                3 => [
                                    'id'               => 1170741654106148853,
                                    'post_id'          => 1170736546769275532,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 115726,
                                    'file_name'        => 'cover-21187837398486819-4',
                                    'path'             => '2025-11/03/video/4/21187837398486819-4.jpg',
                                    'media_group_id'   => 1170735725105121813,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153761,
                                    'origin_file_path' => '2025-11/03/video/4/21187837398486819-4.jpg',
                                ],
                                4 => [
                                    'id'               => 1170741685651505372,
                                    'post_id'          => 1170736546769275532,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 84256,
                                    'file_name'        => 'cover-21187837398486819-5',
                                    'path'             => '2025-11/03/video/4/21187837398486819-5.jpg',
                                    'media_group_id'   => 1170735725105121813,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153769,
                                    'origin_file_path' => '2025-11/03/video/4/21187837398486819-5.jpg',
                                ],
                                5 => [
                                    'id'               => 1170741734418681283,
                                    'post_id'          => 1170736546769275532,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 75678,
                                    'file_name'        => 'cover-21187837398486819-6',
                                    'path'             => '2025-11/03/video/4/21187837398486819-6.jpg',
                                    'media_group_id'   => 1170735725105121813,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153780,
                                    'origin_file_path' => '2025-11/03/video/4/21187837398486819-6.jpg',
                                ],
                                6 => [
                                    'id'               => 1170741794044906346,
                                    'post_id'          => 1170736546769275532,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 78758,
                                    'file_name'        => 'cover-21187837398486819-7',
                                    'path'             => '2025-11/03/video/4/21187837398486819-7.jpg',
                                    'media_group_id'   => 1170735725105121813,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153795,
                                    'origin_file_path' => '2025-11/03/video/4/21187837398486819-7.jpg',
                                ],
                                7 => [
                                    'id'               => 1170741861720000569,
                                    'post_id'          => 1170736546769275532,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 82527,
                                    'file_name'        => 'cover-21187837398486819-8',
                                    'path'             => '2025-11/03/video/4/21187837398486819-8.jpg',
                                    'media_group_id'   => 1170735725105121813,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153811,
                                    'origin_file_path' => '2025-11/03/video/4/21187837398486819-8.jpg',
                                ],
                                8 => [
                                    'id'               => 1170741964719523469,
                                    'post_id'          => 1170736546769275532,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 99145,
                                    'file_name'        => 'cover-21187837398486819-9',
                                    'path'             => '2025-11/03/video/4/21187837398486819-9.jpg',
                                    'media_group_id'   => 1170735725105121813,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153835,
                                    'origin_file_path' => '2025-11/03/video/4/21187837398486819-9.jpg',
                                ],
                            ],
                        ],
                    ],
                    'tele_files' => [
                        0 => [
                            'file_id'          => 'BQACAgUAAx0CcaAwEgACERxo95myHhfAxoNdIa2QcbyAUqZT4wAC6xEAAgzIUVQ1yIZN1-PNcDYE',
                            'file_unique_id'   => 'AgAD6xEAAgzIUVQ',
                            'file_size'        => 23688689,
                            'file_name'        => '介绍.wmv',
                            'origin_ext'       => 'wmv',
                            'origin_mime_type' => 'video/x-ms-wmv',
                            'media_type'       => 'document',
                        ],
                    ],
                ],
                1170736547146761641 => [
                    'post'       => [
                        'id'             => 1170736547146761641,
                        'type_id'        => 2,
                        'contents'       => 'png original test',
                        'media_group_id' => 1170735725155453691,
                        'hashtags'       => '',
                        'date'           => 1761057202,
                        'time'           => 1762152544,
                    ],
                    'image'      => [
                        21187837390808727 => [
                            'id'               => 1170736547150957005,
                            'post_id'          => 1170736547146761641,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'BQACAgUAAx0CcaAwEgACER9o95mya9qnqArOlRsug4s8kWOBngAC7hEAAgzIUVRBfnBBDfezzzYE',
                            'file_unique_id'   => 'AgAD7hEAAgzIUVQ',
                            'file_size'        => 561344,
                            'file_name'        => 'dog.png',
                            'path'             => '2025-11/03/image/D/21187837390808727.png',
                            'media_group_id'   => 1170735725155453691,
                            'ext'              => 'png',
                            'mime_type'        => 'image/png',
                            'media_type'       => 'document',
                            'origin_ext'       => 'png',
                            'origin_mime_type' => 'image/png',
                            'time'             => 1762152544,
                            'origin_file_path' => '2025-11/03/image/D/21187837390808727.png',
                        ],
                    ],
                    'tele_files' => [
                        0 => [
                            'file_id'          => 'BQACAgUAAx0CcaAwEgACER9o95mya9qnqArOlRsug4s8kWOBngAC7hEAAgzIUVRBfnBBDfezzzYE',
                            'file_unique_id'   => 'AgAD7hEAAgzIUVQ',
                            'file_size'        => 561344,
                            'file_name'        => 'dog.png',
                            'origin_ext'       => 'png',
                            'origin_mime_type' => 'image/png',
                            'media_type'       => 'document',
                        ],
                    ],
                ],
                1170736547184511721 => [
                    'post'       => [
                        'id'             => 1170736547184511721,
                        'type_id'        => 2,
                        'contents'       => 'jpg original test',
                        'media_group_id' => 1170735725172229370,
                        'hashtags'       => '',
                        'date'           => 1761057202,
                        'time'           => 1762152544,
                    ],
                    'image'      => [
                        21187837394472867 => [
                            'id'               => 1170736547184509562,
                            'post_id'          => 1170736547184511721,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'BQACAgUAAx0CcaAwEgACESBo95my_zqLvgs4RRGG11fQyKPvnAAC7xEAAgzIUVSxKKu7XbXaUTYE',
                            'file_unique_id'   => 'AgAD7xEAAgzIUVQ',
                            'file_size'        => 228077,
                            'file_name'        => '1.jpg',
                            'path'             => '2025-11/03/image/0/21187837394472867.jpg',
                            'media_group_id'   => 1170735725172229370,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'document',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152544,
                            'origin_file_path' => '2025-11/03/image/0/21187837394472867.jpg',
                        ],
                    ],
                    'tele_files' => [
                        0 => [
                            'file_id'          => 'BQACAgUAAx0CcaAwEgACESBo95my_zqLvgs4RRGG11fQyKPvnAAC7xEAAgzIUVSxKKu7XbXaUTYE',
                            'file_unique_id'   => 'AgAD7xEAAgzIUVQ',
                            'file_size'        => 228077,
                            'file_name'        => '1.jpg',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'document',
                        ],
                    ],
                ],
                1170736559842919581 => [
                    'post'       => [
                        'id'             => 1170736559842919581,
                        'type_id'        => 2,
                        'contents'       => 'exe test',
                        'media_group_id' => 1170735725079955533,
                        'hashtags'       => '',
                        'date'           => 1761057202,
                        'time'           => 1762152547,
                    ],
                    'document'   => [
                        0 => [
                            'id'               => 1170736559842922129,
                            'post_id'          => 1170736559842919581,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'BQACAgUAAx0CcaAwEgACERpo95myXXj2VN2k7U7DcJ9yDQ02_AAC6REAAgzIUVShBqwObI6B0TYE',
                            'file_unique_id'   => 'AgAD6REAAgzIUVQ',
                            'file_size'        => 18814432,
                            'file_name'        => 'WEMediaSetup_2.1.0.7383.exe',
                            'path'             => '2025-11/03/application/D/21187840412134315.bin',
                            'media_group_id'   => 1170735725079955533,
                            'ext'              => 'bin',
                            'mime_type'        => 'application/octet-stream',
                            'media_type'       => 'document',
                            'origin_ext'       => 'bin',
                            'origin_mime_type' => 'application/octet-stream',
                            'time'             => 1762152547,
                            'origin_file_path' => '2025-11/03/application/D/21187840412134315.bin',
                        ],
                    ],
                    'tele_files' => [
                        0 => [
                            'file_id'          => 'BQACAgUAAx0CcaAwEgACERpo95myXXj2VN2k7U7DcJ9yDQ02_AAC6REAAgzIUVShBqwObI6B0TYE',
                            'file_unique_id'   => 'AgAD6REAAgzIUVQ',
                            'file_size'        => 18814432,
                            'file_name'        => 'WEMediaSetup_2.1.0.7383.exe',
                            'origin_ext'       => 'bin',
                            'origin_mime_type' => 'application/octet-stream',
                            'media_type'       => 'document',
                        ],
                    ],
                ],
                1170736559872282282 => [
                    'post'       => [
                        'id'             => 1170736559872282282,
                        'type_id'        => 2,
                        'contents'       => 'text test',
                        'media_group_id' => 1170735725180619489,
                        'hashtags'       => '',
                        'date'           => 1761057203,
                        'time'           => 1762152547,
                    ],
                    'document'   => [
                        0 => [
                            'id'               => 1170736559872279699,
                            'post_id'          => 1170736559872282282,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'BQACAgUAAx0CcaAwEgACESFo95my-djm0VN8_Qhor3IEFd6WnwAC8REAAgzIUVRnPLHURt7ppjYE',
                            'file_unique_id'   => 'AgAD8REAAgzIUVQ',
                            'file_size'        => 44180,
                            'file_name'        => '深海.txt',
                            'path'             => '2025-11/03/text/D/21187840403213880.txt',
                            'media_group_id'   => 1170735725180619489,
                            'ext'              => 'txt',
                            'mime_type'        => 'text/plain',
                            'media_type'       => 'document',
                            'origin_ext'       => 'txt',
                            'origin_mime_type' => 'text/plain',
                            'time'             => 1762152547,
                            'origin_file_path' => '2025-11/03/text/D/21187840403213880.txt',
                        ],
                    ],
                    'tele_files' => [
                        0 => [
                            'file_id'          => 'BQACAgUAAx0CcaAwEgACESFo95my-djm0VN8_Qhor3IEFd6WnwAC8REAAgzIUVRnPLHURt7ppjYE',
                            'file_unique_id'   => 'AgAD8REAAgzIUVQ',
                            'file_size'        => 44180,
                            'file_name'        => '深海.txt',
                            'origin_ext'       => 'txt',
                            'origin_mime_type' => 'text/plain',
                            'media_type'       => 'document',
                        ],
                    ],
                ],
                1170736559905834311 => [
                    'post'       => [
                        'id'             => 1170736559905834311,
                        'type_id'        => 2,
                        'contents'       => 'php test',
                        'media_group_id' => 1170735725193200803,
                        'hashtags'       => '',
                        'date'           => 1761057203,
                        'time'           => 1762152547,
                    ],
                    'document'   => [
                        0 => [
                            'id'               => 1170736559905836300,
                            'post_id'          => 1170736559905834311,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'BQACAgUAAx0CcaAwEgACESJo95myXetnJUcnwTlx_2KuD3BgSQAC8hEAAgzIUVSpr_CJoxZ7-zYE',
                            'file_unique_id'   => 'AgAD8hEAAgzIUVQ',
                            'file_size'        => 44180,
                            'file_name'        => 'aa.php',
                            'path'             => '2025-11/03/application/A/21187840407568118.php',
                            'media_group_id'   => 1170735725193200803,
                            'ext'              => 'php',
                            'mime_type'        => 'application/x-php',
                            'media_type'       => 'document',
                            'origin_ext'       => 'php',
                            'origin_mime_type' => 'application/x-php',
                            'time'             => 1762152547,
                            'origin_file_path' => '2025-11/03/application/A/21187840407568118.php',
                        ],
                    ],
                    'tele_files' => [
                        0 => [
                            'file_id'          => 'BQACAgUAAx0CcaAwEgACESJo95myXetnJUcnwTlx_2KuD3BgSQAC8hEAAgzIUVSpr_CJoxZ7-zYE',
                            'file_unique_id'   => 'AgAD8hEAAgzIUVQ',
                            'file_size'        => 44180,
                            'file_name'        => 'aa.php',
                            'origin_ext'       => 'php',
                            'origin_mime_type' => 'application/x-php',
                            'media_type'       => 'document',
                        ],
                    ],
                ],
                1170736585151349912 => [
                    'post'       => [
                        'id'             => 1170736585151349912,
                        'type_id'        => 2,
                        'contents'       => '❇️将 telegram 群组的图文、视频消息做制作为属于你自己的媒体网站
❇️网站不限制内容，无视审查，一次购买永久有效
❇️基础版演示：https://telegra.ph/%E7%89%9B%E7%89%9B-01-21-6
❇️高级版演示：https://www.7yunchiyuncoy.com
❇️系统介绍：https://telegra.ph/%E7%89%9B%E7%89%9B-01-21-4
❇️❇️❇️牛牛建站系统❇️❇️❇️
联系客服：@nuinui_service',
                        'media_group_id' => 1170735725222563519,
                        'hashtags'       => '',
                        'date'           => 1761057203,
                        'time'           => 1762152553,
                    ],
                    'video'      => [
                        21187846423624891 => [
                            'source' => [
                                'id'               => 1170736585151352524,
                                'post_id'          => 1170736585151349912,
                                'bot_id'           => 6026303590,
                                'file_id'          => 'BAACAgEAAx0CcaAwEgACESRo95mylxtrB6i8vfvX_i7SIKchSQAC9gQAAr76mUTYEWYf2gk1zDYE',
                                'file_unique_id'   => 'AgAD9gQAAr76mUQ',
                                'file_size'        => 15302488,
                                'file_name'        => 'publish.mp4',
                                'path'             => '2025-11/03/video/5/21187846423624891/hls.m3u8',
                                'media_group_id'   => 1170735725222563519,
                                'ext'              => 'm3u8',
                                'mime_type'        => 'application/x-mpegURL',
                                'media_type'       => 'video',
                                'origin_ext'       => 'mp4',
                                'origin_mime_type' => 'video/mp4',
                                'time'             => 1762152553,
                                'origin_file_path' => '2025-11/03/video/5/21187846423624891.mp4',
                            ],
                            'cover'  => [
                                0 => [
                                    'id'               => 1170741966376274042,
                                    'post_id'          => 1170736585151349912,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 106173,
                                    'file_name'        => 'cover-21187846423624891-1',
                                    'path'             => '2025-11/03/video/5/21187846423624891-1.jpg',
                                    'media_group_id'   => 1170735725222563519,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153836,
                                    'origin_file_path' => '2025-11/03/video/5/21187846423624891-1.jpg',
                                ],
                                1 => [
                                    'id'               => 1170741975675046630,
                                    'post_id'          => 1170736585151349912,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 101705,
                                    'file_name'        => 'cover-21187846423624891-2',
                                    'path'             => '2025-11/03/video/5/21187846423624891-2.jpg',
                                    'media_group_id'   => 1170735725222563519,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153838,
                                    'origin_file_path' => '2025-11/03/video/5/21187846423624891-2.jpg',
                                ],
                                2 => [
                                    'id'               => 1170741993848967000,
                                    'post_id'          => 1170736585151349912,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 107311,
                                    'file_name'        => 'cover-21187846423624891-3',
                                    'path'             => '2025-11/03/video/5/21187846423624891-3.jpg',
                                    'media_group_id'   => 1170735725222563519,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153842,
                                    'origin_file_path' => '2025-11/03/video/5/21187846423624891-3.jpg',
                                ],
                                3 => [
                                    'id'               => 1170742019497133442,
                                    'post_id'          => 1170736585151349912,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 108127,
                                    'file_name'        => 'cover-21187846423624891-4',
                                    'path'             => '2025-11/03/video/5/21187846423624891-4.jpg',
                                    'media_group_id'   => 1170735725222563519,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153848,
                                    'origin_file_path' => '2025-11/03/video/5/21187846423624891-4.jpg',
                                ],
                                4 => [
                                    'id'               => 1170742054263721611,
                                    'post_id'          => 1170736585151349912,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 112537,
                                    'file_name'        => 'cover-21187846423624891-5',
                                    'path'             => '2025-11/03/video/5/21187846423624891-5.jpg',
                                    'media_group_id'   => 1170735725222563519,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153857,
                                    'origin_file_path' => '2025-11/03/video/5/21187846423624891-5.jpg',
                                ],
                                5 => [
                                    'id'               => 1170742098236804123,
                                    'post_id'          => 1170736585151349912,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 107223,
                                    'file_name'        => 'cover-21187846423624891-6',
                                    'path'             => '2025-11/03/video/5/21187846423624891-6.jpg',
                                    'media_group_id'   => 1170735725222563519,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153867,
                                    'origin_file_path' => '2025-11/03/video/5/21187846423624891-6.jpg',
                                ],
                                6 => [
                                    'id'               => 1170742151831619015,
                                    'post_id'          => 1170736585151349912,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 33047,
                                    'file_name'        => 'cover-21187846423624891-7',
                                    'path'             => '2025-11/03/video/5/21187846423624891-7.jpg',
                                    'media_group_id'   => 1170735725222563519,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153880,
                                    'origin_file_path' => '2025-11/03/video/5/21187846423624891-7.jpg',
                                ],
                                7 => [
                                    'id'               => 1170742213513053940,
                                    'post_id'          => 1170736585151349912,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 117054,
                                    'file_name'        => 'cover-21187846423624891-8',
                                    'path'             => '2025-11/03/video/5/21187846423624891-8.jpg',
                                    'media_group_id'   => 1170735725222563519,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153895,
                                    'origin_file_path' => '2025-11/03/video/5/21187846423624891-8.jpg',
                                ],
                                8 => [
                                    'id'               => 1170742293523595543,
                                    'post_id'          => 1170736585151349912,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 107809,
                                    'file_name'        => 'cover-21187846423624891-9',
                                    'path'             => '2025-11/03/video/5/21187846423624891-9.jpg',
                                    'media_group_id'   => 1170735725222563519,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153914,
                                    'origin_file_path' => '2025-11/03/video/5/21187846423624891-9.jpg',
                                ],
                            ],
                        ],
                    ],
                    'tele_files' => [
                        0 => [
                            'file_id'          => 'BAACAgEAAx0CcaAwEgACESRo95mylxtrB6i8vfvX_i7SIKchSQAC9gQAAr76mUTYEWYf2gk1zDYE',
                            'file_unique_id'   => 'AgAD9gQAAr76mUQ',
                            'file_size'        => 15302488,
                            'file_name'        => 'publish.mp4',
                            'origin_ext'       => 'mp4',
                            'origin_mime_type' => 'video/mp4',
                            'media_type'       => 'video',
                        ],
                    ],
                ],
                1170736585612722644 => [
                    'post'       => [
                        'id'             => 1170736585612722644,
                        'type_id'        => 2,
                        'contents'       => 'Romm
一款开源的自托管 ROM 管理器，帮助用户高效地管理、浏览和播放复古游戏。支持超过 400 种平台，包括经典游戏机和现代设备，适用于模拟器爱好者和游戏收藏者。
{<访问官网><https://romm.app/>}
#游戏 #复古游戏 #ROM管理器 #RomM #设备同步 #多平台支持 #开源项目',
                        'media_group_id' => 1170735725470026915,
                        'hashtags'       => '#游戏,#复古游戏,#ROM管理器,#RomM,#设备同步,#多平台支持,#开源项目',
                        'date'           => 1761057350,
                        'time'           => 1762152553,
                    ],
                    'image'      => [
                        21187846420361623 => [
                            'id'               => 1170736585612724411,
                            'post_id'          => 1170736585612722644,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACETFo95pFNfSRDJSCixZH03j6hMWyxwACbw1rGztoiFeZLqL3ffCWYgEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADbw1rGztoiFd-',
                            'file_size'        => 339550,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/C/21187846420361623.jpg',
                            'media_group_id'   => 1170735725470026915,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152553,
                            'origin_file_path' => '2025-11/03/image/C/21187846420361623.jpg',
                        ],
                    ],
                    'tele_files' => [
                        0 => [
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACETFo95pFNfSRDJSCixZH03j6hMWyxwACbw1rGztoiFeZLqL3ffCWYgEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADbw1rGztoiFd-',
                            'file_size'        => 339550,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                    ],
                ],
                1170736585650472217 => [
                    'post'       => [
                        'id'             => 1170736585650472217,
                        'type_id'        => 2,
                        'contents'       => 'FileImgSwap 文图变
一款可以把文件与 PNG 图像进行互转的工具，支持加密、图种（附加压缩包）功能，可将任意文件隐藏在 PNG 图像中，也能从生成的 PNG 图像中还原出原始文件。
文件转 PNG：将任意类型的文件（如文本、文档、压缩包等）转换为 PNG 图像，文件数据被编码到图像的像素中
PNG 还原文件：从通过本工具生成的 PNG 图像中提取并还原出原始文件
加密保护：通过同目录同主程序名的密码/密钥文件进行加密保护
图种功能：可附加压缩包到生成的 PNG 图像尾部，实现"图种"效果
元数据：图片中保留原始文件的名称、修改时间
{<项目地址><https://github.com/fzxx/FileImgSwap>}
#开源 #Windows',
                        'media_group_id' => 1170735725507776490,
                        'hashtags'       => '#开源,#Windows',
                        'date'           => 1761057350,
                        'time'           => 1762152553,
                    ],
                    'video'      => [
                        21187846426954768 => [
                            'source' => [
                                'id'               => 1170736585650472669,
                                'post_id'          => 1170736585650472217,
                                'bot_id'           => 6026303590,
                                'file_id'          => 'CgACAgUAAx0CcaAwEgACETNo95pFVXglCZ8FfI1hKO6MPjCMBQACshgAAu8vqFdfx75zLml8_jYE',
                                'file_unique_id'   => 'AgADshgAAu8vqFc',
                                'file_size'        => 69812,
                                'file_name'        => 'FileImgSwap.gif.mp4',
                                'path'             => '2025-11/03/video/6/21187846426954768/hls.m3u8',
                                'media_group_id'   => 1170735725507776490,
                                'ext'              => 'm3u8',
                                'mime_type'        => 'application/x-mpegURL',
                                'media_type'       => 'document',
                                'origin_ext'       => 'mp4',
                                'origin_mime_type' => 'video/mp4',
                                'time'             => 1762152553,
                                'origin_file_path' => '2025-11/03/video/6/21187846426954768.mp4',
                            ],
                            'cover'  => [
                                0 => [
                                    'id'               => 1170742294186297153,
                                    'post_id'          => 1170736585650472217,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 7521,
                                    'file_name'        => 'cover-21187846426954768-1',
                                    'path'             => '2025-11/03/video/6/21187846426954768-1.jpg',
                                    'media_group_id'   => 1170735725507776490,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153914,
                                    'origin_file_path' => '2025-11/03/video/6/21187846426954768-1.jpg',
                                ],
                                1 => [
                                    'id'               => 1170742294769305264,
                                    'post_id'          => 1170736585650472217,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 6956,
                                    'file_name'        => 'cover-21187846426954768-2',
                                    'path'             => '2025-11/03/video/6/21187846426954768-2.jpg',
                                    'media_group_id'   => 1170735725507776490,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153914,
                                    'origin_file_path' => '2025-11/03/video/6/21187846426954768-2.jpg',
                                ],
                                2 => [
                                    'id'               => 1170742295851436244,
                                    'post_id'          => 1170736585650472217,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 6860,
                                    'file_name'        => 'cover-21187846426954768-3',
                                    'path'             => '2025-11/03/video/6/21187846426954768-3.jpg',
                                    'media_group_id'   => 1170735725507776490,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153914,
                                    'origin_file_path' => '2025-11/03/video/6/21187846426954768-3.jpg',
                                ],
                            ],
                        ],
                    ],
                    'tele_files' => [
                        0 => [
                            'file_id'          => 'CgACAgUAAx0CcaAwEgACETNo95pFVXglCZ8FfI1hKO6MPjCMBQACshgAAu8vqFdfx75zLml8_jYE',
                            'file_unique_id'   => 'AgADshgAAu8vqFc',
                            'file_size'        => 69812,
                            'file_name'        => 'FileImgSwap.gif.mp4',
                            'origin_ext'       => 'mp4',
                            'origin_mime_type' => 'video/mp4',
                            'media_type'       => 'document',
                        ],
                    ],
                ],
                1170736598526985148 => [
                    'post'       => [
                        'id'             => 1170736598526985148,
                        'type_id'        => 2,
                        'contents'       => '#EPIC #限免 #游戏
银河历险记3
故事随一个好奇的太空小矮人展开，这个小矮人用一根魔笛的力量在宇宙间穿行，探索宇宙的神秘起源。探访九大独特的外星世界，探索这些世界中丰富多彩的挑战、创造和惊喜，真切感受美妙的原图、声音和音乐。
{<点击领取><https://store.epicgames.com/zh-CN/p/samorost-3-0c4489>}
¥86* 优惠截止于2025/10/23 23:00',
                        'media_group_id' => 1170735725449055125,
                        'hashtags'       => '#EPIC,#限免,#游戏',
                        'date'           => 1761057349,
                        'time'           => 1762152556,
                    ],
                    'video'      => [
                        21187849436386396 => [
                            'source' => [
                                'id'               => 1170736598526986334,
                                'post_id'          => 1170736598526985148,
                                'bot_id'           => 6026303590,
                                'file_id'          => 'BAACAgUAAx0CcaAwEgACETBo95pFI3uyh_4pYmVWDgAB80oMVrgAAkEcAAI7aIhX70VO9MIb4Do2BA',
                                'file_unique_id'   => 'AgADQRwAAjtoiFc',
                                'file_size'        => 36363520,
                                'file_name'        => '银河历险记3_Samorost_3_立刻购买并下载_Epic游戏商城.mp4',
                                'path'             => '2025-11/03/video/5/21187849436386396/hls.m3u8',
                                'media_group_id'   => 1170735725449055125,
                                'ext'              => 'm3u8',
                                'mime_type'        => 'application/x-mpegURL',
                                'media_type'       => 'video',
                                'origin_ext'       => 'mp4',
                                'origin_mime_type' => 'video/mp4',
                                'time'             => 1762152556,
                                'origin_file_path' => '2025-11/03/video/5/21187849436386396.mp4',
                            ],
                            'cover'  => [
                                0 => [
                                    'id'               => 1170742298435128995,
                                    'post_id'          => 1170736598526985148,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 152247,
                                    'file_name'        => 'cover-21187849436386396-1',
                                    'path'             => '2025-11/03/video/5/21187849436386396-1.jpg',
                                    'media_group_id'   => 1170735725449055125,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153915,
                                    'origin_file_path' => '2025-11/03/video/5/21187849436386396-1.jpg',
                                ],
                                1 => [
                                    'id'               => 1170742305716437124,
                                    'post_id'          => 1170736598526985148,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 141008,
                                    'file_name'        => 'cover-21187849436386396-2',
                                    'path'             => '2025-11/03/video/5/21187849436386396-2.jpg',
                                    'media_group_id'   => 1170735725449055125,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153917,
                                    'origin_file_path' => '2025-11/03/video/5/21187849436386396-2.jpg',
                                ],
                                2 => [
                                    'id'               => 1170742320295838097,
                                    'post_id'          => 1170736598526985148,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 190265,
                                    'file_name'        => 'cover-21187849436386396-3',
                                    'path'             => '2025-11/03/video/5/21187849436386396-3.jpg',
                                    'media_group_id'   => 1170735725449055125,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153920,
                                    'origin_file_path' => '2025-11/03/video/5/21187849436386396-3.jpg',
                                ],
                                3 => [
                                    'id'               => 1170742340613046345,
                                    'post_id'          => 1170736598526985148,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 81789,
                                    'file_name'        => 'cover-21187849436386396-4',
                                    'path'             => '2025-11/03/video/5/21187849436386396-4.jpg',
                                    'media_group_id'   => 1170735725449055125,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153925,
                                    'origin_file_path' => '2025-11/03/video/5/21187849436386396-4.jpg',
                                ],
                                4 => [
                                    'id'               => 1170742366076666500,
                                    'post_id'          => 1170736598526985148,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 168942,
                                    'file_name'        => 'cover-21187849436386396-5',
                                    'path'             => '2025-11/03/video/5/21187849436386396-5.jpg',
                                    'media_group_id'   => 1170735725449055125,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153931,
                                    'origin_file_path' => '2025-11/03/video/5/21187849436386396-5.jpg',
                                ],
                                5 => [
                                    'id'               => 1170742404106421126,
                                    'post_id'          => 1170736598526985148,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 100653,
                                    'file_name'        => 'cover-21187849436386396-6',
                                    'path'             => '2025-11/03/video/5/21187849436386396-6.jpg',
                                    'media_group_id'   => 1170735725449055125,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153940,
                                    'origin_file_path' => '2025-11/03/video/5/21187849436386396-6.jpg',
                                ],
                            ],
                        ],
                    ],
                    'tele_files' => [
                        0 => [
                            'file_id'          => 'BAACAgUAAx0CcaAwEgACETBo95pFI3uyh_4pYmVWDgAB80oMVrgAAkEcAAI7aIhX70VO9MIb4Do2BA',
                            'file_unique_id'   => 'AgADQRwAAjtoiFc',
                            'file_size'        => 36363520,
                            'file_name'        => '银河历险记3_Samorost_3_立刻购买并下载_Epic游戏商城.mp4',
                            'origin_ext'       => 'mp4',
                            'origin_mime_type' => 'video/mp4',
                            'media_type'       => 'video',
                        ],
                    ],
                ],
                1170736611504163948 => [
                    'post'       => [
                        'id'             => 1170736611504163948,
                        'type_id'        => 2,
                        'contents'       => 'NeedyClash
深度重构的现代化 Clash 代理客户端。基于 Clash Verge Rev 深度重构，前后端全面优化，提供更稳定高效的使用体验
核心特性
订阅管理增强
分组系统：支持创建多个订阅分组，可设置颜色标识
智能过滤：按分组快速筛选和组织配置文件
批量操作：高效管理大量订阅源
节点收藏
一键标记常用节点
批量管理收藏列表
跨配置文件快速切换
主题系统
全方位的视觉定制能力：
颜色方案：主色调、辅助色、文本色完全自定义
背景类型：图片、视频、纯色三种模式
细节控制：模糊度、透明度、亮度独立调节
组件级定制：导航栏、表头、卡片等元素单独设置
方案管理：内置预设 + 自定义方案保存与分享
流量分析
深度的流量监控与分析：
实时图表：上传/下载分离的趋势可视化
多维统计：按域名、进程分别统计流量消耗
历史记录：长期数据存储与回溯
数据导出：JSON 格式，支持二次分析
智能提醒
自动监控订阅流量配额使用情况
可配置的阈值提醒（默认 80%）
防骚扰机制：1 小时内不重复提醒
剩余流量实时显示
稳定性提升
全局错误边界：防止单点故障导致应用崩溃
模块化降级：关键功能独立保护，局部失败不影响整体
详细诊断：完整的错误堆栈和日志系统
{<项目地址><https://github.com/Lythrilla/NeedyClash>}
已加入{<VPN软件合集><https://t.me/txwl666/4070>}
#VPN软件@txwl666 #电脑软件 #开源',
                        'media_group_id' => 14088458798412005,
                        'hashtags'       => '#VPN软件@txwl666,#电脑软件,#开源',
                        'date'           => 1761057350,
                        'time'           => 1762152559,
                    ],
                    'image'      => [
                        21187846430515800 => [
                            'id'               => 1170736611504162581,
                            'post_id'          => 1170736611504163948,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACETRo95pFnTC8B-_3lBJhlTNczP1u-gACkAtrG-8vqFepiaSKsNil0QEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADkAtrG-8vqFd-',
                            'file_size'        => 56631,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/7/21187846430515800.jpg',
                            'media_group_id'   => 14088458798412005,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152559,
                            'origin_file_path' => '2025-11/03/image/7/21187846430515800.jpg',
                        ],
                        21187852448358886 => [
                            'id'               => 1170736611508358203,
                            'post_id'          => 1170736611504163948,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACETVo95pFWj6z97iHqw11b62TgbnmBgACkQtrG-8vqFdlBSMbJzZw1gEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADkQtrG-8vqFd-',
                            'file_size'        => 60978,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/8/21187852448358886.jpg',
                            'media_group_id'   => 14088458798412005,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152559,
                            'origin_file_path' => '2025-11/03/image/8/21187852448358886.jpg',
                        ],
                    ],
                    'tele_files' => [
                        0 => [
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACETRo95pFnTC8B-_3lBJhlTNczP1u-gACkAtrG-8vqFepiaSKsNil0QEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADkAtrG-8vqFd-',
                            'file_size'        => 56631,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        1 => [
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACETVo95pFWj6z97iHqw11b62TgbnmBgACkQtrG-8vqFdlBSMbJzZw1gEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADkQtrG-8vqFd-',
                            'file_size'        => 60978,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                    ],
                ],
                1170736624149989350 => [
                    'post'       => [
                        'id'             => 1170736624149989350,
                        'type_id'        => 2,
                        'contents'       => '#EPIC #限免 #游戏
Amnesia: The Bunker
一款第一人称恐怖游戏，背景设置在某处第一次世界大战时期的荒凉地堡。 你需要直面黑暗中潜藏的压抑与恐惧，将自己搜索到的各种工具与武器根据情况善加利用，想方设法不让光源熄灭并尽力逃出生天。
{<点击领取><https://store.epicgames.com/zh-CN/p/amnesia-the-bunker-9d5799>}
¥78* 优惠截止于2025/10/23 23:00',
                        'media_group_id' => 1170735725436469939,
                        'hashtags'       => '#EPIC,#限免,#游戏',
                        'date'           => 1761057349,
                        'time'           => 1762152562,
                    ],
                    'video'      => [
                        21187855495036051 => [
                            'source' => [
                                'id'               => 1170736624149989237,
                                'post_id'          => 1170736624149989350,
                                'bot_id'           => 6026303590,
                                'file_id'          => 'BAACAgUAAx0CcaAwEgACES9o95pFcGY8lOIJfLOvj24L-vCZMgACPxwAAjtoiFcnCv_U931RbzYE',
                                'file_unique_id'   => 'AgADPxwAAjtoiFc',
                                'file_size'        => 49690871,
                                'file_name'        => '《Amnesia_The_Bunker》_立刻购买并下载_Epic游戏商城.mp4',
                                'path'             => '2025-11/03/video/4/21187855495036051/hls.m3u8',
                                'media_group_id'   => 1170735725436469939,
                                'ext'              => 'm3u8',
                                'mime_type'        => 'application/x-mpegURL',
                                'media_type'       => 'video',
                                'origin_ext'       => 'mp4',
                                'origin_mime_type' => 'video/mp4',
                                'time'             => 1762152562,
                                'origin_file_path' => '2025-11/03/video/4/21187855495036051.mp4',
                            ],
                            'cover'  => [
                                0 => [
                                    'id'               => 1170742406027412112,
                                    'post_id'          => 1170736624149989350,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 36664,
                                    'file_name'        => 'cover-21187855495036051-1',
                                    'path'             => '2025-11/03/video/4/21187855495036051-1.jpg',
                                    'media_group_id'   => 1170735725436469939,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153940,
                                    'origin_file_path' => '2025-11/03/video/4/21187855495036051-1.jpg',
                                ],
                                1 => [
                                    'id'               => 1170742413128371397,
                                    'post_id'          => 1170736624149989350,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 33529,
                                    'file_name'        => 'cover-21187855495036051-2',
                                    'path'             => '2025-11/03/video/4/21187855495036051-2.jpg',
                                    'media_group_id'   => 1170735725436469939,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153942,
                                    'origin_file_path' => '2025-11/03/video/4/21187855495036051-2.jpg',
                                ],
                                2 => [
                                    'id'               => 1170742431327454595,
                                    'post_id'          => 1170736624149989350,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 40250,
                                    'file_name'        => 'cover-21187855495036051-3',
                                    'path'             => '2025-11/03/video/4/21187855495036051-3.jpg',
                                    'media_group_id'   => 1170735725436469939,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153946,
                                    'origin_file_path' => '2025-11/03/video/4/21187855495036051-3.jpg',
                                ],
                                3 => [
                                    'id'               => 1170742458422658706,
                                    'post_id'          => 1170736624149989350,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 40340,
                                    'file_name'        => 'cover-21187855495036051-4',
                                    'path'             => '2025-11/03/video/4/21187855495036051-4.jpg',
                                    'media_group_id'   => 1170735725436469939,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153953,
                                    'origin_file_path' => '2025-11/03/video/4/21187855495036051-4.jpg',
                                ],
                                4 => [
                                    'id'               => 1170742494489479610,
                                    'post_id'          => 1170736624149989350,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 136272,
                                    'file_name'        => 'cover-21187855495036051-5',
                                    'path'             => '2025-11/03/video/4/21187855495036051-5.jpg',
                                    'media_group_id'   => 1170735725436469939,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153962,
                                    'origin_file_path' => '2025-11/03/video/4/21187855495036051-5.jpg',
                                ],
                                5 => [
                                    'id'               => 1170742548973488264,
                                    'post_id'          => 1170736624149989350,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 43267,
                                    'file_name'        => 'cover-21187855495036051-6',
                                    'path'             => '2025-11/03/video/4/21187855495036051-6.jpg',
                                    'media_group_id'   => 1170735725436469939,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153975,
                                    'origin_file_path' => '2025-11/03/video/4/21187855495036051-6.jpg',
                                ],
                            ],
                        ],
                    ],
                    'tele_files' => [
                        0 => [
                            'file_id'          => 'BAACAgUAAx0CcaAwEgACES9o95pFcGY8lOIJfLOvj24L-vCZMgACPxwAAjtoiFcnCv_U931RbzYE',
                            'file_unique_id'   => 'AgADPxwAAjtoiFc',
                            'file_size'        => 49690871,
                            'file_name'        => '《Amnesia_The_Bunker》_立刻购买并下载_Epic游戏商城.mp4',
                            'origin_ext'       => 'mp4',
                            'origin_mime_type' => 'video/mp4',
                            'media_type'       => 'video',
                        ],
                    ],
                ],
                1170736624498115049 => [
                    'post'       => [
                        'id'             => 1170736624498115049,
                        'type_id'        => 2,
                        'contents'       => '#私拍 #私房照 #诱惑写真 #cos #cosplay 年年 - 情迷法兰西
?{< 点击蓝色字体观看完整版...?><http://t.me/jisou2bot?start=a_7167647364>}
{<?想看什么直接去 暗网 搜...?
><https://t.me/anwangs1>}
{<? 去抢红包??><http://t.me/HBJL?start=invite_7241278032>}',
                        'media_group_id' => 14088458962322245,
                        'hashtags'       => '#私拍,#私房照,#诱惑写真,#cos,#cosplay',
                        'date'           => 1761057370,
                        'time'           => 1762152562,
                    ],
                    'image'      => [
                        21187852443478841 => [
                            'id'               => 1170736624498118330,
                            'post_id'          => 1170736624498115049,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACETZo95pa4V_EPiGCWQF0n-t2R-gjPQACH8cxG_kUiFeGLJ-5FAuCFwEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADH8cxG_kUiFd-',
                            'file_size'        => 113945,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/C/21187852443478841.jpg',
                            'media_group_id'   => 14088458962322245,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152562,
                            'origin_file_path' => '2025-11/03/image/C/21187852443478841.jpg',
                        ],
                        21187852458452106 => [
                            'id'               => 1170736624498118331,
                            'post_id'          => 1170736624498115049,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACETdo95pavcaFDqD7tbBrF-hgaN4UYQACIMcxG_kUiFdMA77b81iRQAEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADIMcxG_kUiFd-',
                            'file_size'        => 95784,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/6/21187852458452106.jpg',
                            'media_group_id'   => 14088458962322245,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152562,
                            'origin_file_path' => '2025-11/03/image/6/21187852458452106.jpg',
                        ],
                        21187852462805595 => [
                            'id'               => 1170736624498118332,
                            'post_id'          => 1170736624498115049,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACETho95paFSJUTTsc8gK736TphrRIwgAC5McxGz4omVcfr4sIh7BvnQEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQAD5McxGz4omVd-',
                            'file_size'        => 92032,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/C/21187852462805595.jpg',
                            'media_group_id'   => 14088458962322245,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152562,
                            'origin_file_path' => '2025-11/03/image/C/21187852462805595.jpg',
                        ],
                        21187852453310078 => [
                            'id'               => 1170736624498118333,
                            'post_id'          => 1170736624498115049,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACETlo95papJ2aLgzvmmRnOOBGkPjE6wACIscxG_kUiFdpbapq9cmeRwEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADIscxG_kUiFd-',
                            'file_size'        => 119013,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/1/21187852453310078.jpg',
                            'media_group_id'   => 14088458962322245,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152562,
                            'origin_file_path' => '2025-11/03/image/1/21187852453310078.jpg',
                        ],
                        21187855484880519 => [
                            'id'               => 1170736624498118334,
                            'post_id'          => 1170736624498115049,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACETpo95paZCQRafYDcnh2yMP_ER0IzgAC6scxGz4omVdvk-Up8HPKoAEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQAD6scxGz4omVd-',
                            'file_size'        => 123231,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/5/21187855484880519.jpg',
                            'media_group_id'   => 14088458962322245,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152562,
                            'origin_file_path' => '2025-11/03/image/5/21187855484880519.jpg',
                        ],
                        21187855491691931 => [
                            'id'               => 1170736624498118335,
                            'post_id'          => 1170736624498115049,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACETto95paO8oolFGWttQwch-30X-kAQACJMcxG_kUiFeMYbVubg93EwEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADJMcxG_kUiFd-',
                            'file_size'        => 125062,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/4/21187855491691931.jpg',
                            'media_group_id'   => 14088458962322245,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152562,
                            'origin_file_path' => '2025-11/03/image/4/21187855491691931.jpg',
                        ],
                        21187855488188483 => [
                            'id'               => 1170736624498118336,
                            'post_id'          => 1170736624498115049,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACETxo95pace_3BcBzebFBMoUmpVuuawAC78cxGz4omVdPmmAWIm540QEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQAD78cxGz4omVd-',
                            'file_size'        => 114767,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/7/21187855488188483.jpg',
                            'media_group_id'   => 14088458962322245,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152562,
                            'origin_file_path' => '2025-11/03/image/7/21187855488188483.jpg',
                        ],
                        21187855469648045 => [
                            'id'               => 1170736624498118337,
                            'post_id'          => 1170736624498115049,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACET1o95paTgm9YyIdlyKz-VKZLwM-YAACJscxG_kUiFe4QlKSSU9KTAEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADJscxG_kUiFd-',
                            'file_size'        => 101562,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/C/21187855469648045.jpg',
                            'media_group_id'   => 14088458962322245,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152562,
                            'origin_file_path' => '2025-11/03/image/C/21187855469648045.jpg',
                        ],
                        21187855480459960 => [
                            'id'               => 1170736624498118338,
                            'post_id'          => 1170736624498115049,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACET5o95paweQE2yjftHruIdb64GBLnQAC88cxGz4omVdUHdkCHe0e3QEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQAD88cxGz4omVd-',
                            'file_size'        => 121292,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/8/21187855480459960.jpg',
                            'media_group_id'   => 14088458962322245,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152562,
                            'origin_file_path' => '2025-11/03/image/8/21187855480459960.jpg',
                        ],
                    ],
                    'tele_files' => [
                        0 => [
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACETZo95pa4V_EPiGCWQF0n-t2R-gjPQACH8cxG_kUiFeGLJ-5FAuCFwEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADH8cxG_kUiFd-',
                            'file_size'        => 113945,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        1 => [
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACETdo95pavcaFDqD7tbBrF-hgaN4UYQACIMcxG_kUiFdMA77b81iRQAEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADIMcxG_kUiFd-',
                            'file_size'        => 95784,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        2 => [
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACETho95paFSJUTTsc8gK736TphrRIwgAC5McxGz4omVcfr4sIh7BvnQEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQAD5McxGz4omVd-',
                            'file_size'        => 92032,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        3 => [
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACETlo95papJ2aLgzvmmRnOOBGkPjE6wACIscxG_kUiFdpbapq9cmeRwEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADIscxG_kUiFd-',
                            'file_size'        => 119013,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        4 => [
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACETpo95paZCQRafYDcnh2yMP_ER0IzgAC6scxGz4omVdvk-Up8HPKoAEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQAD6scxGz4omVd-',
                            'file_size'        => 123231,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        5 => [
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACETto95paO8oolFGWttQwch-30X-kAQACJMcxG_kUiFeMYbVubg93EwEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADJMcxG_kUiFd-',
                            'file_size'        => 125062,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        6 => [
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACETxo95pace_3BcBzebFBMoUmpVuuawAC78cxGz4omVdPmmAWIm540QEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQAD78cxGz4omVd-',
                            'file_size'        => 114767,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        7 => [
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACET1o95paTgm9YyIdlyKz-VKZLwM-YAACJscxG_kUiFe4QlKSSU9KTAEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADJscxG_kUiFd-',
                            'file_size'        => 101562,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        8 => [
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACET5o95paweQE2yjftHruIdb64GBLnQAC88cxGz4omVdUHdkCHe0e3QEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQAD88cxGz4omVd-',
                            'file_size'        => 121292,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                    ],
                ],
                1170736649777187068 => [
                    'post'       => [
                        'id'             => 1170736649777187068,
                        'type_id'        => 2,
                        'contents'       => '',
                        'media_group_id' => 14088459422121877,
                        'hashtags'       => '',
                        'date'           => 1761057427,
                        'time'           => 1762152568,
                    ],
                    'image'      => [
                        21187858522396812 => [
                            'id'               => 1170736649777186518,
                            'post_id'          => 1170736649777187068,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgEAAx0CcaAwEgACEUBo95qU-r9wohhFVRCliDxOmon3rAACuq4xG7Yc6UdL6390qc6etAEAAwIAA3cAAzYE',
                            'file_unique_id'   => 'AQADuq4xG7Yc6Ud8',
                            'file_size'        => 244366,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/6/21187858522396812.jpg',
                            'media_group_id'   => 14088459422121877,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152568,
                            'origin_file_path' => '2025-11/03/image/6/21187858522396812.jpg',
                        ],
                        21187858518606036 => [
                            'id'               => 1170736649777186519,
                            'post_id'          => 1170736649777187068,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgEAAx0CcaAwEgACEUFo95qUvuRSBFd07XFES1qNUetjywACu64xG7Yc6Uexm-yR-nv5OgEAAwIAA3cAAzYE',
                            'file_unique_id'   => 'AQADu64xG7Yc6Ud8',
                            'file_size'        => 279798,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/B/21187858518606036.jpg',
                            'media_group_id'   => 14088459422121877,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152568,
                            'origin_file_path' => '2025-11/03/image/B/21187858518606036.jpg',
                        ],
                        21187858505709841 => [
                            'id'               => 1170736649777186520,
                            'post_id'          => 1170736649777187068,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgEAAx0CcaAwEgACEUJo95qUEnU6n6OcREcRzl8SpsQGygACvK4xG7Yc6Uc5qKSzXhrabgEAAwIAA3cAAzYE',
                            'file_unique_id'   => 'AQADvK4xG7Yc6Ud8',
                            'file_size'        => 303605,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/5/21187858505709841.jpg',
                            'media_group_id'   => 14088459422121877,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152568,
                            'origin_file_path' => '2025-11/03/image/5/21187858505709841.jpg',
                        ],
                        21187858514304458 => [
                            'id'               => 1170736649777186521,
                            'post_id'          => 1170736649777187068,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgEAAx0CcaAwEgACEUNo95qUukiCOqHuGb7VRyn9vcJMHgACva4xG7Yc6Udio9gOVBCjggEAAwIAA3cAAzYE',
                            'file_unique_id'   => 'AQADva4xG7Yc6Ud8',
                            'file_size'        => 291882,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/A/21187858514304458.jpg',
                            'media_group_id'   => 14088459422121877,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152568,
                            'origin_file_path' => '2025-11/03/image/A/21187858514304458.jpg',
                        ],
                        21187858510128673 => [
                            'id'               => 1170736649777186522,
                            'post_id'          => 1170736649777187068,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgEAAx0CcaAwEgACEURo95qUr3FS6mWj4c7P-KX126lMGgACvq4xG7Yc6Uc083gqAAG7G3oBAAMCAAN3AAM2BA',
                            'file_unique_id'   => 'AQADvq4xG7Yc6Ud8',
                            'file_size'        => 322779,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/2/21187858510128673.jpg',
                            'media_group_id'   => 14088459422121877,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152568,
                            'origin_file_path' => '2025-11/03/image/2/21187858510128673.jpg',
                        ],
                        21187858500854041 => [
                            'id'               => 1170736649777186523,
                            'post_id'          => 1170736649777187068,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgEAAx0CcaAwEgACEUVo95qUOJQWWK5KzMRxfxxYzICVVAACv64xG7Yc6UdbDtj9W1VgLgEAAwIAA3cAAzYE',
                            'file_unique_id'   => 'AQADv64xG7Yc6Ud8',
                            'file_size'        => 317207,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/E/21187858500854041.jpg',
                            'media_group_id'   => 14088459422121877,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152568,
                            'origin_file_path' => '2025-11/03/image/E/21187858500854041.jpg',
                        ],
                        21187861534245272 => [
                            'id'               => 1170736649777186524,
                            'post_id'          => 1170736649777187068,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgEAAx0CcaAwEgACEUZo95qU8IO8Cc05FBKhWRQqRu_wLQACwK4xG7Yc6UdO_MnjBTw8NQEAAwIAA3cAAzYE',
                            'file_unique_id'   => 'AQADwK4xG7Yc6Ud8',
                            'file_size'        => 281565,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/E/21187861534245272.jpg',
                            'media_group_id'   => 14088459422121877,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152568,
                            'origin_file_path' => '2025-11/03/image/E/21187861534245272.jpg',
                        ],
                        21187861542746194 => [
                            'id'               => 1170736649777186525,
                            'post_id'          => 1170736649777187068,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgEAAx0CcaAwEgACEUdo95qUVtTzZ3eaUoT2we_MtExgqgACwa4xG7Yc6Ue9mR0ZDAOofgEAAwIAA3cAAzYE',
                            'file_unique_id'   => 'AQADwa4xG7Yc6Ud8',
                            'file_size'        => 266654,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/F/21187861542746194.jpg',
                            'media_group_id'   => 14088459422121877,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152568,
                            'origin_file_path' => '2025-11/03/image/F/21187861542746194.jpg',
                        ],
                        21187861551250137 => [
                            'id'               => 1170736649777186526,
                            'post_id'          => 1170736649777187068,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgEAAx0CcaAwEgACEUho95qU3uclftk56bQ-H6kaPjHyOAACwq4xG7Yc6Ucew_kzOJATkQEAAwIAA3cAAzYE',
                            'file_unique_id'   => 'AQADwq4xG7Yc6Ud8',
                            'file_size'        => 275836,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/F/21187861551250137.jpg',
                            'media_group_id'   => 14088459422121877,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152568,
                            'origin_file_path' => '2025-11/03/image/F/21187861551250137.jpg',
                        ],
                        21187861546954243 => [
                            'id'               => 1170736649777186527,
                            'post_id'          => 1170736649777187068,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgEAAx0CcaAwEgACEUlo95qUnkk3QsV1FDBmoBI5vaPrEQACw64xG7Yc6Uc1l-hEFQ4_sAEAAwIAA3cAAzYE',
                            'file_unique_id'   => 'AQADw64xG7Yc6Ud8',
                            'file_size'        => 293648,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/5/21187861546954243.jpg',
                            'media_group_id'   => 14088459422121877,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152568,
                            'origin_file_path' => '2025-11/03/image/5/21187861546954243.jpg',
                        ],
                    ],
                    'tele_files' => [
                        0 => [
                            'file_id'          => 'AgACAgEAAx0CcaAwEgACEUBo95qU-r9wohhFVRCliDxOmon3rAACuq4xG7Yc6UdL6390qc6etAEAAwIAA3cAAzYE',
                            'file_unique_id'   => 'AQADuq4xG7Yc6Ud8',
                            'file_size'        => 244366,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        1 => [
                            'file_id'          => 'AgACAgEAAx0CcaAwEgACEUFo95qUvuRSBFd07XFES1qNUetjywACu64xG7Yc6Uexm-yR-nv5OgEAAwIAA3cAAzYE',
                            'file_unique_id'   => 'AQADu64xG7Yc6Ud8',
                            'file_size'        => 279798,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        2 => [
                            'file_id'          => 'AgACAgEAAx0CcaAwEgACEUJo95qUEnU6n6OcREcRzl8SpsQGygACvK4xG7Yc6Uc5qKSzXhrabgEAAwIAA3cAAzYE',
                            'file_unique_id'   => 'AQADvK4xG7Yc6Ud8',
                            'file_size'        => 303605,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        3 => [
                            'file_id'          => 'AgACAgEAAx0CcaAwEgACEUNo95qUukiCOqHuGb7VRyn9vcJMHgACva4xG7Yc6Udio9gOVBCjggEAAwIAA3cAAzYE',
                            'file_unique_id'   => 'AQADva4xG7Yc6Ud8',
                            'file_size'        => 291882,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        4 => [
                            'file_id'          => 'AgACAgEAAx0CcaAwEgACEURo95qUr3FS6mWj4c7P-KX126lMGgACvq4xG7Yc6Uc083gqAAG7G3oBAAMCAAN3AAM2BA',
                            'file_unique_id'   => 'AQADvq4xG7Yc6Ud8',
                            'file_size'        => 322779,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        5 => [
                            'file_id'          => 'AgACAgEAAx0CcaAwEgACEUVo95qUOJQWWK5KzMRxfxxYzICVVAACv64xG7Yc6UdbDtj9W1VgLgEAAwIAA3cAAzYE',
                            'file_unique_id'   => 'AQADv64xG7Yc6Ud8',
                            'file_size'        => 317207,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        6 => [
                            'file_id'          => 'AgACAgEAAx0CcaAwEgACEUZo95qU8IO8Cc05FBKhWRQqRu_wLQACwK4xG7Yc6UdO_MnjBTw8NQEAAwIAA3cAAzYE',
                            'file_unique_id'   => 'AQADwK4xG7Yc6Ud8',
                            'file_size'        => 281565,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        7 => [
                            'file_id'          => 'AgACAgEAAx0CcaAwEgACEUdo95qUVtTzZ3eaUoT2we_MtExgqgACwa4xG7Yc6Ue9mR0ZDAOofgEAAwIAA3cAAzYE',
                            'file_unique_id'   => 'AQADwa4xG7Yc6Ud8',
                            'file_size'        => 266654,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        8 => [
                            'file_id'          => 'AgACAgEAAx0CcaAwEgACEUho95qU3uclftk56bQ-H6kaPjHyOAACwq4xG7Yc6Ucew_kzOJATkQEAAwIAA3cAAzYE',
                            'file_unique_id'   => 'AQADwq4xG7Yc6Ud8',
                            'file_size'        => 275836,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        9 => [
                            'file_id'          => 'AgACAgEAAx0CcaAwEgACEUlo95qUnkk3QsV1FDBmoBI5vaPrEQACw64xG7Yc6Uc1l-hEFQ4_sAEAAwIAA3cAAzYE',
                            'file_unique_id'   => 'AQADw64xG7Yc6Ud8',
                            'file_size'        => 293648,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                    ],
                ],
                1170736649844296521 => [
                    'post'       => [
                        'id'             => 1170736649844296521,
                        'type_id'        => 2,
                        'contents'       => '#网友投稿：今早，西港有男子坐窗台，思考人生～～后来应该想通了，悲剧似乎没有发生。',
                        'media_group_id' => 14088459759717021,
                        'hashtags'       => '#网友投稿',
                        'date'           => 1761057469,
                        'time'           => 1762152568,
                    ],
                    'image'      => [
                        21187861538334258 => [
                            'id'               => 1170736649844295667,
                            'post_id'          => 1170736649844296521,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACEUto95q9ZU6XFYliM0sNtgABWjGfNV4AAvAMaxuS9ahXgdshdURqsXgBAAMCAAN5AAM2BA',
                            'file_unique_id'   => 'AQAD8AxrG5L1qFd-',
                            'file_size'        => 195074,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/D/21187861538334258.jpg',
                            'media_group_id'   => 14088459759717021,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152568,
                            'origin_file_path' => '2025-11/03/image/D/21187861538334258.jpg',
                        ],
                    ],
                    'video'      => [
                        21187861529244186 => [
                            'source' => [
                                'id'               => 1170736649844295666,
                                'post_id'          => 1170736649844296521,
                                'bot_id'           => 6026303590,
                                'file_id'          => 'BAACAgUAAx0CcaAwEgACEUpo95q9nP1VPfDIbF2MAgrOSSL37AACtBkAApL1qFev412evzmGdTYE',
                                'file_unique_id'   => 'AgADtBkAApL1qFc',
                                'file_size'        => 325312,
                                'file_name'        => '',
                                'path'             => '2025-11/03/video/8/21187861529244186/hls.m3u8',
                                'media_group_id'   => 14088459759717021,
                                'ext'              => 'm3u8',
                                'mime_type'        => 'application/x-mpegURL',
                                'media_type'       => 'video',
                                'origin_ext'       => 'mp4',
                                'origin_mime_type' => 'video/mp4',
                                'time'             => 1762152568,
                                'origin_file_path' => '2025-11/03/video/8/21187861529244186.mp4',
                            ],
                            'cover'  => [
                                0 => [
                                    'id'               => 1170742550345025849,
                                    'post_id'          => 1170736649844296521,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 63409,
                                    'file_name'        => 'cover-21187861529244186-1',
                                    'path'             => '2025-11/03/video/8/21187861529244186-1.jpg',
                                    'media_group_id'   => 14088459759717021,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153975,
                                    'origin_file_path' => '2025-11/03/video/8/21187861529244186-1.jpg',
                                ],
                                1 => [
                                    'id'               => 1170742551087418765,
                                    'post_id'          => 1170736649844296521,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 60568,
                                    'file_name'        => 'cover-21187861529244186-2',
                                    'path'             => '2025-11/03/video/8/21187861529244186-2.jpg',
                                    'media_group_id'   => 14088459759717021,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153975,
                                    'origin_file_path' => '2025-11/03/video/8/21187861529244186-2.jpg',
                                ],
                                2 => [
                                    'id'               => 1170742552282794923,
                                    'post_id'          => 1170736649844296521,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 63409,
                                    'file_name'        => 'cover-21187861529244186-3',
                                    'path'             => '2025-11/03/video/8/21187861529244186-3.jpg',
                                    'media_group_id'   => 14088459759717021,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153975,
                                    'origin_file_path' => '2025-11/03/video/8/21187861529244186-3.jpg',
                                ],
                            ],
                        ],
                    ],
                    'tele_files' => [
                        0 => [
                            'file_id'          => 'BAACAgUAAx0CcaAwEgACEUpo95q9nP1VPfDIbF2MAgrOSSL37AACtBkAApL1qFev412evzmGdTYE',
                            'file_unique_id'   => 'AgADtBkAApL1qFc',
                            'file_size'        => 325312,
                            'file_name'        => '',
                            'origin_ext'       => 'mp4',
                            'origin_mime_type' => 'video/mp4',
                            'media_type'       => 'video',
                        ],
                        1 => [
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACEUto95q9ZU6XFYliM0sNtgABWjGfNV4AAvAMaxuS9ahXgdshdURqsXgBAAMCAAN5AAM2BA',
                            'file_unique_id'   => 'AQAD8AxrG5L1qFd-',
                            'file_size'        => 195074,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                    ],
                ],
                1170736662741779901 => [
                    'post'       => [
                        'id'             => 1170736662741779901,
                        'type_id'        => 2,
                        'contents'       => '#菲律宾新闻     群岛地区又发6.0级地震
地震台网正式测定，10月17日7时3分，菲律宾群岛地区遭遇了一次6.0级地震。此次地震震源深度为20公里，震中精准定位在北纬9.85度、东经126.25度。地震的发生总是突然且具有一定破坏力。这样规模的地震，很可能会对当地的建筑物、基础设施等带来不同程度的影响，也让当地民众的生活面临诸多挑战。
相关部门想必已迅速行动起来，展开灾情监测与评估工作，以了解具体受灾状况。与此同时，救援力量或许也在集结，准备随时投入到可能的抢险救援行动中，
保障民众生命财产安全，努力将地震带来的损失降到最低限度。',
                        'media_group_id' => 14088459759717029,
                        'hashtags'       => '#菲律宾新闻',
                        'date'           => 1761057470,
                        'time'           => 1762152571,
                    ],
                    'image'      => [
                        21187864564964686 => [
                            'id'               => 1170736662741780687,
                            'post_id'          => 1170736662741779901,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACEUxo95q96ogkZwi4MP-J7cQ5AsynvgAC8QxrG5L1qFf-W_eocgxAUwEAAwIAA3gAAzYE',
                            'file_unique_id'   => 'AQAD8QxrG5L1qFd9',
                            'file_size'        => 26339,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/0/21187864564964686.jpg',
                            'media_group_id'   => 14088459759717029,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152571,
                            'origin_file_path' => '2025-11/03/image/0/21187864564964686.jpg',
                        ],
                        21187864570777970 => [
                            'id'               => 1170736662741780688,
                            'post_id'          => 1170736662741779901,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACEU1o95q9BOhJHM5qyAvC43yYrJTmZAAC8gxrG5L1qFdyqA3l8wJt2AEAAwIAA3gAAzYE',
                            'file_unique_id'   => 'AQAD8gxrG5L1qFd9',
                            'file_size'        => 66049,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/C/21187864570777970.jpg',
                            'media_group_id'   => 14088459759717029,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152571,
                            'origin_file_path' => '2025-11/03/image/C/21187864570777970.jpg',
                        ],
                    ],
                    'tele_files' => [
                        0 => [
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACEUxo95q96ogkZwi4MP-J7cQ5AsynvgAC8QxrG5L1qFf-W_eocgxAUwEAAwIAA3gAAzYE',
                            'file_unique_id'   => 'AQAD8QxrG5L1qFd9',
                            'file_size'        => 26339,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        1 => [
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACEU1o95q9BOhJHM5qyAvC43yYrJTmZAAC8gxrG5L1qFdyqA3l8wJt2AEAAwIAA3gAAzYE',
                            'file_unique_id'   => 'AQAD8gxrG5L1qFd9',
                            'file_size'        => 66049,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                    ],
                ],
                1170736675404382677 => [
                    'post'       => [
                        'id'             => 1170736675404382677,
                        'type_id'        => 2,
                        'contents'       => '#callu3u #反差 #萝莉 #高颜值 #精选
长相清纯甜美 极品反差jk萝莉妹定制福利合集
【6视频+156图片】评论区点击链接拿资源',
                        'media_group_id' => 14088460604160109,
                        'hashtags'       => '#callu3u,#反差,#萝莉,#高颜值,#精选',
                        'date'           => 1761057575,
                        'time'           => 1762152574,
                    ],
                    'image'      => [
                        21187864575371233 => [
                            'id'               => 1170736675404384939,
                            'post_id'          => 1170736675404382677,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgEAAx0CcaAwEgACEVRo95soGVjCKGjdh3RZxGAPwc8yzQACnQtrG2V8qUeBqqy1myWnGgEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADnQtrG2V8qUd-',
                            'file_size'        => 158091,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/4/21187864575371233.jpg',
                            'media_group_id'   => 14088460604160109,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152574,
                            'origin_file_path' => '2025-11/03/image/4/21187864575371233.jpg',
                        ],
                        21187864579992244 => [
                            'id'               => 1170736675404384940,
                            'post_id'          => 1170736675404382677,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgEAAx0CcaAwEgACEVVo95so-72b1ENE6t_5xHpI65SmmQACKQtrG9LMqUctEM77VvvjhgEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADKQtrG9LMqUd-',
                            'file_size'        => 170775,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/5/21187864579992244.jpg',
                            'media_group_id'   => 14088460604160109,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152574,
                            'origin_file_path' => '2025-11/03/image/5/21187864579992244.jpg',
                        ],
                        21187864584411129 => [
                            'id'               => 1170736675404384941,
                            'post_id'          => 1170736675404382677,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgEAAx0CcaAwEgACEVZo95soa3boBXASHbYyVs1dx9xnTgAClAtrG2V8qUeqHggsWLUfngEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADlAtrG2V8qUd-',
                            'file_size'        => 136583,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/4/21187864584411129.jpg',
                            'media_group_id'   => 14088460604160109,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152574,
                            'origin_file_path' => '2025-11/03/image/4/21187864584411129.jpg',
                        ],
                        21187864558512669 => [
                            'id'               => 1170736675404384942,
                            'post_id'          => 1170736675404382677,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgEAAx0CcaAwEgACEVdo95soDPKAdIAmUclG0HaiEJ_6FAAClgtrG2V8qUfgrMS-Zj4pIAEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADlgtrG2V8qUd-',
                            'file_size'        => 179665,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/3/21187864558512669.jpg',
                            'media_group_id'   => 14088460604160109,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152574,
                            'origin_file_path' => '2025-11/03/image/3/21187864558512669.jpg',
                        ],
                        21187867596256661 => [
                            'id'               => 1170736675404384943,
                            'post_id'          => 1170736675404382677,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgEAAx0CcaAwEgACEVho95soTyMG4vacaYhnj6vl_RPgNQAClQtrG2V8qUfRdDZJgFZ-PgEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADlQtrG2V8qUd-',
                            'file_size'        => 131397,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/C/21187867596256661.jpg',
                            'media_group_id'   => 14088460604160109,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152574,
                            'origin_file_path' => '2025-11/03/image/C/21187867596256661.jpg',
                        ],
                        21187867591709877 => [
                            'id'               => 1170736675404384944,
                            'post_id'          => 1170736675404382677,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgEAAx0CcaAwEgACEVlo95sox-caXP_6nfkyE5VhmGg4WgACkwtrG2V8qUeJoaLR4TEJswEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADkwtrG2V8qUd-',
                            'file_size'        => 96424,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/3/21187867591709877.jpg',
                            'media_group_id'   => 14088460604160109,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152574,
                            'origin_file_path' => '2025-11/03/image/3/21187867591709877.jpg',
                        ],
                    ],
                    'tele_files' => [
                        0 => [
                            'file_id'          => 'AgACAgEAAx0CcaAwEgACEVRo95soGVjCKGjdh3RZxGAPwc8yzQACnQtrG2V8qUeBqqy1myWnGgEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADnQtrG2V8qUd-',
                            'file_size'        => 158091,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        1 => [
                            'file_id'          => 'AgACAgEAAx0CcaAwEgACEVVo95so-72b1ENE6t_5xHpI65SmmQACKQtrG9LMqUctEM77VvvjhgEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADKQtrG9LMqUd-',
                            'file_size'        => 170775,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        2 => [
                            'file_id'          => 'AgACAgEAAx0CcaAwEgACEVZo95soa3boBXASHbYyVs1dx9xnTgAClAtrG2V8qUeqHggsWLUfngEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADlAtrG2V8qUd-',
                            'file_size'        => 136583,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        3 => [
                            'file_id'          => 'AgACAgEAAx0CcaAwEgACEVdo95soDPKAdIAmUclG0HaiEJ_6FAAClgtrG2V8qUfgrMS-Zj4pIAEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADlgtrG2V8qUd-',
                            'file_size'        => 179665,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        4 => [
                            'file_id'          => 'AgACAgEAAx0CcaAwEgACEVho95soTyMG4vacaYhnj6vl_RPgNQAClQtrG2V8qUfRdDZJgFZ-PgEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADlQtrG2V8qUd-',
                            'file_size'        => 131397,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        5 => [
                            'file_id'          => 'AgACAgEAAx0CcaAwEgACEVlo95sox-caXP_6nfkyE5VhmGg4WgACkwtrG2V8qUeJoaLR4TEJswEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADkwtrG2V8qUd-',
                            'file_size'        => 96424,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                    ],
                ],
                1170736675437937442 => [
                    'post'       => [
                        'id'             => 1170736675437937442,
                        'type_id'        => 2,
                        'contents'       => 'Arch Doge社工查档
新兴社工查档团队，拥有大量源头渠道对接
实力雄厚，放心选择！
快递/开房/车主/机主/户籍/身份证/手机/邮箱/账号/密码等
QQ查绑/贴吧查绑/微博查绑/Q群关系/机主姓名/同名联系方式/同邮服/姓名查身份证号码（猎魔）等
只要您想，在我们这里都可以查到！
支持查询：
          个人信息
个人户籍      身份正反
盖章户籍      全家户籍
婚姻记录      名下号码
社保单位      社保单位
医疗记录      医疗记录
名下证件      学籍学历
征信记录      营业执照
         反查身份证
手机机主      车牌查人
卡号反查      公司法人
APP反查      支码反查
模糊找人      人脸识别
          网逃犯罪
案件详情      立案调查
犯罪在逃      服刑记录
吸毒记录      犯罪记录
          找人抓人
手机定位      手机轨迹
人物轨迹      开房同住
快递地址      外卖地址
停车记录      水电地址
通话记录      微信强提
7*24小时在线源头对接查询
速度快，数据精准
开户查档人员：@alix123452
开户查档频道：https://t.me/ArchDoge15019
诚信第一！一次合作，终身选择！现在加入频道可参与红包雨抽奖活动?',
                        'media_group_id' => 1170735726338245051,
                        'hashtags'       => '',
                        'date'           => 1761057656,
                        'time'           => 1762152574,
                    ],
                    'image'      => [
                        21187867617317012 => [
                            'id'               => 1170736675437938948,
                            'post_id'          => 1170736675437937442,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACEVxo95t4eulrQwsJrOQaDr-a0JeXBQACgAxrG7E3WVfPqfQXA8hR4QEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADgAxrG7E3WVd-',
                            'file_size'        => 13029,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/1/21187867617317012.jpg',
                            'media_group_id'   => 1170735726338245051,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152574,
                            'origin_file_path' => '2025-11/03/image/1/21187867617317012.jpg',
                        ],
                    ],
                    'tele_files' => [
                        0 => [
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACEVxo95t4eulrQwsJrOQaDr-a0JeXBQACgAxrG7E3WVfPqfQXA8hR4QEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADgAxrG7E3WVd-',
                            'file_size'        => 13029,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                    ],
                ],
                1170736675475687471 => [
                    'post'       => [
                        'id'             => 1170736675475687471,
                        'type_id'        => 2,
                        'contents'       => '⭐️ #软件 #第三方
? {<MiniBili - iOS 第三方哔哩哔哩客户端><https://github.com/ResistanceTo/MiniBili-WEB>}
▎软件平台：#iOS
▎软件介绍：一款免费、无广告、无跟踪器的 iOS 第三方哔哩哔哩客户端，它通过直接与 B站官方服务器通信来获取内容，不经过任何中间服务器，保证用户数据的私密性与安全性。
应用界面简洁，无多余推荐或追踪行为，支持视频缓存、自动横屏播放、主题切换等实用功能，旨在为用户提供更纯净的观影体验。
▎软件下载：{<点击下载><https://minibili.zhaohe.org/>}
✈️  {<频道><https://t.me/LCGFX>}    ?  {<群组><https://t.me/ZYShares>}    ?  {<投稿><https://t.me/SXJQR_Bot>}    ?  {<广告><https://t.me/cxxpq>}',
                        'media_group_id' => 1170735726367605041,
                        'hashtags'       => '#软件,#第三方,#iOS',
                        'date'           => 1761057656,
                        'time'           => 1762152574,
                    ],
                    'image'      => [
                        21187867607907140 => [
                            'id'               => 1170736675475687098,
                            'post_id'          => 1170736675475687471,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACEV1o95t42O87M5eiIXdVW8k9Y0d5dgAC1AxrGwqaqVc7tQcaSNFpZQEAAwIAA3cAAzYE',
                            'file_unique_id'   => 'AQAD1AxrGwqaqVd8',
                            'file_size'        => 576246,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/3/21187867607907140.jpg',
                            'media_group_id'   => 1170735726367605041,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152574,
                            'origin_file_path' => '2025-11/03/image/3/21187867607907140.jpg',
                        ],
                    ],
                    'tele_files' => [
                        0 => [
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACEV1o95t42O87M5eiIXdVW8k9Y0d5dgAC1AxrGwqaqVc7tQcaSNFpZQEAAwIAA3cAAzYE',
                            'file_unique_id'   => 'AQAD1AxrGwqaqVd8',
                            'file_size'        => 576246,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                    ],
                ],
                1170736675496658274 => [
                    'post'       => [
                        'id'             => 1170736675496658274,
                        'type_id'        => 2,
                        'contents'       => 'CODE #P2197
Download Movie
https://t.me/SWFX_COM_BOT?start=_CRCQVQSXPDMCYOB',
                        'media_group_id' => 1170735726384383195,
                        'hashtags'       => '#P2197',
                        'date'           => 1761057689,
                        'time'           => 1762152574,
                    ],
                    'video'      => [
                        21187867601983246 => [
                            'source' => [
                                'id'               => 1170736675496657690,
                                'post_id'          => 1170736675496658274,
                                'bot_id'           => 6026303590,
                                'file_id'          => 'BAACAgQAAx0CcaAwEgACEV5o95uZlxfgOcIpKvgwwrVCL-BDLgAC1xkAAuhteFMpzG8tWPzjHzYE',
                                'file_unique_id'   => 'AgAD1xkAAuhteFM',
                                'file_size'        => 1653165,
                                'file_name'        => 'rywhaz01mh',
                                'path'             => '2025-11/03/video/0/21187867601983246/hls.m3u8',
                                'media_group_id'   => 1170735726384383195,
                                'ext'              => 'm3u8',
                                'mime_type'        => 'application/x-mpegURL',
                                'media_type'       => 'video',
                                'origin_ext'       => 'mp4',
                                'origin_mime_type' => 'video/mp4',
                                'time'             => 1762152574,
                                'origin_file_path' => '2025-11/03/video/0/21187867601983246.mp4',
                            ],
                            'cover'  => [
                                0 => [
                                    'id'               => 1170742553448809747,
                                    'post_id'          => 1170736675496658274,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 41500,
                                    'file_name'        => 'cover-21187867601983246-1',
                                    'path'             => '2025-11/03/video/0/21187867601983246-1.jpg',
                                    'media_group_id'   => 1170735726384383195,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153976,
                                    'origin_file_path' => '2025-11/03/video/0/21187867601983246-1.jpg',
                                ],
                                1 => [
                                    'id'               => 1170742555923448741,
                                    'post_id'          => 1170736675496658274,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 39742,
                                    'file_name'        => 'cover-21187867601983246-2',
                                    'path'             => '2025-11/03/video/0/21187867601983246-2.jpg',
                                    'media_group_id'   => 1170735726384383195,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153976,
                                    'origin_file_path' => '2025-11/03/video/0/21187867601983246-2.jpg',
                                ],
                                2 => [
                                    'id'               => 1170742560809815672,
                                    'post_id'          => 1170736675496658274,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 43744,
                                    'file_name'        => 'cover-21187867601983246-3',
                                    'path'             => '2025-11/03/video/0/21187867601983246-3.jpg',
                                    'media_group_id'   => 1170735726384383195,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153977,
                                    'origin_file_path' => '2025-11/03/video/0/21187867601983246-3.jpg',
                                ],
                            ],
                        ],
                    ],
                    'tele_files' => [
                        0 => [
                            'file_id'          => 'BAACAgQAAx0CcaAwEgACEV5o95uZlxfgOcIpKvgwwrVCL-BDLgAC1xkAAuhteFMpzG8tWPzjHzYE',
                            'file_unique_id'   => 'AgAD1xkAAuhteFM',
                            'file_size'        => 1653165,
                            'file_name'        => 'rywhaz01mh',
                            'origin_ext'       => 'mp4',
                            'origin_mime_type' => 'video/mp4',
                            'media_type'       => 'video',
                        ],
                    ],
                ],
                1170736688410921976 => [
                    'post'       => [
                        'id'             => 1170736688410921976,
                        'type_id'        => 2,
                        'contents'       => '#AugustTaylor
Download Movie
Folder 1
https://t.me/DrDrochilnya_bot?start=_BSJCNGVBMFBRZQI
Folder 2
https://t.me/DrDrochilnya_bot?start=_AWLHXNPDZUFYBPJ
Folder 3
https://t.me/DrDrochilnya_bot?start=_JCXCEPXYXSXYUAT',
                        'media_group_id' => 14088461667040453,
                        'hashtags'       => '#AugustTaylor',
                        'date'           => 1761057708,
                        'time'           => 1762152577,
                    ],
                    'image'      => [
                        21187867612735866 => [
                            'id'               => 1170736688410920286,
                            'post_id'          => 1170736688410921976,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgQAAx0CcaAwEgACEV9o95usWlKrybYWCVLSowZD6sGrvgACccUxG3HdSVLy8s2Jm2FG2AEAAwIAA3gAAzYE',
                            'file_unique_id'   => 'AQADccUxG3HdSVJ9',
                            'file_size'        => 52654,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/C/21187867612735866.jpg',
                            'media_group_id'   => 14088461667040453,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152577,
                            'origin_file_path' => '2025-11/03/image/C/21187867612735866.jpg',
                        ],
                        21187870645300285 => [
                            'id'               => 1170736688410920287,
                            'post_id'          => 1170736688410921976,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgQAAx0CcaAwEgACEWBo95usH1cyBBmiX7D2R9QRWvZLmAACcsUxG3HdSVKfTu3mn-spnQEAAwIAA3gAAzYE',
                            'file_unique_id'   => 'AQADcsUxG3HdSVJ9',
                            'file_size'        => 46550,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/3/21187870645300285.jpg',
                            'media_group_id'   => 14088461667040453,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152577,
                            'origin_file_path' => '2025-11/03/image/3/21187870645300285.jpg',
                        ],
                        21187870624783535 => [
                            'id'               => 1170736688410920288,
                            'post_id'          => 1170736688410921976,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgQAAx0CcaAwEgACEWFo95usSHjjfF3RE0F70WR8spIJ6AACdMUxG3HdSVL6I7krxdXDggEAAwIAA3gAAzYE',
                            'file_unique_id'   => 'AQADdMUxG3HdSVJ9',
                            'file_size'        => 52484,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/5/21187870624783535.jpg',
                            'media_group_id'   => 14088461667040453,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152577,
                            'origin_file_path' => '2025-11/03/image/5/21187870624783535.jpg',
                        ],
                        21187870640773868 => [
                            'id'               => 1170736688410920289,
                            'post_id'          => 1170736688410921976,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgQAAx0CcaAwEgACEWJo95us9GxgTga7aiCyThO8c9LZ6AACdcUxG3HdSVJ59tcS8UE0wwEAAwIAA3gAAzYE',
                            'file_unique_id'   => 'AQADdcUxG3HdSVJ9',
                            'file_size'        => 38726,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/4/21187870640773868.jpg',
                            'media_group_id'   => 14088461667040453,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152577,
                            'origin_file_path' => '2025-11/03/image/4/21187870640773868.jpg',
                        ],
                        21187870630259637 => [
                            'id'               => 1170736688410920290,
                            'post_id'          => 1170736688410921976,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgQAAx0CcaAwEgACEWNo95uskCvz405luZ7B_OpG0NcgjQACdsUxG3HdSVJFA9bHlRyn0gEAAwIAA3gAAzYE',
                            'file_unique_id'   => 'AQADdsUxG3HdSVJ9',
                            'file_size'        => 52492,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/A/21187870630259637.jpg',
                            'media_group_id'   => 14088461667040453,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152577,
                            'origin_file_path' => '2025-11/03/image/A/21187870630259637.jpg',
                        ],
                        21187870635264200 => [
                            'id'               => 1170736688410920291,
                            'post_id'          => 1170736688410921976,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgQAAx0CcaAwEgACEWRo95us4J20BwFPZ5mx4JNQI6nQnQACd8UxG3HdSVKxpLZeL5QJswEAAwIAA3gAAzYE',
                            'file_unique_id'   => 'AQADd8UxG3HdSVJ9',
                            'file_size'        => 62934,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/4/21187870635264200.jpg',
                            'media_group_id'   => 14088461667040453,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152577,
                            'origin_file_path' => '2025-11/03/image/4/21187870635264200.jpg',
                        ],
                    ],
                    'tele_files' => [
                        0 => [
                            'file_id'          => 'AgACAgQAAx0CcaAwEgACEV9o95usWlKrybYWCVLSowZD6sGrvgACccUxG3HdSVLy8s2Jm2FG2AEAAwIAA3gAAzYE',
                            'file_unique_id'   => 'AQADccUxG3HdSVJ9',
                            'file_size'        => 52654,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        1 => [
                            'file_id'          => 'AgACAgQAAx0CcaAwEgACEWBo95usH1cyBBmiX7D2R9QRWvZLmAACcsUxG3HdSVKfTu3mn-spnQEAAwIAA3gAAzYE',
                            'file_unique_id'   => 'AQADcsUxG3HdSVJ9',
                            'file_size'        => 46550,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        2 => [
                            'file_id'          => 'AgACAgQAAx0CcaAwEgACEWFo95usSHjjfF3RE0F70WR8spIJ6AACdMUxG3HdSVL6I7krxdXDggEAAwIAA3gAAzYE',
                            'file_unique_id'   => 'AQADdMUxG3HdSVJ9',
                            'file_size'        => 52484,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        3 => [
                            'file_id'          => 'AgACAgQAAx0CcaAwEgACEWJo95us9GxgTga7aiCyThO8c9LZ6AACdcUxG3HdSVJ59tcS8UE0wwEAAwIAA3gAAzYE',
                            'file_unique_id'   => 'AQADdcUxG3HdSVJ9',
                            'file_size'        => 38726,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        4 => [
                            'file_id'          => 'AgACAgQAAx0CcaAwEgACEWNo95uskCvz405luZ7B_OpG0NcgjQACdsUxG3HdSVJFA9bHlRyn0gEAAwIAA3gAAzYE',
                            'file_unique_id'   => 'AQADdsUxG3HdSVJ9',
                            'file_size'        => 52492,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        5 => [
                            'file_id'          => 'AgACAgQAAx0CcaAwEgACEWRo95us4J20BwFPZ5mx4JNQI6nQnQACd8UxG3HdSVKxpLZeL5QJswEAAwIAA3gAAzYE',
                            'file_unique_id'   => 'AQADd8UxG3HdSVJ9',
                            'file_size'        => 62934,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                    ],
                ],
                1170736701069330997 => [
                    'post'       => [
                        'id'             => 1170736701069330997,
                        'type_id'        => 2,
                        'contents'       => '《芝加哥七君子审判》
真实历史事件改编，法庭内外的激烈交锋，展现理想与信念的较量，充满思辨与力量。
#历史真实改编 #芝加哥七君子审判 #电影推荐
2025-10-08 19:38:21
? https://pan.quark.cn/s/cc4a63af116b',
                        'media_group_id' => 14088461833094421,
                        'hashtags'       => '#历史真实改编,#芝加哥七君子审判,#电影推荐',
                        'date'           => 1761057729,
                        'time'           => 1762152580,
                    ],
                    'image'      => [
                        21187870659843491 => [
                            'id'               => 1170736701069329482,
                            'post_id'          => 1170736701069330997,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACEWVo95vBHRI7AAFD_4HPY0e75RCceLMAApUNaxvDJjFXDyo9Ya-I8MwBAAMCAAN4AAM2BA',
                            'file_unique_id'   => 'AQADlQ1rG8MmMVd9',
                            'file_size'        => 58168,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/A/21187870659843491.jpg',
                            'media_group_id'   => 14088461833094421,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152580,
                            'origin_file_path' => '2025-11/03/image/A/21187870659843491.jpg',
                        ],
                        21187873666288288 => [
                            'id'               => 1170736701069329483,
                            'post_id'          => 1170736701069330997,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACEWZo95vBGOR7LhHqfgIEzAZYrNri6QAClg1rG8MmMVdt1Bve6R3MJgEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADlg1rG8MmMVd-',
                            'file_size'        => 186959,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/7/21187873666288288.jpg',
                            'media_group_id'   => 14088461833094421,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152580,
                            'origin_file_path' => '2025-11/03/image/7/21187873666288288.jpg',
                        ],
                        21187873681330635 => [
                            'id'               => 1170736701069329484,
                            'post_id'          => 1170736701069330997,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACEWdo95vBh7HeRdTzMPxKgf2pn4vihgACmQ1rG8MmMVfl-1HJPxKdFgEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADmQ1rG8MmMVd-',
                            'file_size'        => 240561,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/4/21187873681330635.jpg',
                            'media_group_id'   => 14088461833094421,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152580,
                            'origin_file_path' => '2025-11/03/image/4/21187873681330635.jpg',
                        ],
                        21187873678047215 => [
                            'id'               => 1170736701069329485,
                            'post_id'          => 1170736701069330997,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACEWho95vBN6pS29hk8ySuY1HGeWueHQAClw1rG8MmMVe7vD6pGZDubAEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADlw1rG8MmMVd-',
                            'file_size'        => 93303,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/7/21187873678047215.jpg',
                            'media_group_id'   => 14088461833094421,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152580,
                            'origin_file_path' => '2025-11/03/image/7/21187873678047215.jpg',
                        ],
                        21187873685116866 => [
                            'id'               => 1170736701069329486,
                            'post_id'          => 1170736701069330997,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACEWlo95vBo8LBsimUr4cfYjmBnz6wIwACmw1rG8MmMVer-KfXoCBtJgEAAwIAA3gAAzYE',
                            'file_unique_id'   => 'AQADmw1rG8MmMVd9',
                            'file_size'        => 119943,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/6/21187873685116866.jpg',
                            'media_group_id'   => 14088461833094421,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152580,
                            'origin_file_path' => '2025-11/03/image/6/21187873685116866.jpg',
                        ],
                        21187873674669379 => [
                            'id'               => 1170736701069329487,
                            'post_id'          => 1170736701069330997,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACEWpo95vBwti-4zCvXIwT9acbItY8cAACmA1rG8MmMVfBIC8tVnpMXAEAAwIAA3gAAzYE',
                            'file_unique_id'   => 'AQADmA1rG8MmMVd9',
                            'file_size'        => 176673,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/2/21187873674669379.jpg',
                            'media_group_id'   => 14088461833094421,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152580,
                            'origin_file_path' => '2025-11/03/image/2/21187873674669379.jpg',
                        ],
                    ],
                    'tele_files' => [
                        0 => [
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACEWVo95vBHRI7AAFD_4HPY0e75RCceLMAApUNaxvDJjFXDyo9Ya-I8MwBAAMCAAN4AAM2BA',
                            'file_unique_id'   => 'AQADlQ1rG8MmMVd9',
                            'file_size'        => 58168,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        1 => [
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACEWZo95vBGOR7LhHqfgIEzAZYrNri6QAClg1rG8MmMVdt1Bve6R3MJgEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADlg1rG8MmMVd-',
                            'file_size'        => 186959,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        2 => [
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACEWdo95vBh7HeRdTzMPxKgf2pn4vihgACmQ1rG8MmMVfl-1HJPxKdFgEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADmQ1rG8MmMVd-',
                            'file_size'        => 240561,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        3 => [
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACEWho95vBN6pS29hk8ySuY1HGeWueHQAClw1rG8MmMVe7vD6pGZDubAEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADlw1rG8MmMVd-',
                            'file_size'        => 93303,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        4 => [
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACEWlo95vBo8LBsimUr4cfYjmBnz6wIwACmw1rG8MmMVer-KfXoCBtJgEAAwIAA3gAAzYE',
                            'file_unique_id'   => 'AQADmw1rG8MmMVd9',
                            'file_size'        => 119943,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        5 => [
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACEWpo95vBwti-4zCvXIwT9acbItY8cAACmA1rG8MmMVfBIC8tVnpMXAEAAwIAA3gAAzYE',
                            'file_unique_id'   => 'AQADmA1rG8MmMVd9',
                            'file_size'        => 176673,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                    ],
                ],
                1170736713744518894 => [
                    'post'       => [
                        'id'             => 1170736713744518894,
                        'type_id'        => 2,
                        'contents'       => '《米纳里》
讲述韩裔家庭在阿肯色州的农场梦想，以细腻笔触勾勒移民生活的坚韧与温情，水芹意象贯穿东西方文化共鸣。
#移民家庭 #米纳里 #文艺片 #A24出品
2025-10-08 19:40:03
? https://pan.quark.cn/s/25e495a0a1c6',
                        'media_group_id' => 14088461833094429,
                        'hashtags'       => '#移民家庭,#米纳里,#文艺片,#A24出品',
                        'date'           => 1761057729,
                        'time'           => 1762152583,
                    ],
                    'image'      => [
                        21187873688738584 => [
                            'id'               => 1170736713744517036,
                            'post_id'          => 1170736713744518894,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACEWto95vBGxsmfxfxqSxZjP3GjQjSLQACng1rG8MmMVcMkvC72quWRAEAAwIAA3gAAzYE',
                            'file_unique_id'   => 'AQADng1rG8MmMVd9',
                            'file_size'        => 81745,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/0/21187873688738584.jpg',
                            'media_group_id'   => 14088461833094429,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152583,
                            'origin_file_path' => '2025-11/03/image/0/21187873688738584.jpg',
                        ],
                        21187876700687013 => [
                            'id'               => 1170736713744517037,
                            'post_id'          => 1170736713744518894,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACEWxo95vBTshk1zOv_sau0Ifs_lKQJAACnQ1rG8MmMVcUO9CfaravuwEAAwIAA20AAzYE',
                            'file_unique_id'   => 'AQADnQ1rG8MmMVdy',
                            'file_size'        => 15367,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/E/21187876700687013.jpg',
                            'media_group_id'   => 14088461833094429,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152583,
                            'origin_file_path' => '2025-11/03/image/E/21187876700687013.jpg',
                        ],
                        21187876705326692 => [
                            'id'               => 1170736713744517038,
                            'post_id'          => 1170736713744518894,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACEW1o95vBvVJaKao_8fVcF3YP_akrjwACnw1rG8MmMVeRmyd0oU1ZCwEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADnw1rG8MmMVd-',
                            'file_size'        => 121864,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/E/21187876705326692.jpg',
                            'media_group_id'   => 14088461833094429,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152583,
                            'origin_file_path' => '2025-11/03/image/E/21187876705326692.jpg',
                        ],
                    ],
                    'tele_files' => [
                        0 => [
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACEWto95vBGxsmfxfxqSxZjP3GjQjSLQACng1rG8MmMVcMkvC72quWRAEAAwIAA3gAAzYE',
                            'file_unique_id'   => 'AQADng1rG8MmMVd9',
                            'file_size'        => 81745,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        1 => [
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACEWxo95vBTshk1zOv_sau0Ifs_lKQJAACnQ1rG8MmMVcUO9CfaravuwEAAwIAA20AAzYE',
                            'file_unique_id'   => 'AQADnQ1rG8MmMVdy',
                            'file_size'        => 15367,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        2 => [
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACEW1o95vBvVJaKao_8fVcF3YP_akrjwACnw1rG8MmMVeRmyd0oU1ZCwEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADnw1rG8MmMVd-',
                            'file_size'        => 121864,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                    ],
                ],
                1170736713778069816 => [
                    'post'       => [
                        'id'             => 1170736713778069816,
                        'type_id'        => 2,
                        'contents'       => '? ?最新协议号补货通知?
? 各国协议号价格更新，数量有限，速来对接！
✅ 协议号价格表：
?? +1 ? 0.7U
?? +44（特殊） ? 1U
?? +234 ? 0.5U
?? +60 ? 1U（60个起购）
?? +31 ? 1.2U
?? +63 ? 0.8U
?? +880 ? 0.5U
?? +95 ? 0.5U
?? +33 ? 1.2U
?? +79 ? 1.2U
? 库存有限，先到先得！欢迎各位老板前来对接！
? 联系电报客服： @xthaopp
? 官网自助下单： https://xthao.org/',
                        'media_group_id' => 1170735726652821216,
                        'hashtags'       => '',
                        'date'           => 1761057834,
                        'time'           => 1762152583,
                    ],
                    'image'      => [
                        21187876709725409 => [
                            'id'               => 1170736713778070775,
                            'post_id'          => 1170736713778069816,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACEW9o95wqNMu-V45m36fF1NcU1YGo0QACDMgxGzjIIFf3jKOWLqmb0QEAAwIAA3gAAzYE',
                            'file_unique_id'   => 'AQADDMgxGzjIIFd9',
                            'file_size'        => 37019,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/F/21187876709725409.jpg',
                            'media_group_id'   => 1170735726652821216,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152583,
                            'origin_file_path' => '2025-11/03/image/F/21187876709725409.jpg',
                        ],
                    ],
                    'tele_files' => [
                        0 => [
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACEW9o95wqNMu-V45m36fF1NcU1YGo0QACDMgxGzjIIFf3jKOWLqmb0QEAAwIAA3gAAzYE',
                            'file_unique_id'   => 'AQADDMgxGzjIIFd9',
                            'file_size'        => 37019,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                    ],
                ],
                1170736713807431440 => [
                    'post'       => [
                        'id'             => 1170736713807431440,
                        'type_id'        => 2,
                        'contents'       => '最近有不少小伙伴在登录 Telegram 时，遇到了 “需要绑定邮箱” 的提示，不知道该如何处理？今天给大家详细解析原因，并提供有效的解决方案！
? 为什么会出现这个问题？
1️⃣ 老号机制：以前注册的老账号并不强制要求绑定邮箱，现在系统可能会要求补全信息。
2️⃣ 设备风控：如果 Telegram 认为你的登录设备存在风险，比如频繁切换 IP 或使用了不稳定的网络，就可能触发此验证。
✅ 解决方案
? 方法 1：绑定 Gmail 邮箱，按照提示输入验证码即可。
? 方法 2：卸载 Telegram 客户端 → 重新安装 → 切换 VPN 重新尝试。
? 方法 3：如果仍然无法解决，尝试使用第三方客户端或电脑端登录。
? 如果遇到需要输入邮箱验证码的情况，直接在 Telegram 的验证链接中找到 5 位验证码 并输入即可。
? 针对不同设备的解决方案
? iOS 用户：建议使用 Swiftgram 版本（?下载链接 ? https://apps.apple.com/us/app/swiftgram/id6471879502?l=zh-Hans-CN）
? Android 用户：建议使用 Telegram X 版本（?下载链接 ?https://telegram-x.en.uptodown.com/android ）
⚠️ 注意事项
✅ 尽量使用 Gmail 绑定邮箱，其他邮箱可能不稳定。
✅ 避免频繁切换 IP，否则可能触发更严格的风控。
✅ 如仍然无法登录，可尝试使用电脑端 Telegram 进行验证。
如果你遇到其他 Telegram 登录问题，欢迎在评论区讨论，或者加入我们的交流群一起交流！?',
                        'media_group_id' => 1170735726669596649,
                        'hashtags'       => '',
                        'date'           => 1761057834,
                        'time'           => 1762152583,
                    ],
                    'image'      => [
                        21187876714490248 => [
                            'id'               => 1170736713807429689,
                            'post_id'          => 1170736713807431440,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACEXBo95wqzKuGpiDEsW5PEHEIeNQ6kgACcsMxGz4gKVeaogPQzMHbqQEAAwIAA3gAAzYE',
                            'file_unique_id'   => 'AQADcsMxGz4gKVd9',
                            'file_size'        => 36476,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/7/21187876714490248.jpg',
                            'media_group_id'   => 1170735726669596649,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152583,
                            'origin_file_path' => '2025-11/03/image/7/21187876714490248.jpg',
                        ],
                    ],
                    'tele_files' => [
                        0 => [
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACEXBo95wqzKuGpiDEsW5PEHEIeNQ6kgACcsMxGz4gKVeaogPQzMHbqQEAAwIAA3gAAzYE',
                            'file_unique_id'   => 'AQADcsMxGz4gKVd9',
                            'file_size'        => 36476,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                    ],
                ],
                1170736713832598853 => [
                    'post'       => [
                        'id'             => 1170736713832598853,
                        'type_id'        => 2,
                        'contents'       => '? Telegram 解除双向限制完整教程 ?
如果你的 Telegram 账号被限制，无法给非联系人发送消息，可以按照以下步骤进行申诉解除：
? 1. 搜索并联系 @SpamBot
? 在 Telegram 搜索栏输入 @SpamBot，点击带有机器人图标的官方账号（请注意区分同名群组或频道）。
? 2. 启动对话
? 进入聊天界面后，点击 Start 按钮，机器人将提供多个选项。
? 3. 选择申诉理由
? 选择 But I can’t message non-contacts!（我无法向非联系人发送消息）。
? 4. 回答问题
? 机器人会询问是否曾发送垃圾信息，选择 No, I’ll never do any of this!（从未有过此类行为）。
? 如果需要补充说明，可以回复 accident（误操作）或简短解释，如 I was restricted by mistake（我被错误限制）。
? 5. 等待审核
⏳ 申请提交后，系统通常会在 25 分钟至数小时 内解除限制。
⚠️ 若长时间未恢复，可尝试重新申诉或联系客服。
? 建议： 避免频繁向陌生人发送消息，以防再次被限制！',
                        'media_group_id' => 1170735726690566251,
                        'hashtags'       => '',
                        'date'           => 1761057834,
                        'time'           => 1762152583,
                    ],
                    'image'      => [
                        21187876719286169 => [
                            'id'               => 1170736713832598199,
                            'post_id'          => 1170736713832598853,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACEXFo95wq5VPhO9gbVXtHHaIgXCf2hAACQMExG-jUQVdiymjIs_lVfwEAAwIAA3gAAzYE',
                            'file_unique_id'   => 'AQADQMExG-jUQVd9',
                            'file_size'        => 36978,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/E/21187876719286169.jpg',
                            'media_group_id'   => 1170735726690566251,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152583,
                            'origin_file_path' => '2025-11/03/image/E/21187876719286169.jpg',
                        ],
                    ],
                    'tele_files' => [
                        0 => [
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACEXFo95wq5VPhO9gbVXtHHaIgXCf2hAACQMExG-jUQVdiymjIs_lVfwEAAwIAA3gAAzYE',
                            'file_unique_id'   => 'AQADQMExG-jUQVd9',
                            'file_size'        => 36978,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                    ],
                ],
                1170736713870348192 => [
                    'post'       => [
                        'id'             => 1170736713870348192,
                        'type_id'        => 2,
                        'contents'       => '? 目标：降低风控，提高账号稳定性 ?
✅ 养号基本原则
真实环境：使用稳定的IP，避免频繁更换网络或IP地址。
低调操作：前期不要频繁改资料、加好友、点赞、发帖。
人设真实：完善个人信息（头像、简介、封面图），但不要一次性改太多。
模拟真人行为：逐步增加互动，不要短时间内进行大量操作。
? 养号步骤
? 第 1-3 天：基础养号
✅ 完善资料：添加头像、封面、基本信息（可适当留白）。
✅ 浏览动态：每天登录 1-2 次，浏览首页内容，翻看帖子。
✅ 适量点赞：对少量好友或公共页面的帖子点赞（3-5个/天）。
✅ 加入兴趣小组：选择 1-2 个小组加入，但不要立刻发言。
? 第 4-7 天：增加互动
✅ 添加好友：每天添加 2-5 个与你兴趣相符的好友，避免一次性添加太多。
✅ 简单评论：在好友或公共页面下发表自然的评论（1-3 条/天）。
✅ 适量分享：分享 1-2 条新闻或趣味内容，不涉及营销。
✅ 私信互动：可以和好友简单寒暄，避免同一时间发送大量消息。
? 第 8-14 天：逐步正常使用
✅ 发帖：发布 1-2 条日常动态（文字+图片更自然）。
✅ 稳定活跃：每天浏览、点赞、评论，逐步增加好友数量。
✅ 参与小组讨论：在兴趣小组内互动，发表有价值的内容。
✅ 绑定手机号/邮箱：提升账号安全性（避免频繁更改）。
? 注意事项
❌ 避免使用 VPN 频繁切换 IP，会导致风控。
❌ 不要频繁修改信息，如头像、邮箱、密码等。
❌ 不要短时间内大量加好友，容易触发限制。
❌ 避免敏感词和营销内容，容易被封号或降权。
❌ 尽量模拟真人使用习惯，慢慢增加活跃度。
? 需要Facebook账号？批发购买? xthao.org
按照这个流程操作，养号更稳定，减少被封风险！?',
                        'media_group_id' => 1170735726703152179,
                        'hashtags'       => '',
                        'date'           => 1761057834,
                        'time'           => 1762152583,
                    ],
                    'image'      => [
                        21187876695735290 => [
                            'id'               => 1170736713874542118,
                            'post_id'          => 1170736713870348192,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACEXJo95wqwSXlp1UTW4znqaCgIniK5gAC68IxG-STaVeN24oqiKXEBQEAAwIAA3gAAzYE',
                            'file_unique_id'   => 'AQAD68IxG-STaVd9',
                            'file_size'        => 38741,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/E/21187876695735290.jpg',
                            'media_group_id'   => 1170735726703152179,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152583,
                            'origin_file_path' => '2025-11/03/image/E/21187876695735290.jpg',
                        ],
                    ],
                    'tele_files' => [
                        0 => [
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACEXJo95wqwSXlp1UTW4znqaCgIniK5gAC68IxG-STaVeN24oqiKXEBQEAAwIAA3gAAzYE',
                            'file_unique_id'   => 'AQAD68IxG-STaVd9',
                            'file_size'        => 38741,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                    ],
                ],
                1170736928073450560 => [
                    'post'       => [
                        'id'             => 1170736928073450560,
                        'type_id'        => 2,
                        'contents'       => '不羡鸳鸯不羡仙，羡慕胖哥每一天，推特胖哥的探花对象依旧是粉丝JK白丝超高颜值萝莉嫩妹， 肉弹坦克撞击反差萝莉。【七鹤2.0】#胖爷的奇妙之旅 #胖胖de奇妙旅行 #露脸 #萝莉 #洛丽塔 #JK #白丝 #传教士 #推特',
                        'media_group_id' => 14088464749622317,
                        'hashtags'       => '#胖爷的奇妙之旅,#胖胖de奇妙旅行,#露脸,#萝莉,#洛丽塔,#JK,#白丝,#传教士,#推特',
                        'date'           => 1761058093,
                        'time'           => 1762152635,
                    ],
                    'image'      => [
                        21187879726710715 => [
                            'id'               => 1170736928081841440,
                            'post_id'          => 1170736928073450560,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACEXNo950tLY6HUKt_w5vprDIpNMu_FwACtAxrG2x-sFdb3l2EP5QNGgEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADtAxrG2x-sFd-',
                            'file_size'        => 73988,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/3/21187879726710715.jpg',
                            'media_group_id'   => 14088464749622317,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152634,
                            'origin_file_path' => '2025-11/03/image/3/21187879726710715.jpg',
                        ],
                    ],
                    'video'      => [
                        21187927783601030 => [
                            'source' => [
                                'id'               => 1170736928081841441,
                                'post_id'          => 1170736928073450560,
                                'bot_id'           => 6026303590,
                                'file_id'          => 'BAACAgUAAx0CcaAwEgACEXRo950tUqhGrSmZhZarazj1bObl7QACIBsAAmx-sFdsy2ZnV6PTBjYE',
                                'file_unique_id'   => 'AgADIBsAAmx-sFc',
                                'file_size'        => 15561039,
                                'file_name'        => '七鹤胖哥粉色JK (2).mp4',
                                'path'             => '2025-11/03/video/A/21187927783601030/hls.m3u8',
                                'media_group_id'   => 14088464749622317,
                                'ext'              => 'm3u8',
                                'mime_type'        => 'application/x-mpegURL',
                                'media_type'       => 'video',
                                'origin_ext'       => 'mp4',
                                'origin_mime_type' => 'video/mp4',
                                'time'             => 1762152634,
                                'origin_file_path' => '2025-11/03/video/A/21187927783601030.mp4',
                            ],
                            'cover'  => [
                                0 => [
                                    'id'               => 1170742562302984982,
                                    'post_id'          => 1170736928073450560,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 48427,
                                    'file_name'        => 'cover-21187927783601030-1',
                                    'path'             => '2025-11/03/video/A/21187927783601030-1.jpg',
                                    'media_group_id'   => 14088464749622317,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153978,
                                    'origin_file_path' => '2025-11/03/video/A/21187927783601030-1.jpg',
                                ],
                                1 => [
                                    'id'               => 1170742566635701849,
                                    'post_id'          => 1170736928073450560,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 40675,
                                    'file_name'        => 'cover-21187927783601030-2',
                                    'path'             => '2025-11/03/video/A/21187927783601030-2.jpg',
                                    'media_group_id'   => 14088464749622317,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153979,
                                    'origin_file_path' => '2025-11/03/video/A/21187927783601030-2.jpg',
                                ],
                                2 => [
                                    'id'               => 1170742573896040720,
                                    'post_id'          => 1170736928073450560,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 41582,
                                    'file_name'        => 'cover-21187927783601030-3',
                                    'path'             => '2025-11/03/video/A/21187927783601030-3.jpg',
                                    'media_group_id'   => 14088464749622317,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153980,
                                    'origin_file_path' => '2025-11/03/video/A/21187927783601030-3.jpg',
                                ],
                                3 => [
                                    'id'               => 1170742582775384132,
                                    'post_id'          => 1170736928073450560,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 40154,
                                    'file_name'        => 'cover-21187927783601030-4',
                                    'path'             => '2025-11/03/video/A/21187927783601030-4.jpg',
                                    'media_group_id'   => 14088464749622317,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153983,
                                    'origin_file_path' => '2025-11/03/video/A/21187927783601030-4.jpg',
                                ],
                                4 => [
                                    'id'               => 1170742593282114215,
                                    'post_id'          => 1170736928073450560,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 36896,
                                    'file_name'        => 'cover-21187927783601030-5',
                                    'path'             => '2025-11/03/video/A/21187927783601030-5.jpg',
                                    'media_group_id'   => 14088464749622317,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153985,
                                    'origin_file_path' => '2025-11/03/video/A/21187927783601030-5.jpg',
                                ],
                                5 => [
                                    'id'               => 1170742610579423309,
                                    'post_id'          => 1170736928073450560,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 36114,
                                    'file_name'        => 'cover-21187927783601030-6',
                                    'path'             => '2025-11/03/video/A/21187927783601030-6.jpg',
                                    'media_group_id'   => 14088464749622317,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153989,
                                    'origin_file_path' => '2025-11/03/video/A/21187927783601030-6.jpg',
                                ],
                            ],
                        ],
                        21187927777308647 => [
                            'source' => [
                                'id'               => 1170736928081841442,
                                'post_id'          => 1170736928073450560,
                                'bot_id'           => 6026303590,
                                'file_id'          => 'BAACAgUAAx0CcaAwEgACEXVo950tZ8Vc_OJLdK9_owhPbaF2PQACMxsAAmx-sFdi-OB7a_l9CTYE',
                                'file_unique_id'   => 'AgADMxsAAmx-sFc',
                                'file_size'        => 349839287,
                                'file_name'        => '七鹤胖哥粉色JK.mp4',
                                'path'             => '2025-11/03/video/A/21187927777308647/hls.m3u8',
                                'media_group_id'   => 14088464749622317,
                                'ext'              => 'm3u8',
                                'mime_type'        => 'application/x-mpegURL',
                                'media_type'       => 'video',
                                'origin_ext'       => 'mp4',
                                'origin_mime_type' => 'video/mp4',
                                'time'             => 1762152634,
                                'origin_file_path' => '2025-11/03/video/A/21187927777308647.mp4',
                            ],
                            'cover'  => [
                                0 => [
                                    'id'               => 1170742612798213259,
                                    'post_id'          => 1170736928073450560,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 89943,
                                    'file_name'        => 'cover-21187927777308647-1',
                                    'path'             => '2025-11/03/video/A/21187927777308647-1.jpg',
                                    'media_group_id'   => 14088464749622317,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762153990,
                                    'origin_file_path' => '2025-11/03/video/A/21187927777308647-1.jpg',
                                ],
                                1 => [
                                    'id'               => 1170742683275104225,
                                    'post_id'          => 1170736928073450560,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 93876,
                                    'file_name'        => 'cover-21187927777308647-2',
                                    'path'             => '2025-11/03/video/A/21187927777308647-2.jpg',
                                    'media_group_id'   => 14088464749622317,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762154007,
                                    'origin_file_path' => '2025-11/03/video/A/21187927777308647-2.jpg',
                                ],
                                2 => [
                                    'id'               => 1170742828788089909,
                                    'post_id'          => 1170736928073450560,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 89548,
                                    'file_name'        => 'cover-21187927777308647-3',
                                    'path'             => '2025-11/03/video/A/21187927777308647-3.jpg',
                                    'media_group_id'   => 14088464749622317,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762154041,
                                    'origin_file_path' => '2025-11/03/video/A/21187927777308647-3.jpg',
                                ],
                                3 => [
                                    'id'               => 1170743047705593099,
                                    'post_id'          => 1170736928073450560,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 88470,
                                    'file_name'        => 'cover-21187927777308647-4',
                                    'path'             => '2025-11/03/video/A/21187927777308647-4.jpg',
                                    'media_group_id'   => 14088464749622317,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762154093,
                                    'origin_file_path' => '2025-11/03/video/A/21187927777308647-4.jpg',
                                ],
                                4 => [
                                    'id'               => 1170743337766880256,
                                    'post_id'          => 1170736928073450560,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 86457,
                                    'file_name'        => 'cover-21187927777308647-5',
                                    'path'             => '2025-11/03/video/A/21187927777308647-5.jpg',
                                    'media_group_id'   => 14088464749622317,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762154163,
                                    'origin_file_path' => '2025-11/03/video/A/21187927777308647-5.jpg',
                                ],
                                5 => [
                                    'id'               => 1170743706899189533,
                                    'post_id'          => 1170736928073450560,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 82878,
                                    'file_name'        => 'cover-21187927777308647-6',
                                    'path'             => '2025-11/03/video/A/21187927777308647-6.jpg',
                                    'media_group_id'   => 14088464749622317,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762154251,
                                    'origin_file_path' => '2025-11/03/video/A/21187927777308647-6.jpg',
                                ],
                                6 => [
                                    'id'               => 1170744173637144080,
                                    'post_id'          => 1170736928073450560,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 88385,
                                    'file_name'        => 'cover-21187927777308647-7',
                                    'path'             => '2025-11/03/video/A/21187927777308647-7.jpg',
                                    'media_group_id'   => 14088464749622317,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762154362,
                                    'origin_file_path' => '2025-11/03/video/A/21187927777308647-7.jpg',
                                ],
                                7 => [
                                    'id'               => 1170744756293077181,
                                    'post_id'          => 1170736928073450560,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 89621,
                                    'file_name'        => 'cover-21187927777308647-8',
                                    'path'             => '2025-11/03/video/A/21187927777308647-8.jpg',
                                    'media_group_id'   => 14088464749622317,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762154501,
                                    'origin_file_path' => '2025-11/03/video/A/21187927777308647-8.jpg',
                                ],
                                8 => [
                                    'id'               => 1170745302689255274,
                                    'post_id'          => 1170736928073450560,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 89880,
                                    'file_name'        => 'cover-21187927777308647-9',
                                    'path'             => '2025-11/03/video/A/21187927777308647-9.jpg',
                                    'media_group_id'   => 14088464749622317,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762154631,
                                    'origin_file_path' => '2025-11/03/video/A/21187927777308647-9.jpg',
                                ],
                            ],
                        ],
                    ],
                    'tele_files' => [
                        0 => [
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACEXNo950tLY6HUKt_w5vprDIpNMu_FwACtAxrG2x-sFdb3l2EP5QNGgEAAwIAA3kAAzYE',
                            'file_unique_id'   => 'AQADtAxrG2x-sFd-',
                            'file_size'        => 73988,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        1 => [
                            'file_id'          => 'BAACAgUAAx0CcaAwEgACEXRo950tUqhGrSmZhZarazj1bObl7QACIBsAAmx-sFdsy2ZnV6PTBjYE',
                            'file_unique_id'   => 'AgADIBsAAmx-sFc',
                            'file_size'        => 15561039,
                            'file_name'        => '七鹤胖哥粉色JK (2).mp4',
                            'origin_ext'       => 'mp4',
                            'origin_mime_type' => 'video/mp4',
                            'media_type'       => 'video',
                        ],
                        2 => [
                            'file_id'          => 'BAACAgUAAx0CcaAwEgACEXVo950tZ8Vc_OJLdK9_owhPbaF2PQACMxsAAmx-sFdi-OB7a_l9CTYE',
                            'file_unique_id'   => 'AgADMxsAAmx-sFc',
                            'file_size'        => 349839287,
                            'file_name'        => '七鹤胖哥粉色JK.mp4',
                            'origin_ext'       => 'mp4',
                            'origin_mime_type' => 'video/mp4',
                            'media_type'       => 'video',
                        ],
                    ],
                ],
                1170736967311164454 => [
                    'post'       => [
                        'id'             => 1170736967311164454,
                        'type_id'        => 2,
                        'contents'       => 'apk test',
                        'media_group_id' => 1170735725050595866,
                        'hashtags'       => '',
                        'date'           => 1761057202,
                        'time'           => 1762152644,
                    ],
                    'document'   => [
                        0 => [
                            'id'               => 1170736967311164690,
                            'post_id'          => 1170736967311164454,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'BQACAgUAAx0CcaAwEgACERho95my5jzhQVBT9Ght2FZ-Pd35nAAC5xEAAgzIUVTlzNFE5vh6gjYE',
                            'file_unique_id'   => 'AgAD5xEAAgzIUVQ',
                            'file_size'        => 284657539,
                            'file_name'        => 'okx-android.apk',
                            'path'             => '2025-11/03/application/7/21187936794926367.bin',
                            'media_group_id'   => 1170735725050595866,
                            'ext'              => 'bin',
                            'mime_type'        => 'application/octet-stream',
                            'media_type'       => 'document',
                            'origin_ext'       => 'bin',
                            'origin_mime_type' => 'application/octet-stream',
                            'time'             => 1762152644,
                            'origin_file_path' => '2025-11/03/application/7/21187936794926367.bin',
                        ],
                    ],
                    'tele_files' => [
                        0 => [
                            'file_id'          => 'BQACAgUAAx0CcaAwEgACERho95my5jzhQVBT9Ght2FZ-Pd35nAAC5xEAAgzIUVTlzNFE5vh6gjYE',
                            'file_unique_id'   => 'AgAD5xEAAgzIUVQ',
                            'file_size'        => 284657539,
                            'file_name'        => 'okx-android.apk',
                            'origin_ext'       => 'bin',
                            'origin_mime_type' => 'application/octet-stream',
                            'media_type'       => 'document',
                        ],
                    ],
                ],
                1170736979927631053 => [
                    'post'       => [
                        'id'             => 1170736979927631053,
                        'type_id'        => 2,
                        'contents'       => '9月11日，广东 茂名 镇盛镇一养殖场70余头 #鳄鱼 #暴雨 中出逃。9月14日已抓回36条，警察扛枪射击',
                        'media_group_id' => 14088457185706861,
                        'hashtags'       => '#鳄鱼,#暴雨',
                        'date'           => 1761057148,
                        'time'           => 1762152647,
                    ],
                    'image'      => [
                        21187825341594231 => [
                            'id'               => 1170736979927630477,
                            'post_id'          => 1170736979927631053,
                            'bot_id'           => 6026303590,
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACEQxo95l9YJwmny1gAWgAAarOsvUhvRgAAna3MRsW5yBUzvwESspiFmcBAAMCAAN5AAM2BA',
                            'file_unique_id'   => 'AQADdrcxGxbnIFR-',
                            'file_size'        => 267479,
                            'file_name'        => '',
                            'path'             => '2025-11/03/image/C/21187825341594231.jpg',
                            'media_group_id'   => 14088457185706861,
                            'ext'              => 'jpg',
                            'mime_type'        => 'image/jpeg',
                            'media_type'       => 'photo',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'time'             => 1762152647,
                            'origin_file_path' => '2025-11/03/image/C/21187825341594231.jpg',
                        ],
                    ],
                    'video'      => [
                        21187939810415197 => [
                            'source' => [
                                'id'               => 1170736979927630478,
                                'post_id'          => 1170736979927631053,
                                'bot_id'           => 6026303590,
                                'file_id'          => 'BAACAgUAAx0CcaAwEgACEQ1o95l9x_1eFeKoItgkSuWBfyjs8AACZQsAAuvYGFQrdVSnvaL8IDYE',
                                'file_unique_id'   => 'AgADZQsAAuvYGFQ',
                                'file_size'        => 2001725,
                                'file_name'        => 'IMG_3560.MP4',
                                'path'             => '2025-11/03/video/6/21187939810415197/hls.m3u8',
                                'media_group_id'   => 14088457185706861,
                                'ext'              => 'm3u8',
                                'mime_type'        => 'application/x-mpegURL',
                                'media_type'       => 'video',
                                'origin_ext'       => 'mp4',
                                'origin_mime_type' => 'video/mp4',
                                'time'             => 1762152647,
                                'origin_file_path' => '2025-11/03/video/6/21187939810415197.mp4',
                            ],
                            'cover'  => [
                                0 => [
                                    'id'               => 1170745303205152664,
                                    'post_id'          => 1170736979927631053,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 41864,
                                    'file_name'        => 'cover-21187939810415197-1',
                                    'path'             => '2025-11/03/video/6/21187939810415197-1.jpg',
                                    'media_group_id'   => 14088457185706861,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762154631,
                                    'origin_file_path' => '2025-11/03/video/6/21187939810415197-1.jpg',
                                ],
                                1 => [
                                    'id'               => 1170745304115315138,
                                    'post_id'          => 1170736979927631053,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 42563,
                                    'file_name'        => 'cover-21187939810415197-2',
                                    'path'             => '2025-11/03/video/6/21187939810415197-2.jpg',
                                    'media_group_id'   => 14088457185706861,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762154631,
                                    'origin_file_path' => '2025-11/03/video/6/21187939810415197-2.jpg',
                                ],
                                2 => [
                                    'id'               => 1170745306166330320,
                                    'post_id'          => 1170736979927631053,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 37619,
                                    'file_name'        => 'cover-21187939810415197-3',
                                    'path'             => '2025-11/03/video/6/21187939810415197-3.jpg',
                                    'media_group_id'   => 14088457185706861,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762154632,
                                    'origin_file_path' => '2025-11/03/video/6/21187939810415197-3.jpg',
                                ],
                            ],
                        ],
                        21187939801356116 => [
                            'source' => [
                                'id'               => 1170736979927630479,
                                'post_id'          => 1170736979927631053,
                                'bot_id'           => 6026303590,
                                'file_id'          => 'BAACAgUAAx0CcaAwEgACEQ5o95l9rTQrT_AoWPXd9mfDGKKuVAAC7QoAAhbnIFSSO3NJuiifzzYE',
                                'file_unique_id'   => 'AgAD7QoAAhbnIFQ',
                                'file_size'        => 10375885,
                                'file_name'        => 'IMG_3564.MP4',
                                'path'             => '2025-11/03/video/1/21187939801356116/hls.m3u8',
                                'media_group_id'   => 14088457185706861,
                                'ext'              => 'm3u8',
                                'mime_type'        => 'application/x-mpegURL',
                                'media_type'       => 'video',
                                'origin_ext'       => 'mp4',
                                'origin_mime_type' => 'video/mp4',
                                'time'             => 1762152647,
                                'origin_file_path' => '2025-11/03/video/1/21187939801356116.mp4',
                            ],
                            'cover'  => [
                                0 => [
                                    'id'               => 1170745306686424292,
                                    'post_id'          => 1170736979927631053,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 19880,
                                    'file_name'        => 'cover-21187939801356116-1',
                                    'path'             => '2025-11/03/video/1/21187939801356116-1.jpg',
                                    'media_group_id'   => 14088457185706861,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762154632,
                                    'origin_file_path' => '2025-11/03/video/1/21187939801356116-1.jpg',
                                ],
                                1 => [
                                    'id'               => 1170745308540307461,
                                    'post_id'          => 1170736979927631053,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 16913,
                                    'file_name'        => 'cover-21187939801356116-2',
                                    'path'             => '2025-11/03/video/1/21187939801356116-2.jpg',
                                    'media_group_id'   => 14088457185706861,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762154632,
                                    'origin_file_path' => '2025-11/03/video/1/21187939801356116-2.jpg',
                                ],
                                2 => [
                                    'id'               => 1170745311941889438,
                                    'post_id'          => 1170736979927631053,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 28873,
                                    'file_name'        => 'cover-21187939801356116-3',
                                    'path'             => '2025-11/03/video/1/21187939801356116-3.jpg',
                                    'media_group_id'   => 14088457185706861,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762154633,
                                    'origin_file_path' => '2025-11/03/video/1/21187939801356116-3.jpg',
                                ],
                                3 => [
                                    'id'               => 1170745316882776998,
                                    'post_id'          => 1170736979927631053,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 22981,
                                    'file_name'        => 'cover-21187939801356116-4',
                                    'path'             => '2025-11/03/video/1/21187939801356116-4.jpg',
                                    'media_group_id'   => 14088457185706861,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762154634,
                                    'origin_file_path' => '2025-11/03/video/1/21187939801356116-4.jpg',
                                ],
                                4 => [
                                    'id'               => 1170745323304259547,
                                    'post_id'          => 1170736979927631053,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 17920,
                                    'file_name'        => 'cover-21187939801356116-5',
                                    'path'             => '2025-11/03/video/1/21187939801356116-5.jpg',
                                    'media_group_id'   => 14088457185706861,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762154636,
                                    'origin_file_path' => '2025-11/03/video/1/21187939801356116-5.jpg',
                                ],
                                5 => [
                                    'id'               => 1170745333521580104,
                                    'post_id'          => 1170736979927631053,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 23654,
                                    'file_name'        => 'cover-21187939801356116-6',
                                    'path'             => '2025-11/03/video/1/21187939801356116-6.jpg',
                                    'media_group_id'   => 14088457185706861,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762154638,
                                    'origin_file_path' => '2025-11/03/video/1/21187939801356116-6.jpg',
                                ],
                            ],
                        ],
                        21187939806138066 => [
                            'source' => [
                                'id'               => 1170736979927630480,
                                'post_id'          => 1170736979927631053,
                                'bot_id'           => 6026303590,
                                'file_id'          => 'BAACAgUAAx0CcaAwEgACEQ9o95l9YSPkipBtgqT0JF6FqCZ_UQACZgsAAuvYGFS6EEIMae85AjYE',
                                'file_unique_id'   => 'AgADZgsAAuvYGFQ',
                                'file_size'        => 10476719,
                                'file_name'        => 'IMG_3556.MP4',
                                'path'             => '2025-11/03/video/E/21187939806138066/hls.m3u8',
                                'media_group_id'   => 14088457185706861,
                                'ext'              => 'm3u8',
                                'mime_type'        => 'application/x-mpegURL',
                                'media_type'       => 'video',
                                'origin_ext'       => 'mp4',
                                'origin_mime_type' => 'video/mp4',
                                'time'             => 1762152647,
                                'origin_file_path' => '2025-11/03/video/E/21187939806138066.mp4',
                            ],
                            'cover'  => [
                                0 => [
                                    'id'               => 1170745334083618417,
                                    'post_id'          => 1170736979927631053,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 16210,
                                    'file_name'        => 'cover-21187939806138066-1',
                                    'path'             => '2025-11/03/video/E/21187939806138066-1.jpg',
                                    'media_group_id'   => 14088457185706861,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762154639,
                                    'origin_file_path' => '2025-11/03/video/E/21187939806138066-1.jpg',
                                ],
                                1 => [
                                    'id'               => 1170745336063331050,
                                    'post_id'          => 1170736979927631053,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 11798,
                                    'file_name'        => 'cover-21187939806138066-2',
                                    'path'             => '2025-11/03/video/E/21187939806138066-2.jpg',
                                    'media_group_id'   => 14088457185706861,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762154639,
                                    'origin_file_path' => '2025-11/03/video/E/21187939806138066-2.jpg',
                                ],
                                2 => [
                                    'id'               => 1170745339339083304,
                                    'post_id'          => 1170736979927631053,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 26035,
                                    'file_name'        => 'cover-21187939806138066-3',
                                    'path'             => '2025-11/03/video/E/21187939806138066-3.jpg',
                                    'media_group_id'   => 14088457185706861,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762154640,
                                    'origin_file_path' => '2025-11/03/video/E/21187939806138066-3.jpg',
                                ],
                                3 => [
                                    'id'               => 1170745343898292124,
                                    'post_id'          => 1170736979927631053,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 27648,
                                    'file_name'        => 'cover-21187939806138066-4',
                                    'path'             => '2025-11/03/video/E/21187939806138066-4.jpg',
                                    'media_group_id'   => 14088457185706861,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762154641,
                                    'origin_file_path' => '2025-11/03/video/E/21187939806138066-4.jpg',
                                ],
                                4 => [
                                    'id'               => 1170745350122637845,
                                    'post_id'          => 1170736979927631053,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 15015,
                                    'file_name'        => 'cover-21187939806138066-5',
                                    'path'             => '2025-11/03/video/E/21187939806138066-5.jpg',
                                    'media_group_id'   => 14088457185706861,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762154642,
                                    'origin_file_path' => '2025-11/03/video/E/21187939806138066-5.jpg',
                                ],
                                5 => [
                                    'id'               => 1170745358817429916,
                                    'post_id'          => 1170736979927631053,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 22033,
                                    'file_name'        => 'cover-21187939806138066-6',
                                    'path'             => '2025-11/03/video/E/21187939806138066-6.jpg',
                                    'media_group_id'   => 14088457185706861,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762154644,
                                    'origin_file_path' => '2025-11/03/video/E/21187939806138066-6.jpg',
                                ],
                            ],
                        ],
                        21187828354440475 => [
                            'source' => [
                                'id'               => 1170736979927630481,
                                'post_id'          => 1170736979927631053,
                                'bot_id'           => 6026303590,
                                'file_id'          => 'BAACAgUAAx0CcaAwEgACERBo95l9-KEUPz8LwzVm47sMjwYQEgAC7goAAhbnIFSphjlOCzIb1zYE',
                                'file_unique_id'   => 'AgAD7goAAhbnIFQ',
                                'file_size'        => 4793509,
                                'file_name'        => 'IMG_3557.MP4',
                                'path'             => '2025-11/03/video/4/21187828354440475/hls.m3u8',
                                'media_group_id'   => 14088457185706861,
                                'ext'              => 'm3u8',
                                'mime_type'        => 'application/x-mpegURL',
                                'media_type'       => 'video',
                                'origin_ext'       => 'mp4',
                                'origin_mime_type' => 'video/mp4',
                                'time'             => 1762152647,
                                'origin_file_path' => '2025-11/03/video/4/21187828354440475.mp4',
                            ],
                            'cover'  => [
                                0 => [
                                    'id'               => 1170745359572403916,
                                    'post_id'          => 1170736979927631053,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 31548,
                                    'file_name'        => 'cover-21187828354440475-1',
                                    'path'             => '2025-11/03/video/4/21187828354440475-1.jpg',
                                    'media_group_id'   => 14088457185706861,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762154645,
                                    'origin_file_path' => '2025-11/03/video/4/21187828354440475-1.jpg',
                                ],
                                1 => [
                                    'id'               => 1170745361300455719,
                                    'post_id'          => 1170736979927631053,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 35521,
                                    'file_name'        => 'cover-21187828354440475-2',
                                    'path'             => '2025-11/03/video/4/21187828354440475-2.jpg',
                                    'media_group_id'   => 14088457185706861,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762154645,
                                    'origin_file_path' => '2025-11/03/video/4/21187828354440475-2.jpg',
                                ],
                                2 => [
                                    'id'               => 1170745365834499365,
                                    'post_id'          => 1170736979927631053,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 32265,
                                    'file_name'        => 'cover-21187828354440475-3',
                                    'path'             => '2025-11/03/video/4/21187828354440475-3.jpg',
                                    'media_group_id'   => 14088457185706861,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762154646,
                                    'origin_file_path' => '2025-11/03/video/4/21187828354440475-3.jpg',
                                ],
                            ],
                        ],
                        21187828346995678 => [
                            'source' => [
                                'id'               => 1170736979931826719,
                                'post_id'          => 1170736979927631053,
                                'bot_id'           => 6026303590,
                                'file_id'          => 'BAACAgUAAx0CcaAwEgACERFo95l9sQJ6Kb6rOZa67_mrd3XpUAAC8AoAAhbnIFQ477-eWFEhqTYE',
                                'file_unique_id'   => 'AgAD8AoAAhbnIFQ',
                                'file_size'        => 2982174,
                                'file_name'        => 'IMG_3562.MP4',
                                'path'             => '2025-11/03/video/2/21187828346995678/hls.m3u8',
                                'media_group_id'   => 14088457185706861,
                                'ext'              => 'm3u8',
                                'mime_type'        => 'application/x-mpegURL',
                                'media_type'       => 'video',
                                'origin_ext'       => 'mp4',
                                'origin_mime_type' => 'video/mp4',
                                'time'             => 1762152647,
                                'origin_file_path' => '2025-11/03/video/2/21187828346995678.mp4',
                            ],
                            'cover'  => [
                                0 => [
                                    'id'               => 1170745366392340761,
                                    'post_id'          => 1170736979927631053,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 21290,
                                    'file_name'        => 'cover-21187828346995678-1',
                                    'path'             => '2025-11/03/video/2/21187828346995678-1.jpg',
                                    'media_group_id'   => 14088457185706861,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762154646,
                                    'origin_file_path' => '2025-11/03/video/2/21187828346995678-1.jpg',
                                ],
                                1 => [
                                    'id'               => 1170745367583523056,
                                    'post_id'          => 1170736979927631053,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 28190,
                                    'file_name'        => 'cover-21187828346995678-2',
                                    'path'             => '2025-11/03/video/2/21187828346995678-2.jpg',
                                    'media_group_id'   => 14088457185706861,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762154647,
                                    'origin_file_path' => '2025-11/03/video/2/21187828346995678-2.jpg',
                                ],
                                2 => [
                                    'id'               => 1170745370519539151,
                                    'post_id'          => 1170736979927631053,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 19292,
                                    'file_name'        => 'cover-21187828346995678-3',
                                    'path'             => '2025-11/03/video/2/21187828346995678-3.jpg',
                                    'media_group_id'   => 14088457185706861,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762154647,
                                    'origin_file_path' => '2025-11/03/video/2/21187828346995678-3.jpg',
                                ],
                            ],
                        ],
                        21187828350696360 => [
                            'source' => [
                                'id'               => 1170736979931826720,
                                'post_id'          => 1170736979927631053,
                                'bot_id'           => 6026303590,
                                'file_id'          => 'BAACAgUAAx0CcaAwEgACERJo95l9YhZ5mZwXc6zdacL6U21zfAAC8QoAAhbnIFQ3fls2JHDzZTYE',
                                'file_unique_id'   => 'AgAD8QoAAhbnIFQ',
                                'file_size'        => 2553996,
                                'file_name'        => 'IMG_3566.MP4',
                                'path'             => '2025-11/03/video/8/21187828350696360/hls.m3u8',
                                'media_group_id'   => 14088457185706861,
                                'ext'              => 'm3u8',
                                'mime_type'        => 'application/x-mpegURL',
                                'media_type'       => 'video',
                                'origin_ext'       => 'mp4',
                                'origin_mime_type' => 'video/mp4',
                                'time'             => 1762152647,
                                'origin_file_path' => '2025-11/03/video/8/21187828350696360.mp4',
                            ],
                            'cover'  => [
                                0 => [
                                    'id'               => 1170745371253541911,
                                    'post_id'          => 1170736979927631053,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 43079,
                                    'file_name'        => 'cover-21187828350696360-1',
                                    'path'             => '2025-11/03/video/8/21187828350696360-1.jpg',
                                    'media_group_id'   => 14088457185706861,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762154647,
                                    'origin_file_path' => '2025-11/03/video/8/21187828350696360-1.jpg',
                                ],
                                1 => [
                                    'id'               => 1170745372348252858,
                                    'post_id'          => 1170736979927631053,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 44147,
                                    'file_name'        => 'cover-21187828350696360-2',
                                    'path'             => '2025-11/03/video/8/21187828350696360-2.jpg',
                                    'media_group_id'   => 14088457185706861,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762154648,
                                    'origin_file_path' => '2025-11/03/video/8/21187828350696360-2.jpg',
                                ],
                                2 => [
                                    'id'               => 1170745374810308967,
                                    'post_id'          => 1170736979927631053,
                                    'bot_id'           => 0,
                                    'file_id'          => '',
                                    'file_unique_id'   => '',
                                    'file_size'        => 36578,
                                    'file_name'        => 'cover-21187828350696360-3',
                                    'path'             => '2025-11/03/video/8/21187828350696360-3.jpg',
                                    'media_group_id'   => 14088457185706861,
                                    'ext'              => 'jpg',
                                    'mime_type'        => 'image/jpeg',
                                    'media_type'       => '',
                                    'origin_ext'       => 'jpg',
                                    'origin_mime_type' => 'image/jpeg',
                                    'time'             => 1762154648,
                                    'origin_file_path' => '2025-11/03/video/8/21187828350696360-3.jpg',
                                ],
                            ],
                        ],
                    ],
                    'tele_files' => [
                        0 => [
                            'file_id'          => 'AgACAgUAAx0CcaAwEgACEQxo95l9YJwmny1gAWgAAarOsvUhvRgAAna3MRsW5yBUzvwESspiFmcBAAMCAAN5AAM2BA',
                            'file_unique_id'   => 'AQADdrcxGxbnIFR-',
                            'file_size'        => 267479,
                            'file_name'        => '',
                            'origin_ext'       => 'jpg',
                            'origin_mime_type' => 'image/jpeg',
                            'media_type'       => 'photo',
                        ],
                        1 => [
                            'file_id'          => 'BAACAgUAAx0CcaAwEgACEQ1o95l9x_1eFeKoItgkSuWBfyjs8AACZQsAAuvYGFQrdVSnvaL8IDYE',
                            'file_unique_id'   => 'AgADZQsAAuvYGFQ',
                            'file_size'        => 2001725,
                            'file_name'        => 'IMG_3560.MP4',
                            'origin_ext'       => 'mp4',
                            'origin_mime_type' => 'video/mp4',
                            'media_type'       => 'video',
                        ],
                        2 => [
                            'file_id'          => 'BAACAgUAAx0CcaAwEgACEQ5o95l9rTQrT_AoWPXd9mfDGKKuVAAC7QoAAhbnIFSSO3NJuiifzzYE',
                            'file_unique_id'   => 'AgAD7QoAAhbnIFQ',
                            'file_size'        => 10375885,
                            'file_name'        => 'IMG_3564.MP4',
                            'origin_ext'       => 'mp4',
                            'origin_mime_type' => 'video/mp4',
                            'media_type'       => 'video',
                        ],
                        3 => [
                            'file_id'          => 'BAACAgUAAx0CcaAwEgACEQ9o95l9YSPkipBtgqT0JF6FqCZ_UQACZgsAAuvYGFS6EEIMae85AjYE',
                            'file_unique_id'   => 'AgADZgsAAuvYGFQ',
                            'file_size'        => 10476719,
                            'file_name'        => 'IMG_3556.MP4',
                            'origin_ext'       => 'mp4',
                            'origin_mime_type' => 'video/mp4',
                            'media_type'       => 'video',
                        ],
                        4 => [
                            'file_id'          => 'BAACAgUAAx0CcaAwEgACERBo95l9-KEUPz8LwzVm47sMjwYQEgAC7goAAhbnIFSphjlOCzIb1zYE',
                            'file_unique_id'   => 'AgAD7goAAhbnIFQ',
                            'file_size'        => 4793509,
                            'file_name'        => 'IMG_3557.MP4',
                            'origin_ext'       => 'mp4',
                            'origin_mime_type' => 'video/mp4',
                            'media_type'       => 'video',
                        ],
                        5 => [
                            'file_id'          => 'BAACAgUAAx0CcaAwEgACERFo95l9sQJ6Kb6rOZa67_mrd3XpUAAC8AoAAhbnIFQ477-eWFEhqTYE',
                            'file_unique_id'   => 'AgAD8AoAAhbnIFQ',
                            'file_size'        => 2982174,
                            'file_name'        => 'IMG_3562.MP4',
                            'origin_ext'       => 'mp4',
                            'origin_mime_type' => 'video/mp4',
                            'media_type'       => 'video',
                        ],
                        6 => [
                            'file_id'          => 'BAACAgUAAx0CcaAwEgACERJo95l9YhZ5mZwXc6zdacL6U21zfAAC8QoAAhbnIFQ3fls2JHDzZTYE',
                            'file_unique_id'   => 'AgAD8QoAAhbnIFQ',
                            'file_size'        => 2553996,
                            'file_name'        => 'IMG_3566.MP4',
                            'origin_ext'       => 'mp4',
                            'origin_mime_type' => 'video/mp4',
                            'media_type'       => 'video',
                        ],
                    ],
                ],
            ];

            $posts = $this->postManager->tgMedia->getPostByContinuePostId($lastPost, 1000);

            foreach ($posts as $postId => $post)
            {
                $typeId  = $post['post']['type_id'];
                $content = $post['post']['contents'];

                $content = preg_replace_callback('#{<([^><]+)><([^><]+)>}#iu', function($matches) {
                    $text = $matches[1];
                    $url  = $matches[2];

                    // 对 URL 进行 HTML 转义
                    $encodedUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');

                    // 返回带有 <a> 标签的字符串
                    return Tag::a($encodedUrl, $text);

                }, $content);


                if (is_callable($this->contentsAdv))
                {
                    $content = call_user_func_array($this->contentsAdv, [
                        $content,
                    ]);
                }

                if (!isset($this->typeMap[$typeId]))
                {
                    $this->logInfo($postId . ':未定义的分类 -> ' . $content);
                    continue;
                }

                $groupToSendId = $this->typeMap[$typeId];
                $mediaGroups   = [];

                if (isset($post['tele_files']) && count($post['tele_files']))
                {
                    foreach ($post['tele_files'] as $k => $fileInfo)
                    {
                        $mediaGroups[] = static::mediaGroup($fileInfo['file_id'], $fileInfo[$fileTable->getMediaTypeField()], !$k ? $content : '', 'HTML');
                    }
                }

                if (count($mediaGroups) > 0)
                {
                    $method      = 'sendMediaGroup';
                    $sendOptions = [
                        "chat_id" => $groupToSendId,
                        "media"   => json_encode($mediaGroups, JSON_UNESCAPED_UNICODE),
                    ];
                }
                elseif ($content)
                {
                    $method      = 'sendMessage';
                    $sendOptions = [
                        "chat_id"    => $groupToSendId,
                        "text"       => $content,
                        "parse_mode" => 'html',
                    ];
                }
                else
                {
                    $msg = '消息没有内容: ' . $postId;
                    $this->logInfo($msg);
                    continue;
                }

                $endPoint = $this->postManager->tgMedia->resolveEndponit($method);

                $successCallback = function(string $contents, Downloader $_this, ResponseInterface $response, $index) use ($postId, $content) {

                    $requestInfo = $_this->getRequestInfoByIndex($index);
                    $text        = $content;

                    $resultArray = json_decode($contents, 1);

                    if ($resultArray['ok'] !== false)
                    {
                        $this->putContinuePostId($postId);

                        $msg = '【O】发送成功: ' . trim(Utils::inlineText(Utils::truncateUtf8String($text, 40)));
                        $this->logInfo($msg);
                    }
                    else
                    {
                        $msg = '【x】发送错误: ' . $resultArray['description'] . ' -- ' . trim(Utils::inlineText(Utils::truncateUtf8String($text, 40)));
                        $this->logInfo($msg);
                    }

                    $this->waitSeconds($this->messageDelay);
                };

                $errorCallback = function(RequestException $e, Downloader $_this, $index) use ($postId, $content) {

                    $requestInfo = $_this->getRequestInfoByIndex($index);
                    $text        = $content;
                    $msg         = '【x】RequestException 出错: ' . trim(Utils::inlineText(Utils::truncateUtf8String($text, 40))) . ' -- ' . $e->getMessage();

                    $this->logInfo($msg);

                    $this->waitSeconds($this->messageDelay);
                };

                $this->doPost($endPoint, $sendOptions, $successCallback, $errorCallback);
            }
        }

        private function waitSeconds(int $seconds): void
        {
            $sec = $seconds;
            while ($sec > 0)
            {
                $this->logInfo("延时 $sec/$seconds S");
                sleep(1);
                $sec--;
            }
        }

        private function putContinuePostId($postId): void
        {
            $file = $this->makeContinuePostIdFileName();
            is_dir($this->postIdcachePath) or mkdir($this->postIdcachePath, 0777, true);
            file_put_contents($file, $postId);
        }

        public function getContinuePostId(): bool|string|null
        {
            $file = $this->makeContinuePostIdFileName();

            if (is_file($file))
            {
                return file_get_contents($file);
            }

            return null;
        }

        private function doPost(string $endPoint, array $postData = [], callable $successCallback = null, callable $errorCallback = null): void
        {
            $ins = Downloader::ins();

            $ins->setRetryTimes($this->retryTimes);

            $ins->setEnableCache(false);

            $ins->setSuccessCallback($successCallback);

            $ins->setErrorCallback($errorCallback);

            $ins->addBatchRequest($endPoint, 'post', [
                "form_params"                       => $postData,
                \GuzzleHttp\RequestOptions::HEADERS => [
                    //'Content-Type'     => 'application/x-www-form-urlencoded' ,
                    //'Accept'           => 'application/json, text/javascript' ,
                    //'X-Requested-With' => 'X-Requested-With: XMLHttpRequest' ,
                    'Accept-Encoding' => 'gzip, deflate, br',
                    'Accept-Language' => 'en,zh-CN;q=0.9,zh;q=0.8',
                    'User-Agent'      => 'Mozilla / 5.0 (Windows NT 10.0; Win64; x64) AppleWebKit / 537.36 (KHTML, like Gecko) Chrome / 75.0.3770.142 Safari / 537.36',
                ],
            ]);

            $ins->send();
        }

        private function makeContinuePostIdFileName(): string
        {
            return rtrim($this->postIdcachePath, '/\\') . DIRECTORY_SEPARATOR . 'continuePostId.txt';
        }

        private static function mediaGroup($media, string $type, string $caption = '', string $parseMode = 'HTML'): array
        {
            return ([
                "type"       => $type,
                "media"      => $media,
                "caption"    => $caption,
                "parse_mode" => $parseMode,
            ]);
        }
    }
