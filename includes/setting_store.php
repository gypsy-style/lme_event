<?php

/**
 * Created by PhpStorm.
 * User: sawadakeisuke
 * Date: 5/16/22
 * Time: 6:12 AM
 */
//require_once('../extensions/custom_fields.php');
require_once(plugin_dir_path(plugin_dir_path(__FILE__)) . 'extensions/custom_fields.php');
class settingStore
{

    const LABEL = '店舗';
    const POST_TYPE = 'store';

    static $fields = [
        'status' => 'ステータス',
        'store_category' => 'カテゴリー',
        'store_kind' => '業種・業態',
        'store_name' => '店舗名',
        'address' => '住所',
        'zip1' => '郵便番号1',
        'zip2' => '郵便番号2',
        'phone_number' => '電話番号',
        'line_id' => '管理者LINE ID',
        'point_rate' => 'ポイント付与率',
        'category' => 'カテゴリー',
        'regular_holiday' => '定休日',
        'homepage' => 'ホームページ',
        'instagram' => 'インスタグラム',
        'official_line' => '公式LINE',
        'person_in_charge' => '担当者',
        'email' => 'メールアドレス',
        'point_rate' => '付与ポイント',
        'message' => 'メッセージ',
        'display_button' => '表示ボタン',
        'store_point' => '保有ポイント',
        'store_image' => '店舗画像',
    ];

    /**
     * LINEユーザーのカスタム投稿タイプ作成
     */
    static function set_store_post_type()
    {
        $label = self::LABEL;
        $post_type = self::POST_TYPE;
        register_post_type(
            $post_type, //投稿タイプ名（識別子：半角英数字の小文字）
            array(
                'label' => $label,  //カスタム投稿タイプの名前（管理画面のメニューに表示される）
                'labels' => array(  //管理画面に表示されるラベルの文字を指定
                    'add_new' => '新規' . $label . '追加',
                    'edit_item' => $label . 'の編集',
                    'view_item' => $label . 'を表示',
                    'search_items' => $label . 'を検索',
                    'not_found' => $label . 'は見つかりませんでした。',
                    'not_found_in_trash' => 'ゴミ箱に' . $label . 'はありませんでした。',
                ),
                'public' => true,  // 管理画面に表示しサイト上にも表示する
                'description' => 'カスタム投稿タイプ「' . $label . '」の説明文です。',  //説明文
                'hierarchicla' => false,  //コンテンツを階層構造にするかどうか
                'has_archive' => true,  //trueにすると投稿した記事の一覧ページを作成することができる
                'capability_type' => 'post', // 
                'show_in_rest' => false,  // true:「Gutenberg」/ false:「ClassicEditor」
                'supports' => array(  //記事編集画面に表示する項目を配列で指定することができる
                    'title',  //タイトル
                    'editor',  //本文の編集機能
                    'thumbnail',  //アイキャッチ画像（add_theme_support('post-thumbnails')が必要）
                    'excerpt',  //抜粋
                    'custom-fields', //カスタムフィールド
                    'revisions'  //リビジョンを保存
                ),
                'menu_position' => 5, //「投稿」の下に追加
            )
        );

        // リビジョンを有効にする
        //        add_post_type_support('line_user','revisions');
    }

    /**
     * カスタムフィールド追加
     * @return void 
     */
    static function create_store_custom_fields()
    {
        $label = self::LABEL;
        $post_type = self::POST_TYPE;
        $fields = self::$fields;

        foreach ($fields as $value => $name) {
            if ($value == 'zip1' || $value == 'zip2') {
                if ($value == 'zip2') {
                    continue;
                }

                $function_name = 'show_zipcode';
            } else {
                $function_name = 'show_' . $value;
            }
            add_meta_box(
                $value, //編集画面セクションID
                $name, //編集画面セクションのタイトル
                ['settingStore', $function_name], //編集画面セクションにHTML出力する関数
                $post_type, //投稿タイプ名
                'normal', //編集画面セクションが表示される部分
            );
        }
    }

