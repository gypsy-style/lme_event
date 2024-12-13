<!DOCTYPE html>
<html lang="ja"><head>
<meta charset="UTF-8">

<meta name="robots" content="noindex,follow">
<meta name="viewport" content="width=device-width,user-scalable=no">
<meta name="format-detection" content="telephone=no" />

<link href="../css/default.css" rel="stylesheet" media="all">
<link href="../css/admin.css" rel="stylesheet" media="all">

<title>WAKUWAKU POINT 管理画面 [店舗情報]</title>

<script type="text/javascript" src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script type="text/javascript" src="../js/common.js"></script>

</head>
<body class="lma-point_body lma-dashboard">
<div class="lma-container">
	<input id="burger_btn" class="burger_btn" type="checkbox">
	<aside class="lma-sidebar">
		<div class="lma-burger_open">
			<label class="open_btn" for="burger_btn"><span>&nbsp;</span></label>
		</div>
		<div class="lma-sinner">
			<div class="lma-logo_block">
				<h1><a href="#"><span class="name">WAKUWAKU POINT</span><span class="text">管理画面</span></a></h1>
			</div>
			<div class="lma-user_block">
				<p class="lma-btn_box btn_wh btn_min"><a href="#">ログアウト</a></p>
			</div>
			<div class="lma-navi_block">
				<ul class="lma-navi_list">
					<li class="dashboard"><a href="#"><span class="text">ダッシュボード</span></a></li>
					<li class="store current"><a href="#"><span class="text">加盟店</span></a></li>
					<li class="user"><a href="#"><span class="text">ユーザー</span></a></li>
				</ul>
			</div>
		</div>
	</aside>
	<main class="lma-main_contents">
		<section class="lma-content flex">
			<div class="lma-main_head">
				<div class="lma-title_block">
					<h2><?=$store_name;?> 店舗情報</h2>
				</div>
			</div>
			<div class="lma-content_block store_edit">
				<form action="store_post.php" method="POST">
					<dl class="lma-form_box">
						<dt>ステータス</dt>
						<dd>
							<?php
							$checked = '';
							if($status == 1) {
								$checked = ' checked';
							}
							?>
							<label><input type="checkbox" name="status" id="status" value="1"<?=$checked;?>>承認</label>
						</dd>
						<dt><label for="img">画像</label></dt>
						<dd><div class="img_wrap"><img src="../image/ad/ad_img01.jpg" alt=""></div></dd>
						<!-- <dt><label for="category">カテゴリー</label></dt>
						<dd><input type="text" name="store_category" id="store_category" size="30" value="<?=$store_category;?>"></dd> -->
						<dt><label for="sector">業種・業態</label></dt>
						<dd>
							<select name="store_kind" id="store_kind">
								<option value="">---------</option>
								<?php
								foreach($store_kind_list as $list):
									$selected = '';
									if($list == $store_kind) {
										$selected = ' selected';
									}
									?>
									<option value="<?=$list;?>"<?=$selected;?>><?=$list;?></option>
									<?php
									endforeach;?>
							</select>
						</dd>
						<dt><label for="zip1">住所</label></dt>
						<dd>
							<span class="zip_wrap"><input type="text" name="zip1" id="zip1" value="<?=$zip1;?>">&nbsp;−&nbsp;<input type="text" name="zip2" id="zip2" value="<?=$zip2;?>"></span>
							<input type="text" name="address" id="address" size="50" value="<?=$address;?>">
						</dd>
						<dt><label for="tel">電話番号</label></dt>
						<dd><input type="text" name="phone_number" id="phone_number" size="30" value="<?=$phone_number;?>"></dd>
						<dt><label for="time">営業時間</label></dt>
						<dd><input type="text" name="business_hours" id="business_hours" size="30" value="<?=$business_hours;?>"></dd>
						<dt><label for="holiday">定休日</label></dt>
						<dd><input type="text" name="regular_holiday" id="regular_holiday" size="30" value="<?=$regular_holiday;?>"></dd>
						<dt><label for="hp">ホームページ</label></dt>
						<dd><input type="text" name="homepage" id="homepage" size="50" value="<?=$homepage;?>"></dd>
						<dt><label for="insta">インスタグラム</label></dt>
						<dd><input type="text" name="instagram" id="instagram" size="50" value="<?=$instagram;?>"></dd>
						<dt><label for="line">公式LINE</label></dt>
						<dd><input type="text" name="official_line" id="official_line" size="50" value="<?=$official_line;?>"></dd>
						<dt><label for="charge">担当者</label></dt>
						<dd><input type="text" name="person_in_charge" id="person_in_charge" size="30" value="<?=$person_in_charge;?>"></dd>
						<dt><label for="mail">メールアドレス</label></dt>
						<dd><input type="text" name="email" id="email" size="30" value="<?=$email;?>"></dd>
						<dt><label for="point_per">付与ポイント</label></dt>
						<dd><input type="text" name="store_point" id="store_point" size="4" value="<?=$store_point;?>">％</dd>
						<dt><label for="line_id">管理者LINE ID</label></dt>
						<dd><input type="text" name="line_id" id="line_id" size="30" value="<?=$line_id;?>"></dd>
						<dt><label for="message">メッセージ</label></dt>
						<dd><textarea type="text" name="message" id="message" rows="5"><?=$message;?></textarea></dd>
						<dt>表示ボタン</dt>
						<dd>
							<label><input type="radio" name="display" value="ホームページ" checked>ホームページ</label>
							<label><input type="radio" name="display" value="インスタグラム">インスタグラム</label>
							<label><input type="radio" name="display" value="LINE公式アカウント">LINE公式アカウント</label>
						</dd>
					</dl>
					<input type="hidden" name="store_id" value="<?=$store_id;?>">
					<p class="lma-btn_box"><button type="submit">修正する</button></p>
				</form>
			</div>
		</section>
	</main>
</div><!-- /.lma-container -->
</body>
</html>