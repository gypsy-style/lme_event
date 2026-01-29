<?php
require_once('vendor/autoload.php'); //LINE BOT SDKを読み込み
require_once('../../../wp-load.php'); //WordPressの基本機能を読み込み
require_once('line-members.php'); //LINE Connectを読み込み
require_once('includes/html.php');

// if (
// 	!isset($_GET['event_id']) || empty($_GET['event_id'])
// ) {
// 	echo 'イベントが見つかりません';
// 	exit;
// }
// 初期化
$title = '';
$event_subtitle = '';
$event_date = '';

$weekdays = [];
$formatted_date = '';

$event_id = isset($_GET['event_id']) ? $_GET['event_id'] : 0;

// 投稿データの取得
$event_post = get_post($event_id);

if ($event_post && $event_post->post_type === 'event') {

	// タイトル
	$title = get_the_title($event_id);
	// カスタムフィールド
	$event_subtitle = !empty(get_post_meta($event_id, 'event_subtitle', true)) ? get_post_meta($event_id, 'event_subtitle', true) : '';
	$event_date = !empty(get_post_meta($event_id, 'event_date', true)) ? get_post_meta($event_id, 'event_date', true) : '';



	$weekdays = get_weekdays();

	if (!empty($event_date)) {
		$date = new DateTime($event_date);
		// 整形した日付を生成
		$formatted_date = $date->format('Y年n月j日') . '（' . $weekdays[$date->format('w')] . '）';
	}
} else {
	// echo '<p>指定されたIDの投稿が見つかりません。</p>';
}

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

	<title>イベントチェックイン</title>
	<script src="https://code.jquery.com/jquery-3.7.1.min.js" type="text/javascript"></script>
	<script src="https://static.line-scdn.net/liff/edge/2.1/sdk.js"></script>
	<script>
		// LIFF初期化
		document.addEventListener('DOMContentLoaded', function() {
			const liffId = "<?= get_option('liff_id_event_checkin'); ?>"; // あなたのLIFF IDを設定してください
			const eventId = "<?= isset($_GET['event_id']) ? esc_js($_GET['event_id']) : ''; ?>"; // PHPから取得したevent_idをJavaScriptに渡す

			liff.init({
				liffId: liffId
			}).then(() => {
				if (!liff.isLoggedIn()) {
					// LIFFログインURLにevent_idを追加
					liff.login({
						redirectUri: window.location.origin + window.location.pathname + '?event_id=' + eventId
					});
				} else {
					// ログイン済みの場合、アクセストークンを取得して hidden フィールドにセット
					const accessToken = liff.getAccessToken(); // 直接取得
					document.getElementById('accessToken').value = accessToken;
					const postDataIsExists = {
						event_id: eventId,
						access_token:accessToken
					}
					// 登録済みユーザーかどうか
					let user_id = 0;
					$.ajax({
						type: "GET", // 非同期GETリクエスト
						url: "<?= home_url(); ?>/wp-json/wp/v2/is_event_checkin",
						dataType: "json",
						data: postDataIsExists
					}).done(function(response) {
						console.log(response.user_id);
						if (response.status == 'nouser') {
							// 公式LINEにページ移動
							window.location.href = "https://line.me/R/ti/p/@410ultht";
							return false;
						} else if (response.status == 'checkined') {
							$('#submit-btn').prop('disabled', true).text('参加処理済み');
							return false;
						}
						user_id = response.user_id;
					}).fail(function(XMLHttpRequest, textStatus, errorThrown) {
						console.log(XMLHttpRequest)
						console.log(errorThrown)
						console.log(textStatus)
						// リクエスト失敗時のエラーハンドリング
						alert('ユーザー情報取得時にエラーが発生しました: ' + errorThrown);
					});


					

					// フォーム送信イベントを非同期に変更
					const form = document.querySelector('form');
					form.addEventListener('submit', function(e) {
						e.preventDefault(); // デフォルトのフォーム送信をキャンセル

						const postData = {
							event_id: eventId,
							user_id: user_id,
						};

						console.log(postData);

						// AJAXリクエストを送信
						$.ajax({
							type: "POST", // 非同期GETリクエスト
							url: "<?= home_url(); ?>/wp-json/wp/v2/event_checkin",
							dataType: "json",
							data: postData
						}).done(function(response) {

							// リクエスト成功時にLIFFウィンドウを閉じる
							alert('チェックインが完了しました');
							liff.closeWindow();
						}).fail(function(XMLHttpRequest, textStatus, errorThrown) {
							console.log(XMLHttpRequest)
							console.log(errorThrown)
							console.log(textStatus)
							// リクエスト失敗時のエラーハンドリング
							alert('エラーが発生しました: ' + errorThrown);
						});
					});
				}
			}).catch(err => {
				console.error("LIFF初期化エラー:", err);
			});
		});
	</script>
</head>

</head>

<body class="lmf-schedule_body cust">

	<div class="lmf-container">
		<div class="lmf-title_block">
			<h1 class="title">チェックイン</h1>
		</div>
		<main class="lmf-main_contents">
			<section class="lmf-content">
				<div class="lmf-single_block schedule lmf-white_block">
					<p class="center">以下のイベントにチェックインします。</p>
					<form action="#" id="form">
						<dl class="lmf-info_list--v">
							<dt>イベント名</dt>
							<dd><?= esc_html($title); ?></dd>
							<dt>日時</dt>
							<dd><?= esc_html($formatted_date); ?></dd>
						</dl>
						<p class="lmf-btn_box"><button type="submit" id="submit-btn">チェックイン</button></p>
						<input type="hidden" name="event_id" value="<?= esc_attr($event_id); ?>">
						<input type="hidden" name="access_token" id="accessToken">
					</form>
				</div>
			</section>
		</main>
	</div><!-- /.lmf-container -->
</body>

</html>