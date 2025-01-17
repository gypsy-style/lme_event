<?php

/**
 * Created by PhpStorm.
 * User: sawadakeisuke
 * Date: 5/16/22
 * Time: 10:32 AM
 */

declare(strict_types=1);
require_once('message.php');
require_once('richmenu.php');
require_once(plugin_dir_path(plugin_dir_path(__FILE__)) . 'extensions/custom_fields.php');

use \chillerlan\QRCode\QRCode;
use \chillerlan\QRCode\QROptions;
// use chillerlan\Settings\SettingsContainerInterface;
require_once(plugin_dir_path(plugin_dir_path(__FILE__)) . 'vendor/autoload.php');

class endpoints
{
    static $endpoint_functions = [
        'register_line_user',
        'entry_request',
        'event_entry_list',
        'event_list',

        'line_api_error',
        'point_history',
        'entry_request',
        'get_latest_point_info',
        'now_point',
        'point_card',
        'point_use',
    ];
    /**
     * QRコードからのアクセス
     * 電話番号で個人判別
     * @return void 
     */
    static function search_line_user()
    {
        $phone = $_POST['phone'];
        // ポイントアップいくらするか
        $up_point = isset($_POST['up_point']) && !empty($_POST['up_point']) ? $_POST['up_point'] : 1;
        $line_user_data = get_posts([
            'numberposts' => 1,
            'post_type' => 'line_user',
            'meta_key' => 'phone',
            'meta_value' => $phone
        ]);
    }

    /**
     * 本登録時の処理・更新
     * @return bool 
     */
    static function register_line_user()
    {
        //     $access_token = get_option('channnel_access_token');
        //     $channelSecret = get_option('channnel_access_token_secret');
        $post_type = 'line_user';

        // $type = $_REQUEST['type'];
        $accessToken = $_REQUEST['access_token'];
        // アクセストークンが存在しない場合は終了
        if (!$accessToken) {
            echo json_encode(['status' => 'error', 'message' => 'Access token is missing']);
            exit;
        }
        $lineProfile = self::get_line_profile($accessToken);
        if (!$lineProfile || !isset($lineProfile['userId'])) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to retrieve LINE user ID']);
            exit;
        }
        // echo json_encode(['userId' => $lineProfile['userId'], 'displayName' => $lineProfile['displayName']]);
        // exit;
        $line_id = $lineProfile['userId'];
        $replacedDisplayName = replace_emoji($lineProfile['displayName'], '*');


        // 本登録時はリッチメニューも更新
        $richmenu2 = get_option('richmenu_2');

