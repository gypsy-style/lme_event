<?php
/**
 * Created by PhpStorm.
 * User: sawadakeisuke
 * Date: 5/16/22
 * Time: 6:12 AM
 */
//require_once('../extensions/custom_fields.php');
require_once (plugin_dir_path( plugin_dir_path( __FILE__)).'extensions/custom_fields.php');
class settings {

    const LABEL = 'LINEユーザー';

    /**
     * LINEユーザーのカスタム投稿タイプ作成
     */
    static function set_line_user_post_type()
    {
        $label = self::LABEL;
        register_post_type(
            'line_user',//投稿タイプ名（識別子：半角英数字の小文字）
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
                'taxonomies' => array('rental_cat', 'rental_tag')  //使用するタクソノミー
            )
        );

        // リビジョンを有効にする
//        add_post_type_support('line_user','revisions');
    }


    /**
     * @param $post
     * @param $args
     * カスタムフィールド追加
     */
    static function show_line_user_elements($post,$args)
    {
//        global $post;
        $item_name = $args['args']['item_name'];
        $item_type = $args['args']['item_type'];
        $post_id = $post->ID;

        switch($item_type) {
            // 共通項目
            case 'select':
                $options = $args['args']['options'];
                settings::create_element_select($post_id,$item_name,$options);
                break;
            case 'text':
                settings::create_element_text($post_id,$item_name);
                break;
            case 'datepicker':
                settings::create_element_datepicker($post_id,$item_name);
                break;
            case 'textarea':
                settings::create_element_textarea($post_id,$item_name);
                break;
            case 'radio':
                $options = $args['args']['options'];
                settings::create_element_radio($post_id,$item_name,$options);
                break;

            case 'checkbox':
                $options = $args['args']['options'];
                settings::create_element_checkbox($post_id,$item_name,$options);
                break;

                // カスタム項目
            case 'birthday_y':
                settings::create_element_birthday_y($post_id,$item_name);
                break;
            case 'birthday_m':
                settings::create_element_birthday_m($post_id,$item_name);
                break;
            case 'birthday_d':
                settings::create_element_birthday_d($post_id,$item_name);
                break;
            case 'richmenu_id':
                settings::create_element_richmenu_id($post_id,$item_name);
                break;

        }
//        echo $meta;
    }

    /**
     * @param $post_id
     * @param $item_name
     * @param $options
     * カスタムフィールド（セレクトボックス）
     */
    static function create_element_checkbox($post_id,$item_name,$options)
    {
        $meta = get_post_meta($post_id, $item_name, true);

        ?>
            <?php
            foreach($options as $value):
                $checked = '';
                if(is_array($meta) && in_array($value,$meta)){
                    $checked = ' checked';
                }
                ?>
                <label for="">
                <input type="checkbox" name="<?=$item_name;?>[]" value="<?=$value;?>"<?=$checked;?>> <?=$value;?>
                </label>

            <?php
            endforeach;
            ?>

        <?php
    }

     /**
     * @param $post_id
     * @param $item_name
     * @param $options
     * カスタムフィールド（セレクトボックス）
     */
    static function create_element_radio($post_id,$item_name,$options)
    {
        $meta = get_post_meta($post_id, $item_name, true);
//        echo $meta;
        ?>
            <?php
            foreach($options as $value):
                $checked = '';
                if($value == $meta){
                    $checked = ' checked';
                }
                ?>
                <label for="">
                <input type="radio" name="<?=$item_name;?>" value="<?=$value;?>"<?=$checked;?>> <?=$value;?>
                </label>

            <?php
            endforeach;
            ?>

        <?php
    }

    /**
     * @param $post_id
     * @param $item_name
     * @param $options
     * カスタムフィールド（セレクトボックス）
     */
    static function create_element_select($post_id,$item_name,$options)
    {
        $meta = get_post_meta($post_id, $item_name, true);
//        echo $meta;
        ?>
        <select name="<?=$item_name;?>" id="<?=$item_name;?>">
            <?php
            foreach($options as $value):
                $selected = '';
                if($value == $meta){
                    $selected = ' selected';
                }
                ?>
                <option value="<?=$value;?>"<?=$selected;?>><?=$value;?></option>
            <?php
            endforeach;
            ?>
        </select>
        <?php
    }

    /**
     * @param $post_id
     * @param $item_name
     * カスタムフィールド（テキスト）
     */
    static function create_element_text($post_id,$item_name)
    {
        $item_value = get_post_meta($post_id, $item_name, true); ?>
        <input id="<?=$item_name;?>" type="text" name="<?=$item_name;?>" value="<?=$item_value ?>">
        <?php
    }

    /**
     * @param $post_id
     * @param $item_name
     * カスタムフィールド（テキスト）
     */
    static function create_element_datepicker($post_id,$item_name)
    {
        $item_value = get_post_meta($post_id, $item_name, true); ?>
        <input id="<?=$item_name;?>-datepicker" class="datepicker-line-user" type="text" name="<?=$item_name;?>" value="<?=$item_value ?>">
        <?php
    }

    /**
     * @param $post_id
     * @param $item_name
     * カスタムフィールド（テキストエリア）
     */
    static function create_element_textarea($post_id,$item_name)
    {
        $item_value = get_post_meta($post_id, $item_name, true); ?>
        <textarea name="<?=$item_name ?>" id="<?=$item_name ?>" cols="30" rows="10"><?=$item_value ?></textarea>
        <?php
    }

    /**
     * @param $post_id
     * @param $item_name
     * カスタムフィールド（誕生日年）
     */
    static function create_element_birthday_y($post_id,$item_name)
    {
        $meta = get_post_meta($post_id, $item_name, true);
        for($i = (date('Y') - 90); $i <= (date('Y') - 5);$i++){
            $items[]=$i;
        }
        ?>
        <select name="<?=$item_name;?>" id="<?=$item_name;?>">
            <?php
            foreach($items as $value):
                $selected = '';
                if($value == $meta){
                    $selected = ' selected';
                }
                ?>
                <option value="<?=$value;?>"<?=$selected;?>><?=$value;?></option>
            <?php
            endforeach;
            ?>
        </select>
        <?php
    }

    /**
     * @param $post_id
     * @param $item_name
     * カスタムフィールド（誕生日月）
     */
    static function create_element_birthday_m($post_id,$item_name)
    {
        $meta = get_post_meta($post_id, $item_name, true);
//        echo $meta;
        for($i = 1; $i <= 12;$i++){
            $items[]=$i;
        }
        ?>
        <select name="<?=$item_name;?>" id="<?=$item_name;?>">
            <?php
            foreach($items as $value):
                $selected = '';
                if($value == $meta){
                    $selected = ' selected';
                }
                ?>
                <option value="<?=$value;?>"<?=$selected;?>><?=$value;?></option>
            <?php
            endforeach;
            ?>
        </select>
        <?php
    }

    /**
     * @param $post_id
     * @param $item_name
     * カスタムフィールド（誕生日　日）
     */
    static function create_element_birthday_d($post_id,$item_name)
    {
        $meta = get_post_meta($post_id, $item_name, true);
//        echo $meta;
        for($i = 1; $i <= 31;$i++){
            $items[]=$i;
        }
        ?>
        <select name="<?=$item_name;?>" id="<?=$item_name;?>">
            <?php
            foreach($items as $value):
                $selected = '';
                if($value == $meta){
                    $selected = ' selected';
                }
                ?>
                <option value="<?=$value;?>"<?=$selected;?>><?=$value;?></option>
            <?php
            endforeach;
            ?>
        </select>
        <?php
    }

    /**
     * @param $post_id
     * @param $item_name
     * カスタムフィールド（リッチメニュー選択）
     */
    static function create_element_richmenu_id($post_id,$item_name)
    {
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
        $richmenu = get_post_meta($post_id,'richmenu_id',true);
        ?>
        <select name="richmenu_id" id="richmenu_id">
            <?php

            foreach($richmenus as $i => $richmenu_value){
                $selected = '';
                if($richmenu_value){
                    if($richmenu == $richmenu_value) {
                        $selected = ' selected';
                    }
                    ?>
                    <option value="<?=$richmenu_value;?>" data-outline="<?=$richmenus_outline[$i];?>"<?=$selected;?>><?=$item_name;?><?=($i+1);?></option>
                    <?php
                }
            }
            ?>
        </select>
        <span class="outline"></span>
        <?php
    }

    /**
     * @param $columns
     * 一覧に項目を追加
     */
    static function line_user_columns($columns)
    {
//        if(is_array($columns)){
        $columns['richmenu_id'] = 'リッチメニュー';
        return $columns;
    }

    static function line_user_add_column($column_name, $post_id)
    {
        if($column_name == 'richmenu_id') {
            $text = '';
            $richmenus = [];
            for($i=1;$i<=10;$i++) {
                $richmenus[] = get_option('richmenu_'.$i);
            }
            $richmenus_outline=[];
            for($i=1;$i<=10;$i++) {
                $richmenus_outline[] = get_option('richmenu_outline_'.$i);
            }
            $richmenu = get_post_meta($post_id,'richmenu_id',true);

            foreach($richmenus as $k => $richmenu_value) {
                if($richmenu == $richmenu_value)
                {
                    $text = 'RICH MENU ID '.($k+1).'<span class="line_user_richmenu_id" style="display:none;">'.$richmenu.'</span><span class="line_user_richmenu_outline" style="display:none;">'.$richmenus_outline[$k].'</span>';
                }
            }
//            echo esc_attr($text);
            echo $text;
        }
    }

    static function line_user_edit_quick_edit($column_name, $post_type)
    {
        if($column_name == 'richmenu_id') {
            $post_id = get_the_ID();
            $richmenus = [];
            $richmenus_outline = [];
            for($i=1;$i<=10;$i++) {
                $richmenus[] = get_option('richmenu_'.$i);
                $richmenus_outline[] = get_option('richmenu_outline_'.$i);
            }
            $richmenu = get_post_meta($post_id,'richmenu_id',true);
//            $richmenu = get_post_meta($post_id,'richmenu_id',true);


            ?>
            <fieldset class="'inline-edit-col-right">
                <div class="inline-edit-col">
                    <div class="inline-edit-group wp-clearfix">
                        <label for="" class="inline-edit-status">
                            <span class="title">リッチメニューID</span>
                            <select name="richmenu_id" class="quick_edit_richmenu_id">
                                <?php
                                foreach($richmenus as $k => $richmenu_value) {
                                    $selected = '';
                                    if($richmenu_value):
                                        if($richmenu == $richmenu_value) {
                                            $selected = ' selected';
                                        }
                                    ?>
                                    <option value="<?=$richmenu_value;?>" data-outline="<?=$richmenus_outline[$k];?>"<?=$selected;?>>RICH MENU ID<?=($k+1);?></option>
                                        <?php
                                    endif;
                                }

                                ?>
                            </select>
                            <span class="outline"></span>
                        </label>
                    </div>
                </div>
            </fieldset>

        <?php

        }
    }

    /**
     * @param $actions
     * @return mixed
     * 一括処理のメニュー追加
     */
    static function line_user_bulk_actions($actions)
    {
        $actions['richmenu_id_1'] = 'RICH MENU ID 1にする';
        $actions['richmenu_id_2'] = 'RICH MENU ID 2にする';
        $actions['richmenu_id_3'] = 'RICH MENU ID 3にする';
        $actions['richmenu_id_4'] = 'RICH MENU ID 4にする';
        $actions['richmenu_id_5'] = 'RICH MENU ID 5にする';
        $actions['richmenu_id_6'] = 'RICH MENU ID 6にする';
        $actions['richmenu_id_7'] = 'RICH MENU ID 7にする';
        $actions['richmenu_id_8'] = 'RICH MENU ID 8にする';
        $actions['richmenu_id_9'] = 'RICH MENU ID 9にする';
        $actions['richmenu_id_10'] = 'RICH MENU ID 10にする';
        return $actions;
    }

    /**
     * @param $redirect
     * @param $doaction
     * @param $object_ids
     * 一括処理のアクション設定
     */
    static function line_user_handle_bulk_actions( $redirect, $doaction, $object_ids )
    {
        if($richmenu_number = strstr($doaction,'richmenu_id_'))
        {
            $channel_access_token = get_option('channnel_access_token');
            $channel_secret = get_option('channnel_access_token_secret');
            // LINE BOT SDK
            require_once(plugin_dir_path(__FILE__).'../vendor/autoload.php');

            $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channel_access_token);
            $bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channel_secret]);
            foreach($object_ids as $post_id)
            {
                $richmenu_id = get_option('richmenu_'.$richmenu_number);
                $line_id = get_post_meta($post_id,'line_id',true);
                $response = $bot->linkRichMenu($line_id, $richmenu_id);
                update_post_meta( $post_id, 'richmenu_id', sanitize_text_field( $richmenu_id ) );
            }
        }
    }


    /**
     * @param $post_id
     * 一括編集画面の処理
     */
    static function line_user_quick_edit_save($post_id)
    {
//        echo 'fuck';exit;
        if(isset($_REQUEST['richmenu_id'])) {
            $channel_access_token = get_option('channnel_access_token');
            $channel_secret = get_option('channnel_access_token_secret');

            // LINE BOT SDK
            require_once(plugin_dir_path(__FILE__).'../vendor/autoload.php');

            $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channel_access_token);
            $bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channel_secret]);
            $richmenu_id = $_REQUEST['richmenu_id'];
            $line_id = get_post_meta($post_id,'line_id',true);
