<!DOCTYPE html>
<html lang="ja">

<head>
	<meta charset="UTF-8">

	<meta name="robots" content="noindex,follow">
	<meta name="viewport" content="width=device-width,user-scalable=no">
	<meta name="format-detection" content="telephone=no" />

	<link href="../css/default.css" rel="stylesheet" media="all">
	<link href="../css/admin.css" rel="stylesheet" media="all">

	<title>WAKUWAKU POINT 管理画面 [ユーザーポイント履歴]</title>

	<style>
		.btn_list .point_type {
			padding: 6px 10px;
    border-radius: 5px;
    color: white;
		}

		.btn_list.use .point_type{
			background-color: #559af0;

		}
		.btn_list.give .point_type {
			background-color: #f05555;
		}
	</style>

	<script type="text/javascript" src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
	<script type="text/javascript" src="../js/common.js"></script>


</head>

<body class="lma-point_body lma-dashboard">
	<div class="lma-container">
		<<?php include './view/includes/side_menu.php'; ?>
			<main class="lma-main_contents">
			<section class="lma-content flex">
				<div class="lma-main_head">
					<div class="lma-title_block">
						<h2><?= $user_name; ?> ポイント履歴</h2>
					</div>
				</div>
				<div class="lma-content_block staff nobg">
					<ul class="lma-user_list">
						<?= $html; ?>
					</ul>
				</div>
			</section>
			</main>
	</div><!-- /.lma-container -->
</body>

</html>