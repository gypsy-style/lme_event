<?php
require_once( 'vendor/autoload.php' ); //LINE BOT SDKを読み込み
require_once( '../../../wp-load.php' ); //WordPressの基本機能を読み込み
require_once( 'line-members.php' ); //LINE Connectを読み込み
require_once( 'includes/html.php' );

$enabled_coupon = get_option( 'enabled_coupon' );
$not_exist_redirect = get_option( 'not_exist_redirect' );
$not_exist_alert_message = get_option('not_exist_alert_message');
$show_banner = get_option('show_banner');
?>
<!DOCTYPE html>
<html lang="ja"><head>
<meta charset="UTF-8">

<meta name="robots" content="noindex,follow">
<meta name="viewport" content="width=device-width,user-scalable=no">
<meta name="format-detection" content="telephone=no" />

<link href="./css/default.css" rel="stylesheet" media="all">
<link href="./css/front.css" rel="stylesheet" media="all">

<title>WAKUWAKU POINT [登録情報]</title>
<script
            src="https://code.jquery.com/jquery-3.6.0.min.js"
            integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4="
            crossorigin="anonymous"></script> 
<script src="https://static.line-scdn.net/liff/edge/2.1/sdk.js"></script> 
<script>
       $(function(){
            <?php
            $liff_id_profile = get_option('liff_id_profile');
            $liff_id_form = get_option('liff_id_form');
            $liff_id_point_card = get_option('liff_id_point_card');
            $liff_id_profile_edit = get_option('liff_id_profile_edit');
            ?>
            // 追加
            initializeLiff("<?=$liff_id_profile;?>");

        });
        // 追加
        function initializeLiff(liffId) {
            liff
                .init(
                    {
                        liffId: liffId
                    }
                )
                .then(() => {
                    if(!liff.isLoggedIn()) {
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
        getProfile = function(){
            liff.getProfile()
                .then(profile => {
                    userId= profile.userId;
                    if(!userId || userId == undefined) {
                        <?=Html::api_error_handle('lineIDが取得できませんでした','richmenu_profile','リッチメニューが正常に取得できませんでした。スタッフにお問い合わせください。');?>
                        return false;
                    }
                    displayName= profile.displayName;
                    post = {line_id:userId};

                    $.ajax({
                        type: "GET",
                        url: "<?=home_url();?>/wp-json/wp/v2/richmenu_profile",
                        dataType: "text",
                        data:post
                    }).done(function(data){
                        console.log(data);
                        let jsonData = JSON.parse(data);
                        console.log(jsonData);
                        if(jsonData.length == 0) {
                            let ua = window.navigator.userAgent.toLowerCase();
                            <?php
                            if($not_exist_redirect == 1) {
                                ?>
                                let form_url = 'https://liff.line.me/<?=$liff_id_form;?>';
                                if (ua.indexOf('iphone') != -1) {
                                    liff.closeWindow();
                                    window.location = form_url;
                                }else {
                                    liff.closeWindow();
                                    liff.openWindow({
                                        url: form_url,
                                        external: false
                                    }); 
                                }
                                
                                <?php
                            }else{
                                if(!empty($not_exist_alert_message)){
                                    ?>
                                    alert('<?=$not_exist_alert_message;?>');

                                    <?php
                                }
                            }
                            ?>
                            return false;
                        }

                        $.each(jsonData,function(i,val) {
                            if(i == 'qrcode') {
                                $('#'+i).children('img').attr('src',val);
                            }else {

                                $('#'+i).append(val);
                                $('#'+i).addClass(i+'-'+val);
                            }
                        })

                    }).fail(function(XMLHttpRequest, textStatus, errorThrown){
                        <?php
                            if($not_exist_redirect == 1) :
                                ?>
                                console.log('redirect');
                                let form_url = 'https://liff.line.me/<?=$liff_id_form;?>';
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
                    // alert(err.code);
                    // alert(err.message);
                    // alert("liff getProfile error : " + err);
                });
        }


    </script>
</head>
<body class="lmf-point_body cust">
<div class="lmf-container">
	<div class="lmf-title_block">
		<h1 class="title">登録情報</h1>
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
			<div class="lmf-profile_block lmf-white_block">
				<dl class="lmf-info_list">
					<dt>名前</dt>
					<dd id="name"></dd>
					<dt>性別</dt>
					<dd id="sex"></dd>
					<dt>地域</dt>
					<dd id="area"></dd>
					<dt>登録日時</dt>
					<dd id="post_date"></dd>
				</dl>
                <p class="lmf-btn_box btn_dgy btn_small"><a href="https://liff.line.me/<?=$liff_id_profile_edit;?>">登録情報を修正する</a></p>
			</div>
		</section>
	</main>
</div><!-- /.lmf-container -->
</body>
</html>