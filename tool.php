<?php
require_once 'setting.php';
$err_msg    = array();     // エラーメッセージ
$new_img_filename = '';   // アップロードした新しい画像ファイル名
$pattern = '/^[0-9]+$/';//整数のみ
$gramme = '/^[0-9]+(\.[0-9]{1,2})?$/';//100グラムあたりの量、小数点２桁まで
$message='';//システムが正しく動いたことを伝える
$data = array();

if (isset($_POST['new']) === TRUE) {
  
  if(isset($_POST['company'])){
    $company = $_POST['company'];
  }
  if ( $company === '0') {
    $err_msg[]='会社を選択してください';
   }
  
  
  $name = '';
  if (isset($_POST['name']) === TRUE) {
      $name = trim($_POST['name']);//trimは先頭と末尾の空白を取り除く
  }
  if ($name === '') {
      $err_msg[]='商品名を入力してください'; 
  }
  
  $price = '';
  if (isset($_POST['price']) === TRUE) {
      $price = $_POST['price'];
  }
  if($price===''){
      $err_msg[]='値段を入力してください'; 
  }else if( preg_match($pattern,$price) !== 1){
    $err_msg[]='値段が不適です';
  }
  
  $stock = '';
  if (isset($_POST['quantity']) === TRUE) {
      $stock = $_POST['quantity'];
  }
  if($stock ===''){
      $err_msg[]='個数を入力してください'; 
  }else if(preg_match($pattern,$stock)!==1){
    $err_msg[]='個数が不適です';
  }
  
  $status='';
  if (isset($_POST['release']) === TRUE) {
      $status = $_POST['release'];
  }
  if($status !== '0' && $status !== '1'){//postで送られるものは、文字列
    $err_msg[]='ステータスが不適です';
  }

  // HTTP POST でファイルがアップロードされたかどうかチェック
  if (is_uploaded_file($_FILES['file']['tmp_name']) === TRUE) {//引数がfileだから変える
    // 画像の拡張子を取得
    $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);//引数がfileだから変える
    // 指定の拡張子であるかどうかチェック
    if ($extension === 'jpg' || $extension === 'jpeg' || $extension === 'png') {
      // 保存する新しいファイル名の生成（ユニークな値を設定する）
      $new_img_filename = sha1(uniqid(mt_rand(), true)). '.' . $extension;
      // 同名ファイルが存在するかどうかチェ��ク
      if (is_file($img_dir . $new_img_filename) !== TRUE) {
        // アップロードされたファイルを指定ディレクトリに移動して保存
        if (move_uploaded_file($_FILES['file']['tmp_name'], $img_dir . $new_img_filename) !== TRUE) {
            $err_msg[] = 'ファイルにアップロードに失敗しました';
        }
      } else {
        $err_msg[] = 'ファイルアップロードに����敗しました。再度お試しください。';
      }
    } else {
      $err_msg[] = 'ファイル形式が異なります。画像ファイルはJPEGまたはPNGのみ利用可能です。';
    }
  } else {
    $err_msg[] = 'ファイルを選択してください';
  }
  
  $protein_type='';//プロテインタイプ
  if(isset($_POST['protein_type'])){
    $protein_type = $_POST['protein_type'];
  } 
  if ( $protein_type === '0') {
    $err_msg[]='プロテインの種類を選択してください';
   }
  
  $protein='';//プロテインの量
  if(isset($_POST['protein']) === TRUE){
      $protein = $_POST['protein'];
  }
  if($protein===''){
      $err_msg[]='プロテインの値（g/１００g)を入力してください'; 
  }else if(preg_match($gramme,$protein) !==1 || $protein > 100){
    $err_msg[]='プロテインの値が不適です';
  }
  
  $carb='';//糖質の量
  if(isset($_POST['carb']) === TRUE){
      $carb = $_POST['carb'];
  }
  if($carb===''){
      $err_msg[]='糖質の値（g/１００g)を入力してください'; 
  }else if(preg_match($gramme,$carb) !==1 || $carb >100){
    $err_msg[]='糖質の値が不適です';
  }
  
  $fat='';//脂質の量
  if(isset($_POST['fat']) === TRUE){
      $fat = $_POST['fat'];
  }
  if($fat===''){
      $err_msg[]='脂質の値（g/１００g)を入力してください'; 
  }else if(preg_match($gramme,$fat) !==1 || $fat >100){
    $err_msg[]='脂質の値が不適です';
  }
  
  
  
}else if(isset($_POST['update']) === TRUE){
  
  $stock= '';
  if (isset($_POST['stock']) === TRUE) {
      $stock = $_POST['stock'];
  }
  if($stock === ''){
      $err_msg[]='在庫数を入力してください'; 
  }else if(preg_match($pattern,$stock)!==1){
    $err_msg[]='在庫数が正常でない';
  }
  
  $protein_id= '';
  if (isset($_POST['protein_id']) === TRUE) {
      $protein_id = $_POST['protein_id'];
  }
  
}else if(isset($_POST['status_update']) === TRUE){
  
  $status='';
  if (isset($_POST['status']) === TRUE) {
      $status = $_POST['status'];
  }
  if($status !== '0' && $status !== '1'){
    $err_msg[]='ステータスが不適です';
  }
  
  $protein_id= '';
  if (isset($_POST['protein_id']) === TRUE) {
      $protein_id = $_POST['protein_id'];
  }
  
}else if(isset($_POST['delete']) === TRUE){
  
  $protein_id= '';
  if (isset($_POST['protein_id']) === TRUE) {
      $protein_id = $_POST['protein_id'];
  }
}
 
