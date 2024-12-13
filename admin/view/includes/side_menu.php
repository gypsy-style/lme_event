<input id="burger_btn" class="burger_btn" type="checkbox">
<aside class="lma-sidebar">
	<div class="lma-burger_open">
		<label class="open_btn" for="burger_btn"><span>&nbsp;</span></label>
	</div>
	<div class="lma-sinner">
		<div class="lma-logo_block">
			<h1><a href="./index.php"><span class="name">WAKUWAKU POINT</span><span class="text">管理画面</span></a></h1>
		</div>
		<!-- <div class="lma-user_block">
				<p class="lma-btn_box btn_wh btn_min"><a href="#">ログアウト</a></p>
			</div> -->
		<div class="lma-navi_block">
			<ul class="lma-navi_list">
				<?php
				// Get the current page filename
				$currentPage = basename($_SERVER['PHP_SELF']);
				?>
				<li class="dashboard <?= ($currentPage == 'index.php') ? 'current' : ''; ?>"><a href="index.php"><span class="text">ダッシュボード</span></a></li>
				<li class="store <?= ($currentPage == 'store_list.php' || $currentPage == 'store_detail.php') ? 'current' : ''; ?>"><a href="./store_list.php"><span class="text">加盟店</span></a></li>
				<li class="user <?= ($currentPage == 'user_list.php') ? 'current' : ''; ?>"><a href="./user_list.php"><span class="text">ユーザー</span></a></li>
				<li class="user"><a href="<?= admin_url(); ?>" target="_blank" class="text">WP管理画面</span></a></li>
				<li class="user"><a href="./logout.php" target="_blank" class="text">ログアウト</span></a></li>
			</ul>
		</div>
	</div>
</aside>