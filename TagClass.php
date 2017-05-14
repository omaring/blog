<?php

require_once "config.php";
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TagClass
 *
 * @author tomoya
 */
class TagClass {
    //記事$article_idが持つタグを連想配列として取得
    function get_tags_from_article_id($article_id){
        $pdo = getPDO();
        $stmt = $pdo->prepare(
                "select tags.tag from tags,articles_tags "
                . "where tags.id = articles_tags.tag_id "
                . "and articles_tags.article_id = :article_id"
                );

        $stmt->bindValue(":article_id", $article_id, PDO::PARAM_INT);
        $stmt->execute();
        $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $tags;
    }
    
    //articles_tagsテーブルから参照されていない，tagsテーブルのレコードを削除
    function refresh(){
        $pdo = getPDO();
        $stmt = $pdo->prepare(
                "delete from tags "
                . "where tags.id not in (select tag_id from articles_tags)"
                );
        $stmt->execute();
    }
}
