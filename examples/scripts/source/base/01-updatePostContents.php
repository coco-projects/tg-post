<?php

    use Coco\tgMedia\tables\Post;

    require_once __DIR__ . '/common.php';

    $postManager->tgMedia->updatePostContents(function(array $post, Post $postTable) {
        $origin_contents = $post[$postTable->getContentsField()];
        $contents        = $origin_contents;

        //去除前面部分
        $contents = preg_replace('/^广告/', '【广告】', $contents);

        echo $contents;
        echo PHP_EOL . '------------------------------------' . PHP_EOL . PHP_EOL;

        if (str_contains($origin_contents, '@') || str_contains($origin_contents, 't.me'))
        {
        }

        return trim($contents);
    });
