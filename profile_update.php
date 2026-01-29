<?php
require_once('vendor/autoload.php');    //LINE BOT SDKを読み込み
require_once('../../../wp-load.php');    //WordPressの基本機能を読み込み
require_once('line-members.php');        //LINE Connectを読み込み
require_once('includes/html.php');

$line_user_post_id = $_GET['user_id'] ?? 0;
if (!$line_user_post_id) {
    // profileへリダイレクト
    $liff_id_profile = get_option('liff_id_profile');
    header("Location: https://liff.line.me/" . $liff_id_profile);
    exit;
}
$name = get_post_meta($line_user_post_id, 'name', true);
$sex = get_post_meta($line_user_post_id, 'sex', true);
$campany_name = get_post_meta($line_user_post_id, 'campany_name', true);
$tel = get_post_meta($line_user_post_id, 'tel', true);
$email = get_post_meta($line_user_post_id, 'email', true);

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

    <title>日創研南大阪経営研究会[会員登録]</title>
    <script
        src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4="
        crossorigin="anonymous"></script>
    <script src="https://static.line-scdn.net/liff/edge/2.1/sdk.js"></script>
    <script type="text/javascript">
        $(function() {
            $('.modal_open').click(function(e) {
                e.preventDefault();

                id = $('#modal_terms');
                console.log(id);
                $('#modal_terms').addClass("active");
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


            // $('#form').submit(function(event) {
            //     console.log('submit')
            //     event.preventDefault();
            //     let type = 'register';

            //     let form = document.getElementById('form');
            //     // console.log(document.forms.line-members-form);
            //     let formData = new FormData(form);

            //     // console.log(values);
            //     let post = {};
            //     const pushMessage = '【会員登録済】';
            //     let same_radio;
            //     let birthdayMessage;
            //     $("#form :input").each(function() {
            //         const input = $(this); // This is the jquery object of the input, do what you will
            //         const input_name = input.attr('name');
            //         const val = $('#' + input_name).val();
            //         post[input_name] = val;

            //     });


            //     $.ajax({
            //         type: "GET",
            //         url: "<?= home_url(); ?>/wp-json/wp/v2/update_line_user",
            //         dataType: "json",
            //         data: post
            //     }).done(function(response) {
            //         console.log(response);
            //         if (response.status == 'success') {
            //             alert('更新完了しました');
            //             // profileへリダイレクト
            //             const liffIDProfile = "<?= get_option('liff_id_profile'); ?>";
            //             location.href = "https://liff.line.me/" + liffIDProfile;
            //         } else {
            //             alert('更新に失敗しました');
            //         }
            //         // liff.closeWindow();
            //     }).fail(function(XMLHttpRequest, textStatus, errorThrown) {
            //         alert(errorThrown);
            //     });
            //     event.preventDefault();

            // });

            $('#form').submit(function(event) {
                event.preventDefault();

                const form = document.getElementById('form');
                const formData = new FormData(form); // ファイルも含む

                $.ajax({
                    type: "POST",
                    url: "<?= home_url(); ?>/wp-json/wp/v2/update_line_user",
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: "json",
                }).done(function(response) {
                    if (response.status == 'success') {
                        alert('更新完了しました');
                        const liffIDProfile = "<?= get_option('liff_id_profile'); ?>";
                        location.href = "https://liff.line.me/" + liffIDProfile;
                    } else {
                        alert('更新に失敗しました');
                    }
                }).fail(function(xhr, status, error) {
                    alert(error);
                });
            });
        });
    </script>
</head>

<body class="lmf-point_body cust">
    <div class="lmf-container">
        <div class="lmf-title_block">
            <h1 class="title">登録情報修正</h1>
        </div>

        <main class="lmf-main_contents">
            <section class="lmf-content">
                <form id="form" enctype="multipart/form-data">
                    <div class="lmf-profedit_block lmf-white_block">
                        <p class="mB20">下記内容を入力いただき登録ボタンを押してください。</p>
                        <dl class="lmf-form_box">
                            <dt><label for="name">名前</label></dt>
                            <dd><em class="input"><input type="text" name="name" id="name" value="<?= $name; ?>"></em></dd>
                            <dt><label for="name">性別</label></dt>
                            <dd><select name="sex" id="sex">
                                    <option value="">----選択してください----</option>
                                    <option value="男性" <?= $sex == '男性' ? ' selected' : ''; ?>>男性</option>
                                    <option value="女性" <?= $sex == '女性' ? ' selected' : ''; ?>>女性</option>
                                    <option value="その他" <?= $sex == 'その他' ? ' selected' : ''; ?>>その他</option>
                                </select></dd>
                            <dt><label for="addr">会社名</label></dt>
                            <dd><em class="input"><input type="text" name="campany_name" value="<?= $campany_name; ?>" id="campany_name"></em></dd>
                            <dt><label for="tel">電話番号</label></dt>
                            <dd><em class="input"><input type="tel" name="tel" id="tel" value="<?= $tel; ?>"></em></dd>
                            <dt><label for="tel">メールアドレス</label></dt>
                            <dd><em class="input"><input type="email" name="email" id="email" value="<?= $email; ?>"></em></dd>
                            <dt><label for="thumbnail">プロフィール画像</label></dt>
                            <dd><input type="file" name="thumbnail" id="thumbnail" accept="image/*"></dd>
                            <dt><label for="message">メッセージ</label></dt>
                            <dd><em class="input"><input type="text" name="message" value="<?= $message; ?>" id="message"></em></dd>
                        </dl>
                    </div>

                    <input type="hidden" name="post_id" id="post_id" value="<?= $line_user_post_id; ?>">
                    <p class="lmf-btn_box btn_gy"><button type="submit">更新する</button></p>
                </form>
            </section>
        </main>
    </div><!-- /.lmf-container -->
</body>

</html>