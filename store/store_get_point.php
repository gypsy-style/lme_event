<?php
require_once('../vendor/autoload.php'); //LINE BOT SDKを読み込み
require_once('../../../../wp-load.php'); //WordPressの基本機能を読み込み
require_once('../line-members.php'); //LINE Connectを読み込み
require_once('../includes/html.php');

$enabled_coupon = get_option('enabled_coupon');
$not_exist_redirect = get_option('not_exist_redirect');
$show_banner = get_option('show_banner');

$liff_id_store_point_get = get_option('liff_id_store_point_get');
            $liff_id_form = get_option('liff_id_form');
            $liff_id_store_point_history = get_option('liff_id_store_point_history');
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">

    <meta name="robots" content="noindex,follow">
    <meta name="viewport" content="width=device-width,user-scalable=no">
    <meta name="format-detection" content="telephone=no" />

    <link href="../css/default.css" rel="stylesheet" media="all">
    <link href="../css/front.css" rel="stylesheet" media="all">

    <title>WAKUWAKU POINT [ポイントをもらう]</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" type="text/javascript"></script>
    <script src="https://static.line-scdn.net/liff/edge/2.1/sdk.js"></script>
    <script type="text/javascript">
        $(function() {
            $('.modal_open').click(function() {
                post = {
                    line_id: userId
                };

                $.ajax({
                    type: "GET",
                    url: "<?= home_url(); ?>/wp-json/wp/v2/store_get_latest_point_info",
                    dataType: "json",
                    data: post
                }).done(function(response) {
                    console.log(response)
                    $.each(response, function(key, value) {
                        if (key === 'latest_cta_button') {
                            // QRコード(SVG)を表示
                            // $('#qrcode').html(value);
                            $('#latest_cta_button').html(value);
                        } else {
                            // 他のデータを表示
                            $('#' + key).html(value);
                        }
                    });
                }).fail(function(XMLHttpRequest, textStatus, errorThrown) {

                    alert(errorThrown);
                });
                id = $($(this).data('href'));
                $(id).addClass("active");
                $(id).parent().addClass("active");
                $('.lmf-modal_layer').addClass("active");
            });
            $('.modal_close_btn').click(function() {
                $(this).parent().removeClass("active");
                $(this).parent().parent().removeClass("active");
                $('.lmf-modal_layer').removeClass("active");
            });
            $('.lmf-modal_layer').click(function() {
                $('.lmf-modal_wrap').removeClass("active");
                $('.lmf-modal_content').removeClass("active");
            });
        });
    </script>

    <script>
        $(function() {

            <?php
            
            ?>
            // 追加: LIFFの初期化
            initializeLiff("<?= $liff_id_store_point_get; ?>");

        });

        // 追加: LIFFの初期化関数
        function initializeLiff(liffId) {
            liff
                .init({
                    liffId: liffId
                })
                .then(() => {
                    if (!liff.isLoggedIn()) {
                        liff.login();
                    }
                    getProfile();
                })
                .catch((err) => {
                    console.log('LIFF Initialization failed ', err);
                });
        }

        let userId;
        let displayName;
        let post;

        // LIFFからプロフィールを取得して、QRコードを取得
        function getProfile() {
            liff.getProfile()
                .then(profile => {
                    userId = profile.userId;
                    if (!userId) {
                        <?= Html::api_error_handle('lineIDが取得できませんでした', 'richmenu_profile', 'リッチメニューが正常に取得できませんでした。スタッフにお問い合わせください。'); ?>
                        return false;
                    }
                    displayName = profile.displayName;
                    post = {
                        line_id: userId
                    };

                    // サーバーにAJAXリクエストを送信してQRコードを取得
                    $.ajax({
                        type: "GET",
                        url: "<?= home_url(); ?>/wp-json/wp/v2/store_get_point",
                        dataType: "json", // データ形式をJSONに変更
                        data: post
                    }).done(function(response) {
                        console.log(response);
                        if (response.length === 0) {
                            handleNoUserData();
                        } else {
                            displayUserData(response);
                        }
                        // liff.closeWindow();
                    }).fail(function(XMLHttpRequest, textStatus, errorThrown) {
                        alert(errorThrown);
                    });
                })
                .catch((err) => {
                    alert("LIFF getProfile error: " + err.message);
                });
        }

        // ユーザーデータがない場合の処理
        function handleNoUserData() {
            let ua = window.navigator.userAgent.toLowerCase();
            <?php if ($not_exist_redirect == 1) { ?>
                let form_url = 'https://liff.line.me/<?= $liff_id_form; ?>';
                if (ua.indexOf('iphone') !== -1) {
                    liff.closeWindow();
                    window.location = form_url;
                } else {
                    liff.closeWindow();
                    liff.openWindow({
                        url: form_url,
                        external: false
                    });
                }
            <?php } else if (!empty($not_exist_alert_message)) { ?>
                alert('<?= $not_exist_alert_message; ?>');
            <?php } ?>
        }

        // ユーザーデータとQRコードを表示
        function displayUserData(response) {
            $.each(response, function(key, value) {
                if (key === 'qrcode') {
                    // QRコード(SVG)を表示
                    // $('#qrcode').html(value);
                    $('#qrcode').children('img').attr('src', value);
                } else {
                    // 他のデータを表示
                    $('#' + key).append(value);
                    $('#' + key).addClass(key + '-' + value);
                }
            });
        }
    </script>
</head>

<body class="lmf-point_body shop">
    <div class="lmf-container">
        <div class="lmf-title_block">
            <h1 class="title">ポイントをもらう</h1>
        </div>
        <?php
        if ($show_banner == 1) {
            Html::store_banner();
        }
        ?>
        <main class="lmf-main_contents">
            <section class="lmf-content">
                <div class="lmf-user_block">
                    <h2 class="shop" id="store_name"></h2>
                </div>
                <div class="lmf-white_block lmf-qr_block center">
                    <span class="img" id="qrcode"><img src="" alt=""></span>
                    <p class="text">このQRコードを会員様に見せてください</p>
                </div>
                <p class="lmf-btn_box"><a href="https://liff.line.me/<?=$liff_id_store_point_history;?>"><button type="button" data-href="#modal_latest" class="">ポイント付与を確認</button></a></p>
            </section>
        </main>
    </div><!-- /.lmf-container -->

    <div class="lmf-modal_layer"></div>
    <div class="lmf-modal_wrap">
        <div class="lmf-modal_content" id="modal_latest">
            <div class="modal_close_btn"><button>&times;</button></div>
            <div class="inner">
                <h2>最新ポイント更新情報</h2>
                <div class="point_block">
                    <h3 class="lmf-title_bar"><em class="label" id="user_name"></em></h3>
                    <p class="points color__pk"><b class="point" id="latest_point"></b><span class="unit">pt</span></p>
                    <div class="limit_box"><em class="text">有効期限　<span id="point_limit_date"></span></em></div>
                </div>
            </div>
        </div>
    </div><!-- /.modal_wrap -->
</body>

</html>