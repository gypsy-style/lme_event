<?php
require_once('./vendor/autoload.php'); //LINE BOT SDKを読み込み
require_once('../../../wp-load.php'); //WordPressの基本機能を読み込み
require_once('./line-members.php'); //LINE Connectを読み込み
require_once('./includes/html.php');

$enabled_coupon = get_option('enabled_coupon');
$not_exist_redirect = get_option('not_exist_redirect');
$show_banner = get_option('show_banner');
// store_idから情報を取得
if (
    !isset($_REQUEST['store_id']) || empty($_REQUEST['store_id'])
) {

    if (isset($_GET['liff_state'])) {
        // liff.stateのパラメータを取得
        $liff_state = $_GET['liff_state'];



        // URLデコードして中のパラメータを取り出す
        $decoded_liff_state = urldecode($liff_state);
        $decoded_liff_state = ltrim($decoded_liff_state, '?');


        // store_idの値を解析
        parse_str($decoded_liff_state, $params);


        if (isset($params['store_id'])) {
            $store_id = $params['store_id'];
            $store_id = htmlspecialchars($store_id, ENT_QUOTES, 'UTF-8');
        } else {
            echo '店舗情報が取得できませんでした';
            exit;
        }
    } else {
        echo '店舗情報が取得できませんでした';
        exit;
    }
}
$store_id = $_REQUEST['store_id'];

$store_name = get_post_meta($store_id, 'store_name', true);

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

    <title>WAKUWAKU POINT [ポイントを使う]</title>
    <script
        src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4="
        crossorigin="anonymous"></script>
    <script src="https://static.line-scdn.net/liff/edge/2.1/sdk.js"></script>

    <script>
        $(function() {
            <?php
            $liff_id_point_use = get_option('liff_id_point_use');
            $after_registration_action = get_option('after_registration_action');
            $liff_id_form = get_option('liff_id_form');
            ?>
            // 追加
            initializeLiff("<?= $liff_id_point_use; ?>");

            $('#price').on('change', function() {
                let price = parseFloat($(this).val()); // priceを取得し、floatに変換
                console.log(price);
                let pointRate = parseFloat($('#value_of_point_rate').val()) / 100; // pointRateを%として扱うために100で割る
                console.log(pointRate);

                if (!isNaN(price) && !isNaN(pointRate)) { // 値が数値の場合のみ計算
                    let points = price * pointRate; // priceにpointRateをかけて獲得ポイントを計算
                    points = Math.ceil(points);
                    $('#point_display').text(points); // 結果を表示する場合、例えば #points_display に表示
                    $('#get_point').val(points);
                }
            })
            $('#form').submit(function(event) {
                event.preventDefault();
                let type = 'register';

                let form = document.getElementById('form');
                // console.log(document.forms.line-members-form);
                let formData = new FormData(document.forms.form);
                // console.log(formData);
                let values = formData.values();
                // console.log(values);
                let post = {};
                let pushMessage = [];
                $("#form :input").each(function() {
                    let input = $(this); // This is the jquery object of the input, do what you will
                    let input_name = input.attr('name');
                    let val;
                    if (input_name) {
                        val = $('#' + input_name).val();
                        post[input_name] = val;
                    }
                });
                // pushMessage.push('お誕生日：'+post['birthday_y']+'年'+post['birthday_m']+'月');
                post['line_id'] = userId;
                post['store_id'] = "<?= $store_id; ?>";
                post['user_id'] = postID;
                console.log(post);

                // return false;
                pushMessage = pushMessage.join('\n');
                liff.sendMessages([{
                    type: 'text',
                    text: pushMessage
                }]);
                // console.log(post);
                // return false;
                $.ajax({
                    type: "GET",
                    url: "<?= home_url(); ?>/wp-json/wp/v2/point_use",
                    dataType: "text",
                    data: post
                }).done(function(response) {
                    console.log(response);
                    alert('ポイントを利用しました');
                    liff.closeWindow();
                }).fail(function(XMLHttpRequest, textStatus, errorThrown) {
                    alert(errorThrown);
                });
                event.preventDefault();

            });
        });

        // 追加
        function initializeLiff(liffId) {
            liff
                .init({
                    liffId: liffId
                })
                .then(() => {
                    if (!liff.isLoggedIn()) {
                        liff.login({
                            redirectUri: window.location.href,
                        })
                    }

                    getProfile();
                })
                .catch((err) => {
                    console.log('LIFF Initialization failed ', err)
                });
        }
        let userId;
        let displayName;
        let post;
        let postID;

        getProfile = function() {
            liff.getProfile()
                .then(profile => {
                    userId = profile.userId;
                    displayName = profile.displayName;
                    post = {line_id:userId}

                    $.ajax({
                        type: "GET",
                        url: "<?= home_url(); ?>/wp-json/wp/v2/richmenu_profile",
                        dataType: "text",
                        data: post
                    }).done(function(data) {
                        console.log(data);
                        let jsonData = JSON.parse(data);
                        console.log(jsonData);
                        if (jsonData.length == 0) {
                            let ua = window.navigator.userAgent.toLowerCase();
                            liff.closeWindow();
                            return false;
                        }

                        $.each(jsonData, function(i, val) {
                            $('#' + i).append(val);
                            $('#' + i).addClass(i + '-' + val);

                            if (i == 'point') {
                                document.getElementById('use_point').max = val;
                            }
                            if (i == 'user_id') {
                                postID = val;
                            }
                        })



                    }).fail(function(XMLHttpRequest, textStatus, errorThrown) {
                        <?php
                        if ($not_exist_redirect == 1) :
                        ?>
                            console.log('redirect');
                            let form_url = 'https://liff.line.me/<?= $liff_id_form; ?>';
                            liff.closeWindow();
                            liff.openWindow({
                                url: form_url,
                                external: false
                            });
                        <?php
                        else:
                        ?>
                            alert(errorThrown);
                        <?php
                        endif;
                        ?>

                    });

                })
                .catch((err) => {
                    // alert("liff getProfile error : " + err);
                });
        };
    </script>
</head>

<body class="lmf-point_body cust">
    <div class="lmf-container">
        <div class="lmf-title_block">
            <h1 class="title">ポイントを使う</h1>
        </div>
        <?php
        if($show_banner == 1){
			Html::store_banner();
        }
		?>
        <main class="lmf-main_contents">
            <section class="lmf-content">
                <div class="lmf-user_block">
                    <div class="point_box">
                        <em class="label">保有ポイント</em>
                        <b class="points"><span class="point" id="point"></span><span class="unit">pt</span></b>
                    </div>
                </div>
                <form action="#" id="form">
                    <div class="lmf-puse_block lmf-white_block">
                        <h2 class="lmf-title_bar gy small"><em class="label">使用店舗</em><span class="name" id="store_name"><?= $store_name; ?></span></h2>
                        <dl class="lmf-form_box">
                            <dt><label for="point">利用ポイントを入力</label></dt>
                            <dd><em class="input number"><input type="number" name="use_point" id="use_point" max="100"></em><span class="unit">pt</span></dd>
                        </dl>
                    </div>
                    <p class="lmf-btn_box"><button type="submit">ポイントを使う</button></p>
                </form>
            </section>
        </main>
    </div><!-- /.lmf-container -->
</body>

</html>