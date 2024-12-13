<?php
require_once('../vendor/autoload.php'); //LINE BOT SDKを読み込み
require_once('../../../../wp-load.php'); //WordPressの基本機能を読み込み
require_once('../line-members.php'); //LINE Connectを読み込み
require_once('../includes/html.php');

$enabled_coupon = get_option('enabled_coupon');
$not_exist_redirect = get_option('not_exist_redirect');
$not_exist_alert_message = get_option('not_exist_alert_message');
$show_banner = get_option('show_banner');

$store_kind = [
	"飲食店",
    "ファッション",
    "美容",
    "マッサージ",
    "スポーツ",
    "習い事",
    "ペット",
    "自動車",
    "農業漁業",
    "宿泊施設",
    "その他のサービス"
];

?>
<!DOCTYPE html>
<html lang="ja">

<head>
	<meta charset="UTF-8">

	<meta name="robots" content="noindex,follow">
	<meta name="viewport" content="width=device-width,user-scalable=no">
	<meta name="format-detection" content="telephone=no" />

	<link href="../css/default.css" rel="stylesheet" media="all">
	<link href="../css/front.css" rel="stylesheet" media="all">
	<script
		src="https://code.jquery.com/jquery-3.6.0.min.js"
		integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4="
		crossorigin="anonymous"></script>
	<script src="https://static.line-scdn.net/liff/edge/2.1/sdk.js"></script>
	<!-- load jquery UI -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js" integrity="sha512-uto9mlQzrs59VwILcLiRYeLKPPbS/bT71da/OEBYEwcdNUk8jYIy+D176RYoop1Da+f9mvkYrmj5MCLZWEtQuA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1/i18n/jquery.ui.datepicker-ja.min.js"></script>
	<title>WAKUWAKU POINT [店舗登録]</title>
	<script>
		$(function() {
			$('button[type="submit"]').prop('disabled', true);
			$('#agreement-warning').hide();

			// Enable or disable the button based on the checkbox and show/hide message
			$('#term').change(function() {
				if ($(this).is(':checked')) {
					$('button[type="submit"]').prop('disabled', false);
					$('#agreement-warning').hide(); // Hide the warning message
				} else {
					$('button[type="submit"]').prop('disabled', true);
					$('#agreement-warning').show(); // Show the warning message
				}
			});
			<?php
			$liff_id_store_register = get_option('liff_id_store_register');
			$after_registration_action = get_option('after_registration_action');
			$liff_id_form = get_option('liff_id_form');
			$liff_id_store_info = get_option('liff_id_store_info');
			?>
			// 追加
			initializeLiff("<?= $liff_id_store_register; ?>");
			$('#form').submit(function(event) {
				event.preventDefault();
				let type = 'register';

				let form = document.getElementById('form');
				// console.log(document.forms.line-members-form);
				let post = new FormData();

				// return false;
				// console.log(formData);
				// let values = formData.values();
				// console.log(values);
				let pushMessage = [];
				let same_radio;
				let birthdayMessage;
				$("#form :input").each(function() {

					let input = $(this); // This is the jquery object of the input, do what you will
					let input_name = input.attr('name');
					console.log(input_name);
					let type = input.attr('type');
					let val;
					if (input_name) {
						if (type == 'file') {
							console.log('file');
							console.log(input_name);
							let image = $('#' + input_name).prop("files");
							post.append(input_name, image[0]);
							// post.append(input_name, $('#' + input_name).val());
						} else if (type == 'radio') {
							// post.append(input_name, $( 'input[name="'+input_name+'"]').val());
							let selectedValue = $('input[name="' + input_name + '"]:checked').val();
							post.append(input_name, selectedValue);
						} else {
							post.append(input_name, $('#' + input_name).val());
						}

						// val = $('#' + input_name).val();
						// post[input_name] = val;
						// pushメッセージ作成
						if (!(type == 'radio' && same_radio == input_name) && input_name != 'form_type') {
							same_radio = input_name;
							let title = input.attr('data-title');
							if (title) {
								pushMessage.push(title + '：' + val);
							} else {
								pushMessage.push(val);
							}
						}
					}
				});
				// pushMessage.push('お誕生日：'+post['birthday_y']+'年'+post['birthday_m']+'月');
				post.append('line_id', userId);
				post.append('displayName', displayName);

				$.ajax({
					type: "POST",
					url: "<?= home_url(); ?>/wp-json/wp/v2/register_store",
					contentType: false, // ファイルのアップロードに必要
					processData: false, // データの処理をjQueryに任せない
					dataType: 'json',
					data: post
				}).done(function(response) {
					console.log(response);
					// let redirect_url = 'https://liff.line.me/<?= $liff_id_store_info; ?>';
					// liff.closeWindow();
					// 	window.close();
					// window.location = redirect_url;
					alert('店舗登録が完了しました');
					liff.closeWindow();
				}).fail(function(XMLHttpRequest, textStatus, errorThrown) {
					console.log(XMLHttpRequest)
					console.log(textStatus)
					console.log(errorThrown)
					// alert(errorThrown);
				});
				event.preventDefault();

			});

			$('.img_upload').on('change', function(e) {
				var fileset = $(this).val();
				var place = $(this).next("img.img_preview");

				if (fileset === '') {
					place.attr('src', "");
				} else {
					var reader = new FileReader();
					reader.onload = function(e) {
						place.attr('src', e.target.result);
					}
					reader.readAsDataURL(e.target.files[0]);
				}
			});
		});

		// 追加
		function initializeLiff(liffId) {
			liff
				.init({
					liffId: liffId
				})
				.then(() => {
					if (!liff.isLoggedIn()) {
						liff.login()
					}

					getProfile();
				})
				.catch((err) => {
					console.log('LIFF Initialization failed ', err)
				});
		}
		let userId;
		let displayName;
		let post;

		getProfile = function() {
			liff.getProfile()
				.then(profile => {
					userId = profile.userId;
					displayName = profile.displayName;

				})
				.catch((err) => {
					// alert("liff getProfile error : " + err);
				});
		};
	</script>
