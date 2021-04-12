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
$total=0;
$carts_data= array();

if(isset($_POST['buy_finish']) === TRUE){
  
  //トランザクション開始
  
  try {
        // データベースに接続
        $dbh = new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        
        //ec_itemsから
        $sql='SELECT name, price, img, amount,item_id
        FROM  ec_items INNER JOIN ec_carts
        ON ec_items.id = ec_carts.item_id
        WHERE user_id = ?';
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(1,$user_id, PDO::PARAM_INT);
        // SQLを実行
        $stmt->execute();
        $carts_data=$stmt->fetchAll();//配列に入れる
    
        
        if(count($carts_data) === 0){
          $err_msg[] = 'カートからの情報を取得できませんでした';
        }else{
          foreach($carts_data as $read){
            if ((int)$read['amount'] <= 0) {
                  $err_msg[] = $read['name'] . 'の個数はゼロでした';
              }
            $total+=$read['price']*$read['amount'];
            }
        }
        
      if(count($err_msg) ===0){
        try{
          $dbh->beginTransaction();
          foreach($carts_data as $read){
            $sql ='UPDATE ec_items  SET stock=stock-? ,updatedatetime = NOW() WHERE id= ?';  
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(1,$read['amount'], PDO::PARAM_INT);
            $stmt->bindValue(2,$read['item_id'], PDO::PARAM_INT);
            $stmt->execute();
          }
          
          $sql='DELETE FROM ec_carts WHERE user_id=? ';
          $stmt = $dbh->prepare($sql);
          $stmt->bindValue(1,$user_id, PDO::PARAM_INT);
          $stmt->execute();
          $dbh->commit();
        } catch(PDOException $e){
                //ロールバック処理
                $dbh->rollback();
                //例外をスロー
                throw $e;
              }
      }

   }catch (PDOException $e) {
    $err_msg[] = '接続できませんでした。理由：'.$e->getMessage();
   }
            
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
  <h1>購入完了ページ</h1> 
  <?php foreach ($err_msg as $read) { ?>
  <p><?php print $read ?></p>
  <?php } ?>
  
    
      <?php foreach ($carts_data as $read) { ?>
        <section>
            <div><img src="./img/<?php print htmlspecialchars($read['img'], ENT_QUOTES); ?>"></div>
            <div><?php print htmlspecialchars($read['name'], ENT_QUOTES); ?></div>
            <div><?php print htmlspecialchars($read['price'], ENT_QUOTES); ?></div>
            <div><?php print htmlspecialchars($read['amount'],ENT_QUOTES); ?></div>
            
        </section> 
     <?php } ?>
  
     
<?php echo '合計金額'.$total.'円です'; ?>
<a href="index.php">商品一覧に戻る</a>
<form action="logout.php" method="post">
    <input type="submit" value="ログアウト">
</form>
</body>
</html>