<?php

require_once "config.php";

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PagingClass
 *
 * @author tomoya
 */
class PagingClass {
    private $url = "";
    private $param = "id";
    
    function __construct0(){
    }
    
    function __construct2($url, $param){
        $this->url = $url;
        $this->param = $param;
    }
    
    //1つ前のページidに移動するリンクを作成
    function prev_page($page_id, $str = "前のページ"){
        if($page_id == 1){
            echo h($str);
        }else{
            echo "<a href='" . $this->url . "?" . $this->param . "=" . h($page_id - 1) ."'>" . $str . "</a>";
        }
    }

    //1つ後のページidに移動するリンクを作成
    function next_page($page_id, $str = "次のページ"){
        $AC = new ArticleClass();
        if($page_id * ARTICLE_LIMIT >= $AC->count_articles()){
            echo h($str);
        }else{
            echo "<a href='".$this->url. "?" . $this->param . "=" . h($page_id + 1) ."'>" . $str . "</a>";
            
        }
    }
    
    //view.php
    //1つ前の記事に移動するリンクを作成
    //$str = NULL のとき，$str = 記事のタイトル
    function prev_article($article_created, $str = NULL){
        $pdo = getPDO();

        $stmt = $pdo->prepare("select * from articles where articles.created < :article_created order by created desc limit 1 ");
        $stmt->bindValue(":article_created", $article_created, PDO::PARAM_STR);
        $stmt->execute();
        $prev_article = $stmt->fetch();

        if($str == NULL){
            $str = $prev_article["title"];
        }

        if($prev_article == NULL){
            if($str != NULL){
                echo h($str);
            }
        }else{
            echo "<a href='" . $this->url . "?" . $this->param . "=" . h($prev_article["id"]) ."'>" . $str . "</a>";
        }
    }

    //view.php
    //1つ後の記事に移動するリンクを作成
    //$str = NULL のとき，$str = 記事のタイトル
    function next_article($article_created, $str = NULL){
        $pdo = getPDO();

        $stmt = $pdo->prepare("select * from articles where articles.created > :article_created order by created asc limit 1 ");
        $stmt->bindValue(":article_created", $article_created, PDO::PARAM_STR);
        $stmt->execute();
        $next_article = $stmt->fetch();

        if($str == NULL){
            $str = $next_article["title"];
        }
        if($next_article == NULL){
            if($str != NULL){
                echo h($str);
            }
        }else{
            echo "<a href='" . $this->url . "?" . $this->param . "=" . h($next_article["id"]) ."'>" . $str . "</a>";
        }
    }

}
