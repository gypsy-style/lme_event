<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">

    <meta name="robots" content="noindex,follow">
    <meta name="viewport" content="width=device-width,user-scalable=no">
    <meta name="format-detection" content="telephone=no" />

    <link href="../css/default.css" rel="stylesheet" media="all">
    <link href="../css/admin.css" rel="stylesheet" media="all">

    <title>WAKUWAKU POINT 管理画面 [ログイン画面]</title>

    <script type="text/javascript" src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script type="text/javascript" src="../js/common.js"></script>

</head>

<body class="lma-point_body lma-dashboard">
    <div class="lma-container">

        <main class="lma-main_contents">
            <section class="lma-content flex">
                <div class="lma-main_head">
                    <div class="lma-title_block">
                        <h2>ログインフォーム</h2>
                    </div>
                </div>
                <div class="lma-content_block login">
                    <form method="POST">
                        <dl class="lma-form_box">
                            <dt><label for="tel">USER</label></dt>
                            <dd><input type="text" name="username" id="username" value=""></dd>
                            <dt><label for="tel">PASS</label></dt>
                            <dd><input type="password" name="password" id="password" size="30" value=""></dd>
                        </dl>
                        <p class="lma-btn_box"><button type="submit">ログイン</button></p>
                    </form>
                </div>
            </section>
        </main>
    </div>
</body>

</html>