<?php

// MySQL
define("PDO_DSN", "mysql:dbname=webapp;host=localhost;charset=utf8");
define("DB_USER", "omaring");
define("DB_PASS", "omaru514");

//テーブル
define("DB_TABLE_ARTICLES", "articles");
define("DB_TABLE_COMMENTS", "comments");
define("DB_TABLE_TAGS", "tags");
define("DB_TABLE_ARTICLES_TAGS", "articles_tags");

define("DIR_UPLOAD", "./upload");

// blog
define("BLOG_TITLE", "BLOGG");
define("ARTICLE_LIMIT", 5);
define("SEARCH_LIMIT", 5);
define("TAG_LIMIT", 10);
define("FORMAT_DATETIME", "Y年m月d日 H:i:s");
define("TAG_DELIMITER", " ");
