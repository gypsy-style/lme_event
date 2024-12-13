<?php
require_once( '../vendor/autoload.php' ); //LINE BOT SDKを読み込み
require_once( '../../../../wp-load.php' ); //WordPressの基本機能を読み込み
require_once( '../line-members.php' ); //LINE Connectを読み込み
require_once( '../includes/html.php' );

$enabled_coupon = get_option( 'enabled_coupon' );
$not_exist_redirect = get_option( 'not_exist_redirect' );
$show_banner = get_option('show_banner');
?>

<!DOCTYPE html>
<html lang="ja"><head>
<meta charset="UTF-8">

<meta name="robots" content="noindex,follow">
<meta name="viewport" content="width=device-width,user-scalable=no">
<meta name="format-detection" content="telephone=no" />

<link href="../css/default.css" rel="stylesheet" media="all">
<link href="../css/front.css" rel="stylesheet" media="all">

<title>WAKUWAKU POINT [ポイント履歴]</title>
<script
            src="https://code.jquery.com/jquery-3.6.0.min.js"
            integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4="
            crossorigin="anonymous"></script> 
<script src="https://static.line-scdn.net/liff/edge/2.1/sdk.js"></script> 

<script>
        $(function(){
            <?php

            $liff_id_store_point_history = get_option('liff_id_store_point_history');
            ?>
            // 追加
            initializeLiff("<?=$liff_id_store_point_history;?>");

// { displayName: 'Brown', userId: '123456789', statusMessage: 'hello' }
//             console.log(post);
            $('#edit_btn').on('click',function(event){
                event.preventDefault();
                let pass_value = '8433711';
                let pass = prompt("パスワードを入力してください");
                if(pass == pass_value) {
                    window.location = 'richmenu_profile_edit.php?line_id='+userId+'&display_name='+displayName;
                }
                
            })


            // event.preventDefault();
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
                        url: "<?=home_url();?>/wp-json/wp/v2/store_point_history",
                        dataType: "text",
                        data:post
                    }).done(function(data){
                        console.log(data);
                        $('.lmf-main_contents').html(data);
                        

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
                    alert(err.code);
                    alert(err.message);
                    // alert("liff getProfile error : " + err);
                });
        }


    </script>

</head>
<body class="lmf-point_body shop">
<div class="lmf-container">
	<div class="lmf-title_block">
		<h1 class="title">ポイント履歴</h1>
	</div>
    <?php
        if($show_banner == 1){
			Html::store_banner();
        }
		?>
	<main class="lmf-main_contents">
    <!-- <section class="lmf-content">
			<div class="lmf-user_block">
				<div class="point_box">
					<em class="label">保有ポイント</em>
					<b class="points"><span class="point">200,000,000</span><span class="unit">pt</span></b>
				</div>
			</div>
			<div class="lmf-record_block lmf-white_block">
				<ul class="lmf-record_list">
					<li class="use">
						<span class="icon">ポイント使用</span>
						<em class="data">2023年11月23日 16:04</em>
						<b class="title">鈴木 武夫</b>
						<p class="point">-10pt</p>
					</li>
					<li class="get">
						<span class="icon">ポイント付与</span>
						<em class="data">2023年11月23日 16:04</em>
						<b class="title">鈴木 武夫</b>
						<p class="point">20pt</p>
					</li>
					<li class="use">
						<span class="icon">ポイント使用</span>
						<em class="data">2023年11月23日 16:04</em>
						<b class="title">パブロ・ディエゴ・ホセ・フランシスコ・デ・パウラ・ファン・ネポムセーノ・マリア・デ・ロス・レメディオス・クリスピン・クリスピアーノ・デ・ラ・サンティシマ・トリニダード・ルイス・イ・ピカソ</b>
						<p class="point">-20pt</p>
					</li>
					<li class="get">
						<span class="icon">ポイント付与</span>
						<em class="data">2023年11月23日 16:04</em>
						<b class="title">山田太郎</b>
						<p class="point">10pt</p>
					</li>
					<li class="get">
						<span class="icon">ポイント付与</span>
						<em class="data">2023年11月23日 16:04</em>
						<b class="title">山田太郎</b>
						<p class="point">10pt</p>
					</li>
				</ul>
			</div>
			<p class="lmf-btn_box"><a href="#">月別ポイント付与履歴</a></p>
			<p class="lmf-btn_box btn_gy btn_small"><a href="#">ポイントカードに戻る</a></p>
		</section> -->
	</main>
</div><!-- /.lmf-container -->
</body>
</html>