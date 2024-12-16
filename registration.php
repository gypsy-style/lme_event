<?php
require_once('vendor/autoload.php');    //LINE BOT SDKを読み込み
require_once('../../../wp-load.php');    //WordPressの基本機能を読み込み
require_once('line-members.php');        //LINE Connectを読み込み
require_once('includes/html.php');
require_once('extensions/custom_fields.php');
$custom_fields = custom_fields::$custom_fields;
$after_registration_action = get_option('after_registration_action');
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
    <link href="./css/front.css" rel="stylesheet" media="all">

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
            $liff_id_form = get_option('liff_id_form');
            $after_registration_action = get_option('after_registration_action');
            $liff_id_profile = get_option('liff_id_profile');
            ?>
            // 追加
            initializeLiff("<?= $liff_id_form; ?>");
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
                let same_radio;
                let birthdayMessage;
                pushMessage.push('【登録内容】');
                $("#form :input").each(function() {

                    let input = $(this); // This is the jquery object of the input, do what you will
                    let input_name = input.attr('name');
                    console.log(input_name);
                    let type = input.attr('type');
                    let val;
                    if (input_name) {
                        val = $('#' + input_name).val();
                        post[input_name] = val;
                        // pushメッセージ作成
                        if (!(type == 'radio' && same_radio == input_name) && input_name != 'form_type' && input_name != 'term') {
                            same_radio = input_name;
                            let title = input.attr('data-title');

                            if (title) {
                                pushMessage.push(title + '：' + val);
                            } else {
                                pushMessage.push(val);
                            }
                        }
                    }
                });
                // pushMessage.push('お誕生日：'+post['birthday_y']+'年'+post['birthday_m']+'月');
                post['line_id'] = userId;
                post['displayName'] = displayName;
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
                    url: "<?= home_url(); ?>/wp-json/wp/v2/register_line_user",
                    dataType: "text",
                    data: post
                }).done(function(response) {
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

        getProfile = function() {
            console.log('getprofile called');
            liff.getProfile()
                .then(profile => {
                    console.log(profile);
                    console.log(profile.userId);
                    userId = profile.userId;
                    displayName = profile.displayName;


                })
                .catch((err) => {
                    console.log("Profileが取得できません")
                    // alert("liff getProfile error : " + err);
                });
        };
    </script>
</head>

<body class="lmf-point_body cust">
    <div class="lmf-container">
        <div class="lmf-title_block">
            <h1 class="title">会員登録</h1>
        </div>
        <?php
        if ($show_banner == 1) {
            Html::store_banner();
        }
        ?>
        <main class="lmf-main_contents">
            <section class="lmf-content">
                <form id="form">
                    <div class="lmf-profedit_block lmf-white_block">
                        <p class="mB20">下記内容を入力いただき登録ボタンを押してください。</p>
                        <dl class="lmf-form_box">
                            <dt><label for="addr">会社名</label></dt>
                            <dd><em class="input"><input type="text" name="campany_name" id="campany_name"></em></dd>
                            <dt><label for="name">名前</label></dt>
                            <dd><em class="input"><input type="text" name="name" id="name"></em></dd>
                            <dt><label for="tel">電話番号</label></dt>
                            <dd><em class="input"><input type="tel" name="tel" id="tel"></em></dd>
                            <dt><label for="tel">メールアドレス</label></dt>
                            <dd><em class="input"><input type="email" name="email" id="email"></em></dd>
                            <dt><label for="sex">性別</label></dt>
                            <dd><select name="sex" id="sex">
                                    <option value="">----選択してください----</option>
                                    <option value="">男性</option>
                                    <option value="">女性</option>
                                    <option value="">その他</option>
                                </select></dd>
                            <dd class="text">登録にあたり南大阪経営研究会の<a href="privacy.html">利用規約</a>をご確認ください。</dd>
                            <dd class="center"><label for="term"><input type="checkbox" name="term" id="term">利用規約に同意する</label></dd>
                        </dl>
                        <p id="agreement-warning" style="color: red;text-align:center;">利用規約に同意してください</p>
                    </div>

                    <p class="lmf-btn_box btn_gy"><button type="submit">登録する</button></p>
                </form>
            </section>
        </main>
    </div><!-- /.lmf-container -->
</body>

</html>