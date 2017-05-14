<?php
require_once 'config.php';
require_once 'function.php';
require_once 'ArticleClass.php';
require_once 'TagClass.php';

$pdo = getPDO();
$AC = new ArticleClass();
$TC = new TagClass();

$search_word = filter_input(INPUT_GET, "search_word");
$articles = $AC->search_articles($search_word);
$article_num = count($articles);
?>

<html>
    <head lang="ja">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title><?=h(BLOG_TITLE . " > " . $search_word); ?></title>
        <link rel="stylesheet" href="mystyle.css">
    </head>
    <body>
        <h1 id="blog_title"><a href="index.php"><?=h(BLOG_TITLE); ?></a></h1>
        <h2><?=h("「" . $search_word . "」の検索結果：" . $article_num . "件"); ?></h2>
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