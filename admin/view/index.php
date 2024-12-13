<!DOCTYPE html>
<html lang="ja"><head>
<meta charset="UTF-8">

<meta name="robots" content="noindex,follow">
<meta name="viewport" content="width=device-width,user-scalable=no">
<meta name="format-detection" content="telephone=no" />

<link href="../css/default.css" rel="stylesheet" media="all">
<link href="../css/admin.css" rel="stylesheet" media="all">

<title>WAKUWAKU POINT 管理画面 [ダッシュボード]</title>

<script type="text/javascript" src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script type="text/javascript" src="../js/common.js"></script>

</head>
<body class="lma-point_body lma-dashboard">
<div class="lma-container">
	<<?php include './view/includes/side_menu.php';?>
	<main class="lma-main_contents">
		<section class="lma-content flex">
			<div class="lma-content_block dashboard_store col50">
				<div class="store_box">
					<em class="label">加盟店</em>
					<b class="number color__sky"><span class="num"><?=$count_store;?></span><small class="unit">店舗</small></b>
				</div>
				<p class="lma-btn_box"><a href="store_list.php">加盟店一覧</a></p>
			</div>
			<div class="lma-content_block dashboard_user col50">
				<div class="user_box">
					<em class="label">ユーザー</em>
					<b class="number color__pk"><span class="num"><?=$count_line_user;?></span><small class="unit">名</small></b>
				</div>
				<p class="lma-btn_box"><a href="user_list.php">ユーザー一覧</a></p>
			</div>
			<div class="lma-content_block dashboard_records">
				<div class="record_block">
					<div class="records_caption">
						<h2 class="lma-title_bar sky"><em class="label"><?=$year;?>年<?=$month;?>月</em></h2>
					</div>
					<div class="records_table">
						<table class="records_tbl">
							<tr class="user_grant min noborder">
								<th>ユーザーに付与したポイント</th>
								<td class="sky"><?=$userGivenPoints;?>pt</td>
							</tr>
							<tr class="user_use">
								<th>ユーザーから付与されたポイント</th>
								<td class="pk"><?=$userReceivedPoints;?>pt</td>
							</tr>
							<tr class="admin_grant bg">
								<th>運営から付与するポイント</th>
								<td class="pk"><?=$adminGivenPoints;?>pt</td>
							</tr>
							<tr class="store_grant">
								<th>店舗ポイント付与金額</th>
								<td class=""><?=$storePointsAmount;?>円</td>
							</tr>
							<tr class="point_comm bg">
								<th>ポイント手数料（税込）</th>
								<td class="pk"><?=$pointFeeWithTax;?>円</td>
							</tr>
							<!-- <tr class="comm_sum" >
								<th>運営から付与するポイント＋ポイント手数料合計</th>
								<td class="pk"><>円</td>
							</tr> -->
						</table>
					</div>
				</div>
				<p class="lma-btn_box btn_wide"><a href="get_csv.php?year=<?=$year;?>&month=<?=$month;?>">店舗別CSVダウンロード</a></p>
			</div>
			<div class="lma-content_block nobg">
				<ul class="lma-pnavi_list clearfix">
					<li class="prev"><a href="./?year=<?=$next_year;?>&month=<?=$next_month_number;?>">次月</a></li>
					<li class="next"><a href="./?year=<?=$prev_year;?>&month=<?=$prev_month_number;?>">先月</a></li>
				</ul>
			</div>
		</section>
	</main>
</div><!-- /.lma-container -->
</body>
</html>