<?php
/*
Plugin Name: LINE MEMBERS
Plugin URI: /
Description: line連携
Version: 1.0
Author: GYPSY-STYLE
Author URI:
*/
require_once (plugin_dir_path(__FILE__ ).'includes/functions.php');
require_once (plugin_dir_path(__FILE__ ).'includes/settings.php');
require_once (plugin_dir_path(__FILE__ ).'includes/setting_line_user.php');
require_once (plugin_dir_path(__FILE__ ).'includes/setting_error_log.php');
require_once (plugin_dir_path(__FILE__ ).'includes/setting_entry_history.php');
require_once (plugin_dir_path(__FILE__ ).'includes/setting_store_member.php');
require_once (plugin_dir_path(__FILE__ ).'includes/setting_store_banner.php');
require_once (plugin_dir_path(__FILE__ ).'includes/setting_event.php');
require_once (plugin_dir_path(__FILE__ ).'includes/setting_event_checkin.php');
require_once (plugin_dir_path(__FILE__ ).'includes/endpoints.php');
require_once (plugin_dir_path(__FILE__ ).'includes/endpoints_store.php');
require_once (plugin_dir_path(__FILE__ ).'includes/endpoints_admin.php');
if ( ! defined( 'ABSPATH' ) ) exit;
class lineMembers {

    const META_KEY__LINE = 'line_user';

    const FOLLOW_MESSAGE_TITLE = 'ご登録ありがとうございます';

    const FOLLOW_MESSAGE_BODY = 'こちらから本登録をお願いします';

    const UNLINK_MESSAGE_TITLE = '解除しました';

    const UNLINK_MESSAGE_BODY = 'ご利用ありがとうございました';


    public function __construct()
    {
        // 管理画面設定
        add_action('admin_head',['settings','add_javascript_css']);
        add_action('admin_menu',['settings','create_sub_menu']);


        // カスタム投稿タイプ作成
        // line user
        add_action('init',['settingLineUser','set_line_user_post_type']);
        add_action('admin_menu',['settingLineUser','create_line_user_custom_fields']);
        add_action('save_post', ['settingLineUser','save_custom_fields']);
        // エラーログ関連
        add_action('init',['settingErrorLog','set_error_log_post_type']);
        add_action('admin_menu',['settingErrorLog','create_error_log_custom_fields']);
        add_action('save_post', ['settingErrorLog','save_custom_fields'] );

        // 店舗バナー関連
        add_action('init',['settingStoreBanner','set_store_banner_post_type']);
        add_action('admin_menu',['settingStoreBanner','create_store_banner_custom_fields']);
        add_action('save_post', ['settingStoreBanner','save_custom_fields'] );

        // 申し込み履歴関連
        add_action('init',['settingEntryHistory','set_entry_history_post_type']);
        add_action('admin_menu',['settingEntryHistory','create_entry_history_custom_fields']);
        add_action('save_post', ['settingEntryHistory','save_custom_fields'] );

        // 店舗スタッフ
        add_action('init',['settingStoreMember','set_store_post_type']);
        add_action('admin_menu',['settingStoreMember','create_store_user_custom_fields']);
        add_action('save_post', ['settingStoreMember','save_custom_fields'] );

        // イベント
        add_action('init',['settingEvent','set_event_post_type']);
        add_action('admin_menu',['settingEvent','create_event_custom_fields']);
        add_action('save_post', ['settingEvent','save_custom_fields'] );
        add_action('init', ['settingEvent', 'register_taxonomies']);
        // イベントタグフィールド追加
        add_filter('event_tag_add_form_fields', ['settingEvent','add_event_tag_custom_fields_create']);
        add_filter('event_tag_edit_form_fields', ['settingEvent','add_event_tag_custom_fields']);
        add_filter('edited_event_tag', ['settingEvent','save_event_tag_custom_fields']);
        

        // イベントチェックイン
        add_action('init',['settingEventCheckin','set_event_checkin_post_type']);
        add_action('admin_menu',['settingEventCheckin','create_event_checkin_custom_fields']);
        add_action('save_post', ['settingEventCheckin','save_custom_fields'] );
        

        add_action('add_metaboxes',['settings','set_line_user_custom_fields']);
        add_action('admin_init', ['settings','register_custom_setting']);
        

        add_filter( 'manage_edit-line_user_columns', ['settings','line_user_columns'] );
        add_action( 'manage_posts_custom_column', ['settings','line_user_add_column'], 10, 2 );

        // クイック編集の処理
        add_action( 'quick_edit_custom_box', ['settings','line_user_edit_quick_edit'], 10, 2 );
        add_action( 'bulk_edit_custom_box', ['settings','line_user_edit_quick_edit'], 10, 2 );
        add_action( 'save_post', ['settings','line_user_quick_edit_save'] );
        add_action( 'admin_enqueue_scripts', ['settings','replyToEnqueueResources'] );
        add_action( 'admin_print_footer_scripts', ['settings','replyToEnqueueResourcesFooter'],1000 );

        // 一括処理
//        add_filter( 'bulk_actions-edit-line_user', ['settings','line_user_bulk_actions'] );
//        add_filter( 'handle_bulk_actions-edit-line_user', ['settings','line_user_handle_bulk_actions'] );

        // 非同期のエンドポイント作成
        add_action('wp_ajax_nopriv_register',['endpoints','update_line_user']);
        add_action('wp_head', ['endpoints','set_js_params']);
        add_action('rest_api_init', ['endpoints','set_rest_api']);
        add_action('init', ['endpoints','add_cors_http_header']);

        add_action('rest_api_init', ['endpointsStore','set_rest_api']);

        add_action('rest_api_init', ['endpointsAdmin','set_rest_api']);
    }
}

$GLOBALS['lineconnect'] = new lineMembers;