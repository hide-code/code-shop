<?php
require_once 'setting.php';
// MySQL用のDSN文字列
$dsn = 'mysql:dbname=' . $dbname . ';host=' . $host . ';charset=' . $charset;
$err_msg = array();
$pattern = '/^[1-9][0-9]*$/'; //数字のみ
$total=0;
session_start();
if (isset($_SESSION['user_id']) === FALSE) {
    header('LOCATION: login.php');
    exit;
}
$user_id = $_SESSION['user_id'];

if (isset($_POST['buy']) === TRUE) {
    $protein_id = '';
    if (isset($_POST['sent_id_to_carts']) === TRUE) {
        $protein_id = $_POST['sent_id_to_carts'];
    }
    if ($protein_id === '') {
        $err_msg[] = '商品idがindex.phpから送られていません';
    }

    if (count($err_msg) === 0) {
        //トランザクション開始
        try {
            // データベースに接続
            $dbh = new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            $sql = 'SELECT *  FROM ec_carts WHERE user_id = ? AND item_id = ?';
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
            $stmt->bindValue(2, $protein_id, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetch();

            if ($data === FALSE) {
                $sql = 'INSERT INTO ec_carts (user_id, item_id, amount, create_datetime) VALUES (?,?,1,NOW())';
            } else {
                $sql = 'UPDATE ec_carts SET amount=amount+1, update_datetime=NOW() WHERE user_id=? AND item_id=?';
            }
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
            $stmt->bindValue(2, $protein_id, PDO::PARAM_INT);
            $stmt->execute();

            echo 'データ登録ができました';
        } catch (PDOException $e) {

            $err_msg[] = 'カートの追加に失敗しました。';
        }
    }
} else if (isset($_POST['details']) === TRUE) {

    $amount = '';
    if (isset($_POST['amount']) === TRUE) {
        $amount = $_POST['amount'];
    }
    if (preg_match($pattern, $amount) !== 1) {
        $err_msg[] = '数値が不適です';
    }

    $protein_id = '';
    if (isset($_POST['sent_items_id']) === TRUE) {
        $protein_id = $_POST['sent_items_id'];
    }
    if ($protein_id === '') {
        $err_msg[] = '商品idがcart.phpから送られていません';
    }
    if (count($err_msg) === 0) {
        try {
            // データベースに接続
            $dbh = new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            //ec_itemsから
            $sql = 'UPDATE ec_carts SET amount = ? ,update_datetime = NOW() WHERE user_id = ? AND item_id = ?';
            $stmt = $dbh->prepare($sql);

            $stmt->bindValue(1, $amount, PDO::PARAM_INT);
            $stmt->bindValue(2, $user_id, PDO::PARAM_INT);
            $stmt->bindValue(3, $protein_id, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            $err_msg[] = '数量変更に失敗しました。' . $e->getMessage();
        }
    }
} else if (isset($_POST['delete']) === TRUE) {

    $protein_id = '';
    if (isset($_POST['sent_items_id']) === TRUE) {
        $protein_id = $_POST['sent_items_id'];
    }
    if ($protein_id === '') {
        $err_msg[] = '商品idがcart.phpから送られていません';
    }

    try {
        // データベースに接続
        $dbh = new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        //ec_itemsから
        $sql = 'DELETE FROM ec_carts WHERE user_id=? AND item_id=?  ';
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
        $stmt->bindValue(2, $protein_id, PDO::PARAM_INT);


        // SQLを実行
        $stmt->execute();
    } catch (PDOException $e) {
        $err_msg[] = '商品の削除に失敗しました';
    }
}
try {
    // データベースに接続
    $dbh = new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    $sql = 'SELECT name, price, img, stock, status, amount, item_id
        FROM  ec_items INNER JOIN ec_carts
        ON ec_items.id = ec_carts.item_id
        WHERE user_id = ?';

    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
    // SQLを実行
    $stmt->execute();
    $data = $stmt->fetchAll();
    

    if (count($data) === 0) {
        $err_msg[] = '商品情報取得できませんでした';
    } else {
        foreach ($data as $read) {
            if ((int)$read['stock'] <= 0) {
                $err_msg[] = $read['name'] . 'の在庫がありませんでした';
            }
            if ((int)$read['status'] === 0) {
                $err_msg[] = $read['name'] . 'は公開されていません';
            }
            $total+=$read['price']*$read['amount'];
        }
    }
} catch (PDOException $e) {
    echo '接続できませんでした。理由：' . $e->getMessage();
}


?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>ショッピングカート</title>
    <style>

    </style>
</head>

<body>
    <h1>ショッピングカート</h1>

    <?php foreach ($err_msg as $read) { ?>
        <p><?php print htmlspecialchars($read, ENT_QUOTES); ?></p>
    <?php } ?>

    <?php if (count($err_msg) === 0) { ?>
        <p>カート内の商品一覧</p>
     

          

            <?php foreach ($data as $read) { ?>
                <section>
                   <form method="post">
                    <div><img src="./img/<?php print htmlspecialchars($read['img'], ENT_QUOTES); ?>"></div>
                    <div><?php print htmlspecialchars($read['name'], ENT_QUOTES); ?></div>
                    <div><?php print htmlspecialchars($read['price'], ENT_QUOTES); ?></div>

                    <?php if ((int)$read['stock'] === 0) { ?>
                        <div><?php print '売り切れ' ?></div>
                    <?php } else { ?>
                        <select name="amount">
                            <?php for ($i = 1; $i <= 10; $i++) { ?>
                                <option value="<?php print $i; ?>" <?php if($i === (int)$read['amount']){echo 'selected';} ?>><?php print $i ?>個</option>
                            <?php } ?>
                        </select>
                        <input type="submit" name="details" value='変更'>
                        <input type="submit" name="delete" value='削除'>
                        <input type="hidden" name="sent_items_id" value="<?php print htmlspecialchars($read['item_id'], ENT_QUOTES); ?>">
                    <?php } ?>
                    </form>
                </section>
            <?php } ?>
            <?php echo $total; ?>
        
    <?php } ?>

    <a href="index.php">商品一覧に戻る</a>
    <form method="post" action="finish.php">
      <input type="submit" name="buy_finish" value="購入完了ページへ">
    </form>
  
</body>

</html>