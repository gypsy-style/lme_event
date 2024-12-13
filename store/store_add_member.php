<?php
require_once( '../vendor/autoload.php' ); //LINE BOT SDKを読み込み
require_once( '../../../../wp-load.php' ); //WordPressの基本機能を読み込み
require_once( '../line-members.php' ); //LINE Connectを読み込み
require_once( '../includes/html.php' );

$enabled_coupon = get_option( 'enabled_coupon' );
$not_exist_redirect = get_option( 'not_exist_redirect' );
$show_banner = get_option('show_banner');
// store_idから情報を取得
if(!isset($_REQUEST['store_id']) || empty($_REQUEST['store_id'])) {

	if (isset($_GET['liff_state'])) {
		// liff.stateのパラメータを取得
		$liff_state = $_GET['liff_state'];


	
		// URLデコードして中のパラメータを取り出す
		$decoded_liff_state = urldecode($liff_state);
		$decoded_liff_state = ltrim($decoded_liff_state, '?');
		echo $decoded_liff_state;
	
		// store_idの値を解析
		parse_str($decoded_liff_state, $params);
		print_r($params);
	
		if (isset($params['store_id'])) {
			$store_id = $params['store_id'];
			$store_id = htmlspecialchars($store_id, ENT_QUOTES, 'UTF-8');
		} else {
			echo '店舗情報が取得できませんでした';
			exit;
		}
	}else {
		echo '店舗情報が取得できませんでした';
		exit;
	}
	
}
$store_id = $_REQUEST['store_id'];
$store_name = get_post_meta($store_id,'store_name',true);

?>
<!DOCTYPE html>
<html lang="ja"><head>
<meta charset="UTF-8">

<meta name="robots" content="noindex,follow">
<meta name="viewport" content="width=device-width,user-scalable=no">
<meta name="format-detection" content="telephone=no" />

<link href="../css/default.css" rel="stylesheet" media="all">
<link href="../css/front.css" rel="stylesheet" media="all">

<title>WAKUWAKU POINT [登録スタッフ追加]</title>
<script
            src="https://code.jquery.com/jquery-3.6.0.min.js"
            integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4="
            crossorigin="anonymous"></script> 
<script src="https://static.line-scdn.net/liff/edge/2.1/sdk.js"></script> 
<script>
	$(function() {
		<?php
		$liff_id_store_add_member = get_option('liff_id_store_add_member');
		$after_registration_action = get_option('after_registration_action');
		$liff_id_form = get_option('liff_id_form');
		?>
		// 追加
		initializeLiff("<?= $liff_id_store_add_member; ?>");
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
			
			// pushMessage.push('お誕生日：'+post['birthday_y']+'年'+post['birthday_m']+'月');
			post['line_id'] = userId;
			post['store_id'] = "<?=$store_id;?>";
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
				url: "<?= home_url(); ?>/wp-json/wp/v2/store_add_member",
				dataType: "text",
				data: post
			}).done(function(response) {
				 console.log(response);
				 alert('スタッフを登録しました');

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

	getProfile = function() {
		liff.getProfile()
			.then(profile => {
				userId = profile.userId;
				displayName = profile.displayName;
				console.log(userId);

			})
			.catch((err) => {
				// alert("liff getProfile error : " + err);
			});
	};
</script>
</head>
<body class="lmf-point_body shop">
<div class="lmf-container">
	<div class="lmf-title_block">
		<h1 class="title">登録スタッフ追加</h1>
	</div>
	<?php
        if($show_banner == 1){
			Html::store_banner();
        }
		?>
	<main class="lmf-main_contents">
		<section class="lmf-content">
			<div class="lmf-info_block lmf-white_block">
				<dl class="lmf-info_list">
					<dt>店舗名</dt>
					<dd><?=$store_name;?></dd>
				</dl>
				<p>上記店舗にスタッフとして追加します。<br>
					よろしければ下記のボタンを押してください。</p>
					<form id="form">
				<p class="lmf-btn_box btn_small"><input type="submit" value="スタッフとして追加"></p>
</form>
			</div>
		</section>
	</main>
</div><!-- /.lmf-container -->
</body>
</html>