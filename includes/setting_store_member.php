<?php

/**
 * Created by PhpStorm.
 * User: sawadakeisuke
 * Date: 5/16/22
 * Time: 6:12 AM
 */
//require_once('../extensions/custom_fields.php');
require_once(plugin_dir_path(plugin_dir_path(__FILE__)) . 'extensions/custom_fields.php');
class settingStoreMember
{

    const LABEL = '店舗スタッフ';
    const POST_TYPE = 'storeUser';

    static $fields = [
        'line_id' => 'LINEID',
        'user_id' => 'User ID',
        'store_id' => '店舗 ID',
        'permission' => '権限',
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
    static function create_store_user_custom_fields()
    {
        $label = self::LABEL;
        $post_type = self::POST_TYPE;
        $fields = self::$fields;

        foreach ($fields as $value => $name) {
            add_meta_box(
                $value, //編集画面セクションID
                $name, //編集画面セクションのタイトル
                ['settingStoreMember', 'show_' . $value], //編集画面セクションにHTML出力する関数
                $post_type, //投稿タイプ名
                'normal', //編集画面セクションが表示される部分
            );
        }
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

   static function show_user_id($post)
   {
       $post_id = $post->ID;
       $value = get_post_meta($post_id, 'user_id', true);
   ?>
       <label for="point_rate">User ID</label>
       <input type="text" id="user_id" name="user_id" value="<?= esc_attr($value); ?>">
   <?php
   }

   static function show_store_id($post)
    {
        $post_id = $post->ID;
        $value = get_post_meta($post_id, 'store_id', true);
    ?>
        <label for="category">店舗ID</label>
        <input type="text" id="store_id" name="store_id" value="<?= esc_attr($value); ?>">
    <?php
    }

    static function show_permission($post)
    {
        $post_id = $post->ID;
        $value = get_post_meta($post_id, 'permission', true);
    ?>

        <input type="radio" name="permission" value="1" <?= checked($value, '1', false); ?>> オーナー
        <input type="radio" name="permission" value="2" <?= checked($value, '0', false); ?>> スタッフ
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
