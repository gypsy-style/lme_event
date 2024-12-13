<?php
/**
 * Created by PhpStorm.
 * User: sawadakeisuke
 * Date: 5/16/22
 * Time: 6:12 AM
 */
//require_once('../extensions/custom_fields.php');
require_once (plugin_dir_path( plugin_dir_path( __FILE__)).'extensions/custom_fields.php');
class settingPointHistory {

    const LABEL = 'ポイント履歴';
    const POST_TYPE = 'point_history';

    static $fields = [
        'point_number'=>'ポイント数',
        'point_rate'=>'ポイント付与率',
        'price'=>'ご利用金額',
        'user_id'=>'ユーザーID',
        'point_type'=>'付与 OR 使用',
        'store_id'=>'店舗'
    ];

    /**
     * LINEユーザーのカスタム投稿タイプ作成
     */
    static function set_point_history_post_type()
    {
        $label = self::LABEL;
        $post_type = self::POST_TYPE;
        register_post_type(
            $post_type,//投稿タイプ名（識別子：半角英数字の小文字）
            array(
                'label' => $label,  //カスタム投稿タイプの名前（管理画面のメニューに表示される）
                'labels' => array(  //管理画面に表示されるラベルの文字を指定
                    'add_new' => '新規'.$label.'追加',
                    'edit_item' => $label.'の編集',
                    'view_item' => $label.'を表示',
                    'search_items' => $label.'を検索',
                    'not_found' => $label.'は見つかりませんでした。',
                    'not_found_in_trash' => 'ゴミ箱に'.$label.'はありませんでした。',
                ),
                'public' => true,  // 管理画面に表示しサイト上にも表示する
                'description' => 'カスタム投稿タイプ「'.$label.'」の説明文です。',  //説明文
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
    static function create_point_history_custom_fields()
    {
        $fields = self::$fields;

        foreach($fields as $value=>$name)
        {
            add_meta_box(
                $value, //編集画面セクションID
                $name, //編集画面セクションのタイトル
                ['settingPointHistory','show_'.$value], //編集画面セクションにHTML出力する関数
                'point_history', //投稿タイプ名
                'normal', //編集画面セクションが表示される部分
            );
        }
    }

    /**
     * エラー日時のカスタムフィールド
     * @param mixed $post 
     * @return void 
     */
    static function show_point_number($post)
    {
        $post_id = $post->ID;
        $value = get_post_meta($post_id,'point_number',true);
        ?>
        <label for="point_number">ポイント数</label>
        <input type="text" id="point_number" name="point_number" value="<?=$value;?>">
        <?php
    }

    /**
     * エラー日時のカスタムフィールド
     * @param mixed $post 
     * @return void 
     */
    static function show_point_rate($post)
    {
        $post_id = $post->ID;
        $value = get_post_meta($post_id,'point_rate',true);
        ?>
        <label for="point_rate">ポイント付与率</label>
        <input type="text" id="point_rate" name="point_rate" value="<?=$value;?>">
        <?php
    }

    /**
     * エラー日時のカスタムフィールド
     * @param mixed $post 
     * @return void 
     */
    static function show_price($post)
    {
        $post_id = $post->ID;
        $value = get_post_meta($post_id,'price',true);
        ?>
        <label for="price">ご利用金額</label>
        <input type="text" id="price" name="price" value="<?=$value;?>">
        <?php
    }
    
    /**
     * エラー日時のカスタムフィールド
     * @param mixed $post 
     * @return void 
     */
    static function show_user_id($post)
    {
        $post_id = $post->ID;
        $value = get_post_meta($post_id,'user_id',true);
        ?>
        <label for="user_id">ユーザー ID</label>
        <input type="text" id="user_id" name="user_id" value="<?=$value;?>">
        <?php
    }

    /**
     * エラーメッセージのカスタムフィールド
     * @param mixed $post 
     * @return void 
     */
    static function show_point_type($post)
    {
        $post_id = $post->ID;
        $value = get_post_meta($post_id,'point_type',true);
        ?>
        <label for="point_type_give">ポイント付与種別</label>
        <input type="radio" id="point_type_give" name="point_type" value="付与" <?php checked( $value, '付与' ); ?>> 付与　
        <input type="radio" id="point_type_give" name="point_type" value="使用" <?php checked( $value, '使用' ); ?>> 使用
        <?php
    }

    /**
     * 店舗ID
     * @param mixed $post 
     * @return void 
     */
    static function show_store_id($post)
    {
        $post_id = $post->ID;
        $value = get_post_meta($post_id,'store_id',true);
        ?>
        <label for="store_id">店舗 ID</label>
        <input type="text" id="store_id" name="store_id" value="<?=$value;?>">
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
        foreach($fields as $item_name =>$item_args) {
            
            if(isset($_POST[$item_name]) ){
                update_post_meta( $post_ID, $item_name, sanitize_text_field( $_POST[$item_name] ) );
            }
        }
    }

}