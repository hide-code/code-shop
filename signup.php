<?php 
require_once 'setting.php';
$err_msg=array();
$pattern = '/^[a-zA-Z0-9]{6,}$/';

$user_id='';//
$pass='';//
$message='';
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
            
            /*データベースの中に受け取ったIDが存在しているか調べる*/
            try {
              // データベースに接続
              $dbh = new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
              $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
              $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
              // SQL文を作成
              $sql = 'select user_id from ec_users WHERE user_id=\''.$user_id.'\'';
              // SQLを実行
              $res  = $dbh->query($sql);
              // レコードの取得
              $rows = $res->fetchAll();
            
              if( count ($rows) !== 0){
                  $err_msg[] = 'このIDはすでに存在しています';
              }
            } catch (PDOException $e) {
              $err_msg[] = '接続できませんでした。理由：'.$e->getMessage();
            }
            
            /*データベースの中にIDが存在していなかったら、新規登録を行う*/
            if( count ($err_msg) === 0){
                 try {
                // SQL文を作成
                $sql = 'INSERT INTO ec_users(user_id,password) VALUES(?,?)';
                // SQL文を実行する準備
                $stmt = $dbh->prepare($sql);
                // SQL文のプレースホルダに値をバインド
                $stmt->bindValue(1, $user_id,PDO::PARAM_STR);
                $stmt->bindValue(2, $pass,PDO::PARAM_STR);
                
                 // SQLを実行
                $stmt->execute();
                
                
                $message='登録完了';
                
              } catch (PDOException $e) {
                $err_msg[] = '登録に失敗しました。理由：'.$e->getMessage();
               
              }
            }
            
        }
    
    }
    
    ?>
  
<!DOCTYPE html>
<html lang = "ja">
    <head>
        <meta charset="UTF-8">
        <title>ECサイト新規登録</title>
    </head>
    <body>
        <?php if($message !== ''){ ?>
        <p><?php print $message;?> </p>
        <?php } ?>
        <?php foreach($err_msg as $read){ ?>
            <p><?php print $read; ?></p>
        <?php } ?>
        <h2>ログイン画面</h2>
        <form method="post">
                <label>ユーザID<input type="text" name="user_id" value="<?php print htmlspecialchars($user_id, ENT_QUOTES); ?>"></label>
                <label>パスワード<input type="password" name="password" value="<?php print htmlspecialchars($pass, ENT_QUOTES); ?>"></label>
                <input type="submit" name="signup" value="登録">
        </form>
               
    </body>
</html>