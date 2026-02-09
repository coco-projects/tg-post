<?php

    namespace Coco\tgPost\posts;

    use Coco\tgMedia\Utils;
    use Coco\tgPost\PostManager;

    use Coco\wp\Manager;
    use Coco\wp\Tag;
    use Coco\wp\WpTag;

    class WpPost extends PostAbstract
    {
        const  FILE_IMAGE    = 1;
        const  FILE_VIDEO    = 2;
        const  FILE_DOCUMENT = 3;

        public Manager $wpManager;
        public         $contentsAdv;

        public function __construct(PostManager $postManager)
        {
            parent::__construct($postManager);
        }

        public function setContentsAdv(callable $contentsAdv): static
        {
            $this->contentsAdv = $contentsAdv;

            return $this;
        }

        /**
         * 初始化这个实例后在回调中返回 \Coco\wp\Manager
         *
         * @param callable|null $callback
         *
         * @return $this
         */
        public function initHandler(?callable $callback = null): static
        {
            $this->wpManager = call_user_func($callback, $this);

            $this->wpManager->setEnableRedisLog($this->postManager->enableRedisLog);
            $this->wpManager->setEnableEchoLog($this->postManager->enableEchoLog);
            $this->wpManager->setRedisConfig($this->postManager->redisHost, $this->postManager->redisPassword, $this->postManager->redisPort, $this->postManager->redisDb);
            $this->wpManager->initServer();
            $this->wpManager->initTableStruct();

            $this->setStandardLogger(__CLASS__);
            if ($this->postManager->enableRedisLog)
            {
                $this->addRedisHandler(redisHost: $this->postManager->redisHost, redisPort: $this->postManager->redisPort, password: $this->postManager->redisPassword, db: $this->postManager->redisDb, logName: __CLASS__ . '-log', callback: static::getStandardFormatter());
            }

            if ($this->postManager->enableEchoLog)
            {
                $this->addStdoutHandler(static::getStandardFormatter());
            }

            return $this;
        }

        public function updateWpPost(bool $insertOnly = false, $row = 1000): void
        {
            $postTab   = $this->postManager->tgMedia->getPostTable();
            $wpPostTab = $this->wpManager->getPostsTable();

            //获取 post表中所有的postId
            $postIds = $postTab->tableIns()->order($postTab->getPkField())->column($postTab->getPkField());

            //获取 wp post 表中类型为post，所有的 guid，正则匹配出所有的postId
            $wpIds = $wpPostTab->tableIns()->where([
                [
                    $wpPostTab->getGuidField(),
                    'regexp',
                    '^[0-9]{18,20}$',
                ],
            ])->order($wpPostTab->getGuidField())->column($wpPostTab->getGuidField());

            //对比两组postId，计算出wpPost中，分别要删除，添加，更新的文章执行
            $arrs = static::compareArrays($postIds, $wpIds);

            /*
             * ------------------------------
             * 待新增
             * ------------------------------
             *
             * **/

            $posts = $this->postManager->tgMedia->getPostByPostIds($arrs['toInsertWp'], row: $row);

            $this->logInfo('创建文章个数: ' . count($posts));
            foreach ($posts as $post)
            {
                $postInfo = $this->makePostContentByPostInfo($post);

                $title    = $postInfo['title'];
                $contents = $postInfo['content'];
                $postId   = $post['post'][$postTab->getPkField()];

                $this->logInfo('创建文章: ' . $title);
                $wpPostId = $this->wpManager->addPost($title, $contents, $post['post'][$postTab->getTypeIdField()], $postId);

                if ($post['post'][$postTab->getHashtagsField()])
                {
                    // 添加tag
                    $tags         = explode(',', $post['post'][$postTab->getHashtagsField()]);
                    $tagsToInsert = [];

                    foreach ($tags as $tag)
                    {
                        if (mb_strlen($tag) > 1)
                        {
                            $tagsToInsert[] = trim($tag, '#');
                        }
                    }

                    if (count($tagsToInsert))
                    {
                        $tagIds = $this->wpManager->addTags($tagsToInsert);
                        if (count($tagIds))
                        {
                            $this->wpManager->importPostTerm($wpPostId, $tagIds);
                        }
                    }
                }
            }

            if (!$insertOnly)
            {
                /*
                 * ------------------------------
                 * 待更新
                 * ------------------------------
                 *
                 * **/
                $posts = $this->postManager->tgMedia->getPostByPostIds($arrs['toUpdateWp'], row: $row);

                $this->logInfo('更新文章个数: ' . count($posts));
                foreach ($posts as $post)
                {
                    $postInfo = $this->makePostContentByPostInfo($post);

                    $title    = $postInfo['title'];
                    $contents = $postInfo['content'];
                    $postId   = $post['post'][$postTab->getPkField()];

                    $this->logInfo('更新文章: ' . $title);
                    $this->logInfo('内容: ' . $contents);
                    $this->wpManager->updatePostContentByGuid($postId, $title, $contents);
                }

                /*
                 * ------------------------------
                 * 待删除
                 * ------------------------------
                 *
                 * **/

                $this->logInfo('删除文章个数: ' . count($arrs['toDeleteWp']));
                $this->wpManager->deletePostByGuid($arrs['toDeleteWp']);
            }

            /*
             * ------------------------------
             * 更新一些信息
             * ------------------------------
             *
             * **/
            $this->wpManager->updateTagsCount();

            $this->logInfo( '1000条信息处理完成');

        }

        public function makePostTitle(string $postContent): string
        {
            $postContent = preg_replace_callback('#{<([^><]+)><([^><]+)>}#iu', function($matches) {
                $text = $matches[1];

                return $text;

            }, $postContent);

            $title = trim(Utils::inlineText(Utils::truncateUtf8String($postContent, 40)));
            if (!$title)
            {
                $title = 'Untitled-' . hrtime(true);
            }

            return $title;
        }

        protected function makePostContentByPostInfo($postArray): array
        {
            $fileTab   = $this->postManager->tgMedia->getFileTable();
            $postTab   = $this->postManager->tgMedia->getPostTable();
            $wpPostTab = $this->wpManager->getPostsTable();

            $contents = [];

            if (is_callable($this->contentsAdv))
            {
                call_user_func_array($this->contentsAdv, [
                    &$contents,
                ]);
            }

            $contentsImg      = [];
            $contentsVideo    = [];
            $contentsDocument = [];
            $contentsMusic    = [];

            $post = $postArray['post'];
            $text = $post[$postTab->getContentsField()];

            $title = static::makePostTitle($text);

            $postId = $post[$postTab->getPkField()];

            //{<xxx><http://xxx.xx>} 这种情况，xxx里有换行的先把换行替换掉
            $text = preg_replace_callback('#{<([^><]+)><([^><]+)>}#iu', function($matches) {
                $text = $matches[1];
                $url  = $matches[2];

                $text = preg_replace("#[\r\n]+#", " ", $text);

                // 返回带有 <a> 标签的字符串
                return "{<$text><$url>}";

            }, $text);

            $lines = preg_split('#[\r\n]+#iu', $text, -1, PREG_SPLIT_NO_EMPTY);

            foreach ($lines as $k => $line)
            {
                $line = trim($line);

                $line = preg_replace_callback('#{<([^><]+)><([^><]+)>}#iu', function($matches) {
                    $text = $matches[1];
                    $url  = $matches[2];

                    // 对 URL 进行 HTML 转义
                    $encodedUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');

                    // 返回带有 <a> 标签的字符串
                    return Tag::a($encodedUrl, $text);

                }, $line);

                $line = preg_replace_callback('#(?<!href=")https?://\S+#iu', function($matches) {
                    $url = $matches[0];

                    // 对 URL 进行 HTML 转义
                    $encodedUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');

                    // 返回带有 <a> 标签的字符串
                    return Tag::a($encodedUrl, $encodedUrl);

                }, $line);

                $contents[] = WpTag::p($line);
            }

            if (isset($postArray['document']))
            {
                foreach ($postArray['document'] as $k => $file)
                {
                    $uri = $this->postManager->makeMediaUrl($file[$fileTab->getPathField()]);

                    $fileName = $file[$fileTab->getFileNameField()];
                    $fileName = $fileName ? $fileName : $uri;

                    $contentsDocument[] = WpTag::p([
                        Tag::a($uri, '附件下载: ' . $fileName),
                    ]);
                }
            }

            if (isset($postArray['music']))
            {
                foreach ($postArray['music'] as $k => $file)
                {
                    $uri = $this->postManager->makeMediaUrl($file[$fileTab->getPathField()]);

                    $contentsMusic[] = WpTag::audio($uri);
                    $contentsMusic[] = WpTag::hr();
                }
            }

            if (isset($postArray['image']))
            {
                foreach ($postArray['image'] as $k => $file)
                {
                    $uri = $this->postManager->makeMediaUrl($file[$fileTab->getPathField()]);

                    $contentsImg[] = WpTag::image($uri);
                    $contentsImg[] = WpTag::hr();
                }
            }

            if (isset($postArray['video']))
            {
                foreach ($postArray['video'] as $k => $fileSource)
                {
                    $file = $fileSource['source'];
                    $uri  = $this->postManager->makeMediaUrl($file[$fileTab->getPathField()]);

                    $covers = [];
                    foreach ($fileSource['cover'] as $cover)
                    {
                        $covers[] = ["src" => $this->postManager->makeMediaUrl($cover[$fileTab->getPathField()]),];
                    }

                    $pic = '';
                    if (count($covers))
                    {
                        $pic = $covers[0]['src'];
                    }

                    $contentsVideo[] = WpTag::gallery($covers);
                    $contentsVideo[] = WpTag::dPlayer($uri, pic: $pic);
                    $contentsVideo[] = WpTag::hr();
                }
            }

            foreach ($contentsDocument as $k => $v)
            {
                $contents[] = $v;
            }

            foreach ($contentsMusic as $k => $v)
            {
                $contents[] = $v;
            }

            foreach ($contentsImg as $k => $v)
            {
                $contents[] = $v;
            }

            foreach ($contentsVideo as $k => $v)
            {
                $contents[] = $v;
            }

            return [
                "content" => implode('', $contents),
                "title"   => $title,
            ];
        }

        protected static function compareArrays($a, $b): array
        {
            // 计算a中有，b中没有的元素
            $onlyInA = array_diff($a, $b);

            // 计算a和b中都有的元素
            $inBoth = array_intersect($a, $b);

            // 计算b中有，a中没有的元素
            $onlyInB = array_diff($b, $a);

            return [
                'toInsertWp' => $onlyInA,
                'toUpdateWp' => $inBoth,
                'toDeleteWp' => $onlyInB,
            ];
        }

        protected static function getMediaType(string $meta): int
        {
            if (str_starts_with($meta, 'image'))
            {
                return static::FILE_IMAGE;
            }
            elseif (preg_match('#(?:mp4|webm|ogg|x-mpegURL)$#iu', strtolower($meta)))
            {
                return static::FILE_VIDEO;
            }
            else
            {
                return static::FILE_DOCUMENT;
            }
        }
    }
