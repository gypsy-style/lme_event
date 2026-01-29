<?php
require_once('vendor/autoload.php'); //LINE BOT SDKを読み込み
require_once('../../../wp-load.php'); //WordPressの基本機能を読み込み
require_once('line-members.php'); //LINE Connectを読み込み
require_once('includes/html.php');

$enabled_coupon = get_option('enabled_coupon');
$not_exist_redirect = get_option('not_exist_redirect');

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

    <title>WAKUWAKU POINT [ポイントカード]</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" type="text/javascript"></script>
    <script src="https://static.line-scdn.net/liff/edge/2.1/sdk.js"></script>
    <script type="text/javascript">
        $(function() {
            $('.modal_open').click(function() {
                id = $($(this).data('href'));
                $(id).addClass("active");
                $(id).parent().addClass("active");
            });
            $('.modal_close_btn').click(function() {
                $(this).parent().removeClass("active");
                $(this).parent().parent().removeClass("active");
            });
            $('.lmf-modal_layer').click(function() {
                $(this).parent().removeClass("active");
                $(this).parent().find(".active").removeClass("active");
            });
        });
    </script>
    <script>
        $(function() {
            <?php
            $liff_id_point_card = get_option('liff_id_point_card');
            $liff_id_form = get_option('liff_id_form');
            $liff_id_get_point = get_option('liff_id_get_point');
            $liff_id_point_history = get_option('liff_id_point_history');
            $liff_id_profile = get_option('liff_id_profile');
            ?>
            // 追加: LIFFの初期化
            initializeLiff("<?= $liff_id_point_card; ?>");

            $('#btn_point_use').on('click', function(e) {
                e.preventDefault();
                liff.scanCodeV2()
                    .then((result) => {
                        // e.g. result = { value: 'Hello LIFF app!' }
                        const scannedValue = result.value;
                        alert(scannedValue)
                        // window.location.href = scannedValue;
                        liff.closeWindow();
                        liff.openWindow({
                            url: scannedValue,
                            external: false
                        });

                        // スキャンされた内容がURLであればリダイレクト
                        if (isValidURL(scannedValue)) {
                            window.location.href = scannedValue;
                        } else {
                            alert('スキャンされた内容はURLではありません: ' + scannedValue);
                        }
                    })
                    .catch((err) => {
                        console.log(err);
                    });
            })

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
                        url: "<?= home_url(); ?>/wp-json/wp/v2/point_card",
                        dataType: "json", // データ形式をJSONに変更
                        data: post
                    }).done(function(response) {
                        console.log(response);
                        if (response.length === 0) {
                            handleNoUserData();
                        } else {
                            displayUserData(response);
                        }
                    }).fail(function(XMLHttpRequest, textStatus, errorThrown) {
                        alert(errorThrown);
                    });
                })
                .catch((err) => {
                    // alert("LIFF getProfile error: " + err.message);
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

<body class="lmf-point_body cust">
    <div class="lmf-container">
        <div class="lmf-title_block">
            <h1 class="title">ポイントカード</h1>
        </div>
        <?php
        if ($show_banner == 1) {
            Html::store_banner();
        }
        ?>
        <main class="lmf-main_contents">
            <section class="lmf-content">
                <div class="lmf-cards_block content_user">
                    <div class="inner">
                        <h2 class="lmf-title_bar"><em class="label">保有ポイント</em></h2>
                        <p class="cardpt_box"><b class="points"><span class="point" id="point"></span><span class="pt">pt</span></b></p>
                    </div>
                </div>
                <div class="lmf-user_block">
                    <h2 class="name"><span id="name"></span>様</h2>
                    <p class="id" id="line_id"></p>
                    <div class="limit_box"><em class="text" id="point_limit_date"></em></div>
                </div>
            </section>
            <section class="lmf-content">
                <div class="lmf-visited_area">
                    <h2 class="lmf-title_sub">最近訪れた店舗</h2>
                    <div class="lmf-visited_block lmf-white_block">
                        <ul class="lmf-shop_list" id="point_history_html">
                            <!-- <li>
                                <div class="inner">
                                    <figure class="fig_box"><img src="../image/front/no_img400.jpg" alt=""></figure>
                                    <div class="info_box">
                                        <h3 class="name">green gree garden</h3>
                                        <ul class="sns_list sns">
                                            <li class="line"><a href="#"><span>&nbsp;</span></a></li>
                                            <li class="insta"><a href="#"><span>&nbsp;</span></a></li>
                                            <li class="web"><a href="#"><span>&nbsp;</span></a></li>
                                        </ul>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="inner">
                                    <figure class="fig_box"><img src="../image/front/no_img400.jpg" alt=""></figure>
                                    <div class="info_box">
                                        <h3 class="name">パフォーマンスラボ ノリトレ</h3>
                                        <ul class="sns_list sns">
                                            <li class="line"><a href="#"><span>&nbsp;</span></a></li>
                                            <li class="insta"><a href="#"><span>&nbsp;</span></a></li>
                                            <li class="web"><a href="#"><span>&nbsp;</span></a></li>
                                        </ul>
                                    </div>
                                </div>
                            </li> -->
                        </ul>
                    </div>
                </div>
            </section>
            <section class="lmf-content content_utl">
                <div class="lmf-utl_area">
                    <ul class="lmf-btn_list" style="display:none;">
                        <li class="pget"><a href="https://liff.line.me/<?= $liff_id_get_point; ?>"><span class="text">貯める</span></a></li>
                        <li class="use"><a href="#" id="btn_point_use"><span class="text">使う</span></a></li>
                        <li class="record"><a href="https://liff.line.me/<?= $liff_id_point_history; ?>"><span class="text">ポイント履歴</span></a></li>
                        <li class="info"><a href="https://liff.line.me/<?= $liff_id_profile; ?>"><span class="text">登録情報</span></a></li>
                    </ul>
                </div>
            </section>
        </main>
    </div><!-- /.lmf-container -->

    <div class="lmf-modal_wrap">
        <div class="lmf-modal_layer"></div>
        <div class="lmf-modal_content" id="modal_expired">
            <div class="modal_close_btn"><button>&times;</button></div>
            <div class="inner">
                <h2>ポイント有効期限切れ</h2>
                <div class="text_block">
                    <p>お客様のポイントが<span class="data">2024年10月24日</span>にて失効しました。ポイントの有効期限は最終利用日から1年となります。</p>
                    <p>ポイントカードを再度使用する場合は今まで貯めていたポイントはなくなります。</p>
                    <p>上記内容をご確認の上以下のボタンより再発行のお手続をお願いいたします。</p>
                    <p class="center"><a href="#">ポイントの有効期限切れについて</a></p>
                    <p class="lmf-btn_box">
                        <a href="#">リセットして再発行</a>
                    </p>
                </div>
            </div>
        </div>
    </div><!-- /.modal_wrap -->
</body>

</html>