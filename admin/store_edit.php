<?php
require_once('../vendor/autoload.php'); //LINE BOT SDKを読み込み
require_once('../../../../wp-load.php'); //WordPressの基本機能を読み込み
require_once('../line-members.php'); //LINE Connectを読み込み
require_once('../includes/html.php');
require_once('./adminController.php');
class adminStoreEdit extends adminController 
{

    private $store_kind = [
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

    public function index()
{
    $store_kind_list = $this->store_kind;
    $store_id = $_GET['store_id'];
    $store_name = get_post_meta($store_id, 'store_name', true);
    $status = get_post_meta($store_id, 'status', true);
    $store_kind = get_post_meta($store_id, 'store_kind', true);
    $zip1 = get_post_meta($store_id, 'zip1', true);
    $zip2 = get_post_meta($store_id, 'zip2', true);
    $address = get_post_meta($store_id, 'address', true);
    $phone_number = get_post_meta($store_id, 'phone_number', true);
    $business_hours = get_post_meta($store_id, 'business_hours', true);
    $regular_holiday = get_post_meta($store_id, 'regular_holiday', true);
    $homepage = get_post_meta($store_id, 'homepage', true);
    $instagram = get_post_meta($store_id, 'instagram', true);
    $official_line = get_post_meta($store_id, 'official_line', true);
    $person_in_charge = get_post_meta($store_id, 'person_in_charge', true);
    $email = get_post_meta($store_id, 'email', true);
    $display_button = get_post_meta($store_id, 'display_button', true);
    $line_id = get_post_meta($store_id, 'line_id', true);
    $store_point = get_post_meta($store_id, 'store_point', true);
    $message = get_post_meta($store_id, 'message', true);

    

    include './view/store_edit.php';
}
}
$adminStoreEdit = new adminStoreEdit();
$adminStoreEdit->index();
