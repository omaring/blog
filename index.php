<?php
require_once "config.php";
require_once "function.php";
require_once "ArticleClass.php";
require_once "TagClass.php";
require_once "PagingClass.php";

// データベース接続
$pdo = getPDO();
$AC = new ArticleClass();
$TC = new TagClass();
$PC = new PagingClass("index.php", "id");

//フォームからのデータを取得
$get_page_id = filter_input(INPUT_GET, "id");
$page_id = set_page_id($get_page_id);

//POST：記事の新規作成
$post_article_title = filter_input(INPUT_POST, "article_name");
$post_article_body = filter_input(INPUT_POST, "article_body");
$post_article_tag = filter_input(INPUT_POST, "article_tag");

if($post_article_title != "" && $post_article_body != ""){
    $AC->create($post_article_title, $post_article_body, $post_article_tag, $_FILES['upfile']);
}

//POST：記事の削除
$delete_id = filter_input(INPUT_POST, "article_delete");
if($delete_id != ""){
    $AC->delete($delete_id);
}
//どの記事からも参照されていないタグを削除
$TC->refresh();

//記事とコメントを取得
$articles = $AC->get_articles(ARTICLE_LIMIT, ($page_id - 1) * ARTICLE_LIMIT);

?>

<html>
    <head lang="ja">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title><?=h(BLOG_TITLE); ?></title>
        <link rel="stylesheet" href="mystyle.css">
    </head>
    <body>
        <div class="blog_title">
            <h1 id="blog_title"><a href="index.php"><?=h(BLOG_TITLE); ?></a></h1>
        </div>
        <div class="search">
            <form action="search.php" method="get" accept-charset="utf-8">
                <input type="text" name="search_word" value="" />
                <button type="submit" value="search">記事を検索</button>
            </form>
        </div>
        <div class="add_article">
            <h2>新しい記事を作成</h2>
            <form action="index.php" method="post" enctype="multipart/form-data" accept-charset="utf-8">
                <table>
                    <th width="80"></th>
                    <th></th>
                    <tr>
                        <td class="side">タイトル</td>
                        <td><input type="text" name="article_name" value="" size="30"></td>
                    </tr>
                    <tr>
                        <td class="side">本文</td>
                        <td><textarea name="article_body" rows="8" cols="40"></textarea></td>
                    </tr>
                    <tr>
                        <td class="side">タグ</td>
                        <td><input type="text" name="article_tag" value="" size="30"></td>
                    </tr>
                    <tr>
                        <td class="side">ファイル</td>
                        <td><input type="file" name="upfile" accept="image/*"></td>
                    </tr>
                    <tr>
                        <td><button type="submit" value="add_article">記事を投稿</button></td>
                    </tr>
                </table>
            </form>
        </div>

        <div class="articles">
            <?php foreach($articles as $article): ?>
            <div class="article">
                <div class="main">
                    <h3 class="title"><?=h($article["title"]); ?></h3>
                    <p class="body"><?=hbr($article["body"]); ?></p>
                    <div class="tags">
                        タグ：
                        <?php foreach($TC->get_tags_from_article_id($article["id"]) as $tag): ?>
                        <p class="tag" style="display:inline;"><a href="tag.php?<?="tag=".h($tag["tag"]); ?>"><?=h($tag["tag"]); ?></a></p>
                        <?php endforeach; ?>
                    </div>
                    <p class="created">(<?=h(datetime_to_str($article["created"])); ?>)</p>
                    <div class="options">
                        <a href="view.php?id=<?=h($article["id"]) ?>">コメント
                        (<?=h(count_comments($article["id"])); ?>)</a>
                        <form action="edit.php" method="get" id='edit' style="display: inline-block">
                            <button type="submit" name='id' value="<?=h($article["id"]) ?>">編集</button>
                        </form>
                        <form action="" method="post" id='delete' style="display: inline-block">
                            <button type="submit" name='article_delete' value="<?=h($article["id"]) ?>">削除</button>
                        </form>
                    </div>
                </div>
                <div class="img">
                    <?php $img = $AC->get_file($article["id"]) ?>
                    <?php if($img != NULL): ?>
                    <a href="<?=h(DIR_UPLOAD . "/" . $img); ?>">
                    <img class="article" src="<?=h(DIR_UPLOAD); ?>/<?=h($img); ?>" alt="<?=h($file); ?>">
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <nav class="paging">
            <p><?=h($page_id); ?>ページ目 / 全<?=h($AC->count_pages()); ?>ページ</p>
            <?php $PC->prev_page($page_id, "前のページ"); ?>
            <a href="index.php">トップページに戻る</a>
            <?php $PC->next_page($page_id, "次のページ"); ?>
        </nav>
    </body>
</html>
