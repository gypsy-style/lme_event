<?php

/**
 * Created by PhpStorm.
 * User: sawadakeisuke
 * Date: 5/16/22
 * Time: 6:12 AM
 */
//require_once('../extensions/custom_fields.php');
require_once(plugin_dir_path(plugin_dir_path(__FILE__)) . 'extensions/custom_fields.php');
class settingLineUser
{

    const LABEL = 'LINE USER';
    const POST_TYPE = 'line_user';

    static $fields = [
        'line_id' => 'LINEID',
        'member_rank' => '会員ステータス',
        'richmenu_id' => 'リッチメニュー',
        'campany_name' => '会社名',
        'name' => '名前',
        'tel' => '電話番号',
        'email' => 'メールアドレス',
        'sex' => '性別',
        'member_type' => '理事カテゴリー',
        'message' => 'メッセージ',

    ];

    /**
     * LINEユーザーのカスタム投稿タイプ作成
     */
    static function set_line_user_post_type()
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
                'show_in_rest' => false,  // true:「Gutenberg」/ false:「ClassicEditor」
                'supports' => array(  //記事編集画面に表示する項目を配列で指定することができる
                    'title',  //タイトル
                    'editor',  //本文の編集機能
                    'thumbnail',  //アイキャッチ画像（add_theme_support('post-thumbnails')が必要）
                    'excerpt',  //抜粋
                    'custom-fields', //カスタムフィールド
                    'revisions'  //リビジョンを保存
                ),
                'taxonomies' => array($post_type . '_category'), // カテゴリーを追加
                'menu_position' => 5, //「投稿」の下に追加
            )
        );

        // リビジョンを有効にする
        //        add_post_type_support('line_user','revisions');
    }

    static function register_taxonomies()
    {
        // カテゴリーの登録
        $post_type = self::POST_TYPE;
        register_taxonomy(
            $post_type . '_category',
            $post_type,
            array(
                'labels' => array(
                    'name' => 'ユーザーカテゴリー',
                    'singular_name' => 'ユーザーカテゴリー',
                    'search_items' => 'カテゴリーを検索',
                    'all_items' => 'すべてのカテゴリー',
                    'parent_item' => '親カテゴリー',
                    'parent_item_colon' => '親カテゴリー:',
                    'edit_item' => 'カテゴリーを編集',
                    'update_item' => 'カテゴリーを更新',
                    'add_new_item' => '新しいカテゴリーを追加',
                    'new_item_name' => '新しいカテゴリー名',
                    'menu_name' => 'カテゴリー',
                ),
                'hierarchical' => true, // カテゴリーは階層構造を持つ
                'show_ui' => true,
                'show_admin_column' => true,
                'show_in_rest' => true,
            )
        );
    }

    /**
     * カスタムフィールド追加
     * @return void 
     */
    static function create_line_user_custom_fields()
    {
        $label = self::LABEL;
        $post_type = self::POST_TYPE;
        $fields = self::$fields;

        foreach ($fields as $value => $name) {
            add_meta_box(
                $value, //編集画面セクションID
                $name, //編集画面セクションのタイトル
                ['settingLineUser', 'show_' . $value], //編集画面セクションにHTML出力する関数
                $post_type, //投稿タイプ名
                'normal', //編集画面セクションが表示される部分
            );
        }
    }



    // これ以降、他のフィールドの関数も同様に作成
    static function show_line_id($post)
    {
        $item_name = 'line_id';
        $post_id = $post->ID;
        $value = get_post_meta($post_id, $item_name, true);
?>

        <input type="text" id="<?= $item_name; ?>" name="<?= $item_name; ?>" value="<?= esc_attr($value); ?>">
    <?php
    }

    static function show_member_rank($post)
    {
        $item_name = 'member_rank';
        $options = [
            'ゲスト',
            'トライアル会員',
            '会員企業社員',
            '他経営研究会',
            '日創研',
            '正会員',
            '理事',
            '管理者',
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

    static function show_campany_name($post)
    {
        $item_name = 'campany_name';
        $post_id = $post->ID;
        $value = get_post_meta($post_id, $item_name, true);
    ?>

        <input type="text" id="<?= $item_name; ?>" name="<?= $item_name; ?>" value="<?= esc_attr($value); ?>">
    <?php
    }
    static function show_name($post)
    {
        $item_name = 'name';
        $post_id = $post->ID;
        $value = get_post_meta($post_id, $item_name, true);
    ?>

        <input type="text" id="<?= $item_name; ?>" name="<?= $item_name; ?>" value="<?= esc_attr($value); ?>">
    <?php
    }

    static function show_tel($post)
    {
        $item_name = 'tel';
        $post_id = $post->ID;
        $value = get_post_meta($post_id, $item_name, true);
    ?>

        <input type="tel" id="<?= $item_name; ?>" name="<?= $item_name; ?>" value="<?= esc_attr($value); ?>">
    <?php
    }

    static function show_email($post)
    {
        $item_name = 'email';
        $post_id = $post->ID;
        $value = get_post_meta($post_id, $item_name, true);
    ?>

        <input type="text" id="<?= $item_name; ?>" name="<?= $item_name; ?>" value="<?= esc_attr($value); ?>">
    <?php
    }

    static function show_sex($post)
    {
        $item_name = 'sex';
        $options = [
            '男性',
            '女性',
            'その他',
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



    static function show_richmenu_id($post)
    {
        $item_name = 'richmenu_id';
        $post_id = $post->ID;
        $richmenus[] = get_option('richmenu_1');
        $richmenus[] = get_option('richmenu_2');
        $richmenus[] = get_option('richmenu_3');
        $richmenus[] = get_option('richmenu_4');
        $richmenus[] = get_option('richmenu_5');
        $richmenus[] = get_option('richmenu_6');
        $richmenus[] = get_option('richmenu_7');
        $richmenus[] = get_option('richmenu_8');
        $richmenus[] = get_option('richmenu_9');
        $richmenus[] = get_option('richmenu_10');

        $richmenus_outline[] = get_option('richmenu_outline_1');
        $richmenus_outline[] = get_option('richmenu_outline_2');
        $richmenus_outline[] = get_option('richmenu_outline_3');
        $richmenus_outline[] = get_option('richmenu_outline_4');
        $richmenus_outline[] = get_option('richmenu_outline_5');
        $richmenus_outline[] = get_option('richmenu_outline_6');
        $richmenus_outline[] = get_option('richmenu_outline_7');
        $richmenus_outline[] = get_option('richmenu_outline_8');
        $richmenus_outline[] = get_option('richmenu_outline_9');
        $richmenus_outline[] = get_option('richmenu_outline_10');
        $richmenu = get_post_meta($post_id, 'richmenu_id', true);
    ?>
        <select name="richmenu_id" id="richmenu_id">
            <?php

            foreach ($richmenus as $i => $richmenu_value) {
                $selected = '';
                if ($richmenu_value) {
                    if ($richmenu == $richmenu_value) {
                        $selected = ' selected';
                    }
            ?>
                    <option value="<?= $richmenu_value; ?>" data-outline="<?= $richmenus_outline[$i]; ?>" <?= $selected; ?>><?= $item_name; ?><?= ($i + 1); ?></option>
            <?php
                }
            }
            ?>
        </select>
        <span class="outline"></span>
    <?php
    }

    static function show_point($post)
    {
        $item_name = 'point';
        $post_id = $post->ID;
        $value = get_post_meta($post_id, $item_name, true);
    ?>
        <input type="text" id="<?= $item_name; ?>" name="<?= $item_name; ?>" value="<?= esc_attr($value); ?>">
    <?php
    }

    static function show_point_limit_date($post)
    {
        $item_name = 'point_limit_date';
        $post_id = $post->ID;
        $value = get_post_meta($post_id, $item_name, true);
    ?>
        <input type="text" id="<?= $item_name; ?>" name="<?= $item_name; ?>" value="<?= esc_attr($value); ?>">
    <?php
    }

    static function show_member_type($post)
    {
        $item_name = 'member_type';
        $options = [
            '1.会長',
            '2.事務局長',
            '3.事務局次長',
            '4.副会長',
            '5.委員長',
            '6.副委員長',
            '7.顧問',
            '8.監事',
            '9.本部役員',
        ];
        $post_id = $post->ID;
        $values = get_post_meta($post_id, $item_name, true);
        $values = is_array($values) ? $values : [];

    ?>
        <fieldset>
            <?php foreach ($options as $key => $label) : ?>
                <label>
                    <input type="checkbox" name="<?= $item_name; ?>[]" value="<?= $key; ?>" <?= in_array($key, $values) ? 'checked' : ''; ?>>
                    <?= $label; ?>
                </label><br>
            <?php endforeach; ?>
        </fieldset>
    <?php
    }

    static function show_message($post)
    {
        $item_name = 'message';
        $post_id = $post->ID;
        $value = get_post_meta($post_id, $item_name, true);
    ?>

        <input type="text" id="<?= $item_name; ?>" name="<?= $item_name; ?>" value="<?= esc_attr($value); ?>">
<?php
    }

    /**
     * カスタムフィールド保存
     * @param mixed $post_ID 
     * @return void 
     */


    static function save_custom_fields($post_ID)
    {
        // ゴミ箱に入れられた場合は処理をスキップ
        if (get_post_status($post_ID) === 'trash') {
            return;
        }
        $channel_access_token = get_option('channnel_access_token');
        $channel_secret = get_option('channnel_access_token_secret');
        //        $get_items = settings::$custom_fields;
        $get_items = self::$fields;
        $checkbox_fields = ['member_type']; // 削除対象のみに限定

        // クイック編集かどうかを判定
        $is_quick_edit = isset($_POST['_inline_edit']);

        foreach ($get_items as $item_name => $item_args) {
            if ($is_quick_edit && $item_name === 'member_type') {
                continue;
            }
            if (isset($_POST[$item_name])) {
                if (is_array($_POST[$item_name])) {
                    update_post_meta($post_ID, $item_name, $_POST[$item_name]);
                } else {
                    update_post_meta($post_ID, $item_name, sanitize_text_field($_POST[$item_name]));
                }
            } elseif (in_array($item_name, $checkbox_fields) && !$is_quick_edit) {
                delete_post_meta($post_ID, $item_name);
            }
        }

        if (isset($_POST['richmenu_id'])) {
            // LINE BOT SDK
            require_once(plugin_dir_path(__FILE__) . '../vendor/autoload.php');

            $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channel_access_token);
            $bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channel_secret]);
            $richmenu_id = $_POST['richmenu_id'];
            //            update_post_meta( $post_ID, 'richmenu_id', sanitize_text_field( $_POST['richmenu_id'] ) );
            // line apiからリッチメニューの更新
            if (isset($_POST['line_id'])) {
                if ($richmenu_id == "") {
                    //複数のユーザーのリッチメニューのリンクを解除する
                    $response = $bot->unlinkRichMenu($_POST['line_id']);
                } else {
                    //リッチメニューと複数のユーザーをリンクする
                    $response = $bot->linkRichMenu($_POST['line_id'], $richmenu_id);
                }
            }

            update_post_meta($post_ID, 'richmenu_id', sanitize_text_field($richmenu_id));
        }
        return $post_ID;
    }
}
