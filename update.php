<?php
require_once('vendor/autoload.php');    //LINE BOT SDKを読み込み
require_once('../../../wp-load.php');    //WordPressの基本機能を読み込み
require_once('line-members.php');        //LINE Connectを読み込み
require_once('includes/html.php');
require_once('extensions/custom_fields.php');
$custom_fields = custom_fields::$custom_fields;
$after_registration_action = get_option('after_registration_action');
$show_banner = get_option('show_banner');


?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">

    <meta name="robots" content="noindex,follow">
    <meta name="viewport" content="width=device-width,user-scalable=no">
    <meta name="format-detection" content="telephone=no" />

    <link href="./css/default.css" rel="stylesheet" media="all">
    <link href="./css/front.css" rel="stylesheet" media="all">

    <title>WAKUWAKU POINT [会員登録]</title>
    <script
        src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4="
        crossorigin="anonymous"></script>
    <script src="https://static.line-scdn.net/liff/edge/2.1/sdk.js"></script>

    <script>
        $(function() {


            <?php
            $liff_id_form = get_option('liff_id_form');
            $after_registration_action = get_option('after_registration_action');
            $liff_id_profile = get_option('liff_id_profile');
            $liff_id_profile_edit = get_option('liff_id_profile_edit');
            ?>
            // 追加
            initializeLiff("<?= $liff_id_profile_edit; ?>");
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
                let same_radio;
                let birthdayMessage;
                pushMessage.push('【登録内容】');
                $("#form :input").each(function() {

                    let input = $(this); // This is the jquery object of the input, do what you will
                    let input_name = input.attr('name');
                    console.log(input_name);
                    let type = input.attr('type');
                    let val;
                    if (input_name) {
                        val = $('#' + input_name).val();
                        post[input_name] = val;
                        // pushメッセージ作成
                        if (!(type == 'radio' && same_radio == input_name) && input_name != 'form_type' && input_name != 'term') {
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
                post['line_id'] = userId;
                post['displayName'] = displayName;
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
                    url: "<?= home_url(); ?>/wp-json/wp/v2/update_line_user",
                    dataType: "text",
                    data: post
                }).done(function(response) {
                    liff.closeWindow();
                }).fail(function(XMLHttpRequest, textStatus, errorThrown) {
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
            console.log('getprofile called');
            liff.getProfile()
                .then(profile => {
                    console.log(profile);
                    console.log(profile.userId);
                    userId = profile.userId;
                    displayName = profile.displayName;

                    post = {line_id:userId};

					$.ajax({
                        type: "GET",
                        url: "<?=home_url();?>/wp-json/wp/v2/richmenu_profile",
                        dataType: "text",
                        data:post
                    }).done(function(data){
                        console.log(data);
                        let jsonData = JSON.parse(data);
                        console.log(jsonData.length);
                        if(!jsonData || jsonData.length == 0) {
                            alert('権限がありません');
							liff.closeWindow();
                           
                            return false;
                        }

                        $.each(jsonData,function(i,val) {
                            if(i == 'sex' || i == 'area') {
                                $('#'+i).val(val);
                            }
                        })

                    }).fail(function(XMLHttpRequest, textStatus, errorThrown){
                        console.log(textStatus);
                        liff.closeWindow();
                        
                    });


                })
                .catch((err) => {
                    console.log("Profileが取得できません")
                    // alert("liff getProfile error : " + err);
                });
        };
    </script>
</head>

<body class="lmf-point_body cust">
    <div class="lmf-container">
        <div class="lmf-title_block">
            <h1 class="title">会員情報更新</h1>
        </div>
        <?php
        if($show_banner == 1){
			Html::store_banner();
        }
		?>
        <main class="lmf-main_contents">
            <section class="lmf-content">
                <form id="form">
                    <div class="lmf-profedit_block lmf-white_block">
                        <p class="mB20">下記内容を入力いただき登録ボタンを押してください。</p>
                        <dl class="lmf-form_box">
                            <dt><label for="sex">性別</label></dt>
                            <dd><select name="sex" id="sex">
                                    <option value="">----選択してください----</option>
                                    <option value="男性">男性</option>
                                    <option value="女性">女性</option>
                                    <option value="その他">その他</option>
                                </select></dd>
                            <dt><label for="area">住んでいる地域</label></dt>
                            <dd><select name="area" id="area">
                                    <option value="">----選択してください----</option>
                                    <optgroup label="あ行">
                                        <option label="相ノ谷(アイノタニ)" value="相ノ谷(アイノタニ)">相ノ谷(アイノタニ)</option>
                                        <option label="旭町(アサヒマチ)" value="旭町(アサヒマチ)">旭町(アサヒマチ)</option>
                                        <option label="阿品(アジナ)" value="阿品(アジナ)">阿品(アジナ)</option>
                                        <option label="愛宕町(アタゴマチ)" value="愛宕町(アタゴマチ)">愛宕町(アタゴマチ)</option>
                                        <option label="飯田町(イイダマチ)" value="飯田町(イイダマチ)">飯田町(イイダマチ)</option>
                                        <option label="伊房(イフサ)" value="伊房(イフサ)">伊房(イフサ)</option>
                                        <option label="今津町(イマヅマチ)" value="今津町(イマヅマチ)">今津町(イマヅマチ)</option>
                                        <option label="入野(イリノ)" value="入野(イリノ)">入野(イリノ)</option>
                                        <option label="岩国(イワクニ)" value="岩国(イワクニ)">岩国(イワクニ)</option>
                                        <option label="上田(ウエダ)" value="上田(ウエダ)">上田(ウエダ)</option>
                                        <option label="牛野谷町(ウシノヤマチ)" value="牛野谷町(ウシノヤマチ)">牛野谷町(ウシノヤマチ)</option>
                                        <option label="青木町(オオギマチ)" value="青木町(オオギマチ)">青木町(オオギマチ)</option>
                                        <option label="大谷(オオタニ)" value="大谷(オオタニ)">大谷(オオタニ)</option>
                                        <option label="大山(オオヤマ)" value="大山(オオヤマ)">大山(オオヤマ)</option>
                                        <option label="小瀬(オゼ)" value="小瀬(オゼ)">小瀬(オゼ)</option>
                                        <option label="尾津町(オヅマチ)" value="尾津町(オヅマチ)">尾津町(オヅマチ)</option>
                                    </optgroup>
                                    <optgroup label="か行">
                                        <option label="桂町(カツラマチ)" value="桂町(カツラマチ)">桂町(カツラマチ)</option>
                                        <option label="叶木(カノウギ)" value="叶木(カノウギ)">叶木(カノウギ)</option>
                                        <option label="川口町(カワグチマチ)" value="川口町(カワグチマチ)">川口町(カワグチマチ)</option>
                                        <option label="川下町(カワシモマチ)" value="川下町(カワシモマチ)">川下町(カワシモマチ)</option>
                                        <option label="川西(カワニシ)" value="川西(カワニシ)">川西(カワニシ)</option>
                                        <option label="瓦谷(カワラダニ)" value="瓦谷(カワラダニ)">瓦谷(カワラダニ)</option>
                                        <option label="杭名(クイナ)" value="杭名(クイナ)">杭名(クイナ)</option>
                                        <option label="玖珂町(クガマチ)" value="玖珂町(クガマチ)">玖珂町(クガマチ)</option>
                                        <option label="楠町(クスノキマチ)" value="楠町(クスノキマチ)">楠町(クスノキマチ)</option>
                                        <option label="車町(クルママチ)" value="車町(クルママチ)">車町(クルママチ)</option>
                                        <option label="黒磯町(クロイソマチ)" value="黒磯町(クロイソマチ)">黒磯町(クロイソマチ)</option>
                                    </optgroup>
                                    <optgroup label="さ行">
                                        <option label="下(シモ)" value="下(シモ)">下(シモ)</option>
                                        <option label="守内(シユウチ)" value="守内(シユウチ)">守内(シユウチ)</option>
                                        <option label="周東町(シユウトウマチ)" value="周東町(シユウトウマチ)">周東町(シユウトウマチ)</option>
                                        <option label="装束町(シヨウゾクマチ)" value="装束町(シヨウゾクマチ)">装束町(シヨウゾクマチ)</option>
                                        <option label="昭和町(シヨウワマチ)" value="昭和町(シヨウワマチ)">昭和町(シヨウワマチ)</option>
                                        <option label="新港町(シンミナトマチ)" value="新港町(シンミナトマチ)">新港町(シンミナトマチ)</option>
                                        <option label="砂山町(スナヤママチ)" value="砂山町(スナヤママチ)">砂山町(スナヤママチ)</option>
                                        <option label="角(スミ)" value="角(スミ)">角(スミ)</option>
                                        <option label="関戸(セキド)" value="関戸(セキド)">関戸(セキド)</option>
                                    </optgroup>
                                    <optgroup label="た行">
                                        <option label="竹安(タケヤス)" value="竹安(タケヤス)">竹安(タケヤス)</option>
                                        <option label="多田(タダ)" value="多田(タダ)">多田(タダ)</option>
                                        <option label="立石町(タテイシマチ)" value="立石町(タテイシマチ)">立石町(タテイシマチ)</option>
                                        <option label="田原(タワラ)" value="田原(タワラ)">田原(タワラ)</option>
                                        <option label="近延(チカノブ)" value="近延(チカノブ)">近延(チカノブ)</option>
                                        <option label="通津(ツヅ)" value="通津(ツヅ)">通津(ツヅ)</option>
                                        <option label="寺山(テラヤマ)" value="寺山(テラヤマ)">寺山(テラヤマ)</option>
                                        <option label="天尾(テンノオ)" value="天尾(テンノオ)">天尾(テンノオ)</option>
                                    </optgroup>
                                    <optgroup label="な行">
                                        <option label="中津町(ナカヅマチ)" value="中津町(ナカヅマチ)">中津町(ナカヅマチ)</option>
                                        <option label="長野(ナガノ)" value="長野(ナガノ)">長野(ナガノ)</option>
                                        <option label="灘町(ナダマチ)" value="灘町(ナダマチ)">灘町(ナダマチ)</option>
                                        <option label="錦町(ニシキマチ)" value="錦町(ニシキマチ)">錦町(ニシキマチ)</option>
                                        <option label="錦見(ニシミ)" value="錦見(ニシミ)">錦見(ニシミ)</option>
                                    </optgroup>
                                    <optgroup label="は行">
                                        <option label="柱島(ハシラジマ)" value="柱島(ハシラジマ)">柱島(ハシラジマ)</option>
                                        <option label="柱野(ハシラノ)" value="柱野(ハシラノ)">柱野(ハシラノ)</option>
                                        <option label="廿木(ハタキ)" value="廿木(ハタキ)">廿木(ハタキ)</option>
                                        <option label="土生(ハブ)" value="土生(ハブ)">土生(ハブ)</option>
                                        <option label="日の出町(ヒノデマチ)" value="日の出町(ヒノデマチ)">日の出町(ヒノデマチ)</option>
                                        <option label="平田(ヒラタ)" value="平田(ヒラタ)">平田(ヒラタ)</option>
                                        <option label="藤生町(フジユウマチ)" value="藤生町(フジユウマチ)">藤生町(フジユウマチ)</option>
                                        <option label="二鹿(フタシカ)" value="二鹿(フタシカ)">二鹿(フタシカ)</option>
                                        <option label="保木(ホウキ)" value="保木(ホウキ)">保木(ホウキ)</option>
                                        <option label="保津町(ホウヅマチ)" value="保津町(ホウヅマチ)">保津町(ホウヅマチ)</option>
                                        <option label="本郷町(ホンゴウマチ)" value="本郷町(ホンゴウマチ)">本郷町(ホンゴウマチ)</option>
                                    </optgroup>
                                    <optgroup label="ま行">
                                        <option label="麻里布町(マリフマチ)" value="麻里布町(マリフマチ)">麻里布町(マリフマチ)</option>
                                        <option label="三笠町(ミカサマチ)" value="三笠町(ミカサマチ)">三笠町(ミカサマチ)</option>
                                        <option label="美川町(ミカワマチ)<" value="美川町(ミカワマチ)<">美川町(ミカワマチ)</option>
                                        <option label="御庄(ミシヨウ)" value="御庄(ミシヨウ)">御庄(ミシヨウ)</option>
                                        <option label="三角町(ミスミマチ)" value="三角町(ミスミマチ)">三角町(ミスミマチ)</option>
                                        <option label="海土路町(ミドロマチ)" value="海土路町(ミドロマチ)">海土路町(ミドロマチ)</option>
                                        <option label="南岩国町(ミナミイワクニマチ)" value="南岩国町(ミナミイワクニマチ)">南岩国町(ミナミイワクニマチ)</option>
                                        <option label="室の木町(ムロノキマチ)" value="室の木町(ムロノキマチ)">室の木町(ムロノキマチ)</option>
                                        <option label="持国(モチクニ)" value="持国(モチクニ)">持国(モチクニ)</option>
                                        <option label="元町(モトマチ)" value="元町(モトマチ)">元町(モトマチ)</option>
                                        <option label="門前町(モンゼンマチ)" value="門前町(モンゼンマチ)">門前町(モンゼンマチ)</option>
                                    </optgroup>
                                    <optgroup label="や行">
                                        <option label="山手町(ヤマテマチ)" value="山手町(ヤマテマチ)">山手町(ヤマテマチ)</option>
                                        <option label="由宇町(ユウマチ)" value="由宇町(ユウマチ)">由宇町(ユウマチ)</option>
                                        <option label="行波(ユカバ)" value="行波(ユカバ)">行波(ユカバ)</option>
                                        <option label="行正(ユキマサ)" value="行正(ユキマサ)">行正(ユキマサ)</option>
                                        <option label="横山(ヨコヤマ)" value="横山(ヨコヤマ)">横山(ヨコヤマ)</option>
                                    </optgroup>
                                    <optgroup label="ら行">
                                        <option label="六呂師(ロクロシ)" value="六呂師(ロクロシ)">六呂師(ロクロシ)</option>
                                    </optgroup>
                                </select></dd>
                        </dl>

                    </div>

                    <p class="lmf-btn_box btn_gy"><button type="submit">更新する</button></p>
                </form>
            </section>
        </main>
    </div><!-- /.lmf-container -->

    <div class="lmf-modal_wrap">
        <div class="lmf-modal_layer"></div>
        <div class="lmf-modal_content" id="modal_terms">
            <div class="modal_close_btn"><button>&times;</button></div>
            <div class="inner">
                <h2>わくわくポイント 利用規約</h2>

                <div class="text_block">

                    <div>
                    <h4>1. 適用範囲</h4>
                        <p>本規約は、わくわくポイント（以下、「本プログラム」という）に参加するすべての会員に適用されます。本プログラムを利用することで、会員は以下の利用規約に同意したものとみなされます。</p>
                        <h4>2. プログラムの説明</h4>
                        <p>本プログラムは、指定された事業者での購入時にポイントを獲得し、これらのポイントを次回以降の買い物で使用することができるプログラムです。</p>
                        <h4>3. 会員資格</h4>
                        <p>会員登録は無料ですが、年齢制限が適用される場合があります。<br>会員は、正確かつ最新の情報を提供する義務があります。<br>各会員は、一つのアカウントのみを持つことができます。</p>
                        <h4>4. ポイントの獲得と使用</h4>
                        <p>ポイントは、参加事業者での商品購入や特定のプロモーション活動に参加することで獲得できます。<br>獲得したポイントは、次回以降の買い物で使用することができます。<br>ポイントの有効期限は獲得から１年間とし、期限切れのポイントは自動的に失効します。</p>
                        <h4>5. プライバシーポリシー</h4>
                        <p>会員から収集した情報は、本プログラムの運営、改善およびプロモーションの目的でのみ使用します。</p>
                        <h4>6. アカウントの管理</h4>
                        <p>会員は自身のログイン情報を安全に保管し、第三者による不正利用から保護する責任を負います。</p>
                        <h4>7. 利用規約の変更</h4>
                        <p>当プログラムは、必要に応じて利用規約を変更することがあります。変更後の利用規約は、本プログラムのウェブサイト上で公開し、変更内容を会員に通知します。</p>
                        <h4>8. 免責事項</h4>
                        <p>当プログラムは、システム障害、天災地変、その他不可抗力によりサービスを提供できない場合には、責任を負わないものとします。</p>
                        <h4>9. 解約と退会</h4>
                        <p>会員はいつでも本プログラムの退会手続きを行うことができます。退会に伴い、未使用のポイントは失効します。</p>
                        <!-- <p> 隠れ家風のレトロな空間で、串カツは勿論お造りやその他豊富な逸品をたっぷり堪能♪ </p>
                    <p> ≪自慢の串カツ≫<br> 薄くサクサクに仕上げた衣でしつこくなく軽い味わい。<br> 知る人ぞ知る鼓ソースを使用した甘めの特製ダレでどうぞ </p>
                    <p> ≪お得なコース≫<br> 絶品串カツやもつ鍋が食べれるコースが2時間飲み放題付きで3,850円！単品90分飲み放題のプランもご用意してます◎</p> -->
                    </div>
                </div>
            </div>
        </div>
    </div><!-- /.modal_wrap -->
</body>

</html>