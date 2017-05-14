<?php
require_once "config.php";
require_once "function.php";
require_once "ArticleClass.php";
require_once 'TagClass.php';
require_once 'PagingClass.php';

// データベース接続
$pdo = getPDO();
$AC = new ArticleClass();
$TC = new TagClass();
$PC = new PagingClass("view.php", "id");

//フォームからのデータを取得
$get_article_id = filter_input(INPUT_GET, "id");
$post_comment_name = filter_input(INPUT_POST, "comment_name");
$post_comment_body = filter_input(INPUT_POST, "comment_body");

//GET:記事を取得
if($get_article_id === ""){
    my_error("不正な記事へのアクセス");
} else {
    $article_id = $get_article_id;
    $article = $AC->get_article_from_id($article_id);
    if($article === NULL){
        my_error("記事が見つかりません");
    }
    $AC->view($article_id);
}

//POST:コメントを書き込み
if($post_comment_name != "" && $post_comment_body != "" && $get_article_id != ""){
    $stmt_comment = $pdo->prepare("insert into ". DB_TABLE_COMMENTS . " (article_id, name, body) values (:article_id, :name, :body)");
    $stmt_comment->bindValue(":article_id", $article_id, PDO::PARAM_INT);
    $stmt_comment->bindValue(":name", $post_comment_name, PDO::PARAM_STR);
    $stmt_comment->bindValue(":body", $post_comment_body, PDO::PARAM_STR);
    $stmt_comment->execute();
}

//記事のコメントを取得
$stmt_comments = $pdo->prepare("select * from " . DB_TABLE_COMMENTS . " where article_id = :article_id");
$stmt_comments->bindValue(":article_id", $article_id, PDO::PARAM_INT);
$stmt_comments->execute();
$comments = $stmt_comments->fetchAll(PDO::FETCH_ASSOC);

?>

<html>
    <head lang="ja">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title><?=h(BLOG_TITLE . " > " . $article["title"]); ?></title>
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
            <div class="comments">
                <h3><?=h(count_comments($article_id)); ?>件のコメント：</h3>
                <?php foreach($comments as $comment): ?>
                    <div class="comment">
                        <h3 class="comment_name"><?=h($comment["name"]); ?></h3>
                        <p class="comment_body"><?=hbr($comment["body"]); ?></p>
                        <p class="comment_created">(<?=h(datetime_to_str($comment["created"])); ?>)</p>
                    </div>
                <?php endforeach; ?>
                <div class="add_comment">
                    <h2>この記事にコメントを書く</h2>
                    <form action="" method="post" accept-charset="utf-8">
                        名前<br>
                        <input type="text" name="comment_name" value="" />
                        <br>
                        コメント<br>
                        <textarea name="comment_body" rows="8" cols="40"></textarea>
                        <br>
                        <input type="submit" value="送信" />
                    </form>
                </div>
            </div>
        </div>
        <nav id="paging">
            <?php $PC->next_article($article["created"], "新しい記事"); ?>
            <a href="index.php">記事一覧に戻る</a>
            <?php $PC->prev_article($article["created"], "古い記事"); ?>
        </nav>
    </body>
</html>
