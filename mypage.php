<?php
require_once('vendor/autoload.php'); //LINE BOT SDKを読み込み
require_once('../../../wp-load.php'); //WordPressの基本機能を読み込み
require_once('line-members.php'); //LINE Connectを読み込み
require_once('includes/html.php');
?>
<!DOCTYPE html>
<html lang="ja"><head>
<meta charset="UTF-8">

<meta name="robots" content="noindex,follow">
<meta name="viewport" content="width=device-width,user-scalable=no">
<meta name="format-detection" content="telephone=no" />

<link href="./css/default.css" rel="stylesheet" media="all">
	<link href="./css/front.css" rel="stylesheet" media="all">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" type="text/javascript"></script>
	<script src="https://static.line-scdn.net/liff/edge/2.1/sdk.js"></script>
    <script>
		// LIFF初期化
		document.addEventListener('DOMContentLoaded', function() {
			const liffId = "<?= get_option('liff_id_mypage'); ?>"; // あなたのLIFF IDを設定してください

			liff.init({
				liffId: liffId
			}).then(() => {
				if (!liff.isLoggedIn()) {
					// LIFFログインURLにevent_idを追加
					liff.login();
				}else {
					getProfile();
				}
				
				
			}).catch(err => {
				console.error("LIFF初期化エラー:", err);
			});

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
					url: "<?= home_url(); ?>/wp-json/wp/v2/get_mypage",
					dataType: "json",
					data: post
				}).done(function(response) {
					console.log(response)

					const contents_mypage = response.contents_mypage;

					$('#contents-mypage').html(contents_mypage);

				}).fail(function(XMLHttpRequest, textStatus, errorThrown) {
					alert(errorThrown);
				});
			};
		});
	</script>
<title>マイページ</title>

</head>
<body class="lmf-mypage_body cust">
<div class="lmf-container">
	<div class="lmf-title_block">
		<h1 class="title">マイページ</h1>
	</div>
	<main class="lmf-main_contents">
		<section class="lmf-content">
			<div class="lmf-mypage_block" id="contents-mypage">
				<!-- <h2 class="name">鈴木 武夫</h2>
				<div class="lmf-icon_box center"><span class="icon">広報委員会</span></div>
				<dl class="lmf-attendance_list">
					<dt>例会出席状況</dt>
					<dd>2/2</dd>
					<dt>勉強会出席状況</dt>
					<dd>3/5</dd>
				</dl>
				<ul class="lmf-whbar_list">
					<li><a href="#">登録情報修正</a></li>
					<li><a href="#">過去に参加したイベント</a></li>
					<li><a href="#">過去に参加したイベント</a></li> -->
				</ul>
			</div>
		</section>
	</main>
</div><!-- /.lmf-container -->
</body>
</html>