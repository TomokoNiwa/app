<?php
session_start();

// --- ユーザー設定 ---
$user_id = "guest"; 
$password = "1234"; 

// ログアウト処理（もし必要なら）
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit;
}

// ログイン判定
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_id']) && isset($_POST['login_pass'])) {
    if ($_POST['login_id'] === $user_id && $_POST['login_pass'] === $password) {
        $_SESSION['iruyo_auth'] = true;
    } else {
        $login_error = "IDまたはパスワードが違います";
    }
}

// ログインしていない場合に表示する画面
if (!isset($_SESSION['iruyo_auth']) || $_SESSION['iruyo_auth'] !== true):
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン</title>
    <style>
        body { font-family: sans-serif; height: 100vh; margin: 0; background: #f0f2f5; }
        .login-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 100%; max-width: 320px; text-align: center; margin: 20px auto 0;}
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #6f564d; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; }
        .error { color: #e63946; font-size: 14px; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="login-card">
        <h2 style="color:#6f564d;">管理画面</h2>
        <?php if(isset($login_error)) echo "<p class='error'>$login_error</p>"; ?>
        <form method="POST">
            <input type="text" name="login_id" placeholder="ユーザー名" required>
            <input type="password" name="login_pass" placeholder="パスワード" required>
            <button type="submit">ログイン</button>
        </form>
        <p style="margin-top:20px;"><a href="index.html" style="color:#949494; text-decoration:none; font-size:14px;">トップへ戻る</a></p>
    </div>
</body>
</html>
<?php
exit;
endif;

// --- ここから下は元々の処理（データ保存など） ---

$file = 'schedule_data.json';
if (!file_exists($file)) {
    file_put_contents($file, json_encode([], JSON_UNESCAPED_UNICODE));
}
$json = file_get_contents($file);
$data = json_decode($json, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['date'])) {
    $date = $_POST['date'] ?? '';
    $message = $_POST['message'] ?? ''; 
    
    if ($date) {
        if ($message === "") {
            unset($data[$date]);
        } else {
            $data[$date] = $message;
        }
        file_put_contents($file, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        $status = "「" . $message . "」を保存しました！";
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <title>予定管理</title>
    <style>
        body { font-family: sans-serif; padding: 10px; line-height: 1.6;}
        .card { padding: 15px; border-radius: 8px; background: #f9f9f9; border: 1px solid #949494; }
        .status { color: green; font-weight: bold; }
        header{ text-align: left; width: 100%; height: 100px; position: fixed; top: 0; left: 10px; background-color: #ffffff; z-index: 10;}
        .hamburger { position: fixed; top: 25px; right: 10px; width: 40px; height: 40px; cursor: pointer; z-index: 100;}
        .hamburger span { display: block; width: 30px; height: 2px; margin: 8px auto; transition: all 0.3s; background-color: #6f564d;}
        .nav { position: fixed; top: 0; right: -100%; width: 70%; height: 100%; transition: all 0.3s; z-index: 99; background: #ffffff;}
        .nav.is-active { right: 0;}
        .hamburger.is-active span:nth-child(1) { transform: translateY(10px) rotate(45deg); }
        .hamburger.is-active span:nth-child(2) { opacity: 0; }
        .hamburger.is-active span:nth-child(3) { transform: translateY(-10px) rotate(-45deg); }
        header .nav ul { list-style: none; padding: 80px 20px; margin: 0;}
        header .nav ul li { margin-bottom: 15px;}
        header .nav ul li a { display: block; text-decoration: none; padding: 20px; font-weight: bold; border-radius: 10px; text-align: center; transition: background-color 0.2s; font-size: 1.2rem; color: #ffffff; background-color: #6f564d; border: 2px solid #6f564d;}
        main{ margin-top: 110px;}
    </style>
</head>
<body>
  <header>
    <h1>予定の登録</h1> 
    <div class="hamburger" id="js-hamburger">
      <span></span><span></span><span></span>
    </div>
    <nav class="nav" id="js-nav">
      <ul>
        <li><a href="index.html">トップに戻る</a></li>
        <li><a href="?logout=1" style="background-color:#949494; border-color:#949494;">ログアウト</a></li>
      </ul>
    </nav>
  </header>
  <main>
    <?php if (isset($status)) echo "<p class='status'>$status</p>"; ?>
    <div class="card">
        <form method="POST">
            <p>① 日付：<input type="date" name="date" value="<?php echo date('Y-m-d'); ?>" required></p>
            <p>② 内容：<br>
                <label><input type="radio" name="message" value="だんらんの家" checked>だんらんの家</label><br>
                <label><input type="radio" name="message" value="病院">病院</label><br>
                <label><input type="radio" name="message" value="智子がくる">智子がくる</label><br>
                <label><input type="radio" name="message" value="デイマネージャーさんがくる">デイマネージャーさんがくる</label><br>
                <hr>
                <label><input type="radio" name="message" value="">予定なし（消去）</label>
            </p>
            <button type="submit" style="padding: 10px 20px; background:#6f564d; color:white; border:none; border-radius:5px;">更新する</button>
        </form>
    </div>
    <hr>
    <h3>現在のデータ中身</h3>
    <pre><?php print_r($data); ?></pre>
    <script>
        const ham = document.getElementById('js-hamburger');
        const nav = document.getElementById('js-nav');
        ham.addEventListener('click', function () {
            ham.classList.toggle('is-active');
            nav.classList.toggle('is-active');
        });
    </script>
</main>
</body>
</html>