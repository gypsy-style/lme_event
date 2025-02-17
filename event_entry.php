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
$event_content = '';
$thumbnail_url = '';
$categories = [];
$category_names = [];
$tags = [];
$tag_names = [];
$event_subtitle = '';
$event_date = '';
$event_date_override = '';
$event_time = '';
$event_venue = '';
$event_address = '';
$event_map = '';
$speaker_name = '';
$speaker_profile = '';
$event_committee = '';
$event_chairperson = '';
$contact_phone = '';
$entry_fee = '';
$event_types = '';
$image_id = '';
$image_url = '';
$weekdays = [];
$formatted_date = '';
$formatted_tag_icon = '';
$formatted_event_checkbox = '';
$event_types_array = [];
$event_id = isset($_GET['event_id']) ? $_GET['event_id'] : 0;
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : 0;
$event_schedule_link = '';

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
	$event_date_override = get_post_meta($event_id, 'event_date_override', true);
	$event_time = get_post_meta($event_id, 'event_time', true);
	$event_venue = get_post_meta($event_id, 'event_venue', true);
	$event_address = !empty(get_post_meta($event_id, 'event_address', true)) ? get_post_meta($event_id, 'event_address', true) : '';
	$event_map = !empty(get_post_meta($event_id, 'event_map', true)) ? get_post_meta($event_id, 'event_map', true) : '';
	$speaker_name = !empty(get_post_meta($event_id, 'speaker_name', true)) ? get_post_meta($event_id, 'speaker_name', true) : '';
	$speaker_profile = !empty(get_post_meta($event_id, 'speaker_profile', true)) ? get_post_meta($event_id, 'speaker_profile', true) : '';
	$event_committee = !empty(get_post_meta($event_id, 'event_committee', true)) ? get_post_meta($event_id, 'event_committee', true) : '';
	$event_chairperson = !empty(get_post_meta($event_id, 'event_chairperson', true)) ? get_post_meta($event_id, 'event_chairperson', true) : '';
	$contact_phone = !empty(get_post_meta($event_id, 'contact_phone', true)) ? get_post_meta($event_id, 'contact_phone', true) : '';
	$entry_fee = get_post_meta($event_id, 'entry_fee', true);
	$event_types = get_post_meta($event_id, 'event_types', true);
	$weekdays = get_weekdays();

	// scheduleへのリンク
	$liff_id_event_schedule = get_option('liff_id_event_schedule');
	$event_schedule_link = 'https://liff.line.me/' . $liff_id_event_schedule . '?event_id=' . $event_id . '&user_id=' . $user_id;

	if (!empty($event_date)) {
		$date = new DateTime($event_date);
		// 整形した日付を生成
		$formatted_date = $date->format('Y年n月j日') . '（' . $weekdays[$date->format('w')] . '）';
		if(!empty($event_date_override)) {
			$formatted_date = $event_date_override;
		}
	}

	$formatted_event_icon = '';
	$formatted_event_checkbox = '';
	if (!empty($event_types)) {
		$event_types_array = explode(",", $event_types); // 改行で分割

		foreach ($event_types_array as $type) {
			$type = trim($type); // 不要な空白を削除
			if (!empty($type)) {
				$formatted_event_icon .= '<span class="icon">' . esc_html($type) . '</span>';
				$formatted_event_checkbox .= '<label><input type="checkbox" name="event_types[]" value="' . esc_html($type) . '">' . esc_html($type) . '</label>';
			}
		}
	}
	$tags = get_the_terms($event_id, 'event_tag');

	// タグが存在する場合に処理を実行
	$formatted_tag_icon = '';
	if ($tags && !is_wp_error($tags)) {
		echo '<div class="event-tags">';
		foreach ($tags as $tag) {
			$icon_class_name = get_term_meta($tag->term_id, 'icon_color', true);
			if (!$icon_class_name) {
				$icon_class_name = 'icon_or';
			}
			// タグ名を <span> で囲む
			$formatted_tag_icon .= '<span class="icon '.$icon_class_name.'">' . esc_html($tag->name) . '</span>';
		}
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
	<link href="./css/front.css" rel="stylesheet" media="all">

	<title>スケジュール [申し込む]</title>
	<script src="https://code.jquery.com/jquery-3.7.1.min.js" type="text/javascript"></script>
	<script src="https://static.line-scdn.net/liff/edge/2.1/sdk.js"></script>
	<script>
		// LIFF初期化
		document.addEventListener('DOMContentLoaded', function() {
			const liffId = "<?= get_option('liff_id_event_entry'); ?>"; // あなたのLIFF IDを設定してください
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

						// チェックボックスで選択されたイベントタイプを取得
						const selectedEventTypes = Array.from(document.querySelectorAll('input[name="event_types[]"]:checked')).map(input => input.value);

						if (selectedEventTypes.length === 0) {
							alert('参加するイベントを選択してください。');
							return;
						}


						const comment = document.querySelector('textarea[name="comment"]').value;
						console.log(selectedEventTypes)
						console.log(comment)
						const postData = {
							event_id: eventId,
							access_token: accessToken,
							event_types: selectedEventTypes,
							comment: comment,
							_wpnonce: "<?= wp_create_nonce('wp_rest'); ?>" // CSRFトークンを送信
						};

						// AJAXリクエストを送信
						$.ajax({
							type: "POST", // 非同期GETリクエスト
							url: "<?= home_url(); ?>/wp-json/wp/v2/entry_request",
							dataType: "json",
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
					<li class="back"><a href="event_entry_list.php">一覧へ戻る</a></li>
				</ul>
				<div class="lmf-single_block schedule lmf-white_block">
					<div class="lmf-icon_box"><?= $formatted_tag_icon; ?></div>
					<p class="data_box"><?= esc_html($formatted_date); ?></p>
					<h2><?= $title; ?></h2>

					<p class="lmf-link_box right"><a href="<?= $event_schedule_link; ?>">イベント詳細はこちら</a></p>
					<hr>
					<form action="process_form.php" method="POST">
						<input type="hidden" name="event_id" value="<?= esc_attr($event_id); ?>">
						<input type="hidden" name="access_token" id="accessToken">
						<dl class="lmf-form_box">

							<dt>参加するイベントを選択してください。</dt>
							<dd class="left">
								<?= $formatted_event_checkbox; ?>
							</dd>
							<dt>メッセージ</dt>
							<dd><textarea name="comment" rows="4"></textarea></dd>
						</dl>
						<p class="lmf-btn_box"><button type="submit">申し込む</button></p>
					</form>
				</div>
				<ul class="lmf-pnavi_list clearfix">
					<li class="back"><a href="event_entry_list.php">一覧へ戻る</a></li>
				</ul>
			</section>
		</main>
	</div><!-- /.lmf-container -->
</body>

</html>