<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">

    <meta name="robots" content="noindex,follow">
    <meta name="viewport" content="width=device-width,user-scalable=no">
    <meta name="format-detection" content="telephone=no" />

    <link href="./css/default.css" rel="stylesheet" media="all">
    <link href="./css/front.css" rel="stylesheet" media="all">

    <title>WAKUWAKU POINT [会員登録]</title>
    <script
        src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4="
        crossorigin="anonymous"></script>
    <script src="https://static.line-scdn.net/liff/edge/2.1/sdk.js"></script>
    <script>
        $(function() {
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
                        if (!(type == 'radio' && same_radio == input_name) && input_name != 'form_type') {
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
                    <?php
                    if ($after_registration_action) {
                        if ($after_registration_action == 1) {
                            $registration_redirect_url = get_option('registration_redirect_url');
                            if (!$registration_redirect_url) :
                    ?>
                                liff.closeWindow();
                                return false;
                            <?php
                            endif;
                            ?>
                            let redirect_url = "<?= $registration_redirect_url; ?>";
                        <?php
                        } elseif ($after_registration_action == 2) {
                        ?>
                            let redirect_url = 'https://liff.line.me/<?= $liff_id_profile; ?>';
                        <?php
                        }
                        ?>
                        liff.closeWindow();
                        window.close();
                        window.location = redirect_url;
                    <?php
                    }
                    ?>
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
            liff.getProfile()
                .then(profile => {
                    userId = profile.userId;
                    displayName = profile.displayName;

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
            <h1 class="title">会員登録</h1>
        </div>
        <article class="lmf-ad_area">
            <div class="lmf-ad_block">
                <a href="#">
                    <div class="text_box">
                        <h2 class="title">180分単品飲み放題980円◆宴会のご予約受付中◆半個室席あり｜喫煙可</h2>
                        <p class="from">AD 焼き鳥酒場ゆう</p>
                    </div>
                    <figure class="fig_box"><img src="./image/ad/ad_img01.jpg" alt=""></figure>
                </a>
            </div>
        </article>
        <main class="lmf-main_contents">
            <section class="lmf-content">
                <form id="form">
                    <div class="lmf-profedit_block lmf-white_block">
                        <p class="mB20">下記内容を入力いただき登録ボタンを押してください。</p>
                        <dl class="lmf-form_box">
                            <?php
                            foreach ($custom_fields as $item_name => $item_args):
                                if ($item_name == 'point' || $item_name == 'line_id' || $item_name == 'richmenu_id' || $item_name == 'birthday_m' || $item_name == 'menber_rank' || $item_name == 'coupon_menu' || $item_name == 'coupon')
                                    continue;
                            ?>

                                <?php
                                $type = $item_args['type'];
                                $title = $item_args['title'];
                                switch ($type) {
                                    case 'text':
                                        Html::form_text($title, $item_name);
                                        break;
                                    case 'textarea':
                                        Html::form_textarea($title, $item_name);
                                        break;
                                    case 'select':
                                        $options = $item_args['options'];
                                        Html::form_select($title, $item_name, $options);
                                        break;
                                    case 'radio':
                                        $options = $item_args['options'];
                                        Html::form_radio($title, $item_name, $options);
                                        break;
                                    case 'checkbox':
                                        $options = $item_args['options'];
                                        Html::form_checkbox($title, $item_name, $options);
                                        break;

                                    case 'birthday_y':
                                        Html::form_birthday();
                                        break;
                                    default:
                                        break;
                                }
                                ?>

                            <?php
                            endforeach;
                            ?>
                            <dd class="text">登録にあたりワクワクポイントの<a href="#">利用規約</a>をご確認ください。</dd>
                            <dd class="center"><label for="term"><input type="checkbox" name="term" id="term">利用規約に同意する</label></dd>
                        </dl>
                    </div>
                    <p class="lmf-btn_box btn_gy"><button type="submit">登録する</button></p>
                </form>
            </section>
        </main>
    </div><!-- /.lmf-container -->
</body>

</html>