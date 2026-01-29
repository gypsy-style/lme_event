<?php
require_once('vendor/autoload.php'); //LINE BOT SDKを読み込み
require_once('../../../wp-load.php'); //WordPressの基本機能を読み込み
require_once('line-members.php'); //LINE Connectを読み込み
require_once('includes/html.php');

$enabled_coupon = get_option('enabled_coupon');
$not_exist_redirect = get_option('not_exist_redirect');
$not_exist_alert_message = get_option('not_exist_alert_message');
$show_banner = get_option('show_banner');
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">

    <meta name="robots" content="noindex,follow">
    <meta name="viewport" content="width=device-width,user-scalable=no">
    <meta name="format-detection" content="telephone=no" />

    <link href="./css/default.css" rel="stylesheet" media="all">
    <link href="./css/front260129.css" rel="stylesheet" media="all">
    <link href="./css/front-init-mosaka.css" rel="stylesheet" media="all">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" type="text/javascript"></script>
    <script src="https://static.line-scdn.net/liff/edge/2.1/sdk.js"></script>
    <script>
        // LIFF初期化
        document.addEventListener('DOMContentLoaded', function() {
            const liffId = "<?= get_option('liff_id_profile'); ?>"; // あなたのLIFF IDを設定してください

            liff.init({
                liffId: liffId
            }).then(() => {
                if (!liff.isLoggedIn()) {
                    // LIFFログインURLにevent_idを追加
                    liff.login();
                } else {
                    getProfile();
                }


            }).catch(err => {
                console.error("LIFF初期化エラー:", err);
            });

            let userId;
            let displayName;
            let post;
            let accessToken;

            getProfile = function() {
                let post = {};
                // アクセストークンをセット
                accessToken = liff.getAccessToken();
                post['access_token'] = accessToken;

                $.ajax({
                    type: "GET",
                    url: "<?= home_url(); ?>/wp-json/wp/v2/get_profile",
                    dataType: "json",
                    data: post
                }).done(function(response) {
                    console.log(response);

                    const contents_mypage = response.html;

                    $('#contents-area').html(contents_mypage);

                }).fail(function(XMLHttpRequest, textStatus, errorThrown) {
                    alert(errorThrown);
                });
            };
        });
    </script>

    <title>登録情報</title>

</head>

<body class="lmf-point_body cust">
    <div class="lmf-container">
        <div class="lmf-title_block">
            <h1 class="title">登録情報</h1>
        </div>

        <main class="lmf-main_contents">
            <section class="lmf-content">
                <div class="lmf-user_block">

                </div>
                <div id="contents-area">
                    <!-- <div class="lmf-profile_block lmf-white_block"> -->
                        <!-- <dl class="lmf-info_list">
					<dt>名前</dt>
					<dd>鈴木武夫</dd>
					<dt>会社名</dt>
					<dd>株式会社ブランニューデイズ</dd>
					<dt>電話番号</dt>
					<dd>08040219871</dd>
					<dt>メールアドレス</dt>
					<dd>suzuki@bran-new-days.com</dd>
				</dl> -->
                    <!-- </div>
                    <p class="lmf-btn_box btn_gy btn_small"><a href="profile_update.php?user_id=">登録情報を修正する</a></p> -->
                </div>

            </section>
        </main>
    </div><!-- /.lmf-container -->
</body>

</html>