<!DOCTYPE html>
<html lang="ja">

<head>
	<meta charset="UTF-8">

	<meta name="robots" content="noindex,follow">
	<meta name="viewport" content="width=device-width,user-scalable=no">
	<meta name="format-detection" content="telephone=no" />

	<link href="../css/default.css" rel="stylesheet" media="all">
	<link href="../css/admin.css" rel="stylesheet" media="all">

	<title>WAKUWAKU POINT 管理画面 [加盟店一覧]</title>

	<script type="text/javascript" src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
	<script type="text/javascript" src="../js/common.js"></script>
	<script>
		$(document).ready(function() {
			$('.bu').on('click', function(e) {
				e.preventDefault(); // リンクのデフォルト動作を停止
				var url = $(this).attr('href'); // リンクのURLを取得

				// アラートの表示と処理の確認
				if (confirm('この店舗を削除してもよろしいですか？')) {
					// OKを押した場合のみ処理を実行
					window.location.href = url;
				}
			});
		});
	</script>
</head>

<body class="lma-point_body lma-dashboard">
	<div class="lma-container">
		<<?php include './view/includes/side_menu.php'; ?>
			<main class="lma-main_contents">
			<section class="lma-content flex">
				<div class="lma-main_head">
					<div class="lma-title_block">
						<h2>加盟店一覧</h2>
					</div>
				</div>
				<div class="lma-content_block staff nobg">
					<ul class="lma-user_list store">
						<?= $store_html; ?>
						<!-- <li>
							<div class="lma-user_box">
								<div class="user_info">
									<h3 class="name">gree green garden</h3>
								</div>
								<div class="lma-btn_box btn_list">
									<button class="gy" type="button">明細確認</button>
									<button class="lgy" type="button">加盟店情報修正</button>
									<button class="gy" type="button">スタッフ管理</button>
									<button class="bu" type="button">削除</button>
								</div>
							</div>
						</li>
						<li>
							<div class="lma-user_box tbd">
								<div class="user_info">
									<h3 class="name">gree green garden<span class="icon">未承認</span></h3>
								</div>
								<div class="lma-btn_box btn_list">
									<button class="gy" type="button">明細確認</button>
									<button class="lgy" type="button">加盟店情報修正</button>
									<button class="gy" type="button">スタッフ管理</button>
									<button class="bu" type="button">削除</button>
								</div>
							</div>
						</li> -->
					</ul>
				</div>
			</section>
			</main>
	</div><!-- /.lma-container -->
</body>

</html>