        // 存在チェック
        $query = new WP_Query(array(
            'post_type' => 'line_user', // 投稿タイプを指定 (必要に応じてカスタム投稿タイプに変更)
            'meta_key' => 'line_id', // カスタムフィールドのキーを指定
            'meta_value' => $line_id, // カスタムフィールドの値を指定
            'posts_per_page' => 1, // 1件だけ取得（該当する投稿があれば）
        ));
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                if ($richmenu2) {
                    update_post_meta($post_id, 'richmenu_id', $richmenu2);
                    lineconnectRichmenu::updateRichMenu($line_id, $richmenu2);
                }
            }

            return true;
        }
        wp_reset_postdata();

        $my_post = array(
            'post_title' => $replacedDisplayName,
            'post_type' => $post_type,
            'post_content' => '',
            'post_status' => 'publish', //公開ステータス
            'post_author' => 1, //ユーザーID
            'post_name' => $line_id, //投稿スラッグ（パーマリンク）
            'post_excerpt' => '概要',
            'post_category' => array(), //カテゴリを配列で
            'tags_input' => array() //タグを配列で
        );

        $post_id = wp_insert_post($my_post, true);

        if (is_array($_REQUEST)) {
            foreach ($_REQUEST as $key_name => $key_value) {
                update_post_meta($post_id, $key_name, $key_value);
            }
        }
        // line_idも更新
        update_post_meta($post_id, 'line_id', $line_id);

        if ($richmenu2) {
            update_post_meta($post_id, 'richmenu_id', $richmenu2);
        }

        // リッチメニュー更新
        // if ($type != 'edit') {
        //     if ($richmenu2) {
        //         lineconnectRichmenu::updateRichMenu($line_id, $richmenu2);
        //     }

        // $form_thanks_text = get_option('form_thanks_text');
        // $message = lineconnectMessage::createTextMessage($form_thanks_text);

        // if ($message != null) {
        //     //Bot作成
        //     $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($access_token);
        //     $bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channelSecret]);

        //     //応答メッセージ送信
        //     $resp = $bot->pushMessage($line_id, $message);
        // }
        // }
        return true;
    }

    /**
     * 申し込み用の一覧ページ
     * すでに申し込んだイベントは省く
     * @return void 
     */
    static function event_entry_list()
    {
        $weekdays = get_weekdays();
        $html = '';
        $liff_id_event_entry = get_option('liff_id_event_entry');
        $accessToken = $_REQUEST['access_token'];
        // アクセストークンが存在しない場合は終了
        if (!$accessToken) {
            echo json_encode(['status' => 'error', 'message' => 'Access token is missing']);
            exit;
        }
        $lineProfile = self::get_line_profile($accessToken);
        if (!$lineProfile || !isset($lineProfile['userId'])) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to retrieve LINE user ID']);
            exit;
        }

        $line_id = $lineProfile['userId'];

        // line_user から user_id(postId) を取得
        $line_user_query = new WP_Query([
            'post_type' => 'line_user',
            'meta_query' => [
                [
                    'key' => 'line_id',
                    'value' => $line_id,
                    'compare' => '='
                ]
            ]
        ]);

        if (!$line_user_query->have_posts()) {
            echo json_encode(['status' => 'error', 'message' => 'User not found']);
            exit;
        }
        $line_user_query->the_post();
        $user_id = get_the_ID();
        wp_reset_postdata();

        // entry_history から申し込まれたイベント ID を取得
        $entry_history_query = new WP_Query([
            'post_type' => 'entry_history',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => 'user_id',
                    'value' => $user_id,
                    'compare' => '='
                ]
            ]
        ]);

        $applied_event_ids = [];
        while ($entry_history_query->have_posts()) {
            $entry_history_query->the_post();
            $applied_event_ids[] = get_post_meta(get_the_ID(), 'event_id', true);
        }
        wp_reset_postdata();

        // カテゴリーを取得
        $categories = get_categories(array(
            'taxonomy' => 'event_category', // カテゴリータクソノミー
            'hide_empty' => false,    // 投稿がないカテゴリーも表示
        ));

        // html生成
        $html .= '<input type="radio" name="rank_tab" id="tab_check01" class="vibtn" checked>';
        foreach ($categories as $index => $category) {
            $html .= '<input type="radio" name="rank_tab" id="tab_' . esc_attr($category->slug) . '" class="vibtn">';
        }
        $html .= '<ul class="lmf-tab_list">';
        $html .= '<li><label for="tab_check01">すべて</label></li>';
        foreach ($categories as $category) {
            $html .= '<li><label for="tab_' . esc_attr($category->slug) . '">' . esc_attr($category->name) . '</label></li>';
        }
        $html .= '</ul>';



        $html .= '<div class="lmf-tab_area">';
        $html .= '<section class="section">';
        // カテゴリーすべてを取得
        $event_query_args = [
            'post_type'      => 'event',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_key'       => 'event_date', // ソート基準となるカスタムフィールド
            'orderby'        => 'meta_value', // meta_valueでソート
            'order'          => 'ASC',        // 昇順
            'meta_type'      => 'DATE',       // meta_valueのデータ型を指定 (日付の場合)
        ];

        if (!empty($applied_event_ids)) {
            $event_query_args['post__not_in'] = $applied_event_ids; // 申し込まれていないイベントを取得
        }

        $event_query = new WP_Query($event_query_args);

        while ($event_query->have_posts()) {
            $event_query->the_post();
            $event_id = get_the_ID();
            $event_title = get_the_title();

            $event_types = get_post_meta($event_id, 'event_types', true); // カスタムフィールド
            $formatted_event_types = '';
            if (!empty($event_types)) {
                $event_types_array = explode("\n", $event_types); // 改行で分割

                foreach ($event_types_array as $type) {
                    $type = trim($type); // 不要な空白を削除
                    if (!empty($type)) {
                        $formatted_event_types .= '<span class="icon">' . esc_html($type) . '</span>';
                    }
                }
            }
            $event_date = get_post_meta($event_id, 'event_date', true); // カスタムフィールド
            $formatted_date = '';
            if (!empty($event_date)) {
                $date = new DateTime($event_date);
                // 整形した日付を生成
                $formatted_date = $date->format('Y年n月j日') . '（' . $weekdays[$date->format('w')] . '）';
            }

            $html .= '<ul class="lmf-card_list">';
            $html .= '<li><a href="https://liff.line.me/' . $liff_id_event_entry . '?event_id=' . $event_id . '">';
            $html .= '<p class="data_box">' . $formatted_date . '</p>';
            $html .= '<h3 class="name">' . $event_title . '</h3>';
            // タグ
            $tags = get_the_terms($event_id, 'event_tag'); // デフォルトのタグタクソノミー
            $formatted_tag_icon = '';
            if ($tags && !is_wp_error($tags)) {
                foreach ($tags as $tag) {
                    // タグ名を <span> で囲む
                    $formatted_tag_icon .= '<span class="icon">' . esc_html($tag->name) . '</span>';
                }
            }
            $html .= '<div class="lmf-icon_box">' . $formatted_tag_icon . '</div>';
            $html .= '</ul>';
        }
        $html .= '</section>';
        wp_reset_postdata();

        // カテゴリーごと
        foreach ($categories as $category) {
            $event_query_args['tax_query'] = [
                [
                    'taxonomy' => 'event_category',
                    'field'    => 'slug',
                    'terms'    => $category->slug,
                ],
            ];
            $event_query = new WP_Query($event_query_args);
            $html .= '<section class="section">';
            while ($event_query->have_posts()) {
                $event_query->the_post();
                $event_id = get_the_ID();
                $event_title = get_the_title();

                $event_types = get_post_meta($event_id, 'event_types', true); // カスタムフィールド
                $formatted_event_types = '';
                if (!empty($event_types)) {
                    $event_types_array = explode("\n", $event_types); // 改行で分割

                    foreach ($event_types_array as $type) {
                        $type = trim($type); // 不要な空白を削除
                        if (!empty($type)) {
                            $formatted_event_types .= '<span class="icon">' . esc_html($type) . '</span>';
                        }
                    }
                }
                $event_date = get_post_meta($event_id, 'event_date', true); // カスタムフィールド
                $formatted_date = '';
                if (!empty($event_date)) {
                    $date = new DateTime($event_date);
                    // 整形した日付を生成
                    $formatted_date = $date->format('Y年n月j日') . '（' . $weekdays[$date->format('w')] . '）';
                }

                $html .= '<ul class="lmf-card_list">';
                $html .= '<li><a href="https://liff.line.me/' . $liff_id_event_entry . '?event_id=' . $event_id . '">';
                $html .= '<p class="data_box">' . $formatted_date . '</p>';
                $html .= '<h3 class="name">' . $event_title . '</h3>';
                // タグ
                $tags = get_the_terms($event_id, 'event_tag'); // デフォルトのタグタクソノミー
                $formatted_tag_icon = '';
                if ($tags && !is_wp_error($tags)) {
                    foreach ($tags as $tag) {
                        // タグ名を <span> で囲む
                        $formatted_tag_icon .= '<span class="icon">' . esc_html($tag->name) . '</span>';
                    }
                }
                $html .= '<div class="lmf-icon_box">' . $formatted_tag_icon . '</div>';

                $html .= '</ul>';
            }
            $html .= '</section>';
            wp_reset_postdata();
        }


        $html .= '</div>';
        $return['html'] = $html;

        header("Content-type: application/json; charset=UTF-8");
        echo json_encode($return);
    }

    /**
     * イベント一覧ページ
     * 全データを出力
     * @return void 
     */
    static function event_list()
    {
        $weekdays = get_weekdays();
        $html = '';
        $liff_id_event_schedule = get_option('liff_id_event_schedule');
        $accessToken = $_REQUEST['access_token'];
        // アクセストークンが存在しない場合は終了
        if (!$accessToken) {
            echo json_encode(['status' => 'error', 'message' => 'Access token is missing']);
            exit;
        }
        $lineProfile = self::get_line_profile($accessToken);
        if (!$lineProfile || !isset($lineProfile['userId'])) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to retrieve LINE user ID']);
            exit;
        }

        wp_reset_postdata();

        // カテゴリーを取得
        $categories = get_categories(array(
            'taxonomy' => 'event_category', // カテゴリータクソノミー
            'hide_empty' => false,    // 投稿がないカテゴリーも表示
        ));

        // html生成
        $html .= '<input type="radio" name="rank_tab" id="tab_check01" class="vibtn" checked>';
        foreach ($categories as $index => $category) {
            $html .= '<input type="radio" name="rank_tab" id="tab_' . esc_attr($category->slug) . '" class="vibtn">';
        }
        $html .= '<ul class="lmf-tab_list">';
        $html .= '<li><label for="tab_check01">すべて</label></li>';
        foreach ($categories as $category) {
            $html .= '<li><label for="tab_' . esc_attr($category->slug) . '">' . esc_attr($category->name) . '</label></li>';
        }
        $html .= '</ul>';



        $html .= '<div class="lmf-tab_area">';
        $html .= '<section class="section">';
        // カテゴリーすべてを取得
        // $event_query_args = [ 
        $event_query_args = [
            'post_type'      => 'event',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_key'       => 'event_date', // ソート基準となるカスタムフィールド
            'orderby'        => 'meta_value', // meta_valueでソート
            'order'          => 'ASC',        // 昇順
            'meta_type'      => 'DATE',       // meta_valueのデータ型を指定 (日付の場合)
        ];

        $event_query = new WP_Query($event_query_args);
        while ($event_query->have_posts()) {
            $event_query->the_post();
            $event_id = get_the_ID();
            $event_title = get_the_title();
            $event_types = get_post_meta($event_id, 'event_types', true); // カスタムフィールド
            $formatted_event_types = '';
            if (!empty($event_types)) {
                $event_types_array = explode("\n", $event_types); // 改行で分割

                foreach ($event_types_array as $type) {
                    $type = trim($type); // 不要な空白を削除
                    if (!empty($type)) {
                        $formatted_event_types .= '<span class="icon">' . esc_html($type) . '</span>';
                    }
                }
            }
            $event_date = get_post_meta($event_id, 'event_date', true); // カスタムフィールド
            $formatted_date = '';
            if (!empty($event_date)) {
                $date = new DateTime($event_date);
                // 整形した日付を生成
                $formatted_date = $date->format('Y年n月j日') . '（' . $weekdays[$date->format('w')] . '）';
            }

            $html .= '<ul class="lmf-card_list">';
            $html .= '<li><a href="https://liff.line.me/' . $liff_id_event_schedule . '?event_id=' . $event_id . '">';
            $html .= '<p class="data_box">' . $formatted_date . '</p>';
            $html .= '<h3 class="name">' . $event_title . '</h3>';

            // タグ
            $tags = get_the_terms($event_id, 'event_tag'); // デフォルトのタグタクソノミー
            $formatted_tag_icon = '';
            if ($tags && !is_wp_error($tags)) {
                foreach ($tags as $tag) {
                    // タグ名を <span> で囲む
                    $formatted_tag_icon .= '<span class="icon">' . esc_html($tag->name) . '</span>';
                }
            }
            $html .= '<div class="lmf-icon_box">' . $formatted_tag_icon . '</div>';
            $html .= '</ul>';
        }
        $html .= '</section>';
        wp_reset_postdata();

        // カテゴリーごと
        foreach ($categories as $category) {
            $event_query_args['tax_query'] = [
                [
                    'taxonomy' => 'event_category',
                    'field'    => 'slug',
                    'terms'    => $category->slug,
                ],
            ];
            $event_query = new WP_Query($event_query_args);
            $html .= '<section class="section">';

            while ($event_query->have_posts()) {
                $event_query->the_post();
                $event_id = get_the_ID();
                $event_title = get_the_title();
                $event_types = get_post_meta($event_id, 'event_types', true); // カスタムフィールド
                $formatted_event_types = '';
                if (!empty($event_types)) {
                    $event_types_array = explode("\n", $event_types); // 改行で分割

                    foreach ($event_types_array as $type) {
                        $type = trim($type); // 不要な空白を削除
                        if (!empty($type)) {
                            $formatted_event_types .= '<span class="icon">' . esc_html($type) . '</span>';
                        }
                    }
                }
                $event_date = get_post_meta($event_id, 'event_date', true); // カスタムフィールド
                $formatted_date = '';
                if (!empty($event_date)) {
                    $date = new DateTime($event_date);
                    // 整形した日付を生成
                    $formatted_date = $date->format('Y年n月j日') . '（' . $weekdays[$date->format('w')] . '）';
                }

                $html .= '<ul class="lmf-card_list">';
                $html .= '<li><a href="https://liff.line.me/' . $liff_id_event_schedule . '?event_id=' . $event_id . '">';
                $html .= '<p class="data_box">' . $formatted_date . '</p>';
                $html .= '<h3 class="name">' . $event_title . '</h3>';
                // タグ
                $tags = get_the_terms($event_id, 'event_tag'); // デフォルトのタグタクソノミー
                $formatted_tag_icon = '';
                if ($tags && !is_wp_error($tags)) {
                    foreach ($tags as $tag) {
                        // タグ名を <span> で囲む
                        $formatted_tag_icon .= '<span class="icon">' . esc_html($tag->name) . '</span>';
                    }
                }
                $html .= '<div class="lmf-icon_box">' . $formatted_tag_icon . '</div>';
                $html .= '</ul>';
            }
            $html .= '</section>';
            wp_reset_postdata();
        }


        $html .= '</div>';
        $return['html'] = $html;

        header("Content-type: application/json; charset=UTF-8");
        echo json_encode($return);
    }

    /**
     * entry_request
     * @return void 
     */
    static function entry_request()
    {
        // print_r($_POST);
        // POSTデータを取得
        $accessToken = $_POST['access_token'];
        $event_id = $_POST['event_id'];

        $event_types = isset($_POST['event_types']) && is_array($_POST['event_types']) ? implode(',', $_POST['event_types']) : '';

        // アクセストークンが存在しない場合は終了
        if (!$accessToken) {
            echo json_encode(['status' => 'error', 'message' => 'Access token is missing']);
            exit;
        }

        // 1. アクセストークンからLINEユーザーID（line_id）を取得
        $lineProfile = self::get_line_profile($accessToken);
        if (!$lineProfile || !isset($lineProfile['userId'])) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to retrieve LINE user ID']);
            exit;
        }
        $line_id = $lineProfile['userId'];

        // 2. カスタム投稿タイプ line_user で line_id をキーに検索
        $line_user_query = new WP_Query([
            'post_type' => 'line_user',
            'meta_query' => [
                [
                    'key' => 'line_id',
                    'value' => $line_id,
                    'compare' => '='
                ]
            ]
        ]);

        if ($line_user_query->have_posts()) {
            // 3. line_user が存在すれば postID を取得
            $line_user_post_id = $line_user_query->posts[0]->ID;
        } else {
            // 会員登録ページへリダイレクト https://liff.line.me/2006629843-MgmjwJxk

        }

        // 5. entry_history に user_id と event_id を保存
        $entry_post_id = wp_insert_post([
            'post_type' => 'entry_history',
            'post_title' => "User {$line_user_post_id} Event ID {$event_id}",
            'post_status' => 'publish',
        ]);

        // 関連データを保存
        update_post_meta($entry_post_id, 'user_id', $line_user_post_id);
        update_post_meta($entry_post_id, 'event_id', $event_id);
        update_post_meta($entry_post_id, 'event_types', $event_types);

        // 6. 成功メッセージを返す
        echo json_encode(['status' => 'success', 'message' => 'Entry registered successfully']);
        exit;
    }

    static function update_line_user()
    {
        $access_token = get_option('channnel_access_token');
        $channelSecret = get_option('channnel_access_token_secret');
        $post_type = 'line_user';

        $type = $_REQUEST['type'];
        $line_id = $_REQUEST['line_id'];
        //        unset($_REQUEST['line_id']);

        $title = isset($_REQUEST['displayName']) ? $_REQUEST['displayName'] : ''; // お名前はタイトルで使用する
        unset($_REQUEST['displayName']);

        $args = array(
            'post_type' => array($post_type), //投稿タイプを指定
            'posts_per_page' => '-1', //取得する投稿件数を指定
            'meta_key' => 'line_id', //カスタムフィールドのキーを指定
            'meta_value' => $line_id, //カスタムフィールドの値を指定
            'orderby' => 'meta_value', //ソートの基準を指定
            'order' => 'asc' //ソート方法を指定（昇順：asc, 降順：desc）
        );
        $the_query = new WP_Query($args);
        if ($the_query->have_posts()) {
            while ($the_query->have_posts()) {
                $the_query->the_post();
                $post_id = get_the_ID();
                //                    echo 'post-id='.$post_id;
            }
        } else {
            return false;
        }

        if (is_array($_REQUEST)) {
            foreach ($_REQUEST as $key_name => $key_value) {
                update_post_meta($post_id, $key_name, $key_value);
            }
        }

        return true;
    }





    /**
     * richmenuのプロフィール表示
     */
    static function richmenu_profile()
    {
        $fields = [
            'line_id' => 'LINEID',
            'richmenu_id' => 'リッチメニュー',
            'sex' => '性別',
            'area' => '住んでいる地域',
            'point' => '所持ポイント数',
        ];
        $line_id = $_REQUEST['line_id'];
        $enabled_coupon = get_option('enabled_coupon');
        $coupon_expired_date = get_option('coupon_expired_date');

        $get_items = [];
        foreach ($fields as $input_name => $values) {
            array_push($get_items, $input_name);
        }


        // line_idで絞り込み
        $args = array(
            'post_type' => array('line_user'), //投稿タイプを指定
            'posts_per_page' => '-1', //取得する投稿件数を指定
            'meta_key' => 'line_id', //カスタムフィールドのキーを指定
            'meta_value' => $line_id, //カスタムフィールドの値を指定
            'orderby' => 'meta_value', //ソートの基準を指定
            'order' => 'asc' //ソート方法を指定（昇順：asc, 降順：desc）
        );
        $the_query = new WP_Query($args);
        if ($the_query->have_posts()) {
            while ($the_query->have_posts()) {
                $return = [];
                $the_query->the_post();
                $post_id = get_the_ID();
                $name = get_the_title($post_id);
                $return['user_id'] = $post_id;
                $return['name'] = $name;
                foreach ($get_items as $item) {
                    //                    echo 'item='.get_post_meta($post_id,$item);
                    $item_value = get_post_meta($post_id, $item);

                    if (isset($item_value[0])) {
                        $return[$item] = $item_value[0];
                    }
                }

                // 更新日取得
                $modified_date = get_the_modified_date('Y年n月j日（D）H時i分');
                $return['modified_date'] = $modified_date;

                // 投稿日取得
                $post_date = get_the_time('Y年n月j日（D）H時i分');
                $return['post_date'] = $post_date;

                // qrcode
                $options = new QROptions([
                    // ここにオプション値
                    'eccLevel' => QRCode::ECC_L,
                    'outputType' => QRCode::OUTPUT_MARKUP_SVG,
                    'scale' => 3
                ]);
                // $qrcode = new QRCode($options);
                // // $path = storage_path('qrcode_2.png');
                // $url = 'https://lsm-app.com/sakuratessen/line_user/'.$line_id.'/';
                // $qr_data = $qrcode->render($url);
                // $qrcode = (new QRCode($options))->render('https://caldo-hair.com/contents/wp-admin/post.php?post='.$post_id.'&action=edit');

                // $return['qrcode'] = $qr_data;

                if ($enabled_coupon) {
                    $post_date = get_post_time('Y-m-d');
                    $coupon_expired_date = $coupon_expired_date ? $coupon_expired_date : 45;
                    //                $return['coupon'] = $post_date;
                    $return['coupon'] = date('Y年m月d日', strtotime('+' . $coupon_expired_date . ' day', strtotime($post_date)));
                }


                header("Content-type: application/json; charset=UTF-8");
                echo json_encode($return);
                //                wp_die();
                exit;
            }
        } else {
            echo json_encode([]);
            exit;
        }
    }

    /**
     * richmenuのプロフィール表示
     */
    static function point_card()
    {
        $fields = [
            'line_id' => 'LINEID',
            'richmenu_id' => 'リッチメニュー',
            'point' => '所持ポイント数',
        ];
        $line_id = $_REQUEST['line_id'];
        $enabled_coupon = get_option('enabled_coupon');
        $coupon_expired_date = get_option('coupon_expired_date');

        $get_items = [];
        foreach ($fields as $input_name => $values) {
            array_push($get_items, $input_name);
        }


        // line_idで絞り込み
        $args = array(
            'post_type' => array('line_user'), //投稿タイプを指定
            'posts_per_page' => '1', //取得する投稿件数を指定
            'meta_key' => 'line_id', //カスタムフィールドのキーを指定
            'meta_value' => $line_id, //カスタムフィールドの値を指定
            'orderby' => 'meta_value', //ソートの基準を指定
            'order' => 'asc' //ソート方法を指定（昇順：asc, 降順：desc）
        );
        $the_query = new WP_Query($args);
        if ($the_query->have_posts()) {
            while ($the_query->have_posts()) {
                $return = [];
                $the_query->the_post();
                $post_id = get_the_ID();
                $name = get_the_title($post_id);
                $return['name'] = $name;
                foreach ($get_items as $item) {
                    //                    echo 'item='.get_post_meta($post_id,$item);
                    $item_value = get_post_meta($post_id, $item);

                    if (isset($item_value[0])) {
                        $return[$item] = $item_value[0];
                    }
                }

                // 更新日取得
                $modified_date = get_the_modified_date('Y年n月j日（D）H時i分');
                $return['modified_date'] = $modified_date;

                // 投稿日取得
                $post_date = get_the_time('Y年n月j日（D）H時i分');
                $return['post_date'] = $post_date;

                // ポイント利用期限取得
                $point_limit_date = get_post_meta($post_id, 'point_limit_date', true);
                if (!$point_limit_date) {
                    $point_limit_date = '最終ポイント付与後 1 年';
                } else {
                    $point_limit_date = '有効期限　' . $point_limit_date;
                }
                $return['point_limit_date'] = $point_limit_date;


                if ($enabled_coupon) {
                    $post_date = get_post_time('Y-m-d');
                    $coupon_expired_date = $coupon_expired_date ? $coupon_expired_date : 45;
                    //                $return['coupon'] = $post_date;
                    $return['coupon'] = date('Y年m月d日', strtotime('+' . $coupon_expired_date . ' day', strtotime($post_date)));
                }

                // 最近訪れた店舗
                // point_historyから取得
                $point_history_html = '';
                $point_history_args = array(
                    'post_type' => array('point_history'), //投稿タイプを指定
                    'posts_per_page' => '3', //取得する投稿件数を指定
                    'meta_key' => 'user_id', //カスタムフィールドのキーを指定
                    'meta_value' => $post_id, //カスタムフィールドの値を指定
                    'orderby' => 'date', //ソートの基準を指定
                    'order' => 'desc' //ソート方法を指定（昇順：asc, 降順：desc）
                );
                $point_history_the_query = new WP_Query($point_history_args);
                if ($point_history_the_query->have_posts()) {
                    $point_history_html .= '<div class="lmf-visited_block lmf-white_block">
                    <ul class="lmf-shop_list">';
                    while ($point_history_the_query->have_posts()) {
                        $point_history_the_query->the_post();
                        $point_history_id = get_the_ID();
                        $store_id = get_post_meta($point_history_id, 'store_id', true);
                        $store_name = get_post_meta($store_id, 'store_name', true);
                        $homepage = get_post_meta($store_id, 'homepage', true);
                        if ($homepage) {
                            $homepage = '<li class="web"><a href="' . $homepage . '"><span>&nbsp;</span></a></li>';
                        }
                        $instagram = get_post_meta($store_id, 'instagram', true);
                        if ($instagram) {
                            $instagram = '<li class="insta"><a href="' . $instagram . '"><span>&nbsp;</span></a></li>';
                        }
                        $official_line = get_post_meta($store_id, 'official_line', true);
                        if ($official_line) {
                            $official_line = '<li class="line"><a href="' . $official_line . '"><span>&nbsp;</span></a></li>';
                        }
                        $store_image = get_the_post_thumbnail_url($store_id, 'full');
                        if (!$store_image) {
                            $store_image = './image/front/no_img400.jpg';
                        }
                        $post_date = get_the_date('Y年m月d日 H:i', $post_id);


                        // html生成
                        $point_history_html = '<li>
                        <div class="inner">
                            <figure class="fig_box "><img src="' . $store_image . '" alt=""></figure>
                            <div class="info_box">
                                <h3 class="name">' . $store_name . '</h3>
                                <ul class="sns_list sns">
                                    ' . $official_line
                            . $instagram
                            . $homepage . '
                                </ul>
                            </div>
                        </div>
                    </li>';

                        header("Content-type: application/json; charset=UTF-8");

                        //                wp_die();

                    }
                    $point_history_html .= '</ul>
                    </div>';
                    wp_reset_postdata();
                    $return['point_history_html'] = $point_history_html;
                } else {
                    $point_history_html .= '';
                }



                header("Content-type: application/json; charset=UTF-8");
                echo json_encode($return);
                //                wp_die();
                exit;
            }
        } else {
            echo json_encode([]);
            exit;
        }
    }

    /**
     * ポイント付与履歴
     */
    static function point_history()
    {
        $line_id = $_REQUEST['line_id'];
        $fullname = '';
        $now_point = 0;

        $html = '<section class="lmf-content">';

        // line_idから個人情報取得
        $args_line_user = [
            'post_type' => array('line_user'), //投稿タイプを指定
            'posts_per_page' => '-1', //取得する投稿件数を指定
            'meta_key' => 'line_id', //カスタムフィールドのキーを指定
            'meta_value' => $line_id, //カスタムフィールドの値を指定
            'orderby' => 'meta_value', //ソートの基準を指定
            'order' => 'asc' //ソート方法を指定（昇順：asc, 降順：desc）
        ];
        $line_user_query = new WP_Query($args_line_user);
        if ($line_user_query->have_posts()) {
            while ($line_user_query->have_posts()) {
                $line_user_query->the_post();
                $line_user_post_id = get_the_ID();
                $fullname = get_post_meta($line_user_post_id, 'fullname', true);
                $now_point = get_post_meta($line_user_post_id, 'point', true);
            }
            wp_reset_postdata();
        }
        $html .= '
        <div class="lmf-user_block">
        <div class="point_box">
            <em class="label">保有ポイント</em>
            <b class="points"><span class="point">' . $now_point . '</span><span class="unit">pt</span></b>
        </div>
    </div>
    ';



        // line_idで絞り込み
        $args = array(
            'post_type' => array('point_history'), //投稿タイプを指定
            'posts_per_page' => '-1', //取得する投稿件数を指定
            'meta_key' => 'user_id', //カスタムフィールドのキーを指定
            'meta_value' => $line_user_post_id, //カスタムフィールドの値を指定
            'orderby' => 'date', //ソートの基準を指定
            'order' => 'desc' //ソート方法を指定（昇順：asc, 降順：desc）
        );
        $the_query = new WP_Query($args);
        if ($the_query->have_posts()) {
            $html .= '<div class="lmf-record_block lmf-white_block">
            <ul class="lmf-record_list">';
            while ($the_query->have_posts()) {
                $return = [];
                $the_query->the_post();
                $post_id = get_the_ID();
                $point_number = get_post_meta($post_id, 'point_number', true);
                $point_type = get_post_meta($post_id, 'point_type', true);
                $store_id = get_post_meta($post_id, 'store_id', true);
                $store_name = get_post_meta($store_id, 'store_name', true);
                $post_date = get_the_date('Y年m月d日 H:i', $post_id);
                if ($point_type == '付与') {
                    $get_or_use_class = 'get';
                    $point_number = '+' . $point_number;
                } else {
                    $get_or_use_class = 'use';
                    $point_number = '-' . $point_number;
                }

                // html生成
                $html .= '<li class="' . $get_or_use_class . '">';
                $html .= '<span class="icon">ポイント' . $point_type . '</span>';
                $html .= '<em class="data">' . $post_date . '</em>';
                $html .= '<b class="title">' . $store_name . '</b>';
                $html .= '<p class="point">' . $point_number . 'pt</p>';
                $html .= '</li>';

                header("Content-type: application/json; charset=UTF-8");

                //                wp_die();

            }
            $html .= '</ul>
			</div>
		</section>';
            wp_reset_postdata();
        } else {
            $html .= '<p>ポイント付与履歴が取得できませんでした</p>';
        }
        echo $html;
        exit;
    }



    /**
     * LINEプロフィールを取得する
     *
     * @param string $accessToken アクセストークン
     * @return array|null LINEプロフィール情報
     */
    static function get_line_profile($accessToken)
    {
        $url = 'https://api.line.me/v2/profile';
        $headers = [
            'Authorization: Bearer ' . $accessToken,
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        curl_close($ch);

        if ($response) {
            return json_decode($response, true);
        }

        return null;
    }

    static function get_latest_point_info()
    {

        $line_id = $_REQUEST['line_id'];
        $enabled_coupon = get_option('enabled_coupon');
        $coupon_expired_date = get_option('coupon_expired_date');

        // line_idからユーザーIDを取得
        $args = array(
            'post_type' => array('line_user'), //投稿タイプを指定
            'posts_per_page' => '1', //取得する投稿件数を指定
            'meta_key' => 'line_id', //カスタムフィールドのキーを指定
            'meta_value' => $line_id, //カスタムフィールドの値を指定
            'orderby' => 'date', //投稿の日付を基準にソート
            'order' => 'desc' //最新の投稿を取得するために降順にソート
        );
        $the_query = new WP_Query($args);
        if ($the_query->have_posts()) {
            while ($the_query->have_posts()) {
                $the_query->the_post();
                $user_id = get_the_ID();
            }
        } else {
            echo 'ERROR';
            exit;
        }



        wp_reset_postdata();

        // user_idからポイント履歴の一番最新情報を取得
        // line_idで絞り込み
        $args = array(
            'post_type' => array('point_history'), //投稿タイプを指定
            'posts_per_page' => '1', //取得する投稿件数を指定
            'orderby' => 'date', //投稿の日付を基準にソート
            'order' => 'desc', //最新の投稿を取得するために降順にソート
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'user_id', // user_id フィールド
                    'value' => $user_id, // 特定の user_id を指定
                    'compare' => '='
                ),
                array(
                    'key' => 'point_type', // point_type フィールド
                    'value' => '付与', // '付与' を指定
                    'compare' => '='
                )
            )
        );
        $the_query = new WP_Query($args);
        if ($the_query->have_posts()) {
            while ($the_query->have_posts()) {
                $return = [];
                $the_query->the_post();
                $post_point_history_id = get_the_ID();
                $point_number = get_post_meta($post_point_history_id, 'point_number', true);
                $store_id = get_post_meta($post_point_history_id, 'store_id', true);
                // $user_id = get_post_meta($post_point_history_id,'user_id',true);
                $point_rate = get_post_meta($post_point_history_id, 'point_rate', true);
                $price = get_post_meta($post_point_history_id, 'price', true);
                $store_name = get_post_meta($store_id, 'store_name', true);
                $message = get_post_meta($store_id, 'message', true);
                $display_button = get_post_meta($store_id, 'display_button', true);

                $return['latest_point'] = $point_number;
                $return['store_id'] = $store_id;
                $return['latest_point_rate'] = $point_rate;
                $return['latest_price'] = $price;
                $return['latest_store_name'] = $store_name;
                $return['latest_message'] = $message;
                $cta_button = '';
                switch ($display_button) {
                    case 'homepage':
                        $cta_url = get_post_meta($store_id, 'homepage', true);
                        $cta_button = '<p class="lmf-btn_box btn_line">
                        <a href="' . $cta_url . '"><span class="text">WEBサイトへアクセス</span></a>
                    </p>';
                        break;

                    case 'instagram':
                        $cta_url = get_post_meta($store_id, 'instagram', true);
                        $cta_button = '<p class="lmf-btn_box btn_line">
                        <a href="' . $cta_url . '"><span class="text">インスタグラムをフォロー</span></a>
                    </p>';
                        break;

                    case 'official_line':
                        $cta_url = get_post_meta($store_id, 'official_line', true);
                        $cta_button = '<p class="lmf-btn_box btn_line">
                        <a href="' . $cta_url . '"><span class="text">LINEでお友達登録</span></a>
                    </p>';
                        break;

                    default:
                        break;
                }
                $return['latest_cta_button'] = $cta_button;



                // 更新日取得
                $modified_date = get_the_modified_date('Y年n月j日（D）H時i分');
                $return['modified_date'] = $modified_date;

                // 投稿日取得
                $post_date = get_the_time('Y年n月j日（D）H時i分');
                $return['post_date'] = $post_date;

                // qrcode
                $options = new QROptions([
                    'eccLevel' => QRCode::ECC_L,
                    'outputType' => QRCode::OUTPUT_MARKUP_SVG, // SVG形式で出力
                    'scale' => 3
                ]);
                // $qrcode = new QRCode($options);
                // $path = storage_path('qrcode_2.png');
                // $url = plugins_url('store/give_point.php', dirname(__FILE__) . '/line-members/') . '?line_id=' . $line_id;
                // $qrcode = (new QRCode($options))->render($url);
                // $qrcode = (new QRCode($options))->render('https://caldo-hair.com/contents/wp-admin/post.php?post='.$post_id.'&action=edit');

                // $return['qrcode'] = $qrcode;

                if ($enabled_coupon) {
                    $post_date = get_post_time('Y-m-d');
                    $coupon_expired_date = $coupon_expired_date ? $coupon_expired_date : 45;
                    //                $return['coupon'] = $post_date;
                    $return['coupon'] = date('Y年m月d日', strtotime('+' . $coupon_expired_date . ' day', strtotime($post_date)));
                }


                header("Content-type: application/json; charset=UTF-8");
                echo json_encode($return);
                //                wp_die();
                exit;
            }
        } else {
            echo json_encode([]);
            exit;
        }
    }

    static function point_use()
    {
        $use_point = (int)$_GET['use_point'];
        $store_id = (int)$_GET['store_id'];
        $user_id = (int)$_GET['user_id'];
        $fullname = '';
        $now_point = 0;

        // ユーザー情報取得
        $fullname = get_post_meta($user_id, 'fullname', true);
        $now_point = (int)get_post_meta($user_id, 'point', true);

        // ポイントが足りるか確認
        if ($now_point < $use_point) {
            echo 'error: insufficient points';
            exit;
        }

        // 所持ポイント数を更新
        $now_point -= $use_point;
        update_post_meta($user_id, 'point', $now_point);
        // ポイント有効期限を登録
        // $one_year_later = date_i18n( 'Y-m-d', strtotime('+1 year') );
        // update_post_meta($user_id,'point_limit_date',$one_year_later);

        // お店のポイントも更新
        $store_point = (int)get_post_meta($store_id, 'store_point', true);
        $store_name = get_post_meta($store_id, 'store_name', true);
        $store_point += $use_point;
        update_post_meta($store_id, 'store_point', $store_point); // 修正済み

        // ポイント履歴を追加
        $point_history_post = array(
            'post_title' => $store_name,
            'post_type' => 'point_history',
            'post_content' => '',
            'post_status' => 'publish', //公開ステータス
        );

        $point_history_post_id = wp_insert_post($point_history_post, true);
        if (is_wp_error($point_history_post_id)) {
            echo 'error: failed to insert post' . ' store_id=' . $store_id;
            exit;
        }

        update_post_meta($point_history_post_id, 'point_number', $use_point);
        update_post_meta($point_history_post_id, 'user_id', $user_id);
        update_post_meta($point_history_post_id, 'store_id', $store_id);
        update_post_meta($point_history_post_id, 'point_type', '使用');

        echo 'success';
        exit;
    }


    /*static function check_permission_callback()
    {
        return current_user_can('publish_posts');
    }*/


    /**
     * エンド出力
     */
    static function set_js_params()
    {
?>
        <script>
            let ajaxUrl = '<?php echo esc_html(admin_url('admin-ajax.php')); ?>';
        </script>
<?php
    }

    /**
     * rest_apiセット
     * @return void 
     */
    static function set_rest_api()
    {
        $endpoint_functions = self::$endpoint_functions;
        foreach ($endpoint_functions as $function_name) {
            $postOrGet = 'GET';
            if ($function_name == 'entry_request') {
                $postOrGet = 'POST';
            }
            register_rest_route('wp/v2', '/' . $function_name, [
                'methods' => $postOrGet,
                'permission_callback' => '__return_true',
                'callback' => ['endpoints', $function_name],
            ]);
        }
    }


    static function add_cors_http_header()
    {
        header("Access-Control-Allow-Origin: *");
    }

    /**
     * line api エラー時の処理 エラーログに残す
     * @return void 
     */
    static function line_api_error()
    {
        $post_type = 'error_log';
        $now_date = date_i18n('Y-m-d H:i:s');
        $error_message = $_REQUEST['error_message'];
        $error_action = $_REQUEST['error_action'];
        $title = 'error-' . $now_date;

        // データ挿入
        $error_log__post = array(
            'post_title' => $title,
            'post_type' => $post_type,
            'post_content' => '',
            'post_status' => 'publish', //公開ステータス
        );
        $error_log_id = wp_insert_post($error_log__post, true);

        // meta情報更新
        update_post_meta($error_log_id, 'error_log_date', $now_date);
        update_post_meta($error_log_id, 'error_action', $error_action);
        update_post_meta($error_log_id, 'error_message', $error_message);
    }
}
