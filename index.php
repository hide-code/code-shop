<?php
require_once 'setting.php';
session_start();
if(isset($_SESSION['user_id']) === FALSE){
  header('LOCATION: login.php');
  exit;
}
// MySQL用のDSN文字列
$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;
$err_msg = array();

     
      try {
        // データベースに接続
        $dbh = new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        
        //ec_itemsから
        $sql='SELECT img, name, price, id, stock
              FROM ec_items 
              WHERE status=1';
        
        $stmt = $dbh->prepare($sql);
          
        // SQLを実行
        $stmt->execute();
        $data=$stmt->fetchAll();//配列に入れる
       
        
        
      } catch (PDOException $e) {
        $err_msg[] = '接続できませんでした。理由：'.$e->getMessage();
      }
            

?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>ECサイト販売</title>
  <style>
      
  </style>
</head>
<body>
  <h1>商品一覧ページ</h1> 
  <?php foreach ($err_msg as $read) { ?>
  <p><?php print $read ?></p>
  <?php } ?>
  

    
      <?php foreach ($data as $read) { ?>
      <form method="post" action="shopping_cart.php">
        <section>
          
            <div><img src="./img/<?php print htmlspecialchars($read['img'], ENT_QUOTES); ?>"></div>
            <div><?php print htmlspecialchars($read['name'], ENT_QUOTES); ?></div>
            <div><?php print htmlspecialchars($read['price'], ENT_QUOTES); ?></div>
            
            <?php if((int)$read['stock'] === 0){ ?>
            <div><?php print '売り切れ'?></div>
            <?php }else{ ?>
              <input type="submit" name="buy" value="カートに入れる">
              <input type="hidden" name="sent_id_to_carts" value="<?php print htmlspecialchars($read['id'], ENT_QUOTES); ?>">
            <?php } ?>
            
        </section> 
      </form>
      <form method="post" action="details.php">
          <input type="submit" name="search" value="商品の詳細を検索">
          <input type="hidden" name="sent_id_to_details" value="<?php print htmlspecialchars($read['id'],ENT_QUOTES); ?>">
      </form>
     <?php } ?>
     
    

</body>
</html>
