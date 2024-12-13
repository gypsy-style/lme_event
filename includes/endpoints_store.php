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

class endpointsStore
{
    static $endpoint_functions = [
        'register_store',
        'update_store',
        'store_update_point',
        'store_info',
        'store_update_by_qr',
        'get_store_member',
        'delete_store_member',
        'store_point_history',
        'store_point_history_month',
        'store_give_point',
        'store_add_member',
        'store_get_point',
        'store_get_latest_point_info',
        'get_store_info',
    ];

    static $fields = [
        'store_name' => '店舗名',
        'zip1' => '郵便番号1',
        'zip2' => '郵便番号2',
        'address' => '住所',
        'phone_number' => '電話番号',
        'line_id' => 'LINEID(UserID)',
        'point_rate' => 'ポイント付与率',
        'category' => 'カテゴリー',
        'industry_type' => '業種・業態',
        'business_hours' => '営業時間',
        'regular_holiday' => '定休日',
        'homepage' => 'ホームページ',
        'instagram' => 'インスタグラム',
        'official_line' => '公式LINE',
        'person_in_charge' => '担当者',
        'email' => 'メールアドレス',
        'message' => 'メッセージ',
        'display_button' => '表示ボタン'
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
    static function register_store()
    {
        // header("Content-type: application/json; charset=UTF-8");
        //         echo json_encode($_REQUEST);exit;
        // print_r($_REQUEST);exit;
        $access_token = get_option('channnel_access_token');
        $channelSecret = get_option('channnel_access_token_secret');
        $post_type = 'store';

        $type = $_REQUEST['type'];
        $line_id = $_REQUEST['line_id'];
        //        unset($_REQUEST['line_id']);

        // 本登録時はリッチメニューも更新
        $richmenu3 = get_option('richmenu_3');

        $title = isset($_REQUEST['store_name']) ? $_REQUEST['store_name'] : ''; // お名前はタイトルで使用する
        unset($_REQUEST['displayName']);
        // line_idで存在チェック
        $args = [
            'post_type' => array('store'), //投稿タイプを指定
            'posts_per_page' => '-1', //取得する投稿件数を指定
            'meta_key' => 'line_id', //カスタムフィールドのキーを指定
            'meta_value' => $line_id, //カスタムフィールドの値を指定
            'orderby' => 'meta_value', //ソートの基準を指定
            'order' => 'asc' //ソート方法を指定（昇順：asc, 降順：desc）
        ];
        $the_query = new WP_Query($args);

        if ($the_query->have_posts()) {
            echo json_encode(['status' => 'error', 'message' => 'このLINE IDに関連するユーザーは既に存在します。']);
            return false; // 処理を止める
        }


        // 本登録時はリッチメニューも更新
        // $richmenu2 = get_option('richmenu_2');

        if ($type == 'edit') {

            $args = array(
                'post_type' => array('store'), //投稿タイプを指定
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
                }
            } else {
                echo 'NOT_REGISTERED';
                exit;
            }
        } else {
            $my_post = array(
                'post_title' => $title,
                'post_type' => 'store',
                'post_content' => '',
                'post_status' => 'publish', //公開ステータス
                'post_author' => 1, //ユーザーID
                'post_name' => $line_id, //投稿スラッグ（パーマリンク）
                'post_excerpt' => '概要',
                'post_category' => array(), //カテゴリを配列で
                'tags_input' => array() //タグを配列で
            );

            $post_id = wp_insert_post($my_post, true);
            $user_id = get_post_meta($post_id, 'user_id', true);
            $default_point_register = get_option('default_point_register');
        }

        if (is_array($_REQUEST)) {
            foreach ($_REQUEST as $key_name => $key_value) {
                update_post_meta($post_id, $key_name, $key_value);
            }
        }

        // 画像がアップロードされた場合の処理
        $store_image = isset($_FILES['store_image']) ? $_FILES['store_image'] : '';
        if ($store_image) {
            require_once ABSPATH . 'wp-admin' . '/includes/image.php';
            require_once ABSPATH . 'wp-admin' . '/includes/file.php';
            require_once ABSPATH . 'wp-admin' . '/includes/media.php';
            $thumbnail_id = media_handle_upload('store_image', $post_id);
            set_post_thumbnail($post_id, $thumbnail_id);
            // header("Content-type: application/json; charset=UTF-8");
            //     echo json_encode([$thumbnail_id]);exit;
        }
        // if (!empty($_FILES['store_image']) && !empty($_FILES['store_image']['tmp_name'])) {
        //     require_once(ABSPATH . 'wp-admin/includes/file.php');
        //     require_once(ABSPATH . 'wp-admin/includes/image.php');
        //     require_once(ABSPATH . 'wp-admin/includes/media.php');

        //     $attachment_id = media_handle_upload('store_image', $post_id);

        //     if (is_wp_error($attachment_id)) {
        //         // エラーハンドリング: 画像アップロード失敗時
        //         echo "画像のアップロードに失敗しました。";
        //     } else {
        //         // 画像が正常にアップロードされた場合、投稿に添付
        //         update_post_meta($post_id, '_thumbnail_id', $attachment_id);
        //     }
        // }



        if ($type != 'edit' && $richmenu3) {
            // update_post_meta($user_id, 'richmenu_id', $richmenu3);
        }

        // リッチメニュー更新
        if ($type != 'edit') {
            if ($richmenu3) {
                // lineconnectRichmenu::updateRichMenu($line_id, $richmenu3);
            }
        }
        return true;
    }

