<?php

    namespace Coco\tgPost\posts;

    use Coco\logger\Logger;
    use Coco\tgPost\PostManager;

    abstract class PostAbstract
    {
        use Logger;

        public function __construct(public PostManager $postManager)
        {
        }

        abstract public function initHandler(?callable $callback = null): static;
    }
