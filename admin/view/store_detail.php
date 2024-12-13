<!DOCTYPE html>
<html lang="ja"><head>
<meta charset="UTF-8">

<meta name="robots" content="noindex,follow">
<meta name="viewport" content="width=device-width,user-scalable=no">
<meta name="format-detection" content="telephone=no" />

<link href="../css/default.css" rel="stylesheet" media="all">
<link href="../css/admin.css" rel="stylesheet" media="all">

<title>WAKUWAKU POINT 管理画面 [加盟店明細]</title>

<script type="text/javascript" src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script type="text/javascript" src="../js/common.js"></script>

</head>
<body class="lma-point_body lma-dashboard">
<div class="lma-container">
<<?php include './view/includes/side_menu.php';?>
	<main class="lma-main_contents">
		<section class="lma-content flex">
			<div class="lma-main_head">
				<div class="lma-title_block">
					<h2><?=$store_name;?> 明細</h2>
				</div>
			</div>
			<div class="lma-content_block store_records">
				<div class="record_block">
					<div class="records_caption">
						<h2 class="lma-title_bar sky"><em class="label"><?=$year;?>月<?=$month;?></em></h2>
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
						</table>
					</div>
				</div>
				<!-- <p class="lma-btn_box btn_wide"><a href="#">店舗別CSVダウンロード</a></p> -->
			</div>
			<div class="lma-content_block nobg">
				<ul class="lma-pnavi_list clearfix">
					<!--<li class="prev"><a href="#">次月</a></li>-->
					<li class="next"><a href="?store_id=<?php echo $store_id; ?>&year=<?php echo $prev_year; ?>&month=<?php echo $prev_month_number; ?>">先月</a></li>
					<li class="prev"><a href="?store_id=<?php echo $store_id; ?>&year=<?php echo $next_year; ?>&month=<?php echo $next_month_number; ?>">次月</a></li>
				</ul>
				<div>
    </div>
			</div>
		</section>
	</main>
</div><!-- /.lma-container -->
</body>
</html>