</head>

<body class="lmf-point_body shop" style="overflow-y:scroll;">
	<div class="lmf-container">
		<div class="lmf-title_block">
			<h1 class="title">店舗登録</h1>
		</div>
		<?php
        if($show_banner == 1){
			Html::store_banner();
        }
		?>
		<main class="lmf-main_contents">
			<section class="lmf-content">
				<form id="form" enctype="multipart/form-data">
					<div class="lmf-profedit_block lmf-white_block">
						<p class="mB20">下記内容を入力いただき登録ボタンを押してください。</p>
						<dl class="lmf-form_box">
							<dt><label for="name">店舗名</label></dt>
							<dd><em class="input"><input type="text" name="store_name" id="store_name"></em></dd>
							<dt><label for="name">画像</label></dt>
							<dd><em class="input"><input type="file" name="store_image" id="store_image" class="img_upload"></em></dd>
							<dt><label for="name">業種・業態</label></dt>
							<dd>
								<em class="input">
									<select name="store_kind" id="store_kind">
										<option value="">選択してください</option>
										<?php foreach ($store_kind as $option): ?>
											<option value="<?= htmlspecialchars($option); ?>">
												<?= htmlspecialchars($option); ?>
											</option>
										<?php endforeach; ?>
									</select>
								</em>
							</dd>
							<dt><label for="addr">郵便番号</label></dt>
							<dd><em class="input"><input type="text" name="zip1" id="zip1">-<input type="text" name="zip2" id="zip2"></em></dd>
							<dt><label for="addr">住所</label></dt>
							<dd><em class="input"><input type="text" name="address" id="address"></em></dd>
							<dt><label for="tel">電話番号</label></dt>
							<dd><em class="input"><input type="tel" name="phone_number" id="phone_number"></em></dd>
							<dt><label for="addr">営業時間</label></dt>
							<dd><em class="input"><textarea name="business_hours" id="business_hours"></textarea></em></dd>
							<dt><label for="addr">定休日</label></dt>
							<dd><em class="input"><input type="text" name="regular_holiday" id="regular_holiday"></em></dd>
							<dt><label for="addr">ホームページ</label></dt>
							<dd><em class="input"><input type="text" name="homepage" id="homepage"></em></dd>
							<dt><label for="addr">インスタグラム</label></dt>
							<dd><em class="input"><input type="text" name="instagram" id="instagram"></em></dd>
							<dt><label for="addr">公式LINE</label></dt>
							<dd><em class="input"><input type="text" name="official_line" id="official_line"></em></dd>
							<dt><label for="addr">担当者</label></dt>
							<dd><em class="input"><input type="text" name="person_in_charge" id="person_in_charge"></em></dd>
							<dt><label for="addr">メールアドレス</label></dt>
							<dd><em class="input"><input type="text" name="email" id="email"></em></dd>
							<dt><label for="addr">メッセージ</label></dt>
							<dd><em class="input"><textarea name="message" id="message"></textarea></em></dd>
							<dt><label for="addr">ポイント付与率</label></dt>
							<dd><em class="input"><input type="text" name="point_rate" id="point_rate">%</em></dd>
							<dt><label for="addr">表示ボタン</label></dt>
							<dd class="text">
								<label><input type="radio" name="display_button" value="homepage" checked="">ホームページ</label>
								<label><input type="radio" name="display_button" value="instagram" checked="">インスタグラム</label>
								<label><input type="radio" name="display_button" value="official_line" checked="">公式LINE</label>
							</dd>

							<dd class="text">登録にあたりワクワクポイントの<a href="#">利用規約</a>をご確認ください。</dd>
							<dd class="center"><label for="term"><input type="checkbox" name="term" id="term">利用規約に同意する</label></dd>
						</dl>
						<p id="agreement-warning" style="color: red;text-align:center;">利用規約に同意してください</p>
					</div>
					<p class="lmf-btn_box"><button type="submit">登録する</button></p>
				</form>
			</section>
		</main>
	</div><!-- /.lmf-container -->
</body>

</html>