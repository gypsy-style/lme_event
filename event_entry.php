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
$event_id = $_GET['event_id'];

// 投稿データの取得
$event_post = get_post($event_id);

if ($event_post && $event_post->post_type === 'event') {

	// タイトル
	$title = get_the_title($event_id);

	// アイキャッチ画像
	$thumbnail_url = get_the_post_thumbnail_url($event_id, 'full');

	// カテゴリー
	$categories = get_the_terms($event_id, 'event_category'); // カスタムタクソノミー名に置き換える
	$category_names = [];
	if (!empty($categories) && !is_wp_error($categories)) {
		foreach ($categories as $category) {
			$category_names[] = $category->name;
		}
	}

	// タグ
	$tags = get_the_terms($event_id, 'post_tag'); // デフォルトのタグタクソノミー
	$tag_names = [];
	if (!empty($tags) && !is_wp_error($tags)) {
		foreach ($tags as $tag) {
			$tag_names[] = $tag->name;
		}
	}
	// カスタムフィールド
	$event_subtitle = get_post_meta($event_id, 'event_subtitle', true);
	$event_date = get_post_meta($event_id, 'event_date', true);
	$event_time = get_post_meta($event_id, 'event_time', true);
	$event_venue = get_post_meta($event_id, 'event_venue', true);
	$entry_fee = get_post_meta($event_id, 'entry_fee', true);
	$event_types = get_post_meta($event_id, 'event_types', true);

	$formatted_event_icon = '';
	$formatted_event_checkbox = '';
	if (!empty($event_types)) {
		$event_types_array = explode(",", $event_types); // 改行で分割

		foreach ($event_types_array as $type) {
			$type = trim($type); // 不要な空白を削除
			if (!empty($type)) {
				$formatted_event_icon .= '<span class="icon">' . esc_html($type) . '</span>';
				$formatted_event_checkbox .= '<label><input type="checkbox" name="types[]" value="' . esc_html($type) . '">' . esc_html($type) . '</label>';
			}
		}
	}
} else {
	echo '<p>指定されたIDの投稿が見つかりません。</p>';
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
	<link href="./css/front.css" rel="stylesheet" media="all">

	<title>スケジュール [申し込む]</title>
	<script src="https://static.line-scdn.net/liff/edge/2.1/sdk.js"></script>
	<script>
		// LIFF初期化
		document.addEventListener('DOMContentLoaded', function() {
			const liffId = "2006629843-rzZ2l4Xb"; // あなたのLIFF IDを設定してください
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

					// フォーム送信イベントを非同期に変更
					const form = document.querySelector('form');
					form.addEventListener('submit', function(e) {
						e.preventDefault(); // デフォルトのフォーム送信をキャンセル

						const postData = {
							event_id: eventId,
							access_token: accessToken
						};

						// AJAXリクエストを送信
						$.ajax({
							type: "GET", // 非同期GETリクエスト
							url: "<?= home_url(); ?>/wp-json/wp/v2/entry_request",
							dataType: "text",
							data: postData
						}).done(function(response) {
							// リクエスト成功時にLIFFウィンドウを閉じる
							alert('申し込みが完了しました');
							liff.closeWindow();
						}).fail(function(XMLHttpRequest, textStatus, errorThrown) {
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
			<h1 class="title">申し込む</h1>
		</div>
		<main class="lmf-main_contents">
			<section class="lmf-content">
				<ul class="lmf-pnavi_list clearfix">
					<li class="back"><a href="event_list.php">一覧へ戻る</a></li>
				</ul>
				<div class="lmf-single_block schedule lmf-white_block">
					<div class="lmf-icon_box"><?= $formatted_event_icon; ?></div>
					<h2><?= $title; ?></h2>
					<?= !empty($event_subtitle) ? '<h3>' . esc_html($event_subtitle) . '</h3>' : ''; ?>
					<p><?= !empty($event_date) ? '日時：' . esc_html($event_date) . '<br>' : ''; ?>
						<?= !empty($event_time) ? '時間：' . esc_html($event_time) . '<br>' : ''; ?>
						<?= !empty($event_venue) ? '会場：' . esc_html($event_venue) . '<br>' : ''; ?>
						<?= !empty($entry_fee) ? '参加費：' . esc_html($entry_fee) : ''; ?></p>
					<h3 class="title_gy">申し込みフォーム</h3>
					<form action="process_form.php" method="POST">
						<input type="hidden" name="event_id" value="<?= esc_attr($event_id); ?>">
						<input type="hidden" name="access_token" id="accessToken">
						<dl class="lmf-form_box">
							<dt>参加するイベントを選択してください。</dt>
							<dd class="left">
								<?= $formatted_event_checkbox; ?>
							</dd>
						</dl>
						<p class="lmf-btn_box"><button type="submit">申し込む</button></p>
					</form>
				</div>
			</section>
		</main>
	</div><!-- /.lmf-container -->
</body>

</html>