<?php
require_once('../vendor/autoload.php'); //LINE BOT SDKを読み込み
require_once('../../../../wp-load.php'); //WordPressの基本機能を読み込み
require_once('../line-members.php'); //LINE Connectを読み込み
require_once('../includes/html.php');

$enabled_coupon = get_option('enabled_coupon');
$not_exist_redirect = get_option('not_exist_redirect');
// store_idから情報を取得
$show_banner = get_option('show_banner');
if (
	isset($_REQUEST['line_id']) && !empty($_REQUEST['line_id'])
) {
	$line_id = $_REQUEST['line_id'];
	// 年月を取得 (指定がない場合は現在の年月)
	$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
	$month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
	// 月の開始日と終了日を計算
	$first_day_of_month = date('Y-m-01', strtotime("$year-$month-01"));
	$last_day_of_month = date('Y-m-t', strtotime($first_day_of_month));

	$invoice_date = $year.'年'.$month.'月末';
	// user_idで絞り込み
	$args = array(
		'post_type' => array('store'), //投稿タイプを指定
		'posts_per_page' => '1', //取得する投稿件数を指定
		'meta_key' => 'line_id', //カスタムフィールドのキーを指定
		'meta_value' => $line_id, //カスタムフィールドの値を指定
		'orderby' => 'meta_value', //ソートの基準を指定
		'order' => 'asc' //ソート方法を指定（昇順：asc, 降順：desc）
	);
	$the_query = new WP_Query($args);
	if ($the_query->have_posts()) {
		// オーナー
		while ($the_query->have_posts()) {
			$return = [];
			$the_query->the_post();
			$store_post_id = get_the_ID();
			$store_name = get_post_meta($store_post_id, 'store_name',true);
			$address = get_post_meta($store_post_id, 'address',true);
			$zip1 = get_post_meta($store_post_id, 'zip1',true);
			$zip2 = get_post_meta($store_post_id, 'zip2',true);
			$zip = $zip1 . '-' . $zip2;
			$phone_number = get_post_meta($store_post_id, 'phone_number',true);
			$person_in_charge = get_post_meta($store_post_id, 'person_in_charge',true);
		}
	}
	wp_reset_query();

	$tax_rate = 0.10;
	$point_rate = 0.01;

	// ユーザーに付与したポイント
	$userGivenPoints = 0;

	// ユーザーから付与されたポイント
	$userReceivedPoints = 0;

	// 運営から付与するポイント
	$adminGivenPoints = 0;

	// 店舗ポイント付与金額
	$storePointsAmount = 0;

	$pointFee = 0;

	// ポイント手数料（税込）
	$pointFeeWithTax = 0;

	$total_invoice = 0;

	// 月間フィルタをクエリに追加
	$args_point_history = array(
		'post_type' => array('point_history'), //投稿タイプを指定
		'posts_per_page' => '-1', //取得する投稿件数を指定
		'meta_key' => 'store_id', //カスタムフィールドのキーを指定
		'meta_value' => $store_post_id, //カスタムフィールドの値を指定
		'date_query' => array(
			array(
				'after' => $first_day_of_month,
				'before' => $last_day_of_month,
				'inclusive' => true,
			),
		),
		'orderby' => 'date', //投稿の日付を基準にソート
		'order' => 'desc' //最新の投稿を取得するために降順にソート
	);

	$the_query_args_point_history = new WP_Query($args_point_history);

	if ($the_query_args_point_history->have_posts()) {
		while ($the_query_args_point_history->have_posts()) {
			$the_query_args_point_history->the_post();
			$point_history_id = get_the_ID();
			$point_type = get_post_meta($point_history_id, 'point_type', true);
			$price = get_post_meta($point_history_id, 'price', true);
			$point_number = get_post_meta($point_history_id, 'point_number', true);

			if (is_numeric($point_number)) {
				$point_number = floatval($point_number);
			} else {
				$point_number = 0;
			}

			if (is_numeric($price)) {
				$price = floatval($price);
			} else {
				$price = 0;
			}


			switch ($point_type) {
				case '付与':
					$userGivenPoints += $point_number;
					$storePointsAmount += $price;
					break;
				case '使用':
					$userReceivedPoints += $point_number;
					break;
				case '運営':
					$adminGivenPoints += $point_number;
					break;
			}
		}

		$adminGivenPoints = $userGivenPoints - $userReceivedPoints;
		$pointWithoutTax = $storePointsAmount / (1 + $tax_rate);
		$pointFee = $pointWithoutTax * $point_rate;
		$pointFeeWithTax = $pointFee * (1 + $tax_rate);
		$pointFeeWithTax = round($pointFeeWithTax);

		$total_invoice = $pointFeeWithTax + $adminGivenPoints;
		$pointFeeWithTax = number_format($pointFeeWithTax);
		$adminGivenPoints = number_format($adminGivenPoints);
		$total_invoice = number_format($total_invoice);
		// // $pointFee = $storePointsAmount * 0.01;
		// $pointFee = ($storePointsAmount / 1.1) * 0.01;
		// $pointFee = ceil($pointFee);
		
		// 10%の税を足す


	}
}

