<?php
$host     = 'localhost';
$username = 'codecamp39934';        // MySQLのユーザ名（マイページのアカウント情報を参照）
$password = 'codecamp39934';       // MySQLのパスワード（マイページのアカウント情報を参照）
$dbname   = 'codecamp39934';   // MySQLのDB名(このコースではMySQLのユーザ名と同じです）
$charset  = 'utf8';   // データベースの文字コード
 
// MySQL用のDSN文字列
$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;
 
$img_dir    = './img/';    // アップロードした画像ファイルの保存ディレクトリ

const COMPANY = ['選択してください', 'マイプロテイン', 'ゴールドスタンダード','ビーレジェンド','ZAVAS','GOLD GYM'];
const PROTEIN = ['選択してください','WPC','WPI','ソイ','エッグ','カゼイン'];

?>