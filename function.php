<?php
require_once 'config.php';

//データベースに接続
function getPDO(){
    static $pdo = NULL;
    if(!isset($pdo)){
        try{
            $pdo = new PDO(PDO_DSN, DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            exit('データベース接続失敗。'.$e->getMessage());
        }
    }
    return $pdo;
}

//文字列のタグを無視
function h($str, $flag = ENT_QUOTES, $encoding = "utf-8"){
    return htmlspecialchars($str, $flag, $encoding);
}

//文字列のタグを無視（<br>を改行として扱う）
function hbr($str, $flag = ENT_QUOTES, $encoding = "utf-8"){
    return nl2br(htmlspecialchars($str, $flag, $encoding));
}

//エラー時の処理
function my_error($str){
    header('Location: http://localhost:8765/error.php');
    exit;
}

//DATETIME型の引数を，FORMAT_DATETIMEで定義した文字列に変換
function datetime_to_str($datetime){
    return date(FORMAT_DATETIME, strtotime($datetime));
}

//記事のコメント数を取得
function count_comments($article_id){
    $pdo = getPDO();
    
    $stmt = $pdo->prepare("select count(*) from " . DB_TABLE_COMMENTS . " where article_id = :id");
    $stmt->bindvalue(":id", $article_id, PDO::PARAM_INT);
    $stmt->execute();
    $num_comment = (int) $stmt->fetchColumn();
    return $num_comment;
}

//index.php
//文字列として受け取った$get_page_idを，適切な値の$page_idに変換
function set_page_id($get_page_id){
    $page_id = 1;
    $AC = new ArticleClass();
    
    if($get_page_id != ""){
        $page_id = (int)$get_page_id;
        
        if($page_id < 1){
            header('Location: http://localhost:8765/index.php?id=1');
            exit;
        }else if($page_id > $AC->count_pages()){
            header('Location: http://localhost:8765/index.php?id=' . $AC->count_pages());
            exit;
        }
    }
    return $page_id;
}