// アップロードした新しい画像ファイル名の登録、既存の画像ファイル名の取得
try {
  // データベースに接続
  $dbh = new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
  
  


    // エラーがなければ���アップロードし��新��い画像��ァイ��名������保��
    if (count($err_msg) === 0 && isset($_POST['new']) === TRUE) {
      
      
        // SQL文を作成
        $sql = 'INSERT INTO ec_items(name,price,img,stock,createdatetime,status,company,protein_type,protein,carb,fat) 
        VALUES(?,?,?,?,NOW(),?,?,?,?,?,?)';
        // SQL文を実行する準備
        $stmt = $dbh->prepare($sql);
        // SQL文のプレースホルダに値をバインド
        $stmt->bindValue(1, $name,PDO::PARAM_STR);
        $stmt->bindValue(2, $price,PDO::PARAM_INT);
        $stmt->bindValue(3, $new_img_filename, PDO::PARAM_STR);
        $stmt->bindValue(4, $stock,PDO::PARAM_INT);
        $stmt->bindValue(5, $status, PDO::PARAM_STR);
        $stmt->bindValue(6, $company, PDO::PARAM_INT);
        $stmt->bindValue(7, $protein_type, PDO::PARAM_INT);
        $stmt->bindValue(8, $protein,PDO::PARAM_INT);
        $stmt->bindValue(9, $carb,PDO::PARAM_INT);
        $stmt->bindValue(10, $fat,PDO::PARAM_INT);
        //var_dump($sql,$name,$price,$new_img_filename,$status);
         // SQLを実行
        $stmt->execute();
        
        
        $message='登録完了';
        
    
  }else if(count($err_msg) === 0 && isset($_POST['update']) === TRUE ){
    
    $sql = 'UPDATE ec_items SET stock=?,updatedatetime = NOW() WHERE id = ?';
    // SQL文を実行する準備
    $stmt = $dbh->prepare($sql);
    
    $stmt->bindValue(1, $stock,PDO::PARAM_INT);
    $stmt->bindValue(2, $protein_id,PDO::PARAM_INT);
    // SQLを実行
    $stmt->execute();
    $message='在庫変更完了';
    
  }else if(count($err_msg) === 0 && isset($_POST['status_update']) === TRUE){
    
    $sql = 'UPDATE ec_items SET status=?,updatedatetime = NOW() WHERE id = ?';
    // SQL文を実行する準備
    $stmt = $dbh->prepare($sql);
    
    $stmt->bindValue(1, $status,PDO::PARAM_INT);
    $stmt->bindValue(2, $protein_id,PDO::PARAM_INT);
    
    // SQLを実行
    $stmt->execute();
    $message='ステータス変更完了';
    
  }else if(count($err_msg) === 0 && isset($_POST['delete']) === TRUE){
    
    $sql = 'DELETE FROM ec_items  WHERE id = ?';
    // SQL文を実行する準備
    $stmt = $dbh->prepare($sql);
    
    $stmt->bindValue(1, $protein_id,PDO::PARAM_INT);
    
    // SQLを実行
    $stmt->execute();
    $message='削除完了';
  }
  
  $sql = 'SELECT *
    FROM ec_items';
    
    $stmt = $dbh->prepare($sql);
    
    // SQLを実行
    $stmt->execute();
    $data=$stmt->fetchAll();
  
} catch (PDOException $e) {
  // 接続失敗した場合
  $err_msg[] = 'DBエラー：'.$e->getMessage();
}
      
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>CodeShop管理ぺージ</title>
  <style>
      table{
          border:1px solid;
          border-collapse:collapse;
      }
      th,td{
          border:1px solid;
      } 
  </style>