    static function show_zipcode($post)
    {
        $post_id = $post->ID;
        $zip1 = get_post_meta($post_id, 'zip1', true);
        $zip2 = get_post_meta($post_id, 'zip2', true);
?>
        <label for="store_name">郵便番号</label>
        <input type="text" id="zip1" name="zip1" value="<?= esc_attr($zip1); ?>">-
        <input type="text" id="zip2" name="zip2" value="<?= esc_attr($zip2); ?>">

    <?php
    }

    // 各フィールド用の関数を個別に作成
    static function show_status($post)
    {
        $post_id = $post->ID;
        $value = get_post_meta($post_id, 'status', true);
    ?>
        <label for="status">承認</label>
        <input type="checkbox" id="status" name="status" value="1" <?php checked($value, 1); ?>>
    <?php
    }

    static function show_store_category($post)
    {
        $post_id = $post->ID;
        $value = get_post_meta($post_id, 'store_category', true);
    ?>
        <label for="store_category">カテゴリー</label>
        <input type="text" id="store_category" name="store_category" value="<?= esc_attr($value); ?>">
    <?php
    }

    static function show_store_kind($post)
    {
        $item_name = 'store_kind';
        $options = [
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
        $post_id = $post->ID;
        $value = get_post_meta($post_id, $item_name, true);


        //        echo $meta;
    ?>

        <select name="<?= $item_name; ?>" id="<?= $item_name; ?>">
            <?php
            foreach ($options as $option_value):
                $selected = '';
                if ($option_value == $value) {
                    $selected = ' selected';
                }
            ?>
                <option value="<?= $option_value; ?>" <?= $selected; ?>><?= $option_value; ?></option>
            <?php
            endforeach;
            ?>
        </select>
    <?php
    }

    // 各フィールド用の関数を個別に作成
    static function show_store_name($post)
    {
        $post_id = $post->ID;
        $value = get_post_meta($post_id, 'store_name', true);
    ?>
        <label for="store_name">店舗名</label>
        <input type="text" id="store_name" name="store_name" value="<?= esc_attr($value); ?>">
    <?php
    }

    static function show_address($post)
    {
        $post_id = $post->ID;
        $value = get_post_meta($post_id, 'address', true);
    ?>
        <label for="address">住所</label>
        <input type="text" id="address" name="address" value="<?= esc_attr($value); ?>">
    <?php
    }

    static function show_phone_number($post)
    {
        $post_id = $post->ID;
        $value = get_post_meta($post_id, 'phone_number', true);
    ?>
        <label for="phone_number">電話番号</label>
        <input type="text" id="phone_number" name="phone_number" value="<?= esc_attr($value); ?>">
    <?php
    }

    // これ以降、他のフィールドの関数も同様に作成
    static function show_line_id($post)
    {
        $post_id = $post->ID;
        $value = get_post_meta($post_id, 'line_id', true);
    ?>
        <label for="line_id">LINEID(UserID)</label>
        <input type="text" id="line_id" name="line_id" value="<?= esc_attr($value); ?>">
    <?php
    }

    static function show_point_rate($post)
    {
        $post_id = $post->ID;
        $value = get_post_meta($post_id, 'point_rate', true);
    ?>
        <label for="point_rate">ポイント付与率</label>
        <input type="text" id="point_rate" name="point_rate" value="<?= esc_attr($value); ?>">
    <?php
    }

    static function show_category($post)
    {
        $post_id = $post->ID;
        $value = get_post_meta($post_id, 'category', true);
    ?>
        <label for="category">カテゴリー</label>
        <input type="text" id="category" name="category" value="<?= esc_attr($value); ?>">
    <?php
    }



    static function show_business_hours($post)
    {
        $post_id = $post->ID;
        $value = get_post_meta($post_id, 'business_hours', true);
    ?>
        <label for="business_hours">営業時間</label>
        <input type="text" id="business_hours" name="business_hours" value="<?= esc_attr($value); ?>">
    <?php
    }

    static function show_regular_holiday($post)
    {
        $post_id = $post->ID;
        $value = get_post_meta($post_id, 'regular_holiday', true);
    ?>
        <label for="regular_holiday">定休日</label>
        <input type="text" id="regular_holiday" name="regular_holiday" value="<?= esc_attr($value); ?>">
    <?php
    }

    static function show_homepage($post)
    {
        $post_id = $post->ID;
        $value = get_post_meta($post_id, 'homepage', true);
    ?>
        <label for="homepage">ホームページ</label>
        <input type="text" id="homepage" name="homepage" value="<?= esc_attr($value); ?>">
    <?php
    }

    static function show_instagram($post)
    {
        $post_id = $post->ID;
        $value = get_post_meta($post_id, 'instagram', true);
    ?>
        <label for="instagram">インスタグラム</label>
        <input type="text" id="instagram" name="instagram" value="<?= esc_attr($value); ?>">
    <?php
    }

    static function show_official_line($post)
    {
        $post_id = $post->ID;
        $value = get_post_meta($post_id, 'official_line', true);
    ?>
        <label for="official_line">公式LINE</label>
        <input type="text" id="official_line" name="official_line" value="<?= esc_attr($value); ?>">
    <?php
    }

    static function show_person_in_charge($post)
    {
        $post_id = $post->ID;
        $value = get_post_meta($post_id, 'person_in_charge', true);
    ?>
        <label for="person_in_charge">担当者</label>
        <input type="text" id="person_in_charge" name="person_in_charge" value="<?= esc_attr($value); ?>">
    <?php
    }

    static function show_email($post)
    {
        $post_id = $post->ID;
        $value = get_post_meta($post_id, 'email', true);
    ?>
        <label for="email">メールアドレス</label>
        <input type="email" id="email" name="email" value="<?= esc_attr($value); ?>">
    <?php
    }

    static function show_message($post)
    {
        $post_id = $post->ID;
        $value = get_post_meta($post_id, 'message', true);
    ?>
        <label for="message">メッセージ</label>
        <textarea id="message" name="message"><?= esc_textarea($value); ?></textarea>
    <?php
    }

    static function show_store_point($post)
    {
        $post_id = $post->ID;
        $value = get_post_meta($post_id, 'store_point', true);
    ?>
        <label for="email">保有ポイント</label>
        <input type="text" id="store_point" name="store_point" value="<?= esc_attr($value); ?>">
    <?php
    }

    static function show_store_image($post)
    {
        $post_id = $post->ID;
        $value = get_post_meta($post_id, 'store_image', true);
    ?>
        <label for="email">店舗画像</label>
        <p><img src="<?= $value; ?>" alt=""></p>
    <?php
    }

    static function show_display_button($post)
    {
        $post_id = $post->ID;
        $value = get_post_meta($post_id, 'display_button', true);
    ?>
        <label for="display_button">表示ボタン</label>
        <input type="radio" id="display_button" name="display_button" value="homepage" <?= checked($value, 'homepage', false); ?>> ホームページ
        <input type="radio" id="display_button" name="display_button" value="instagram" <?= checked($value, 'instagram', false); ?>> インスタグラム
        <input type="radio" id="display_button" name="display_button" value="official_line" <?= checked($value, 'official_line', false); ?>> 公式ライン
<?php
    }

    /**
     * カスタムフィールド保存
     * @param mixed $post_ID 
     * @return void 
     */
    static function save_custom_fields($post_ID)
    {
        $fields = self::$fields;
        foreach ($fields as $item_name => $item_args) {

            if (isset($_POST[$item_name])) {
                update_post_meta($post_ID, $item_name, sanitize_text_field($_POST[$item_name]));
            }
        }
    }
}
