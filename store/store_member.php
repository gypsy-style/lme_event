<?php
require_once('../vendor/autoload.php'); //LINE BOT SDKを読み込み
require_once('../../../../wp-load.php'); //WordPressの基本機能を読み込み
require_once('../line-members.php'); //LINE Connectを読み込み
require_once('../includes/html.php');

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

    <link href="../css/default.css" rel="stylesheet" media="all">
    <link href="../css/front.css" rel="stylesheet" media="all">

    <title>WAKUWAKU POINT [登録スタッフ]</title>
    <script
        src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4="
        crossorigin="anonymous"></script>
    <script src="https://static.line-scdn.net/liff/edge/2.1/sdk.js"></script>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" type="text/javascript"></script>
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
            $(document).on('click', '.modal_delete', function() {
                let userID = $(this).attr('data-user_id');
                if(!userID) {
                    alert('LINE IDが取得できませんでした');
                    return false;
                }
                let deleteStaffName = $(this).attr('data-delete_staff_name');
                console.log(deleteStaffName)
                $('#delete_staff_name').html(deleteStaffName);
                $('input[name=delete_staff_user_id]').val(userID);

                $('#modal_delete').addClass("active");
                $('#modal_delete').parent().addClass("active");
                // $(id).parent().addClass("active");
            });

            $('#member-delete-btn').on('click',function(){
                let deleteUserID = $('input[name=delete_staff_user_id]').val();
                let post = {
                        user_id: deleteUserID
                    };

                    $.ajax({
                        type: "GET",
                        url: "<?= home_url(); ?>/wp-json/wp/v2/delete_store_member",
                        dataType: "text",
                        data: post
                    }).done(function(data) {
                        console.log(data);
                        alert('スタッフを削除しました');
                        liff.closeWindow();


                    }).fail(function(XMLHttpRequest, textStatus, errorThrown) {
                        alert('削除に失敗しました');
                        liff.closeWindow();

                    });

            })
        });
    </script>
    <script>
        $(function() {
            <?php
            $liff_id_store_member = get_option('liff_id_store_member');
            $liff_id_store_add_member = get_option('liff_id_store_add_member');
            ?>
            // 追加
            initializeLiff("<?= $liff_id_store_member; ?>");

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
        let store_id;
        getProfile = function() {
            liff.getProfile()
                .then(profile => {
                    userId = profile.userId;
                    if (!userId || userId == undefined) {
                        <?= Html::api_error_handle('lineIDが取得できませんでした', 'richmenu_profile', 'リッチメニューが正常に取得できませんでした。スタッフにお問い合わせください。'); ?>
                        return false;
                    }
                    displayName = profile.displayName;
                    post = {
                        line_id: userId
                    };

                    $.ajax({
                        type: "GET",
                        url: "<?= home_url(); ?>/wp-json/wp/v2/get_store_member",
                        dataType: "json",
                        data: post
                    }).done(function(data) {
                        console.log(data);
                        store_id = data['store_id'];
                        if (!store_id) {
                            $('.modal_open').prop('disabled', true);

                        }
                        $('.staff-list').html(data['html']);


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
                    alert(err.code);
                    alert(err.message);
                    // alert("liff getProfile error : " + err);
                });
        }
    </script>
    <script>
        // スタッフ紹介URLをコピー
        function copyToClipboard() {
            const liffIdStoreAddMember = "<?= $liff_id_store_add_member ?>"; // Replace this with your actual PHP variable
            const url = `https://liff.line.me/${liffIdStoreAddMember}?store_id=${store_id}`;

            // Create a temporary input element to hold the URL
            const tempInput = document.createElement("input");
            document.body.appendChild(tempInput);
            tempInput.value = url;

            // Select the text in the input element and copy it to the clipboard
            tempInput.select();
            tempInput.setSelectionRange(0, 99999); // For mobile devices
            document.execCommand("copy");

            // Remove the temporary input element
            document.body.removeChild(tempInput);

            // Show an alert to notify the user
            alert("クリップボードにコピーしました");
        }
    </script>
</head>

<body class="lmf-point_body shop">
    <div class="lmf-container">
        <div class="lmf-title_block">
            <h1 class="title">登録スタッフ</h1>
        </div>
        <?php
        if($show_banner == 1){
			Html::store_banner();
        }
		?>
        <main class="lmf-main_contents">
            <section class="lmf-content">
                <div class="staff-list">

                </div>
                <!-- <div class="lmf-staff_block lmf-white_block">
				<dl class="lmf-info_list">
					<dt>名前</dt>
					<dd class="name">YUKA</dd>
					<dt>LINE ID</dt>
					<dd class="id">U45336016f2898df141870e212bf3f7f6</dd>
				</dl>
				<p class="lmf-btn_box btn_pk btn_min"><button type="button" data-href="#modal_delete" class="modal_open">削除する</button></p>
			</div>
			<div class="lmf-staff_block lmf-white_block">
				<dl class="lmf-info_list">
					<dt>名前</dt>
					<dd class="name">YUKA</dd>
					<dt>LINE ID</dt>
					<dd class="id">U45336016f2898df141870e212bf3f7f6</dd>
				</dl>
				<p class="lmf-btn_box btn_pk btn_min"><button type="button" data-href="#modal_delete" class="modal_open">削除する</button></p>
			</div> -->
                <p class="lmf-btn_box"><button type="button" data-href="#modal_add" class="modal_open">スタッフを追加する</button></p>

            </section>
        </main>
    </div><!-- /.lmf-container -->

    <div class="lmf-modal_wrap">
        <div class="lmf-modal_layer"></div>
        <div class="lmf-modal_content staff" id="modal_add">
            <div class="modal_close_btn"><button>&times;</button></div>
            <div class="inner">
                <div class="text_block">
                    <p> スタッフを追加するには下記URLをスタッフに送信し追加するスタッフ自身で登録をしてください。</p>
                </div>
                <p class="lmf-btn_box btn_small">
                    <a href="#" onclick="copyToClipboard()">URLをコピーする</a>
                </p>
            </div>
        </div>
        <div class="lmf-modal_content staff" id="modal_delete">
            <div class="modal_close_btn"><button>&times;</button></div>
            <div class="inner">
                <div class="text_block">
                    <p>名前：<span id="delete_staff_name"></span><br>
                        のスタッフ情報を削除します</p>
                    <p>※削除すると戻すことはできません。ご注意ください。</p>
                </div>
                <input type="hidden" name="delete_staff_user_id" value="">
                <p class="lmf-btn_box btn_small"><a href="#" id="member-delete-btn">スタッフを削除する</a></p>
            </div>
        </div>
    </div><!-- /.modal_wrap -->
</body>

</html>