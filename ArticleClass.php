<?php

require_once "config.php";
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ArticleClass
 *
 * @author tomoya
 */
class ArticleClass {
    private $table_name;
    private $last_insert_id = null;
    
    function __consrtuct($table){
        $this->table_name = $table;
    }
    
    function get_last_insert_id(){
        return $this->last_insert_id;
    }

    function create($title, $body, $tag, $file){
        if($title === "" || $body === ""){
            return;
        }
        
        $pdo = getPDO();
        $pdo->beginTransaction();
        try{
            //記事を追加
            $stmt = $pdo->prepare("insert into articles (title, body) values (:title, :body)");
            $stmt->bindValue(":title", $title, PDO::PARAM_STR);
            $stmt->bindValue(":body", $body, PDO::PARAM_STR);
            $stmt->execute();
            $new_article_id = $pdo->lastInsertId('id');
            $this->last_insert_id = $new_article_id;
            
            if(isset($file)){
                $tmp_file = $file['tmp_name'];

                if(is_uploaded_file($tmp_file)){
                    if(preg_match("/\.png$|\.jpg$|\.jpeg$|\.bmp$/i", $file['name'])){
                        $up_file_name = $new_article_id . "."
                                . pathinfo($file['name'], PATHINFO_EXTENSION);
                        $up_dir = DIR_UPLOAD . "/" . $up_file_name;

                        if(!move_uploaded_file($tmp_file, $up_dir)){
                            echo h("アップロード失敗：" . $up_file_name);
                        }
                    }else{
                        echo h("アップロードできるファイルは'png', 'jpg', 'bmp'のみです．");
                    }
                }
            }
            $stmt = $pdo->prepare("update articles set file = :file where id = :id");
            $stmt->bindValue(":file", $up_file_name, PDO::PARAM_STR);
            $stmt->bindValue(":id", $new_article_id, PDO::PARAM_INT);
            $stmt->execute();

            //タグを追加（重複する場合は追加しない）
            $tags = explode(TAG_DELIMITER, $tag, TAG_LIMIT);
            $tags = array_unique($tags);

            $stmt = $pdo->prepare("insert ignore into tags (tag) values (:tag)");
            $stmt_tag = $pdo->prepare("select id from tags where tag = :tag limit 1");
            $stmt_at = $pdo->prepare("insert into articles_tags (article_id, tag_id) value (:article_id, :tag_id)"                );
            foreach ($tags as $tag){
                $stmt->bindValue(":tag", $tag, PDO::PARAM_STR);
                $stmt->execute();

                $stmt_tag->bindValue(":tag", $tag, PDO::PARAM_STR);
                $stmt_tag->execute();
                $fetch_tag = $stmt_tag->fetch();
                $tag_id = $fetch_tag["id"];

                $stmt_at->bindValue(":article_id", $new_article_id, PDO::PARAM_INT);
                $stmt_at->bindValue(":tag_id", $tag_id, PDO::PARAM_STR);
                $stmt_at->execute();
            }
            $pdo->commit();

        } catch (PDOException $e) {
            $pdo->rollBack();
            exit($e->getMessage());
        }
    }
    
    //閲覧数viewedを+1
    function view($article_id){
        $pdo = getPDO();
        $stmt = $pdo->prepare("update articles set viewed = viewed + 1 where id = :id");
        $stmt->bindValue(":id", $article_id, PDO::PARAM_STR);
        $stmt->execute();
    }
    
    function delete($article_id){
        $pdo = getPDO();
        $stmt = $pdo->prepare("delete from articles where id = :article_id");
        $stmt->bindValue(":article_id", $article_id, PDO::PARAM_INT);
        $stmt->execute();
    }

    function edit($article_id, $title, $body){
        if($title == "" || $body == ""){
            return;
        }
        $pdo = getPDO();
        $stmt = $pdo->prepare("update articles set title = :article_name, body = :article_body where id = :article_id");
        $stmt->bindValue(":article_name", $title, PDO::PARAM_STR);
        $stmt->bindValue(":article_body", $body, PDO::PARAM_STR);
        $stmt->bindValue(":article_id", $article_id, PDO::PARAM_INT);
        $stmt->execute();
    }

    function get_articles($num_article = PHP_INT_MAX, $start_id = 0){
        $pdo = getPDO();
        $stmt = $pdo->prepare("select * from articles order by created desc limit :start, :end");
        $stmt->bindValue(":start", $start_id, PDO::PARAM_INT);
        $stmt->bindValue(":end", $num_article, PDO::PARAM_INT);
        $stmt->execute();
        $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $articles;
    }
    
    //ページ数（記事の件数 / 1ページ辺りの表示数）を取得
    function count_pages(){
        $page = ceil($this->count_articles() / ARTICLE_LIMIT);
        if($page == 0){
            $page = 1;
        }
        return $page;
    }
    
    //記事の件数を取得
    function count_articles($tag = ""){
        $pdo = getPDO();
        if($tag != ""){
            $stmt = $pdo->prepare(
                    "select count(articles.id) from articles, articles_tags, tags "
                    . "where articles.id = articles_tags.article_id "
                    . "and articles_tags.tag_id = tags.id "
                    . "and tags.tag = :tag"
                    );
            $stmt->bindValue(":tag", $tag, PDO::PARAM_STR);
        }else{
            $stmt = $pdo->prepare("select count(*) from articles");
        }
        $stmt->execute();
        $num_article = (int) $stmt->fetchColumn();
        return $num_article;
    }
    
    function count_search_articles($word){
        $articles = search_articles($word);
        return count($articles);
    }

    function search_articles($word, $limit = SEARCH_LIMIT){
        $pdo = getPDO();
        $stmt = $pdo->prepare(
                "select * from articles "
                . "where "
                    . "title like :word "
                . "union "
                . "select * from articles "
                . "where "
                    . "body like :word "
                . "union "
                . "select articles.* from articles, tags, articles_tags "
                . "where "
                    . "(tags.tag like :word) "
                    . "and (tags.id = articles_tags.tag_id) "
                    . "and (articles_tags.article_id = articles.id) "
                . "order by created desc"
                );
        $word = "%".$word."%";
        $stmt->bindValue(":word", $word, PDO::PARAM_STR);
        $stmt->execute();
        $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $articles;
    }

    function get_article_from_id($article_id){
        $pdo = getPDO();
        $stmt = $pdo->prepare("select * from articles where id = :article_id");
        $stmt->bindValue(":article_id", $article_id, PDO::PARAM_INT);
        $stmt->execute();
        $article = $stmt->fetch();
        return $article;
    }

    //タグ$tagを持つ記事を連想配列として取得
    function get_articles_from_tag($tag){
        $pdo = getPDO();
        $stmt = $pdo->prepare(
                "select articles.* from articles, tags, articles_tags "
                . "where articles.id = articles_tags.article_id "
                . "and articles_tags.tag_id = tags.id "
                . "and tags.tag = :tag "
                . "order by articles.created desc"
                );
        $stmt->bindValue(":tag", $tag, PDO::PARAM_STR);
        $stmt->execute();
        $articles = $stmt->fetchAll();

        return $articles;
    }
    
    function get_comments(){
        $pdo = getPDO();
        $stmt =  $pdo->prepare("select * from " . DB_TABLE_COMMENTS);
        $stmt->execute();
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $comments;
    }
    
    function get_file($article_id){
        $pdo = getPDO();
        $stmt = $pdo->prepare("select * from articles where id = :id");
        $stmt->bindvalue(":id", $article_id, PDO::PARAM_INT);
        $stmt->execute();
        $file = $stmt->fetch();
        return $file["file"];
    }
}
