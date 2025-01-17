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

// 投稿データの取得
$event_post = get_post($event_id);

if ($event_post && $event_post->post_type === 'event') {

	// タイトル
	$title = get_the_title($event_id);

	// おイベント内容
	$event_content = wpautop(get_post_field('post_content', $event_id));


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

	// カスタムフィールド
	$event_subtitle = !empty(get_post_meta($event_id, 'event_subtitle', true)) ? get_post_meta($event_id, 'event_subtitle', true) : '';
	$event_date = !empty(get_post_meta($event_id, 'event_date', true)) ? get_post_meta($event_id, 'event_date', true) : '';
	$event_time = !empty(get_post_meta($event_id, 'event_time', true)) ? get_post_meta($event_id, 'event_time', true) : '';
	$event_venue = !empty(get_post_meta($event_id, 'event_venue', true)) ? get_post_meta($event_id, 'event_venue', true) : '';
	$event_address = !empty(get_post_meta($event_id, 'event_address', true)) ? get_post_meta($event_id, 'event_address', true) : '';
	$event_map = !empty(get_post_meta($event_id, 'event_map', true)) ? get_post_meta($event_id, 'event_map', true) : '';
	$speaker_name = !empty(get_post_meta($event_id, 'speaker_name', true)) ? get_post_meta($event_id, 'speaker_name', true) : '';
	$speaker_profile = !empty(get_post_meta($event_id, 'speaker_profile', true)) ? get_post_meta($event_id, 'speaker_profile', true) : '';
	$event_committee = !empty(get_post_meta($event_id, 'event_committee', true)) ? get_post_meta($event_id, 'event_committee', true) : '';
	$event_chairperson = !empty(get_post_meta($event_id, 'event_chairperson', true)) ? get_post_meta($event_id, 'event_chairperson', true) : '';
	$contact_phone = !empty(get_post_meta($event_id, 'contact_phone', true)) ? get_post_meta($event_id, 'contact_phone', true) : '';
	$entry_fee = !empty(get_post_meta($event_id, 'entry_fee', true)) ? get_post_meta($event_id, 'entry_fee', true) : '';
	$event_types = !empty(get_post_meta($event_id, 'event_types', true)) ? get_post_meta($event_id, 'event_types', true) : '';
	$image_id = !empty(get_post_meta($event_id, 'event_image', true)) ? get_post_meta($event_id, 'event_image', true) : '';
        

	$weekdays = get_weekdays();

	if (!empty($event_date)) {
		$date = new DateTime($event_date);
		// 整形した日付を生成
		$formatted_date = $date->format('Y年n月j日') . '（' . $weekdays[$date->format('w')] . '）';
	}

	
	$formatted_event_checkbox = '';
	if (!empty($event_types)) {
		$event_types_array = explode(",", $event_types); // 改行で分割

		foreach ($event_types_array as $type) {
			$type = trim($type); // 不要な空白を削除
			if (!empty($type)) {
				$formatted_event_checkbox .= '<label><input type="checkbox" name="event_types[]" value="' . esc_html($type) . '">' . esc_html($type) . '</label>';
			}
		}
	}
	$tags = get_the_terms($event_id, 'event_tag');


	// タグが存在する場合に処理を実行
	$formatted_tag_icon = '';
	if ($tags && !is_wp_error($tags)) {
		foreach ($tags as $tag) {
			// タグ名を <span> で囲む
			$formatted_tag_icon .= '<span class="icon">' . esc_html($tag->name) . '</span>';
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

	<title>スケジュール</title>
	<script src="https://code.jquery.com/jquery-3.7.1.min.js" type="text/javascript"></script>
	<script src="https://static.line-scdn.net/liff/edge/2.1/sdk.js"></script>
	<script>
		// LIFF初期化
		document.addEventListener('DOMContentLoaded', function() {
			const liffId = "<?= get_option('liff_id_event_schedule'); ?>"; // あなたのLIFF IDを設定してください
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
			<h1 class="title">スケジュール</h1>
		</div>
		<main class="lmf-main_contents">
			<section class="lmf-content">
				<ul class="lmf-pnavi_list clearfix">
					<li class="back"><a href="event_list.php">一覧へ戻る</a></li>
				</ul>
				<div class="lmf-single_block schedule lmf-white_block">
					<div class="lmf-icon_box"><?= $formatted_tag_icon; ?></div>
					<img src="<?= $thumbnail_url; ?>" alt="">
					<?php
					if ($image_id) : 
						$image_url = $image_id ? wp_get_attachment_url($image_id) : ''; // 画像URLを取得?>
						<img src="<?= esc_url($image_url); ?>" alt="イベント画像">
					<?php
					endif; ?>
					<h2><?= $title; ?></h2>
					<h3><?= esc_html($event_subtitle); ?></h3>
					<?= $event_content; ?>
					<dl class="lmf-info_list--v">
						<dt>開催日時</dt>
						<dd>
							<?= esc_html($formatted_date); ?><br>
							<?= nl2br(esc_html($event_time)); ?>
						</dd>
						<dt>会場</dt>
						<dd>
							<?= esc_html($event_venue); ?><br>
							<?= esc_html($event_address); ?>
							<?php
							if (!empty($event_map)): ?>
								<br><a href="<?= esc_html($event_map); ?>">Google MAP</a>
							<?php
							endif; ?>
						</dd>
						<dt>参加費</dt>
						<dd><?= esc_html($entry_fee); ?></dd>
						<dt>講師</dt>
						<dd>
							<?= esc_html($speaker_name); ?><br>
							<?= esc_html($speaker_profile); ?>
						</dd>
						<dt>担当委員会</dt>
						<dd>
							<?= esc_html($event_committee); ?><br>
							<?= esc_html($event_chairperson); ?><br>
							<?= esc_html($contact_phone); ?>
						</dd>
					</dl>
				</div>
			</section>


		</main>
	</div><!-- /.lmf-container -->
</body>

</html>