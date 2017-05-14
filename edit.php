<?php
require_once "config.php";
require_once 'function.php';
require_once 'ArticleClass.php';
require_once 'TagClass.php';

// データベース接続
$pdo = getPDO();
$AC = new ArticleClass();
$TC = new TagClass();

//フォームからのデータを取得
$post_article_id = filter_input(INPUT_GET, "id");
$post_article_title = filter_input(INPUT_POST, "article_name");
$post_article_body = filter_input(INPUT_POST, "article_body");

//POST:記事の編集
if($post_article_title != "" && $post_article_body != ""){
    $AC->edit($post_article_id, $post_article_title, $post_article_body);
}

//GET:記事を取得
if($post_article_id === ""){
    my_error("不正な記事へのアクセス");
}else {
    $article = $AC->get_article_from_id($post_article_id);
    if($article === NULL){
        my_error("記事が見つかりません");
    }
}
?>

<html>
    <head lang="ja">
        <meta charset="UTF-8">
        <title>記事の名前</title>
        <link rel="stylesheet" href="mystyle.css">
    </head>
    <body>
        <h1 id="blog_title"><a href="index.php"><?=h(BLOG_TITLE); ?></a></h1>
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
        </div>
        
        <div class="edit_article">
            <h2>この記事を編集する</h2>
            <form action="" method="post" accept-charset="utf-8">
                タイトル<br>
                <input type="text" name="article_name" value=<?=h($article["title"]); ?> />
                <br>
                本文<br>
                <textarea name="article_body" rows="8" cols="40"><?=h($article["body"]); ?></textarea>
                <br>
                <button type="submit" value="add">送信</button>
            </form>
        </div>
        <nav id="paging">
            <a href='index.php'>記事一覧に戻る</a>
        </nav>
    </body>
</html>