    /**
     * 更新
     * @return bool 
     */
    static function update_store()
    {
        // header("Content-type: application/json; charset=UTF-8");
        //         echo json_encode($_REQUEST);exit;
        // print_r($_REQUEST);exit;
        $access_token = get_option('channnel_access_token');
        $channelSecret = get_option('channnel_access_token_secret');
        $post_type = 'store';

        $type = $_REQUEST['type'];
        $line_id = $_REQUEST['line_id'];
        //        unset($_REQUEST['line_id']);

        // 本登録時はリッチメニューも更新
        $richmenu3 = get_option('richmenu_3');

        $title = isset($_REQUEST['store_name']) ? $_REQUEST['store_name'] : ''; // お名前はタイトルで使用する
        unset($_REQUEST['displayName']);
        // line_idで存在チェック
        $args = [
            'post_type' => array('line_user'), //投稿タイプを指定
            'posts_per_page' => '-1', //取得する投稿件数を指定
            'meta_key' => 'line_id', //カスタムフィールドのキーを指定
            'meta_value' => $line_id, //カスタムフィールドの値を指定
            'orderby' => 'meta_value', //ソートの基準を指定
            'order' => 'asc' //ソート方法を指定（昇順：asc, 降順：desc）
        ];
        $the_query = new WP_Query($args);

        if (!$the_query->have_posts()) {
            return false;
        }



        $args = array(
            'post_type' => array('store'), //投稿タイプを指定
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
            }
        } else {
            // LINE IDで登録がない場合は
            // 登録スタッフで検索
            // なければメンバーを取得
            $args_line_user = [
                'post_type' => array('line_user'), //投稿タイプを指定
                'posts_per_page' => '-1', //取得する投稿件数を指定
                'meta_key' => 'line_id', //カスタムフィールドのキーを指定
                'meta_value' => $line_id, //カスタムフィールドの値を指定
                'orderby' => 'date', //ソートの基準を指定
                'order' => 'asc' //ソート方法を指定（昇順：asc, 降順：desc）
            ];
            $query = new WP_Query($args_line_user);
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    $user_id = get_the_ID();
                    // ユーザーIDからメンバー情報を取得
                    $args_store_user = [
                        'post_type' => array('storeUser'), //投稿タイプを指定
                        'posts_per_page' => '-1', //取得する投稿件数を指定
                        'meta_key' => 'user_id', //カスタムフィールドのキーを指定
                        'meta_value' => $user_id, //カスタムフィールドの値を指定
                        'orderby' => 'date', //ソートの基準を指定
                        'order' => 'asc' //ソート方法を指定（昇順：asc, 降順：desc）
                    ];
                    $store_user_query = new WP_Query($args_store_user);
                    if ($store_user_query->have_posts()) {
                        while ($store_user_query->have_posts()) {
                            $store_user_query->the_post();
                            $store_user_id = get_the_ID();
                            $store_id = get_post_meta($store_user_id, 'store_id', true);
                            $store_name = get_post_meta($store_id, 'store_name', true);
                            $point_rate = get_post_meta($store_id, 'point_rate', true);
                            $store_point = get_post_meta($store_id, 'store_point', true);
                            $return['store_point'] = $store_point;
                            $return['store_name'] = $store_name;
                            $return['point_rate'] = $point_rate;
                            $return['store_id'] = $store_id;
                        }
                        wp_reset_postdata();
                        header("Content-type: application/json; charset=UTF-8");
                        echo json_encode($return);
                    }
                }
                wp_reset_postdata();
                header("Content-type: application/json; charset=UTF-8");
                echo json_encode($return);
                exit;
            }
            echo 'NOT_REGISTERED';
            exit;
        }

        if (is_array($_REQUEST)) {
            foreach ($_REQUEST as $key_name => $key_value) {
                update_post_meta($post_id, $key_name, $key_value);
            }
        }

        // 画像がアップロードされた場合の処理
        $store_image = isset($_FILES['store_image']) ? $_FILES['store_image'] : '';
        if ($store_image) {
            require_once ABSPATH . 'wp-admin' . '/includes/image.php';
            require_once ABSPATH . 'wp-admin' . '/includes/file.php';
            require_once ABSPATH . 'wp-admin' . '/includes/media.php';
            $thumbnail_id = media_handle_upload('store_image', $post_id);
            set_post_thumbnail($post_id, $thumbnail_id);
            // header("Content-type: application/json; charset=UTF-8");
            //     echo json_encode([$thumbnail_id]);exit;
        }

        return true;
    }

    /**
     * ポイントを貯める画面　
     * @return void 
     */
    static function store_get_point()
    {
        $fields = [
            'line_id' => 'LINEID',
        ];
        $line_id = $_REQUEST['line_id'];
        $enabled_coupon = get_option('enabled_coupon');
        $coupon_expired_date = get_option('coupon_expired_date');

        $get_items = [];
        foreach ($fields as $input_name => $values) {
            array_push($get_items, $input_name);
        }


        // line_idからstore_idを取得
        $args = array(
            'post_type' => array('store'), //投稿タイプを指定
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
                $store_id = get_the_ID();
            }
            wp_reset_postdata();
        } else {

            // 無ければ店舗スタッフからstore_idを取得
            $args_line_user = [
                'post_type' => array('line_user'), //投稿タイプを指定
                'posts_per_page' => '1', //取得する投稿件数を指定
                'meta_key' => 'line_id', //カスタムフィールドのキーを指定
                'meta_value' => $line_id, //カスタムフィールドの値を指定
                'orderby' => 'date', //ソートの基準を指定
                'order' => 'asc' //ソート方法を指定（昇順：asc, 降順：desc）
            ];

            $query = new WP_Query($args_line_user);

            if ($query->have_posts()) {

                while ($query->have_posts()) {

                    $query->the_post();

                    $user_id = get_the_ID();
                    //         echo json_encode(['user_id'=>$line_id]);
                    // exit;
                }

                wp_reset_postdata();

                // スタッフ一覧からデータを取得
                $args_store_user = [
                    'post_type' => array('storeUser'), //投稿タイプを指定
                    'posts_per_page' => '1', //取得する投稿件数を指定
                    'meta_key' => 'user_id', //カスタムフィールドのキーを指定
                    'meta_value' => $user_id, //カスタムフィールドの値を指定
                    'orderby' => 'date', //ソートの基準を指定
                    'order' => 'asc' //ソート方法を指定（昇順：asc, 降順：desc）
                ];
                $store_user_query = new WP_Query($args_store_user);
                if ($store_user_query->have_posts()) {
                    while ($store_user_query->have_posts()) {
                        $store_user_query->the_post();
                        $store_user_id = get_the_ID();
                        $store_id = get_post_meta($store_user_id, 'store_id', true);
                    }
                } else {
                    echo json_encode([]);
                    exit;
                }
            } else {
                echo json_encode([]);
                exit;
            }
        }
        if (!$store_id) {
            echo json_encode([]);
            exit;
        }

        // 取得したstore_idから情報を取得
        $store_name = get_post_meta($store_id, 'store_name', true);
        $return['store_name'] = $store_name;
        foreach ($get_items as $item) {
            //                    echo 'item='.get_post_meta($post_id,$item);
            $item_value = get_post_meta($store_id, $item);

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
            'eccLevel' => QRCode::ECC_L,
            'outputType' => QRCode::OUTPUT_MARKUP_SVG, // SVG形式で出力
            'scale' => 3
        ]);
        // $qrcode = new QRCode($options);
        // $path = storage_path('qrcode_2.png');
        $url = plugins_url('point_use.php', dirname(dirname(__FILE__)) . '/line-members/') . '?store_id=' . $store_id;
        $qrcode = (new QRCode($options))->render($url);
        // $qrcode = (new QRCode($options))->render('https://caldo-hair.com/contents/wp-admin/post.php?post='.$post_id.'&action=edit');

        $return['qrcode'] = $qrcode;

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

    static function store_add_member()
    {
        $line_id = $_REQUEST['line_id'];
        $store_id = $_REQUEST['store_id'];
        // line_idで存在チェック
        $args = [
            'post_type' => array('line_user'), //投稿タイプを指定
            'posts_per_page' => '-1', //取得する投稿件数を指定
            'meta_key' => 'line_id', //カスタムフィールドのキーを指定
            'meta_value' => $line_id, //カスタムフィールドの値を指定
            'orderby' => 'meta_value', //ソートの基準を指定
            'order' => 'asc' //ソート方法を指定（昇順：asc, 降順：desc）
        ];
        $the_query = new WP_Query($args);

        if (!$the_query->have_posts()) {
            return false;
        } else {
            while ($the_query->have_posts()) {
                // line_idヵらユーザーIDを取得
                $the_query->the_post();
                $user_id = get_the_ID();
            }
        }

        // ユーザーIDがstore_memberに既に入っていないかどうか
        $args = [
            'post_type' => array('storeUser'), //投稿タイプを指定
            'posts_per_page' => '-1', //取得する投稿件数を指定
            'meta_key' => 'user_id', //カスタムフィールドのキーを指定
            'meta_value' => $user_id, //カスタムフィールドの値を指定
            'orderby' => 'meta_value', //ソートの基準を指定
            'order' => 'asc' //ソート方法を指定（昇順：asc, 降順：desc）
        ];
        $the_query = new WP_Query($args);
        if ($the_query->have_posts()) {
            echo 'すでに登録されているスタッフです';
            return false;
        }

        // ユーザーIDをstore_memberに登録
        $store_name = get_post_meta($store_id, 'store_name', true);
        $title = $store_name . '_' . $user_id;
        $my_post = array(
            'post_title' => $title,
            'post_type' => 'storeUser',
            'post_content' => '',
            'post_status' => 'publish', //公開ステータス
        );

        $post_id = wp_insert_post($my_post, true);
        update_post_meta($post_id, 'user_id', $user_id);
        update_post_meta($post_id, 'store_id', $store_id);
        update_post_meta($post_id, 'permission', 'スタッフ');
        // リッチメニュー更新
        $richmenu3 = get_option('richmenu_3');
        if ($richmenu3) {
            update_post_meta($user_id, 'richmenu_id', $richmenu3);
            lineconnectRichmenu::updateRichMenu($line_id, $richmenu3);
        }


        echo 'store_id=' . $store_id . ' user_id=' . $user_id . 'success';
        exit;
    }

    /**
     * ポイントのアップデート
     */
    static function store_update_point()
    {
        $update_point_num = $_REQUEST['point_num'];
        $line_id = $_REQUEST['line_id'];

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
                $the_query->the_post();
                $post_id = get_the_ID();

                $now_point = intval(get_post_meta($post_id, 'point'));
            }
        }
    }

    /**
     * QRコードによる更新
     */
    static function store_update_by_qr()
    {

        // @ini_set( 'display_errors', 1 );
        // $line_id = '';
        $line_id = $_REQUEST['line_id'];

        $name = $_REQUEST['name'];
        $value = $_REQUEST['value'];

        if (!$line_id) {
            // エラーログに追加
            $error_post = array(
                'post_title' => 'ポイントアップエラー',
                'post_type' => 'error_log',
                'post_content' => '',
                'post_status' => 'publish', //公開ステータス
                'post_author' => 1, //ユーザーID
            );

            $error_log_post_id = wp_insert_post($error_post, true);

            update_post_meta($error_log_post_id, 'point', 1); // ポイントは1からスタート

            echo 'error';
            exit;
        }

        // 1日一回ではないかどうか
        $today = date_i18n('Y-m-d');
        $today_from = $today . ' 00:00:00';
        $today_to = $today . '23:59:59';

        $args_once_a_day = [
            'post_type' => array('point_history'), //投稿タイプを指定
            'posts_per_page' => '-1', //取得する投稿件数を指定
            'orderby' => 'meta_value', //ソートの基準を指定
            'order' => 'asc', //ソート方法を指定（昇順：asc, 降順：desc）
            'date_query' => [
                [
                    'after' => $today_from,
                    'before' => $today_to,
                    'inclusive' => true
                ]
            ],
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => 'point_type',
                    'value' => 'QR'
                ],
                [
                    'key' => 'line_id',
                    'value' => $line_id
                ]
            ]
        ];
        $once_a_day_query = new WP_Query($args_once_a_day);
        if ($once_a_day_query->have_posts()) {
            echo 'pointed';
            exit;
        }
        wp_reset_postdata();

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
                $the_query->the_post();
                $post_id = get_the_ID();

                $now_value = get_post_meta($post_id, $name, true);

                $update_value = ($now_value + $value);

                update_post_meta($post_id, $name, $update_value);

                // ポイント履歴に登録
                $point_history_post = array(
                    'post_title' => 'QRによるポイント追加',
                    'post_type' => 'point_history',
                    'post_content' => '',
                    'post_status' => 'publish', //公開ステータス
                    'post_author' => 1, //ユーザーID
                );

                $point_history_post_id = wp_insert_post($point_history_post, true);
                update_post_meta($point_history_post_id, 'point_number', $value);
                update_post_meta($point_history_post_id, 'line_id', $line_id);
                update_post_meta($point_history_post_id, 'point_type', 'QR');
            }
        }
    }

    /**
     * 店舗スタッフ一覧表示
     */
    static function get_store_member()
    {
        $line_id = $_REQUEST['line_id'];
        $enabled_coupon = get_option('enabled_coupon');
        $coupon_expired_date = get_option('coupon_expired_date');
        $custom_fields = self::$fields;
        $get_items = [];
        foreach ($custom_fields as $input_name => $title) {
            array_push($get_items, $input_name);
        }

        $args = array(
            'post_type' => array('store'),
            'posts_per_page' => '-1',
            'meta_key' => 'line_id',
            'meta_value' => $line_id,
            'orderby' => 'meta_value',
            'order' => 'asc'
        );
        $the_query = new WP_Query($args);
        $html = ''; // 初期化
        if ($the_query->have_posts()) {
            while ($the_query->have_posts()) {
                $the_query->the_post();
                $store_id = get_the_ID();

                $store_member_arg = [
                    'post_type' => array('storeUser'),
                    'posts_per_page' => '-1',
                    'meta_key' => 'store_id',
                    'meta_value' => $store_id,
                    'orderby' => 'meta_value',
                    'order' => 'asc'
                ];

                $store_member_query = new WP_Query($store_member_arg);

                if ($store_member_query->have_posts()) {
                    while ($store_member_query->have_posts()) {
                        $store_member_query->the_post();
                        $member_id = get_the_ID();

                        $staff_name = get_the_title();
                        $staff_position = get_post_meta($member_id, 'position', true);
                        $line_user_id = get_post_meta($member_id, 'user_id', true);

                        if ($line_user_id) {
                            $user_line_id = get_post_meta($line_user_id, 'line_id', true);
                            $user_name = get_the_title($line_user_id);
                            $user_email = get_post_meta($line_user_id, 'email', true);
                        }

                        // フィールドが空でないかを確認
                        $user_name = !empty($user_name) ? $user_name : '未登録';
                        $user_line_id = !empty($user_line_id) ? $user_line_id : '未登録';

                        // HTMLを結合
                        $html .= '<div class="lmf-staff_block lmf-white_block">
                        <dl class="lmf-info_list">
                            <dt>名前</dt>
                            <dd class="name">' . esc_html($user_name) . '</dd>
                        </dl>
                        <p class="lmf-btn_box btn_pk btn_min">
                            <button type="button" data-href="#modal_delete" data-user_id="' . esc_html($line_user_id) . '" data-delete_staff_name="' . esc_html($user_name) . '" class="modal_delete">削除する</button>
                        </p>
                    </div>';
                    }
                    wp_reset_postdata(); // クエリ後のリセット
                }
            }
            // HTMLが空の場合にエラーメッセージを表示
            if (empty($html)) {
                $html = '<p style="text-align:center;">スタッフが登録されていません</p>';
            }
            $return['html'] = $html;
            $return['store_id'] = $store_id;
            header("Content-type: application/json; charset=UTF-8");
            echo json_encode($return);
            exit;
        } else {
            $return['html'] = '<p style="text-align:center;">権限がありません</p>';
            $return['store_id'] = 0;
            header("Content-type: application/json; charset=UTF-8");
            echo json_encode($return);
            exit;
        }
    }

    /**
     * 店舗スタッフ削除
     * @return void 
     */
    static function delete_store_member()
    {
        $user_id = $_REQUEST['user_id'];

        if (!$user_id) {
            echo 'user idが取得できませんでした';
            return false;
        }

        // user_id をカスタムフィールドとして持つユーザーを取得
        $args = array(
            'post_type'  => 'storeUser', // カスタム投稿タイプ
            'meta_query' => array(
                array(
                    'key'     => 'user_id',    // カスタムフィールドキー
                    'value'   => $user_id,     // カスタムフィールドの値
                    'compare' => '='
                )
            )
        );

        $query = new WP_Query($args);

        if (!$query->have_posts()) {
            echo '該当する投稿が見つかりませんでした。';
            return false;
        }

        // 該当する投稿を取得して削除する
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();

            // 投稿を削除する（ゴミ箱に移動）
            wp_delete_post($post_id, false);
        }

        wp_reset_postdata();

        echo 'SUCCESS';
        exit;
    }

    /**
     * richmenuのプロフィール表示
     */
    static function store_info()
    {
        $line_id = $_REQUEST['line_id'];
        $enabled_coupon = get_option('enabled_coupon');
        $coupon_expired_date = get_option('coupon_expired_date');
        // $custom_fields = custom_fields::$custom_fields;
        $custom_fields = self::$fields;
        $get_items = [];
        foreach ($custom_fields as $input_name => $title) {
            array_push($get_items, $input_name);
        }


        // line_idで絞り込み
        $args = array(
            'post_type' => array('store'), //投稿タイプを指定
            'posts_per_page' => '-1', //取得する投稿件数を指定
            'meta_key' => 'line_id', //カスタムフィールドのキーを指定
            'meta_value' => $line_id, //カスタムフィールドの値を指定
            'orderby' => 'meta_value', //ソートの基準を指定
            'order' => 'asc' //ソート方法を指定（昇順：asc, 降順：desc）
        );
        $the_query = new WP_Query($args);
        if ($the_query->have_posts()) {
            // オーナー
            while ($the_query->have_posts()) {
                $return = [];
                $the_query->the_post();
                $store_post_id = get_the_ID();
            }
        } else {
            // 登録スタッフ
            $return = [];
            header("Content-type: application/json; charset=UTF-8");
            echo json_encode($return);
            //                wp_die();
            exit;
        }

        if (!$store_post_id) {
            return false;
        }
        foreach ($get_items as $item) {
            //                    echo 'item='.get_post_meta($post_id,$item);
            $item_value = get_post_meta($store_post_id, $item);

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

        if ($enabled_coupon) {
            $post_date = get_post_time('Y-m-d');
            $coupon_expired_date = $coupon_expired_date ? $coupon_expired_date : 45;
            //                $return['coupon'] = $post_date;
            $return['coupon'] = date('Y年m月d日', strtotime('+' . $coupon_expired_date . ' day', strtotime($post_date)));
        }

        // サムネイル取得
        $return['store_image'] = '';
        if (has_post_thumbnail($store_post_id)) {
            $return['store_image'] = get_the_post_thumbnail_url($store_post_id, 'full'); // アイキャッチ画像を表示
        }

        header("Content-type: application/json; charset=UTF-8");
        echo json_encode($return);
        //                wp_die();
        exit;
    }

    /**
     * ポイント付与履歴
     */
    static function store_point_history()
    {
        $line_id = $_REQUEST['line_id'];
        $fullname = '';
        $now_point_formatted = 0;
        $liff_id_store_info = get_option('liff_id_store_info');
        $liff_id_store_point_history_month = get_option('liff_id_store_point_history_month');

        $html = '<section class="lmf-content">';

        // line_idから個人情報取得
        $args_store = [
            'post_type' => array('store'), //投稿タイプを指定
            'posts_per_page' => '-1', //取得する投稿件数を指定
            'meta_key' => 'line_id', //カスタムフィールドのキーを指定
            'meta_value' => $line_id, //カスタムフィールドの値を指定
            'orderby' => 'meta_value', //ソートの基準を指定
            'order' => 'asc' //ソート方法を指定（昇順：asc, 降順：desc）
        ];
        $store_query = new WP_Query($args_store);
        if ($store_query->have_posts()) {
            while ($store_query->have_posts()) {
                $store_query->the_post();
                $store_post_id = get_the_ID();
                $now_point = get_post_meta($store_post_id, 'store_point', true);
                $now_point_int = intval($now_point);
                $now_point_formatted = number_format($now_point_int);
            }
            wp_reset_postdata();
        } else {
            $args_line_user = [
                'post_type' => array('line_user'), //投稿タイプを指定
                'posts_per_page' => '-1', //取得する投稿件数を指定
                'meta_key' => 'line_id', //カスタムフィールドのキーを指定
                'meta_value' => $line_id, //カスタムフィールドの値を指定
                'orderby' => 'date', //ソートの基準を指定
                'order' => 'asc' //ソート方法を指定（昇順：asc, 降順：desc）
            ];
            $query = new WP_Query($args_line_user);
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    $user_id = get_the_ID();
                    // ユーザーIDからメンバー情報を取得
                    $args_store_user = [
                        'post_type' => array('storeUser'), //投稿タイプを指定
                        'posts_per_page' => '-1', //取得する投稿件数を指定
                        'meta_key' => 'user_id', //カスタムフィールドのキーを指定
                        'meta_value' => $user_id, //カスタムフィールドの値を指定
                        'orderby' => 'date', //ソートの基準を指定
                        'order' => 'asc' //ソート方法を指定（昇順：asc, 降順：desc）
                    ];
                    $store_user_query = new WP_Query($args_store_user);
                    if ($store_user_query->have_posts()) {
                        while ($store_user_query->have_posts()) {
                            $store_user_query->the_post();
                            $store_user_id = get_the_ID();
                            $store_post_id = get_post_meta($store_user_id, 'store_id', true);
                            $now_point = get_post_meta($store_post_id, 'store_point', true);
                            $now_point_int = intval($now_point);
                            $now_point_formatted = number_format($now_point_int);
                        }
                        wp_reset_postdata();
                    }
                }
                wp_reset_postdata();
            } else {
                $html .= '<p>ポイント付与履歴が取得できませんでした</p>';
                exit;
            }
        }
        $html .= '
        <div class="lmf-user_block">
        <div class="point_box">
        <p class="lmf-btn_box"><a href="https://liff.line.me/' . $liff_id_store_point_history_month . '">月別ポイント付与履歴</a></p>
        </div>
    </div>
    ';



        // line_idで絞り込み
        $args = array(
            'post_type' => array('point_history'), //投稿タイプを指定
            'posts_per_page' => '-1', //取得する投稿件数を指定
            'meta_key' => 'store_id', //カスタムフィールドのキーを指定
            'meta_value' => $store_post_id, //カスタムフィールドの値を指定
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
                $user_id = get_post_meta($post_id, 'user_id', true);
                // $user_name = get_post_meta($user_id,'user_name',true);
                $user_name = get_the_title($user_id);

                $post_date = get_the_date('Y年m月d日 H:i', $post_id);
                if ($point_type == '付与') {
                    $get_or_use_class = 'use';
                    $get_or_use_text = 'ユーザー付与';
                    $point_number = '-' . $point_number;
                } else {
                    $get_or_use_class = 'get';
                    $get_or_use_text = 'ユーザー使用';
                    $point_number = '+' . $point_number;
                }
                // html生成
                $html .= '<li class="' . $get_or_use_class . '">';
                $html .= '<span class="icon">ポイント' . $get_or_use_text . '</span>';
                $html .= '<em class="data">' . $post_date . '</em>';
                $html .= '<b class="title">' . $user_name . '</b>';
                $html .= '<p class="point">' . $point_number . 'pt</p>';
                $html .= '</li>';

                header("Content-type: application/json; charset=UTF-8");
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
     * 月別ポイント付与履歴
     */
    static function store_point_history_month()
    {
        $line_id = $_REQUEST['line_id'];
        $year = isset($_REQUEST['year']) && !empty($_REQUEST['year']) ? $_REQUEST['year'] : date('Y');
        $month = isset($_REQUEST['month']) && !empty($_REQUEST['month']) ? $_REQUEST['month'] : date('n');
        if ($month == 12) {
            $next_month = 1;
            $next_year = $year + 1;
        } else {
            $next_month = $month + 1;
            $next_year = $year;
        }

        // 先月の計算
        if ($month == 1) {
            $prev_month = 12;
            $prev_year = $year - 1;
        } else {
            $prev_month = $month - 1;
            $prev_year = $year;
        }
        $fullname = '';
        $now_point_formatted = 0;

        $html = '<section class="lmf-content">';

        // line_idから店舗ID
        $args_store = [
            'post_type' => array('store'), //投稿タイプを指定
            'posts_per_page' => '-1', //取得する投稿件数を指定
            'meta_key' => 'line_id', //カスタムフィールドのキーを指定
            'meta_value' => $line_id, //カスタムフィールドの値を指定
            'orderby' => 'meta_value', //ソートの基準を指定
            'order' => 'asc' //ソート方法を指定（昇順：asc, 降順：desc）
        ];
        $store_query = new WP_Query($args_store);
        if ($store_query->have_posts()) {
            while ($store_query->have_posts()) {
                $store_query->the_post();
                $store_post_id = get_the_ID();
                $now_point = get_post_meta($store_post_id, 'store_point', true);
                $now_point_int = intval($now_point);
                $now_point_formatted = number_format($now_point_int);
            }
            wp_reset_postdata();
        } else {
            $args_line_user = [
                'post_type' => array('line_user'), //投稿タイプを指定
                'posts_per_page' => '-1', //取得する投稿件数を指定
                'meta_key' => 'line_id', //カスタムフィールドのキーを指定
                'meta_value' => $line_id, //カスタムフィールドの値を指定
                'orderby' => 'date', //ソートの基準を指定
                'order' => 'asc' //ソート方法を指定（昇順：asc, 降順：desc）
            ];
            $query = new WP_Query($args_line_user);
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    $user_id = get_the_ID();
                    // ユーザーIDからメンバー情報を取得
                    $args_store_user = [
                        'post_type' => array('storeUser'), //投稿タイプを指定
                        'posts_per_page' => '-1', //取得する投稿件数を指定
                        'meta_key' => 'user_id', //カスタムフィールドのキーを指定
                        'meta_value' => $user_id, //カスタムフィールドの値を指定
                        'orderby' => 'date', //ソートの基準を指定
                        'order' => 'asc' //ソート方法を指定（昇順：asc, 降順：desc）
                    ];
                    $store_user_query = new WP_Query($args_store_user);
                    if ($store_user_query->have_posts()) {
                        while ($store_user_query->have_posts()) {
                            $store_user_query->the_post();
                            $store_user_id = get_the_ID();
                            $store_post_id = get_post_meta($store_user_id, 'store_id', true);
                            $now_point = get_post_meta($store_post_id, 'store_point', true);
                            $now_point_int = intval($now_point);
                            $now_point_formatted = number_format($now_point_int);
                        }
                        wp_reset_postdata();
                    }
                }
                wp_reset_postdata();
            } else {
                $html .= '<p>ポイント付与履歴が取得できませんでした</p>';
                exit;
            }
        }

        // line_idで絞り込み
        $args = array(
            'post_type' => array('point_history'), //投稿タイプを指定
            'posts_per_page' => '-1', //取得する投稿件数を指定
            'meta_key' => 'store_id', //カスタムフィールドのキーを指定
            'meta_value' => $store_post_id, //カスタムフィールドの値を指定
            'orderby' => 'meta_value', //ソートの基準を指定
            'order' => 'asc', //ソート方法を指定（昇順：asc, 降順：desc）
            'date_query' => array( // 日付クエリを追加
                array(
                    'year'  => $year, // ユーザー指定の年を使用
                    'month' => $month, // ユーザー指定の月を使用
                ),
            ),
        );
        $the_query = new WP_Query($args);
        if ($the_query->have_posts()) {
            // お客様へのポイント付与
            $customer_points_granted = 0;
            // お客様が使ったポイント
            $customer_points_used = 0;
            // 運営からもらったポイント
            $points_received_from_admin = 0;

            while ($the_query->have_posts()) {
                $return = [];
                $the_query->the_post();
                $post_id = get_the_ID();

                $point_number = get_post_meta($post_id, 'point_number', true);
                if (!$point_number) {
                    $point_number = 0;
                }
                $point_type = get_post_meta($post_id, 'point_type', true);
                if ($point_type == '付与') {
                    $customer_points_granted += $point_number;
                } elseif ($point_type == '使用') {
                    $customer_points_used += $point_number;
                } elseif ($point_type == '運営') {
                    $points_received_from_admin += $point_number;
                }
                //                wp_die();
            }
            $points_received_from_admin = $customer_points_granted - $customer_points_used;
            if ($customer_points_granted > 0) {
                $customer_points_granted = '-' . $customer_points_granted;
            }

            wp_reset_postdata();
            $html .= '<div class="lmf-user_block">
            <div class="point_box" style="display:none;">
                <em class="label">保有ポイント</em>
                <b class="points"><span class="point">' . $now_point_formatted . '</span><span class="unit">pt</span></b>
            </div>
        </div>
        <div class="lmf-record_block lmf-white_block">
            <h2 class="lmf-title_bar sky"><em class="label">' . $year . '年' . $month . '月</em></h2>
            <ul class="lmf-record_list">
                <li class="use">
                    <b class="title">お客様へのポイント付与</b>
                    <p class="point">' . $customer_points_granted . 'pt</p>
                </li>
                <li class="get">
                    <b class="title">お客様からのポイント付与</b>
                    <p class="point">' . $customer_points_used . 'pt</p>
                </li>
                <li class="get">
                    <b class="title">運営からのポイント付与</b>
                    <p class="point">' . $points_received_from_admin . 'pt</p>
                </li>
            </ul>
        </div>
        <ul class="lmf-pnavi_list clearfix">
        <li class="prev"><a href="?year=' . $prev_year . '&month=' . $prev_month . '">先月</a></li>
        <li class="next"><a href="?year=' . $next_year . '&month=' . $next_month . '">次月</a></li>
        </ul>';
            // echo $html;
            // exit;
        } else {
            $html .= '<div class="lmf-user_block">
            <div class="point_box" style="display:none;">
                <em class="label">保有ポイント</em>
                <b class="points"><span class="point">' . $now_point_formatted . '</span><span class="unit">pt</span></b>
            </div>
        </div><div class="lmf-record_block lmf-white_block">
            <h2 class="lmf-title_bar sky"><em class="label">' . $year . '年' . $month . '月</em></h2>
            <ul class="lmf-record_list">
                <li class="use">
                    <b class="title">お客様へのポイント付与</b>
                    <p class="point">0pt</p>
                </li>
                <li class="get">
                    <b class="title">お客様からのポイント付与</b>
                    <p class="point">0pt</p>
                </li>
                <li class="get">
                    <b class="title">運営からのポイント付与</b>
                    <p class="point">0pt</p>
                </li>
            </ul>
        </div>
            <ul class="lmf-pnavi_list clearfix">
        <li class="prev"><a href="?year=' . $prev_year . '&month=' . $prev_month . '">先月</a></li>
        <li class="next"><a href="?year=' . $next_year . '&month=' . $next_month . '">次月</a></li>
        </ul>';
        }
        $html .= '<p class="lmf-btn_box btn_gy btn_small"><a href="invoice.php?line_id=' . $line_id . '&year=' . $year . '&month=' . $month . '">請求書</a></p>';
        $html .= '<p class="lmf-btn_box btn_gy btn_small">
        <a href="javascript:void(0);" onclick="copyToClipboard('.plugins_url('line-members/store/').'\'invoice.php?line_id=' . $line_id . '&year=' . $year . '&month=' . $month . '\')">URLをコピー</a></p>';
        $html .= '<script>
        function copyToClipboard(url) {
            const fullUrl = window.location.origin + "/" + url; // 現在のドメインを含むフルURLを作成
            navigator.clipboard.writeText(fullUrl)
                .then(() => {
                    alert("URLをコピーしました: ");
                })
                .catch(err => {
                    console.error("URLのコピーに失敗しました", err);
                });
        }
    </script>';
        echo $html;
        exit;
    }

    /**
     * ポイント付与
     */
    static function store_give_point()
    {
        $line_id = $_REQUEST['line_id'];
        $store_id = $_REQUEST['store_id'];
        $user_id = $_REQUEST['user_id'];
        $price = $_REQUEST['price'];
        $get_point = $_REQUEST['get_point'];
        $point_rate = isset($_REQUEST['value_of_point_rate']) ? $_REQUEST['value_of_point_rate'] : '1';
        $return = [];

        try {
            // line_idからstore_idを取得
            $args_store = [
                'post_type' => array('store'), //投稿タイプを指定
                'posts_per_page' => '-1', //取得する投稿件数を指定
                'meta_key' => 'line_id', //カスタムフィールドのキーを指定
                'meta_value' => $line_id, //カスタムフィールドの値を指定
                'orderby' => 'meta_value', //ソートの基準を指定
                'order' => 'asc' //ソート方法を指定（昇順：asc, 降順：desc）
            ];
            $store_query = new WP_Query($args_store);
            if ($store_query->have_posts()) {
                while ($store_query->have_posts()) {
                    $store_query->the_post();
                    $store_post_id = get_the_ID();
                }
                wp_reset_postdata();
            } else {
                $args_line_user = [
                    'post_type' => array('line_user'), //投稿タイプを指定
                    'posts_per_page' => '-1', //取得する投稿件数を指定
                    'meta_key' => 'line_id', //カスタムフィールドのキーを指定
                    'meta_value' => $line_id, //カスタムフィールドの値を指定
                    'orderby' => 'date', //ソートの基準を指定
                    'order' => 'asc' //ソート方法を指定（昇順：asc, 降順：desc）
                ];
                $query = new WP_Query($args_line_user);
                if ($query->have_posts()) {
                    while ($query->have_posts()) {
                        $query->the_post();
                        $line_user_id = get_the_ID();
                        // ユーザーIDからメンバー情報を取得
                        $args_store_user = [
                            'post_type' => array('storeUser'), //投稿タイプを指定
                            'posts_per_page' => '-1', //取得する投稿件数を指定
                            'meta_key' => 'user_id', //カスタムフィールドのキーを指定
                            'meta_value' => $line_user_id, //カスタムフィールドの値を指定
                            'orderby' => 'date', //ソートの基準を指定
                            'order' => 'asc' //ソート方法を指定（昇順：asc, 降順：desc）
                        ];
                        $store_user_query = new WP_Query($args_store_user);
                        if ($store_user_query->have_posts()) {
                            while ($store_user_query->have_posts()) {
                                $store_user_query->the_post();
                                $store_user_id = get_the_ID();
                                $store_post_id = get_post_meta($store_user_id, 'store_id', true);
                            }
                            wp_reset_postdata();
                        }
                    }
                    wp_reset_postdata();
                } else {
                    exit;
                }
            }
            // return $return['store_post_id'] = $store_post_id;
            // return $return;
            if (!$store_post_id) {
                return false;
            }
            // 店舗が現在所持しているポイント
            $store_point = get_post_meta($store_post_id, 'store_point', true);
            $store_name = get_post_meta($store_post_id, 'store_name', true);
            $point_rate = get_post_meta($store_post_id, 'point_rate', true);
            if (!$store_point) {
                $store_point = 0;
            }

            // ユーザが所持しているポイント
            $user_point = get_post_meta($user_id, 'point', true);
            // return $return['store_post_id'] = $user_id;

            // 店舗所持ポイントから獲得ポイントをマイナス
            $store_point = $store_point - $get_point;

            // 店舗所持ポイントをアップデート
            update_post_meta($store_post_id, 'store_point', $store_point);

            // ユーザーが所持しているポイントに獲得ポイントを足す
            $user_point = $user_point + $get_point;
            update_post_meta($user_id, 'point', $user_point);
            $one_year_later = date_i18n('Y年m月d日', strtotime('+1 year'));
            update_post_meta($user_id, 'point_limit_date', $one_year_later);

            // ポイント履歴も更新
            $point_history = [
                'post_title' => $store_name,
                'post_type' => 'point_history',
                'post_content' => '',
                'post_status' => 'publish', //公開ステータス
            ];
            $post_id = wp_insert_post($point_history, true);
            update_post_meta($post_id, 'point_number', $get_point);
            update_post_meta($post_id, 'point_rate', $point_rate);
            update_post_meta($post_id, 'price', $price);
            update_post_meta($post_id, 'user_id', $user_id);
            update_post_meta($post_id, 'store_id', $store_id);
            update_post_meta($post_id, 'point_type', '付与');


            header("Content-type: application/json; charset=UTF-8");
            echo json_encode($return);
            exit;
        } catch (Exception $e) {
            header("Content-type: application/json; charset=UTF-8");
            $return['error'] = $e->getMessage();
            return $return;
        }
    }

    /**
     * ポイント付与
     */
    static function get_store_info()
    {
        $line_id = $_REQUEST['line_id'];
        $return = [];
        // line_idからstore_idを取得
        $args_store = [
            'post_type' => array('store'), //投稿タイプを指定
            'posts_per_page' => '-1', //取得する投稿件数を指定
            'meta_key' => 'line_id', //カスタムフィールドのキーを指定
            'meta_value' => $line_id, //カスタムフィールドの値を指定
            'orderby' => 'meta_value', //ソートの基準を指定
            'order' => 'asc' //ソート方法を指定（昇順：asc, 降順：desc）
        ];
        $store_query = new WP_Query($args_store);
        if ($store_query->have_posts()) {
            while ($store_query->have_posts()) {
                $store_query->the_post();
                $store_post_id = get_the_ID();
            }
            wp_reset_postdata();
        } else {
            // なければメンバーを取得
            $args_line_user = [
                'post_type' => array('line_user'), //投稿タイプを指定
                'posts_per_page' => '-1', //取得する投稿件数を指定
                'meta_key' => 'line_id', //カスタムフィールドのキーを指定
                'meta_value' => $line_id, //カスタムフィールドの値を指定
                'orderby' => 'date', //ソートの基準を指定
                'order' => 'asc' //ソート方法を指定（昇順：asc, 降順：desc）
            ];

            $query = new WP_Query($args_line_user);
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    $user_id = get_the_ID();

                    // ユーザーIDからメンバー情報を取得
                    $args_store_user = [
                        'post_type' => array('storeUser'), //投稿タイプを指定
                        'posts_per_page' => '-1', //取得する投稿件数を指定
                        'meta_key' => 'user_id', //カスタムフィールドのキーを指定
                        'meta_value' => $user_id, //カスタムフィールドの値を指定
                        'orderby' => 'date', //ソートの基準を指定
                        'order' => 'asc' //ソート方法を指定（昇順：asc, 降順：desc）
                    ];
                    $store_user_query = new WP_Query($args_store_user);
                    if ($store_user_query->have_posts()) {
                        while ($store_user_query->have_posts()) {
                            $store_user_query->the_post();
                            $store_user_id = get_the_ID();
                            $store_post_id = get_post_meta($store_user_id, 'store_id', true);
                        }
                        wp_reset_postdata();
                    }
                }
                wp_reset_postdata();
            }
        }

        if (!$store_post_id) {
            return false;
        }
        $store_point = get_post_meta($store_post_id, 'store_point', true);
        $store_name = get_post_meta($store_post_id, 'store_name', true);
        $point_rate = get_post_meta($store_post_id, 'point_rate', true);
        $return['store_point'] = $store_point;
        $return['store_name'] = $store_name;
        $return['point_rate'] = $point_rate;
        $return['store_id'] = $store_post_id;
        header("Content-type: application/json; charset=UTF-8");
        echo json_encode($return);
    }

    static function store_get_latest_point_info()
    {

        $line_id = $_REQUEST['line_id'];
        $enabled_coupon = get_option('enabled_coupon');
        $coupon_expired_date = get_option('coupon_expired_date');

        // line_idからユーザーIDを取得
        $args_store = [
            'post_type' => array('store'), //投稿タイプを指定
            'posts_per_page' => '-1', //取得する投稿件数を指定
            'meta_key' => 'line_id', //カスタムフィールドのキーを指定
            'meta_value' => $line_id, //カスタムフィールドの値を指定
            'orderby' => 'meta_value', //ソートの基準を指定
            'order' => 'asc' //ソート方法を指定（昇順：asc, 降順：desc）
        ];
        $store_query = new WP_Query($args_store);
        if ($store_query->have_posts()) {
            while ($store_query->have_posts()) {
                $store_query->the_post();
                $store_post_id = get_the_ID();
            }
            wp_reset_postdata();
        } else {
            // なければメンバーを取得
            $args_line_user = [
                'post_type' => array('line_user'), //投稿タイプを指定
                'posts_per_page' => '-1', //取得する投稿件数を指定
                'meta_key' => 'line_id', //カスタムフィールドのキーを指定
                'meta_value' => $line_id, //カスタムフィールドの値を指定
                'orderby' => 'date', //ソートの基準を指定
                'order' => 'asc' //ソート方法を指定（昇順：asc, 降順：desc）
            ];

            $query = new WP_Query($args_line_user);
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    $user_id = get_the_ID();

                    // ユーザーIDからメンバー情報を取得
                    $args_store_user = [
                        'post_type' => array('storeUser'), //投稿タイプを指定
                        'posts_per_page' => '-1', //取得する投稿件数を指定
                        'meta_key' => 'user_id', //カスタムフィールドのキーを指定
                        'meta_value' => $user_id, //カスタムフィールドの値を指定
                        'orderby' => 'date', //ソートの基準を指定
                        'order' => 'asc' //ソート方法を指定（昇順：asc, 降順：desc）
                    ];
                    $store_user_query = new WP_Query($args_store_user);
                    if ($store_user_query->have_posts()) {
                        while ($store_user_query->have_posts()) {
                            $store_user_query->the_post();
                            $store_user_id = get_the_ID();
                            $store_post_id = get_post_meta($store_user_id, 'store_id', true);
                        }
                        wp_reset_postdata();
                    }
                }
                wp_reset_postdata();
            }
        }


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
                    'key' => 'store_id', // user_id フィールド
                    'value' => $store_post_id, // 特定の user_id を指定
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
                $line_user_id = get_post_meta($post_point_history_id, 'user_id', true);
                $point_limit_date = get_post_meta($line_user_id, 'point_limit_date', true);
                // if($point_limit_date) {
                //     $date = new DateTime($point_limit_date);
                //     $point_limit_date = $date->format('Y年n月j日');
                // }

                $user_name = get_the_title($line_user_id);

                $return['user_name'] = $user_name;
                $return['latest_point'] = $point_number;
                $return['store_id'] = $store_id;
                $return['point_limit_date'] = $point_limit_date;


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
            if ($function_name == 'register_store' || $function_name == 'update_store') {
                register_rest_route('wp/v2', '/' . $function_name, [
                    'methods' => 'POST',
                    'permission_callback' => '__return_true',
                    'callback' => ['endpointsStore', $function_name],
                ]);
            } else {
                register_rest_route('wp/v2', '/' . $function_name, [
                    'methods' => 'GET',
                    'permission_callback' => '__return_true',
                    'callback' => ['endpointsStore', $function_name],
                ]);
            }
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
