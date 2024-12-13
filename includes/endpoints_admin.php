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

class endpointsAdmin
{
    static $endpoint_functions = [
        'admin_update_point',
    ];

    static $fields = [
        'store_name' => '店舗名',
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


    static function admin_update_point()
    {
        if(
            isset($_GET['point']) && !empty($_GET['point']) &&
            isset($_GET['user_id']) && !empty($_GET['user_id']) 
        ) {
            $point = $_GET['point'];
            $user_id = $_GET['user_id'];
    
            update_post_meta($user_id,'point',$point);
            echo 'success';
            exit;
        }
        echo 'ERROR';
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
            register_rest_route('wp/v2', '/' . $function_name, [
                'methods' => 'GET',
                'permission_callback' => '__return_true',
                'callback' => ['endpointsAdmin', $function_name],
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
