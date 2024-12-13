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
	!isset($_REQUEST['user_id']) || empty($_REQUEST['user_id'])
) {

	if (isset($_GET['liff_state'])) {
		// liff.stateのパラメータを取得
		$liff_state = $_GET['liff_state'];



		// URLデコードして中のパラメータを取り出す
		$decoded_liff_state = urldecode($liff_state);
		$decoded_liff_state = ltrim($decoded_liff_state, '?');


		// store_idの値を解析
		parse_str($decoded_liff_state, $params);


		if (isset($params['user_id'])) {
			$user_id = $params['user_id'];
			$user_id = htmlspecialchars($user_id, ENT_QUOTES, 'UTF-8');
		} else {
			echo '店舗情報が取得できませんでした';
			exit;
		}
	} else {
		echo '店舗情報が取得できませんでした';
		exit;
	}
}
$user_id = $_REQUEST['user_id'];
// $point_rate = get_post_meta($store_id, 'point_rate', true);

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

	<title>WAKUWAKU POINT [ポイントを付与する]</title>
	<script src="https://static.line-scdn.net/liff/edge/2.1/sdk.js"></script>
	<script
		src="https://code.jquery.com/jquery-3.6.0.min.js"
		integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4="
		crossorigin="anonymous"></script>
	<script src="https://static.line-scdn.net/liff/edge/2.1/sdk.js"></script>
	<script>
		$(function() {
			<?php
			$liff_id_store_give_point = get_option('liff_id_store_give_point');
			$after_registration_action = get_option('after_registration_action');
			$liff_id_form = get_option('liff_id_form');
			?>
			// 追加
			initializeLiff("<?= $liff_id_store_give_point; ?>");

			$('#percent').on('change',function(){
				let price = parseFloat($('#price').val());
				let point_rate = $(this).val();

				let priceWithoutTax = price / 1.1;
				priceWithoutTax = Math.ceil(priceWithoutTax);

				let pointRate = parseFloat(point_rate) / 100; // pointRateを%として扱うために100で割る

			
				let points = 0;
				if (!isNaN(priceWithoutTax) && !isNaN(pointRate)) { // 税抜き価格とpointRateが数値の場合のみ計算
					points = priceWithoutTax * pointRate; // 税抜き価格にpointRateをかけて獲得ポイントを計算
					points = Math.ceil(points);
					$('#point_display').text(points); // 結果を表示する場合、例えば #points_display に表示
					$('#get_point').val(points);
				}

				// 計算式にも反映
				$('#calculation-pointRate').text(point_rate);
				$('#calculation-priceTarget').text(priceWithoutTax);
				$('#calculation-withoutTax').text(priceWithoutTax);
				$('#calculation-withTax').text(price);
				$('#calculation-point').text(points);

				$('#value_of_point_rate').val(point_rate);
			})

			$('#price').on('change', function() {
				let price = parseFloat($(this).val()); // 税込価格を取得し、floatに変換
				let point_rate = $('#percent').val();
				console.log(price);

				// 価格から10%の税を引いて税抜き価格を計算
				let priceWithoutTax = price / 1.1;
				priceWithoutTax = Math.ceil(priceWithoutTax);
				console.log(priceWithoutTax);

				let pointRate = parseFloat(point_rate) / 100; // pointRateを%として扱うために100で割る
				console.log(point_rate);

				
				let points = 0;
				if (!isNaN(priceWithoutTax) && !isNaN(pointRate)) { // 税抜き価格とpointRateが数値の場合のみ計算
					points = priceWithoutTax * pointRate; // 税抜き価格にpointRateをかけて獲得ポイントを計算
					points = Math.ceil(points);
					$('#point_display').text(points); // 結果を表示する場合、例えば #points_display に表示
					$('#get_point').val(points);
					
				}

				// 計算式にも反映
				$('#calculation-pointRate').text(point_rate);
				$('#calculation-priceTarget').text(priceWithoutTax);
				$('#calculation-withoutTax').text(priceWithoutTax);
				$('#calculation-withTax').text(price);
				$('#calculation-point').text(points);

				$('#value_of_point_rate').val(point_rate);
			});
			$('#form').submit(function(event) {
				event.preventDefault();
				let type = 'register';

				let form = document.getElementById('form');
				// console.log(document.forms.line-members-form);
				let formData = new FormData(document.forms.form);
				// console.log(formData);
				let values = formData.values();
				// console.log(values);
				let post = {};
				let pushMessage = [];
				$("#form :input").each(function() {
					let input = $(this); // This is the jquery object of the input, do what you will
					let input_name = input.attr('name');
					console.log(input_name);
					let type = input.attr('type');
					let val;
					if (input_name) {
						val = $('#' + input_name).val();
						post[input_name] = val;
					}
				});
				// pushMessage.push('お誕生日：'+post['birthday_y']+'年'+post['birthday_m']+'月');
				post['line_id'] = userId;
				post['store_id'] = storeId;
				post['user_id'] = "<?= $user_id; ?>";
				console.log(post);

				// return false;
				pushMessage = pushMessage.join('\n');
				liff.sendMessages([{
					type: 'text',
					text: pushMessage
				}]);
				// console.log(post);
				// return false;
				$.ajax({
					type: "GET",
					url: "<?= home_url(); ?>/wp-json/wp/v2/store_give_point",
					dataType: "text",
					data: post
				}).done(function(response) {
					console.log(response);
					alert('ポイントを渡しました')
					window.close();
					liff.closeWindow();

				}).fail(function(XMLHttpRequest, textStatus, errorThrown) {
					console.log(textStatus);
					alert(errorThrown);
				});
				event.preventDefault();

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
						liff.login({
							redirectUri: window.location.href,
						})
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
		let storeId;
		// let point_rate;

		getProfile = function() {
			liff.getProfile()
				.then(profile => {
					userId = profile.userId;
					displayName = profile.displayName;
					post = {
						line_id: userId
					};
					$.ajax({
						type: "GET",
						url: "<?= home_url(); ?>/wp-json/wp/v2/get_store_info",
						dataType: "text",
						data: post
					}).done(function(data) {
						console.log(data);
						let jsonData = JSON.parse(data);


						$.each(jsonData, function(i, val) {
							// if (i == 'point_rate') {
							// 	$('.' + i).append(val);
							// 	$('input[name="username"]').val(val);
							// 	point_rate = val;
							// } else if (i == 'store_id') {
							// 	storeId = val;

							// }
							if (i == 'store_id') {
								storeId = val;

							}
						})
						// liff.closeWindow();

					}).fail(function(XMLHttpRequest, textStatus, errorThrown) {
						liff.closeWindow();

					});

				})
				.catch((err) => {
					// alert("liff getProfile error : " + err);
				});
		};
	</script>
</head>

<body class="lmf-point_body shop">
	<div class="lmf-container">
		<div class="lmf-title_block">
			<h1 class="title">ポイントを付与する</h1>
		</div>
		<?php
		if ($show_banner == 1) {
			Html::store_banner();
		}
		?>
		<main class="lmf-main_contents">
			<section class="lmf-content">
				<form id="form">
					<div class="lmf-grant_block lmf-white_block">
						<!-- <h2 class="lmf-title_bar pk small center"><em class="label">ポイント付与率<span class="point_rate"></span>%</em></h2> -->
						<dl class="lmf-form_box">
							<dt><label for="price">利用金額（税込）</label></dt>
							<dd><em class="input number">
								<input type="tel" name="price" id="price" value="">
							</em>
							<span class="unit">円</span></dd>
							<dd class="horizontal">
								<label for="percent">ポイント付与率</label>
								<em class="select per">
									<select name="percent" id="percent">
										<option value="1">1</option>
										<option value="2">2</option>
										<option value="3">3</option>
										<option value="4">4</option>
										<option value="5">5</option>
									</select>
								</em>
								<span class="unit">％</span>
							</dd>
							<dd class="center"><span class="icon_arrow">&nbsp;</span></dd>
							<dd class="center result">
								<em class="label_bar">付与ポイント</em>
								<b class="points"><span class="point" id="point_display"></span><span class="pt">pt</span></b>
							</dd>
							<dd class="center percentage">
								<span id="calculation-withTax">0</span>円（税込）→<span id="calculation-withoutTax">0</span>円（税抜）<br>
								<span id="calculation-priceTarget">0</span>円×付与率<span id="calculation-pointRate">1</span>%=<span id="calculation-point">0</span>
							</dd>
						</dl>
					</div>
					<p class="lmf-btn_box"><button type="submit">ポイントを付与する</button></p>
					<input type="hidden" name="value_of_point_rate" class="point_rate" value="" id="value_of_point_rate">
					<input type="hidden" name="get_point" value="" id="get_point">
				</form>
			</section>
		</main>
	</div><!-- /.lmf-container -->
</body>

</html>