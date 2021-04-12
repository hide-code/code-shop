<?php
require_once 'setting.php';
session_start();
if(isset($_SESSION['user_id']) === FALSE){
  header('LOCATION: login.php');
  exit;
}
$user_id = $_SESSION['user_id'];
// MySQL用のDSN文字列
$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;
$err_msg = array();
$data=array();

if(isset($_POST['search']) === TRUE){
  
    $protein_id = '';
    if (isset($_POST['sent_id_to_details']) === TRUE) {
        $protein_id = $_POST['sent_id_to_details'];
    }
    if ($protein_id === '') {
        $err_msg[] = '商品idがindex.phpから送られていません';
    }

  try {
        // データベースに接続
        $dbh = new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        
        //ec_itemsから
        $sql='SELECT * FROM ec_items WHERE id=? ';
        $stmt = $dbh->prepare($sql);
        // SQLを実行
        $stmt->bindValue(1, $protein_id, PDO::PARAM_INT);
        $stmt->execute();
        $data=$stmt->fetchAll();//配列に入れる
        
        if(count($data) === 0){
          $err_msg[] = 'itemsからの情報を取得できませんでした';
        }
       
      } catch (PDOException $e) {
        $err_msg[] = '接続できませんでした。理由：'.$e->getMessage();
      }
            
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>商品詳細</title>
  <style>
      
  </style>
</head>
<body>
  <h1>商品詳細ページ</h1> 
  <?php foreach ($err_msg as $read) { ?>
  <p><?php print $read ?></p>
  <?php } ?>
  
      <form method="post">
    
      <?php foreach ($data as $read) { ?>
        <section>
            <p><img src="./img/<?php print htmlspecialchars($read['img'], ENT_QUOTES); ?>"></p>
            <p>商品名:<?php print htmlspecialchars($read['name'], ENT_QUOTES); ?></p>
            <p>値段:<?php print htmlspecialchars($read['price'], ENT_QUOTES); ?></p>
            <p>在庫数:<?php print htmlspecialchars($read['stock'],ENT_QUOTES); ?></p>
            <p>プロテイン種類:<?php print htmlspecialchars($read['protein_type'],ENT_QUOTES); ?></p>
            <p>プロテイン会社<?php print htmlspecialchars($read['company'],ENT_QUOTES); ?></p>
            <p>タンパク質(/100g):<?php print htmlspecialchars($read['protein'],ENT_QUOTES); ?></p>
            <p>脂質(/100g):<?php print htmlspecialchars($read['fat'],ENT_QUOTES); ?></p>
            <p>糖質(/100g):<?php print htmlspecialchars($read['carb'],ENT_QUOTES); ?></p>
            
        </section> 
     <?php } ?>
     </form>
<a href="index.php">商品一覧に戻る</a>

</body>
</html>