// $point_rate = get_post_meta($store_id, 'point_rate', true);

?>
<!DOCTYPE html>
<html lang="ja">

<head>
	<meta charset="UTF-8">

	<meta name="robots" content="noindex,follow">
	<meta name="viewport" content="width=640, user-scalable=no,target-densitydpi=device-dpi">
	<meta name="format-detection" content="telephone=no" />

	<link href="../css/default.css" rel="stylesheet" media="all">
	<link href="../css/doc.css" rel="stylesheet" media="all">

	<title>請求書</title>

</head>

<body class="lmd_body">
	<div class="lmd_container">
		<div class="lmd-head_area">
			<div class="lmd-title_block">
				<h1 class="title">請求書</h1>
				<p class="data_box" style="display:none;">注文日 : </p>
			</div>
			<div class="lmd-info_area">
				<div class="lmd-orderer_block">
					<div class="name_box">
						<h2 class="company"><?= $store_name; ?></h2>
						<h3 class="name"><?= !empty($person_in_charge) ? $person_in_charge.'様':''; ?> </h3>
					</div>
					<div class="info_box">
						<p class="text">下記の通り請求申し上げます。</p>
						<div class="price_box">
							<b class="label">合計金額</b>
							<span class="price">¥<?=$total_invoice;?>-</span>
						</div>
						<div class="limit_box">
							<span class="label">お支払い日：</span>
							<em class="data"><?=$invoice_date;?></em>
						</div>
					</div>
				</div>
				<div class="lmd-contractor_block">
					<div class="name_box">
						<h2 class="company">株式会社 Bace</h2>
					</div>
					<div class="info_box">
						<p class="addr">
							〒740-0018<br>
							岩国市麻里布町 2-3-6<br>
						</p>
					</div>
				</div>
			</div>
		</div>
		<div class="lmd-main_contents">
			<table class="lmd-tbl">
				<thead>
					<tr>
						<th>品名</th>
						<td>数量</td>
						<td>単価</td>
						<td>金額</td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<th>運営から付与するポイント</th>
						<td><?=$adminGivenPoints;?></td>
						<td>¥1</td>
						<td>¥<?=$adminGivenPoints;?></td>
					</tr>
					<tr>
						<th>ポイント手数料</th>
						<td>1</td>
						<td>¥<?=$pointFeeWithTax;?></td>
						<td>¥<?=$pointFeeWithTax;?></td>
					</tr>
					<tr>
						<th>&nbsp;</th>
						<td></td>
						<td></td>
						<td></td>
					</tr>
					<tr>
						<th>&nbsp;</th>
						<td></td>
						<td></td>
						<td></td>
					</tr>
					<tr>
						<th>&nbsp;</th>
						<td></td>
						<td></td>
						<td></td>
					</tr>
				</tbody>
				<tfoot>
					<tr>
						<th colspan="3">小計</th>
						<td>¥<?=$total_invoice;?></td>
					</tr>
					<tr>
						<th colspan="3">税(10%)</th>
						<td>税込</td>
					</tr>
					<tr>
						<th colspan="3">合計</th>
						<td>¥<?=$total_invoice;?></td>
					</tr>
				</tfoot>
			</table>
			<div class="memo_block">
				<p>備考：</p>
			</div>
		</div>
	</div><!-- /.lmf-doc_container -->
</body>

</html>