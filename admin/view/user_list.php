<!DOCTYPE html>
<html lang="ja">

<head>
	<meta charset="UTF-8">

	<meta name="robots" content="noindex,follow">
	<meta name="viewport" content="width=device-width,user-scalable=no">
	<meta name="format-detection" content="telephone=no" />

	<link href="<?= home_url(); ?>/wp-content/plugins/line-members/css/default.css" rel="stylesheet" media="all">
	<link href="<?= home_url(); ?>/wp-content/plugins/line-members/css/admin.css" rel="stylesheet" media="all">

	<title>WAKUWAKU POINT 管理画面 [ユーザー一覧]</title>

	<script type="text/javascript" src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
	<script type="text/javascript" src="<?= home_url(); ?>/wp-content/plugins/line-members/js/common.js"></script>

	<script>
		$(function() {
			$('.btn_fix_point').click(function() {
				let userID = $(this).attr('data-user_id');
				let fixed_point = $('#fixed_point_' + userID).val();
				console.log(userID);
				console.log(fixed_point);
				post = {
					user_id: userID,
					point: fixed_point
				};

				$.ajax({
					type: "GET",
					url: "<?= home_url(); ?>/wp-json/wp/v2/admin_update_point",
					dataType: "text",
					data: post
				}).done(function(response) {
					if (response == 'success') {
						location.reload(true);
					} else {
						alert(response);
					}

				}).fail(function(XMLHttpRequest, textStatus, errorThrown) {

					alert(errorThrown);
				});
			})
		})
	</script>
	<script>
		$(document).ready(function() {
			$('.bu').on('click', function(e) {
				e.preventDefault(); // リンクのデフォルト動作を停止
				var url = $(this).attr('href'); // リンクのURLを取得

				// アラートの表示と処理の確認
				if (confirm('このユーザーを削除してもよろしいですか？')) {
					// OKを押した場合のみ処理を実行
					window.location.href = url;
				}
			});
		});
	</script>

</head>

<body class="lma-point_body lma-dashboard">
	<div class="lma-container">
	<?php include(ABSPATH . 'wp-content/plugins/line-members/admin/view/includes/side_menu.php'); ?>
			<main class="lma-main_contents">
			<section class="lma-content">
				<div class="lma-main_head">
					<div class="lma-title_block">
						<h2>gree green garden ユーザー一覧</h2>
					</div>
					<div class="lma-search_area" id="search">
					<form action="<?= home_url(); ?>/wp-content/plugins/line-members/admin/user_list.php" method="post">
							<input class="lma-search_input" name="s" type="text" value="<?= $search_term; ?>">
							<input class="lma-search_submit" type="submit" value="検索">
						</form>
					</div>
				</div>
				<div class="lma-content_block staff nobg">
					<ul class="lma-user_list">
						<?= $user_list_html; ?>
	
					</ul>
					<?php if ($pagination) : ?>
    <div class="pagination-container">
        <?php echo $pagination; ?>
    </div>
<?php endif; ?>
				</div>
			</section>
			</main>
	</div><!-- /.lma-container -->
</body>

</html>