</head>
<body>
 <?php foreach ($err_msg as $read) { ?>
 <p><?php print $read; ?></p>
 <?php } ?>
 <?php if($message !== ''){ ?>
 <p><?php print $message; ?></p>
 <?php } ?>
 

  <h1>CodeShop管理ツール</h1>
  <section>
          <h2>新規商品追加</h2>
        
          <form method="post" enctype="multipart/form-data">
            <label>サプリメント会社:
              <select name="company">
                <?php foreach(COMPANY as $key=>$value) { ?>
                <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                <?php } ?>
              </select>
              </label>
            <label>名前：<input type="text" name="name"></labbel>
            <label>値段：<input type="text" name="price"></label>
            <label>個数：<input type="text" name="quantity"></label>
            <input type="file" name="file">
            <select name=release>
                <option value="0">非公開</option>
                <option value="1">公開</option>
            </select>
            <h3>栄養成分追加</h3>
                  　<label>プロテインの種類:
                  　<select name="protein_type">
                    　<?php foreach(PROTEIN as $key=>$value){ ?>
                    　<option value="<?php echo $key; ?>"><?php echo $value; ?></optipn>
                    　<?php } ?>
                  　</select>
                  　</label>
                    <label>タンパク質(g/100g):<input type="text" name="protein"></label>
                    <label>脂質(g/100g):<input type="text" name="fat"></label>
                    <label>糖質(g/100g):<input type="text" name="carb"></label>
                
            <input type="submit" name="new" value="商品を追加">
            
          </form>
  </section>
  
  <p>商品情報変更</p>
  <table>
      <tr>
          <th>商品画像</th> 
          <th>商品名</th> 
          <th>価格</th> 
          <th>在個数</th> 
          <th>ステータス</th>
          <th></th>
      </tr>

<?php foreach ($data as $read) { ?>
      <tr>
          <td><img src="./img/<?php print htmlspecialchars($read['img'], ENT_QUOTES); ?>"></td>
          <td><?php print htmlspecialchars($read['name'], ENT_QUOTES); ?></td>
          <td><?php print htmlspecialchars($read['price'], ENT_QUOTES); ?></td>
          <td>
            <form method="post">
            <input type="text" name="stock" value="<?php print htmlspecialchars($read['stock'], ENT_QUOTES); ?>">
            <input type="hidden" name="protein_id" value="<?php print htmlspecialchars($read['id'], ENT_QUOTES); ?>">
            <input type="submit" name="update" value="変更">
            </form>
          </td>
          <td>
            <form method="post">
              
            <?php if((int)$read['status']===0){ ?>  
            <input type="submit" name='status_update' value="非公開->公開">
            <input type="hidden" name="status" value="1">
            <?php }else{ ?>
            <input type="submit" name='status_update' value="公開->非公開">
            <input type="hidden" name="status" value="0">
            <?php } ?>
            
            <input type="hidden" name="protein_id" value="<?php print htmlspecialchars($read['id'], ENT_QUOTES); ?>">
            </form>
          </td>
          <td>
            <form method="post">
              <input type="hidden" name="protein_id" value="<?php print htmlspecialchars($read['id'], ENT_QUOTES); ?>">
              <input type="submit" name="delete" value="削除">
            </form>
          </td>
      </tr>
  
<?php } ?>
</table>
</body>
</html>