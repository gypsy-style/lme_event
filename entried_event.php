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
$event_id = isset($_GET['event_id']) ? $_GET['event_id'] : 0;
$liff_id_event_schedule_history = get_option('liff_id_event_schedule_history');

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

    <title>スケジュール</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" type="text/javascript"></script>
    <script src="https://static.line-scdn.net/liff/edge/2.1/sdk.js"></script>
    <script>
        $(function() {
            // ここで eventId を取得
            const eventId = <?= isset($_GET['event_id']) ? '"' . esc_js($_GET['event_id']) . '"' : '""'; ?>;

            $('button[type="submit"]').prop('disabled', true);
            $('#agreement-warning').hide();

            // Enable or disable the button based on the checkbox and show/hide message
            $('#term').change(function() {
                if ($(this).is(':checked')) {
                    $('button[type="submit"]').prop('disabled', false);
                    $('#agreement-warning').hide();
                } else {
                    $('button[type="submit"]').prop('disabled', true);
                    $('#agreement-warning').show();
                }
            });

            <?php
            $liff_id_entried_event = get_option('liff_id_entried_event');
            ?>

            initializeLiff("<?= $liff_id_entried_event; ?>", eventId);
        });

        function initializeLiff(liffId, eventId) {
            liff.init({
                liffId: liffId
            }).then(() => {
                console.log("Event ID:", eventId);
                if (!liff.isLoggedIn()) {
                    let redirectUri = window.location.origin + window.location.pathname;
                    if (eventId) {
                        redirectUri += '?event_id=' + eventId;
                    }
                    liff.login({
                        redirectUri: redirectUri
                    });
                } else {
                    getProfile(eventId);
                }
            }).catch((err) => {
                console.log('LIFF Initialization failed:', err);
            });
        }

        function getProfile(eventId) {
            let post = {};
            let accessToken = liff.getAccessToken();
            post['access_token'] = accessToken;
            post['event_id'] = eventId;

            console.log("Fetching profile for event ID:", eventId);

            $.ajax({
                type: "GET",
                url: "<?= home_url(); ?>/wp-json/wp/v2/entried_event",
                dataType: "json",
                data: post
            }).done(function(response) {
                console.log(response);
                let html = response.html;
                $('#contents-area').html(html);
            }).fail(function(XMLHttpRequest, textStatus, errorThrown) {
                console.log(textStatus);
                console.log(errorThrown);
                alert(errorThrown);
            });
        }
    </script>
    <script>
        $(function() {
            $(document).on('click', '#btn-cancel button', function() {
                const userId = $(this).data('user_id');
                const eventId = $(this).data('event_id');
                let post = {};
                post['user_id'] = userId;
                post['event_id'] = eventId;
                console.log('userId:' + userId);
                console.log('eventId:' + eventId);
                // alert('キャンセルボタンがクリックされました');
                if (!confirm("本当にキャンセルしますか？")) {
                    return; // ユーザーがキャンセルを押した場合は処理を中断
                }
                $.ajax({
                    type: "GET",
                    url: "<?= home_url(); ?>/wp-json/wp/v2/cancel_event",
                    dataType: "json",
                    data: post
                }).done(function(response) {
                    const status = response.status;
                    if (status == 'success') {
                        alert('イベントをキャンセルしました');
                        // 申し込み済み一覧へリダイレクト
                        location.href = "https://liff.line.me/<?= $liff_id_event_schedule_history; ?>";
                    }
                }).fail(function(XMLHttpRequest, textStatus, errorThrown) {
                    console.log(textStatus);
                    console.log(errorThrown);
                    alert(errorThrown);
                });
            });
        })
    </script>
</head>

<body class="lmf-schedule_body cust">
    <div class="lmf-container">
        <div class="lmf-title_block">
            <h1 class="title">スケジュール</h1>
        </div>
        <main class="lmf-main_contents">
            <section class="lmf-content">
                <ul class="lmf-pnavi_list clearfix">
                    <li class="back"><a href="https://liff.line.me/<?= $liff_id_event_schedule_history; ?>">一覧へ戻る</a></li>
                </ul>
                <div class="lmf-single_block schedule lmf-white_block" id="contents-area">

                </div>
                <ul class="lmf-pnavi_list clearfix">
                    <li class="back"><a href="https://liff.line.me/<?= $liff_id_event_schedule_history; ?>">一覧へ戻る</a></li>
                </ul>
            </section>

        </main>
    </div><!-- /.lmf-container -->
</body>

</html>