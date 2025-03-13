<?php
require_once('vendor/autoload.php'); //LINE BOT SDKを読み込み
require_once('../../../wp-load.php'); //WordPressの基本機能を読み込み
require_once('line-members.php'); //LINE Connectを読み込み
require_once('includes/html.php');

$enabled_coupon = get_option('enabled_coupon');
$not_exist_redirect = get_option('not_exist_redirect');

$show_banner = get_option('show_banner');

$categories = get_categories(array(
    'taxonomy' => 'event_category', // カテゴリータクソノミー
    'hide_empty' => false,    // 投稿がないカテゴリーも表示
));

$liff_id_mypage = get_option('liff_id_mypage');

?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">

    <meta name="robots" content="noindex,follow">
    <meta name="viewport" content="width=device-width,user-scalable=no">
    <meta name="format-detection" content="telephone=no" />

    <link href="./css/default.css" rel="stylesheet" media="all">
    <link href="./css/front.css" rel="stylesheet" media="all">

    <title>マイページ 申し込み済みイベント</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" type="text/javascript"></script>
    <script src="https://static.line-scdn.net/liff/edge/2.1/sdk.js"></script>
    <script>
        $(function() {
            $('button[type="submit"]').prop('disabled', true);
            $('#agreement-warning').hide();

            // Enable or disable the button based on the checkbox and show/hide message
            $('#term').change(function() {
                if ($(this).is(':checked')) {
                    $('button[type="submit"]').prop('disabled', false);
                    $('#agreement-warning').hide(); // Hide the warning message
                } else {
                    $('button[type="submit"]').prop('disabled', true);
                    $('#agreement-warning').show(); // Show the warning message
                }
            });
            <?php
            $liff_id_event_schedule_history = get_option('liff_id_event_schedule_history');
            $after_registration_action = get_option('after_registration_action');
            $liff_id_profile = get_option('liff_id_profile');
            ?>
            // 追加
            initializeLiff("<?= $liff_id_event_schedule_history; ?>");

        });

        // 追加
        function initializeLiff(liffId) {
            liff
                .init({
                    liffId: liffId
                })
                .then(() => {
                    if (!liff.isLoggedIn()) {
                        liff.login()
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
        let accessToken;

        getProfile = function() {   
            let post = {};
                // アクセストークンをセット
                accessToken = liff.getAccessToken();
                post['access_token'] = accessToken;
            
            $.ajax({
                    type: "GET",
                    url: "<?= home_url(); ?>/wp-json/wp/v2/event_entry_list_history",
                    dataType: "json",
                    data: post
                }).done(function(response) {

                    let html = response.html;
                    $('#contents-area').html(html);
                }).fail(function(XMLHttpRequest, textStatus, errorThrown) {
                    alert(errorThrown);
                });
        };
    </script>
</head>

<body class="lmf-schedule_body cust">
    <div class="lmf-container">
        <div class="lmf-title_block">
            <h1 class="title">マイページ</h1>
        </div>
        <main class="lmf-main_contents">
            <section class="lmf-content">
                <div class="lmf-tab_wrapper" id="contents-area">
                    
                </div>
                <p class="lmf-btn_box btn_gy"><a href="https://liff.line.me/<?=$liff_id_mypage;?>">戻る</a></p>
            </section>
        </main>
    </div><!-- /.lmf-container -->
</body>

</html>