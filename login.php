<?php 
require_once 'setting.php';
$err_msg=array();
$pattern = '/[a-zA-Z0-9]{6,}$/';

$user_id='';
$pass='';
session_start();

if(isset($_POST["signup"])){
    
    
    if(isset($_POST['user_id']) === TRUE){
        $user_id = $_POST['user_id'];
    }
    if( preg_match($pattern, $user_id) !== 1 ){
        $err_msg[] = '６文字以上の半角英数字を入力してください';
    }
    
    
    
    if(isset($_POST['password']) === TRUE){
        $pass = $_POST['password'];
    }
    if( preg_match($pattern, $pass) !== 1 ){
        $err_msg[] = '６文字以上の半角英数字を入力してください';
    }
    
    
    if( count ($err_msg) === 0){
        
        /*データベースの中に受け取ったIDとパスワードが存在しているか調べる*/
        try {
          // データベースに接続
          $dbh = new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
          $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
          $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
          // SQL文を作成
          $sql = 'select id, user_id, password  from ec_users WHERE user_id=\''.$user_id.'\' AND password=\''.$pass.'\' ';
         
          // SQLを実行
          $res  = $dbh->query($sql);
          // レコードの取得
          $rows = $res->fetchAll();
          if(count( $rows ) !== 0){
             $_SESSION['user_id'] = $rows[0]['id'];
          }else{
              $err_msg[] = 'ユーザ名またはパスワードが違います';
          }
          
        } catch (PDOException $e) {
            
          echo 'データベース処理でエラーが発生しました'.$e->getMessage();
        }
    }
        
}
    if(isset($_SESSION['user_id']) === TRUE){
        header('LOCATION: index.php');
        exit;
    }

?>
<!DOCTYPE html>
<html lang = "ja">
    <head>
        <meta charset="UTF-8">
        <title>ECサイトログイン</title>
    </head>
    <body>
        <?php foreach ($err_msg as $read) { ?>
          <p><?php print $read ?></p>
        <?php } ?>
      <h2>ログイン画面</h2>
        <form method="post">
                <label>ユーザID<input type="text" name="user_id" value=""></label>
                <label>パスワード<input type="password" name="password" value=""></label>
                <input type="submit" name="signup" value="ログイン">
        </form>
        <form method="post" action="signup.php">
                <input type="submit" value="新規作成">
        </form>
    </body>
</html>