//            update_post_meta( $post_ID, 'richmenu_id', sanitize_text_field( $_POST['richmenu_id'] ) );
            // line apiからリッチメニューの更新
            if($richmenu_id == ""){
                //複数のユーザーのリッチメニューのリンクを解除する
                $response = $bot->unlinkRichMenu($line_id);
            }else{
                //リッチメニューと複数のユーザーをリンクする
                $response = $bot->linkRichMenu($line_id, $richmenu_id);
            }
            update_post_meta( $post_id, 'richmenu_id', sanitize_text_field( $richmenu_id ) );
        }
    }

    static function replyToAddInlineData( $post, $post_type ) {
//        if ( ! $post_type->hierarchical ) {
//            echo '<div class="richmenu_id">' . $post->richmenu_id . '</div>';
//        }
    }

    static function replyToEnqueueResources() {
//         wp_deregister_script('jquery');
        wp_deregister_script('jqueryUI-js');
//         wp_enqueue_script(
//             'jquery',
//             'https://code.jquery.com/jquery-3.6.0.min.js',
//             array(),
//             '3.6.0',
//             false
//         );
        // wp_enqueue_script(
        //     'quick-edit-line_user',
        //     plugins_url('js/quick_edit_line_user.js',dirname(__FILE__)),
        //     array( 'jquery' ),  // dependencies
        //     false,          // version
        //     true        // in footer
        // );

        // wp_enqueue_script('jqueryUI','https://code.jquery.com/ui/1.13.2/jquery-ui.js');
        // wp_enqueue_script('jquery-ui-core');
wp_enqueue_script('jquery-ui-datepicker');
wp_enqueue_media();
        wp_enqueue_script(
            'common-line_user',
            plugins_url('js/common.js',dirname(__FILE__))
        );
        $custom_fields = custom_fields::$custom_fields;
        $fields_datepicker = [];
        if(is_array($custom_fields))
        {
            foreach($custom_fields as $field_name => $filed_data)
            {
                if($filed_data['type'] == 'datepicker')
                {
                    $date_format = 'yy/mm/dd';
                    if(isset($filed_data['date_format'])) {
                        $date_format = $filed_data['date_format'];
                    }
                    $fields_datepicker[] = [
                        'field_name'=>$field_name,
                        'date_format' => $date_format
                    ];
                }
            }
        }
        wp_localize_script('common-line_user','lineUserValues',['datepicker_fields'=>$fields_datepicker]);
  wp_enqueue_style('jquery-ui-css', '//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css');
//   wp_enqueue_script('jquery-ui-js-ja', '//ajax.googleapis.com/ajax/libs/jqueryui/1/i18n/jquery.ui.datepicker-ja.min.js');

    }

    static function replyToEnqueueResourcesFooter() {
        ?>
 
  <?php
    }



    /**
     * javascript追加
     */
    static function add_javascript_css()
    {
        global $post_type;
        if($post_type == 'line_user'):
        ?>
        <style type="text/css">
            #postdivrich,#postexcerpt,#postcustom {
                display: none;
            }
        </style>
        <script>
            jQuery(function($){
                let outline = $('#richmenu_id option:selected').data('outline')
                $('.outline').text(outline);
                $('#richmenu_id').on('change',function(){
                    outline = $('#richmenu_id option:selected').data('outline');
                    $('.outline').text(outline);
                });

                // let quicj_edit_outline = $('.outline').text(outline);
                $('.quick_edit_richmenu_id').on('change',function(){
                    outline = $(this).find('option:selected').data('outline');
                    $('.outline').text(outline);
                })

            })

            function copyToClipboard(id) {
                // コピー対象をJavaScript上で変数として定義する
                var copyTarget = document.getElementById(id);

                // コピー対象のテキストを選択する
                copyTarget.select();

                // 選択しているテキストをクリップボードにコピーする
                document.execCommand("Copy");

            }
        </script>

        <?php
        endif;
    }

    /**
     * cssで不要な項目を非表示
     */
    static function hide_editor_by_css() {
        ?>
        <style type="text/css">
            #postdivrich {
                display: none;
            }
        </style>
        <?php
    }

    /**
     * サブメニュー追加
     */
    static function create_sub_menu()
    {
//        add_submenu_page('line_user','LINE設定','LINE設定','manage_options','line_members_options');
        add_submenu_page('edit.php?post_type=line_user', 'LINE MEMBERS設定', 'LINE MEMBERS設定', 'manage_options','line_members_settings', ['settings','sub_menu_page']);
    }

    /**
     * オプション画面設定
     */
    static function register_custom_setting()
    {
        register_setting('line_user', 'channnel_access_token');
        register_setting('line_user', 'channnel_access_token_secret');
        register_setting('line_user', 'liff_id_register');
        register_setting('line_user', 'liff_id_event_entry_list');
        register_setting('line_user', 'liff_id_event_list');
        register_setting('line_user', 'liff_id_event_entry');
        register_setting('line_user', 'liff_id_event_schedule');




        $max_count = 10;
        for($i = 1;$i<=$max_count;$i++) {
            register_setting('line_user', 'richmenu_'.$i);
            register_setting('line_user', 'richmenu_outline_'.$i);
        }
        register_setting('line_user', 'form_thanks_text');
        register_setting('line_user', 'enabled_coupon');
        register_setting('line_user', 'coupon_expired_date');
        register_setting('line_user', 'after_registration_action');
        register_setting('line_user', 'registration_redirect_url');
        register_setting('line_user', 'not_exist_redirect');
        register_setting('line_user', 'show_banner');
        register_setting('line_user', 'not_exist_alert_message');
        register_setting('line_user', 'after_qr_update_redirect');
        register_setting('line_user', 'default_point_register');
    }

    static function sub_menu_page() {
        ?>
        <div class="wrap">
            <h2>LINE設定</h2>
            <form method="post" action="options.php" enctype="multipart/form-data" encoding="multipart/form-data">
                <?php
                settings_fields('line_user');
                do_settings_sections('line_user'); ?>
                <div class="metabox-holder">
                    <div class="postbox ">
                        <h3 class='hndle'><span>Messaging API アクセストークン</span></h3>
                        <div class="inside">
                            <div class="main">
                                <h4>チャネルアクセストークン</h4>
                                <p><input class="regular-text" type="text" id="channnel_access_token" name="channnel_access_token" value="<?php echo get_option('channnel_access_token'); ?>"></p>
                                <h4>チャネルシークレット</h4>
                                <p><input class="regular-text" type="text" id="channnel_access_token_secret" name="channnel_access_token_secret" value="<?php echo get_option('channnel_access_token_secret'); ?>"></p>
                            </div>
                        </div>
                    </div>
                    <div class="postbox ">
                        <h3 class='hndle'><span>LIFF ID</span></h3>
                        <div class="inside">
                            <h4 class=''><span>メンバー</span></h4>
                            <div class="main">
                                <h4>会員登録用 <input id="copy_webhook" type="text" class="regular-text" value="<?=plugins_url().'/line-members/registration.php';?>" readonly> <span onclick="copyToClipboard('copy_webhook')">コピー</span></h4>
                                <p><input type="text" id="liff_id" name="liff_id_register" value="<?php echo get_option('liff_id_register'); ?>"></p>
                                <h4>申し込みリスト用 <input id="copy_richmenu_profile" type="text" class="regular-text" value="<?=plugins_url().'/line-members/event_entry_list.php';?>" readonly> <span onclick="copyToClipboard('copy_richmenu_profile')">コピー</span></h4>
                                <p><input type="text" id="liff_id" name="liff_id_event_entry_list" value="<?php echo get_option('liff_id_event_entry_list'); ?>"></p>
                                <h4>申し込みページ用 <input id="copy_richmenu_profile" type="text" class="regular-text" value="<?=plugins_url().'/line-members/event_entry.php';?>" readonly> <span onclick="copyToClipboard('copy_richmenu_profile')">コピー</span></h4>
                                <p><input type="text" id="liff_id" name="liff_id_event_entry" value="<?php echo get_option('liff_id_event_entry'); ?>"></p>
                                <h4>イベントリスト用 <input id="copy_richmenu_profile" type="text" class="regular-text" value="<?=plugins_url().'/line-members/event_list.php';?>" readonly> <span onclick="copyToClipboard('copy_richmenu_profile')">コピー</span></h4>
                                <p><input type="text" id="liff_id" name="liff_id_event_list" value="<?php echo get_option('liff_id_event_list'); ?>"></p>
                                <h4>イベントスケジュール用 <input id="copy_richmenu_profile" type="text" class="regular-text" value="<?=plugins_url().'/line-members/event_schedule.php';?>" readonly> <span onclick="copyToClipboard('copy_richmenu_profile')">コピー</span></h4>
                                <p><input type="text" id="liff_id" name="liff_id_event_schedule" value="<?php echo get_option('liff_id_event_schedule'); ?>"></p>
                            </div>
                        </div>
                    </div>
                    <div class="postbox ">
                        <h3 class='hndle'><span>クーポン</span></h3>
                        <div class="inside">
                            <div class="main">
                                <h4>会員登録後のクーポンの表示 </h4>
                                <p><label for="enabled_coupon">表示する<input type="checkbox" value="1" id="enabled_coupon" name="enabled_coupon"<?php checked( 1, get_option('enabled_coupon')); ?>></label></p>
                                <h4>クーポンの有効期限 </h4>
                                <p><input type="text" id="coupon_expired_date" name="coupon_expired_date" value="<?php echo get_option('coupon_expired_date'); ?>"></p>
                            </div>
                        </div>
                    </div>
                    <div class="postbox ">
                        <h3 class='hndle'><span>リッチメニュー</span></h3>
                        <div class="inside">
                            <div class="main">
                                <h4>RICHMENU ID 1（お友達追加時）</h4>
                                <p>
                                    <input type="text" id="richmenu_1" name="richmenu_1" value="<?php echo get_option('richmenu_1'); ?>">
                                    <input type="text" id="richmenu_outline_1" name="richmenu_outline_1" value="<?php echo get_option('richmenu_outline_1');?>">
                                </p>
                                <h4>RICHMENU ID 2（フォーム登録完了時）</h4>
                                <p>
                                    <input type="text" id="richmenu_2" name="richmenu_2" value="<?php echo get_option('richmenu_2'); ?>">
                                    <input type="text" id="richmenu_outline_2" name="richmenu_outline_2" value="<?php echo get_option('richmenu_outline_2');?>">
                                </p>
                                <h4>RICHMENU ID 3</h4>
                                <p>
                                    <input type="text" id="richmenu_3" name="richmenu_3" value="<?php echo get_option('richmenu_3'); ?>">
                                    <input type="text" id="richmenu_outline_3" name="richmenu_outline_3" value="<?php echo get_option('richmenu_outline_3');?>">
                                </p>
                                <h4>RICHMENU ID 4</h4>
                                <p>
                                    <input type="text" id="richmenu_4" name="richmenu_4" value="<?php echo get_option('richmenu_4'); ?>">
                                    <input type="text" id="richmenu_outline_4" name="richmenu_outline_4" value="<?php echo get_option('richmenu_outline_4');?>">
                                </p>
                                <h4>RICHMENU ID 5</h4>
                                <p>
                                    <input type="text" id="richmenu_5" name="richmenu_5" value="<?php echo get_option('richmenu_5'); ?>">
                                    <input type="text" id="richmenu_outline_5" name="richmenu_outline_5" value="<?php echo get_option('richmenu_outline_5');?>">
                                </p>
                                <h4>RICHMENU ID 6</h4>
                                <p>
                                    <input type="text" id="richmenu_6" name="richmenu_6" value="<?php echo get_option('richmenu_6'); ?>">
                                    <input type="text" id="richmenu_outline_6" name="richmenu_outline_6" value="<?php echo get_option('richmenu_outline_6');?>">
                                </p>
                                <h4>RICHMENU ID 7</h4>
                                <p>
                                    <input type="text" id="richmenu_7" name="richmenu_7" value="<?php echo get_option('richmenu_7'); ?>">
                                    <input type="text" id="richmenu_outline_7" name="richmenu_outline_7" value="<?php echo get_option('richmenu_outline_7');?>">
                                </p>
                                <h4>RICHMENU ID 8</h4>
                                <p>
                                    <input type="text" id="richmenu_8" name="richmenu_8" value="<?php echo get_option('richmenu_8'); ?>">
                                    <input type="text" id="richmenu_outline_8" name="richmenu_outline_8" value="<?php echo get_option('richmenu_outline_8');?>">
                                </p>
                                <h4>RICHMENU ID 9</h4>
                                <p>
                                    <input type="text" id="richmenu_9" name="richmenu_9" value="<?php echo get_option('richmenu_9'); ?>">
                                    <input type="text" id="richmenu_outline_9" name="richmenu_outline_9" value="<?php echo get_option('richmenu_outline_9');?>">
                                </p>
                                <h4>RICHMENU ID 10</h4>
                                <p>
                                    <input type="text" id="richmenu_10" name="richmenu_10" value="<?php echo get_option('richmenu_10'); ?>">
                                    <input type="text" id="richmenu_outline_10" name="richmenu_outline_10" value="<?php echo get_option('richmenu_outline_10');?>">
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="postbox">
                        <h3 class='hndle'><span>本登録完了文言</span></h3>
                        <div class="inside">
                            <div class="main">
                                <textarea name="form_thanks_text" id="form_thanks_text"><?php echo get_option('form_thanks_text');?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="postbox">
                        <h3 class='hndle'><span>登録後の挙動</span></h3>
                        <div class="inside">
                            <div class="main">
                            <label>
                                <input type="radio" id="after_registration_action" name="after_registration_action" value="1" <?php checked(get_option('after_registration_action') , '1'); ?>>
                                webhook.phpで登録後に公式アカウントのページに自動で移動する<br>
                                <p><input class="regular-text" type="text" id="registration_redirect_url" name="registration_redirect_url" value="<?php echo get_option('registration_redirect_url'); ?>" placeholder="公式アカウントURL"></p>
                            </label>
                            <label>
                                <input type="radio" id="after_registration_action" name="after_registration_action" value="2" <?php checked(get_option('after_registration_action') , '2'); ?>>
                                webhook.phpで登録後にrichmenu_profile.phpに自動で移動する
                            </label>
                            </div>
                        </div>
                    </div>

                    <div class="postbox">
                        <h3 class='hndle'><span>ポイントの設定</span></h3>
                        <div class="inside">
                            <div class="main">
                            <h4>初回登録時のポイント</h4>
                            <label>
                                <input type="text" id="default_point" name="default_point_register"  value="<?=get_option('default_point_register', 0); ?>">
                            </label>
                            </div>
                        </div>
                    </div>

                    <div class="postbox">
                        <h3 class='hndle'><span>その他の設定</span></h3>
                        <div class="inside">
                            <div class="main">
                            <label>
                                <input type="checkbox" id="show_banner" name="show_banner" value="1" <?php checked(get_option('show_banner') , '1'); ?>>
                                バナーを表示する
                            </label>
                            </div>
                        </div>

                        <div class="inside">
                            <div class="main">
                            <label>
                                <input type="checkbox" id="not_exist_redirect" name="not_exist_redirect" value="1" <?php checked(get_option('not_exist_redirect') , '1'); ?>>
                                richmenu_profile.phpにアクセスした場合にWordpressの登録がない場合にはwebhook.phpに自動で移動する<br>
                                <h4 class="hndle"><span>未登録時のアラートメッセージ</span></h4>
                                <input type="text" name="not_exist_alert_message" id="not_exist_alert_message" value="<?=get_option('not_exist_alert_message');?>">
                            </label>
                            </div>
                        </div>
                        <div class="inside">
                            <div class="main">
                            <label>
                                <input type="checkbox" id="after_qr_update_redirect" name="after_qr_update_redirect" value="1" <?php checked(get_option('after_qr_update_redirect') , '1'); ?>>
                                ポイント付与QRをスキャンした場合にはrichmenu_profile.phpに自動で移動する
                            </label>
                            </div>
                        </div>
                    </div>
                </div>
                <?php submit_button(); ?>
            </form>
        </div>
<?php
    }

    /**
     * custom fields追加
     */
    static function create_line_user_custom_fields()
    {

//        $custom_fields = settings::$custom_fields;
        $custom_fields = custom_fields::$custom_fields;
        foreach($custom_fields as $item_name => $item_args) {
            $item_type = $item_args['type'];
            $item_title = $item_args['title'];
            $args = [
                'item_type'=>$item_type,
                'item_name'=>$item_name
            ];
            if($item_type == 'select' || $item_type == 'radio' || $item_type == 'checkbox') {
                $item_options = $item_args['options'];
                $args['options'] = $item_options;
            }
            add_meta_box(
                $item_name, //編集画面セクションID
                $item_title, //編集画面セクションのタイトル
                ['settings','show_line_user_elements'], //編集画面セクションにHTML出力する関数
                'line_user', //投稿タイプ名
                'normal', //編集画面セクションが表示される部分
                'default',
                $args
            );
        }
    }

    /**
     * saving custom fields
     */
    static function save_line_user_custom_field($post_ID)
    {
        $channel_access_token = get_option('channnel_access_token');
        $channel_secret = get_option('channnel_access_token_secret');
//        $get_items = settings::$custom_fields;
        $get_items = custom_fields::$custom_fields;
        foreach($get_items as $item_name =>$item_args) {
            if(isset($_POST[$item_name])) {
                update_post_meta( $post_ID, $item_name, $_POST[$item_name]  );
            }
        }

        if(isset($_POST['richmenu_id'])) {
            // LINE BOT SDK
            require_once(plugin_dir_path(__FILE__).'../vendor/autoload.php');

            $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channel_access_token);
            $bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channel_secret]);
            $richmenu_id = $_POST['richmenu_id'];
