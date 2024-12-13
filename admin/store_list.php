<?php
require_once('../vendor/autoload.php'); //LINE BOT SDKを読み込み
require_once('../../../../wp-load.php'); //WordPressの基本機能を読み込み
require_once('../line-members.php'); //LINE Connectを読み込み
require_once('../includes/html.php');
require_once('./adminController.php');
class adminStoreList extends adminController 
{

    public function index()
{
    // 店舗一覧
    $args = array(
        'post_type' => array('store'), //投稿タイプを指定
        'posts_per_page' => '-1', //取得する投稿件数を指定
        'orderby' => 'date', //投稿の日付を基準にソート
        'order' => 'desc' //最新の投稿を取得するために降順にソート
    );
    $store_html = '';
    $the_query = new WP_Query($args);
    $counter = 0; // カウンタを初期化
    if ($the_query->have_posts()) {
        while ($the_query->have_posts()) {
            $the_query->the_post();
            $store_id = get_the_ID();
            $store_name = get_post_meta($store_id, 'store_name', true);
            $status = get_post_meta($store_id, 'status', true);


            // カウンタが偶数の場合に 'tbd' クラスを追加
            $class = ($status ==1) ? 'lma-user_box' : 'lma-user_box tbd';

            $store_html .= '<li>
            <div class="' . $class . '">
                <div class="user_info">
                    <h3 class="name">' . $store_name .'</h3>
                </div>
                <div class="lma-btn_box btn_list">
                    <a class="gy" href="store_detail.php?store_id=' . $store_id . '">明細確認</a>
                    <a class="lgy" href="store_edit.php?store_id=' . $store_id . '">加盟店情報修正</a>
                    <a class="gy" href="staff_list.php?store_id=' . $store_id . '">スタッフ管理</a>
                    <a class="bu" href="delete.php?type=store&store_id=' . $store_id . '">削除</a>
                </div>
            </div>
        </li>';

            $counter++; // カウンタをインクリメント
        }
    }

    include './view/store_list.php';
}
}
$adminStoreList = new adminStoreList();
$adminStoreList->index();
