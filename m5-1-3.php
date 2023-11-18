<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>最近見たアニメ・ドラマ</title>
</head>
<body>
    <h1 style="font-size:45px;">最近見たアニメ・ドラマ</h1>

    <h2 style="font-size:30px;">投稿フォーム</h2>

    <form action="" method="post">
        <input type="text" name="user" placeholder="名前"><br>
        <input type="text" name="str" placeholder="コメントを入力してください">
        <input type="text" name="password" placeholder="パスワード">
        <input type="submit" name="submit" value="投稿">
         <input type="text" name="text_num" placeholder="削除番号">
        <input type="submit" name="delete" value="削除"><br>
        <input type="text" name="edit_num" placeholder="編集対象番号">
        <input type="submit" name="edit" value="編集する">
        <!-- 編集モードのフォーム -->
        <?php if (!empty($edited_id) && isset($_POST["password"])) : ?>
    <form action="" method="post">
        <input type="hidden" name="edited_id" value="<?php echo $edited_id; ?>">
        <input type="text" name="edited_user" value="<?php echo $edited_user; ?>">
        <input type="text" name="edited_str" value="<?php echo $edited_str; ?>">
        <input type="text" name="password" placeholder="パスワード">
        <input type="submit" name="save_edit" value="保存"><br>
        <注>"保存"を押す際はパスワードを入力してください。
    </form>
<?php endif; ?>
    </form>

    <?php
   $dsn = 'mysql:dbname=tb250443db;host=localhost';
   $user = 'tb-250443';
   $password = 'hFMw4AnkhP';
   $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

// posts テーブルが存在しない場合は作成
   $createTableSql = "CREATE TABLE IF NOT EXISTS kendata"
    . " ("
    . "id INT AUTO_INCREMENT PRIMARY KEY,"
    . "user VARCHAR(50) NOT NULL,"
    . "str TEXT NOT NULL,"
    . "password VARCHAR(255) NOT NULL,"
    . "date TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
    . ");";

$stmt = $pdo->query($createTableSql);
$date=date("Y/m/d H:i:s");
    // 新規投稿機能
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["submit"]) && !empty($_POST["str"]) && !empty($_POST["user"]) && !empty($_POST["password"])) {
        $str = $_POST["str"];
        $user = $_POST["user"];
        $password = $_POST["password"];
        // データベースへの挿入
        $sql = 'INSERT INTO kendata (user, str, password) VALUES (:user, :str, :password)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':user', $user, PDO::PARAM_STR);
        $stmt->bindValue(':str', $str, PDO::PARAM_STR);
        $stmt->bindValue(':password', $password, PDO::PARAM_STR);
        $stmt->execute();
    }
    
    // 削除機能
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["text_num"]) && isset($_POST["password"])) {
        $input_password = $_POST["password"];
        $delete = $_POST["text_num"];
        $id = intval($delete);
        $pass_id = getPasswordByid($pdo, $id);
        if ($input_password == $pass_id) {
            $sql = "DELETE FROM kendata WHERE id = :id AND password = :password";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':password', $input_password, PDO::PARAM_STR);
            $stmt->execute();
        } 
    }
    
     // 編集機能
    if (isset($_POST["edit"]) && isset($_POST["edit_num"])) {
    $edit_id = $_POST["edit_num"];
    $pass_id = getPasswordByid($pdo, $edit_id);

    if ($_POST["password"] == $pass_id) {
        $sql = "SELECT * FROM kendata WHERE id = :edit_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':edit_id', $edit_id, PDO::PARAM_INT);
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $edited_user = $row["user"];
            $edited_str = $row["str"];
            $edited_id = $row["id"];
        }
        //編集モードのフォーム表示
        echo '<form action="" method="post">';
echo '<input type="hidden" name="edited_id" value="' . $edited_id . '">';
echo '<input type="text" name="edited_user" value="' . $edited_user . '">';
echo '<input type="text" name="edited_str" value="' . $edited_str . '">';
echo '<input type="text" name="password" placeholder="パスワード">';  // 修正: パスワード入力欄を追加
echo '<input type="submit" name="save_edit" value="保存"><br>';
echo '<注>"保存"を押す際はパスワードを入力してください。';
echo '</form>';
    } else {
        echo "パスワードが違います.<br>";
    }
}
// 保存機能
if (isset($_POST["save_edit"])) {
    $edited_id = $_POST["edited_id"];
    $edited_user = $_POST["edited_user"];
    $edited_str = $_POST["edited_str"];
    $pass_id = getPasswordByid($pdo, $edited_id);

    if ($_POST["password"] == $pass_id) {
        $date = date("Y/m/d H:i:s"); 
       $sql = "UPDATE kendata SET user = :user, str = :str, date = :date WHERE id = :edited_id AND password = :password";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':user', $edited_user, PDO::PARAM_STR);
$stmt->bindParam(':str', $edited_str, PDO::PARAM_STR);
$stmt->bindParam(':date', $date, PDO::PARAM_STR);
$stmt->bindParam(':edited_id', $edited_id, PDO::PARAM_INT);
$stmt->bindParam(':password', $_POST["password"], PDO::PARAM_STR);
$stmt->execute();

        // 編集モードを終了する
    } 
   }
    // コメント表示機能
    $sql = 'SELECT * FROM kendata';
    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($results as $row){
        echo $row['id'].',';
        echo $row['user'].',';
        echo $row['str'].',';
        echo $row['date'];
        echo "<br>";
    }
    // パスワード取得関数
    function getPasswordByid($pdo, $id) {
        $sql = "SELECT password FROM kendata WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();
        $pass = $row["password"];
        return $pass;
    }
    ?>
</body>
</html>