//            update_post_meta( $post_ID, 'richmenu_id', sanitize_text_field( $_POST['richmenu_id'] ) );
            // line apiからリッチメニューの更新
            if(isset($_POST['line_id'])) {
                if($richmenu_id == ""){
                    //複数のユーザーのリッチメニューのリンクを解除する
                    $response = $bot->unlinkRichMenu($_POST['line_id']);
                }else{
                    //リッチメニューと複数のユーザーをリンクする
                    $response = $bot->linkRichMenu($_POST['line_id'], $richmenu_id);
                }
            }

            update_post_meta( $post_ID, 'richmenu_id', sanitize_text_field( $richmenu_id ) );
        }
        return $post_ID;
    }

    /**
     * QRから受ける側のpage作成
     */
    static function create_input_phone_form()
    {
        $form = <<<EOF
        <form method="post" action="">
        <input type="text" name="phone" value="">
        <input type="submit" value="送信">
</form>
EOF;



        $params = array(
            'post_author' => 1,				// ユーザID
            'post_name' => 'input_phone',			// パーマリンク
            'post_title' => '電話番号をご入力ください',			// 投稿タイトル
            'post_content' => $form,	// 投稿本文
            'post_category' => [],			// カテゴリID
            'tags_input' => [],	// タグ名
            'post_status' => 'publish'			// ステータス
        );
        $id = wp_insert_post($params);
    }

    static function bulk_action_rich_menus($post_id)
    {
        if ( ! wp_verify_nonce( $_REQUEST[ '_wpnonce' ], 'bulk-posts' ) ) {
            return;
        }

        if(!empty($_REQUEST['richmenu_id'])) {
            $channel_access_token = get_option('channnel_access_token');
            $channel_secret = get_option('channnel_access_token_secret');
            $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channel_access_token);
            $bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channel_secret]);
            $line_id = get_post_meta($post_id,'line_id',true);
//            update_post_meta( $post_ID, 'richmenu_id', sanitize_text_field( $_POST['richmenu_id'] ) );
            $richmenu_id = $_REQUEST['richmenu_id'];
            // line apiからリッチメニューの更新
            if($richmenu_id){
                //リッチメニューと複数のユーザーをリンクする
                $response = $bot->linkRichMenu($line_id, $richmenu_id);
            }
            update_post_meta($post_id,'richmenu_id',$_REQUEST['richmenu_id']);
        }
    }

}