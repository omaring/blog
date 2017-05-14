<?php
require_once 'config.php';
require_once 'function.php';
require_once 'ArticleClass.php';
require_once 'TagClass.php';

$pdo = getPDO();
$AC = new ArticleClass();
$TC = new TagClass();

$get_tag = filter_input(INPUT_GET, "tag");
$articles = $AC->get_articles_from_tag($get_tag);
?>

<html>
    <head lang="ja">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title><?=h(BLOG_TITLE . " > " . $get_tag); ?></title>
        <link rel="stylesheet" href="mystyle.css">
    </head>
    <body>
        <h1 id="blog_title"><a href="index.php"><?=h(BLOG_TITLE); ?></a></h1>
        <h2><?=h($get_tag . "の記事：" . $AC->count_articles($get_tag) . "件"); ?></h2>
        <div class="articles">
            <?php foreach($articles as $article): ?>
            <div class="article">
                <h3 class="article_title"><?=h($article["title"]); ?></h3>
                <p class="article_body"><?=hbr($article["body"]); ?></p>
                <div class="tags">
                    タグ：
                    <?php foreach($TC->get_tags_from_article_id($article["id"]) as $tag): ?>
                    <p class="tag" style="display:inline;"><a href="tag.php?<?="tag=".h($tag["tag"]); ?>"><?=h($tag["tag"]); ?></a></p>
                    <?php endforeach; ?>
                </div>
                <p class="article_created">(<?=h(datetime_to_str($article["created"])); ?>)</p>
                <div class="options">
                    <a href="view.php?id=<?=h($article["id"]) ?>">コメント
                    (<?=h(count_comments($article["id"])); ?>)</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </body>
</html>