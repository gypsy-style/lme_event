<?php
require_once('../vendor/autoload.php'); //LINE BOT SDKを読み込み
require_once('../../../../wp-load.php'); //WordPressの基本機能を読み込み
require_once('../line-members.php'); //LINE Connectを読み込み
require_once('../includes/html.php');
require_once('./adminController.php');
class adminStaffList extends adminController
{

    public function index()
{
    if(!isset($_GET['store_id']) || empty($store_id))
    {
        // 店舗一覧へリダイレクト
    }
    $store_id = $_GET['store_id'];
    // 店舗一覧
    $args = array(
        'post_type' => array('storeUser'), //投稿タイプを指定
        'posts_per_page' => '-1', //取得する投稿件数を指定
        'meta_key' => 'store_id', //カスタムフィールドのキーを指定
            'meta_value' => $store_id, //カスタムフィールドの値を指定
        'orderby' => 'date', //投稿の日付を基準にソート
        'order' => 'desc' //最新の投稿を取得するために降順にソート
    );
    $store_name = get_post_meta($store_id,'store_name',true);
    $staff_html = '';
    $the_query = new WP_Query($args);
    $counter = 0; // カウンタを初期化
    if ($the_query->have_posts()) {
        while ($the_query->have_posts()) {
            $the_query->the_post();
            $store_member = get_the_ID();
            $user_id = get_post_meta($store_member, 'user_id', true);
            $user_name = get_the_title($user_id);
            $line_id = get_post_meta($user_id, 'line_id', true);

            // カウンタが偶数の場合に 'tbd' クラスを追加
            $class = ($counter % 2 === 0) ? 'lma-user_box tbd' : 'lma-user_box';

            $staff_html .= '<li>
            <div class="lma-user_box">
                <div class="user_info">
                    <h3 class="name">'.$user_name.'</h3>
                    <p class="line_id">'.$line_id.'</p>
                </div>
                <div class="lma-btn_box btn_min">
                    <a class="bu" href="delete.php?type=storeUser&store_user_id=' . $store_member . '">削除</a>
                </div>
            </div>
        </li>';

            $counter++; // カウンタをインクリメント
        }
    }

    include './view/staff_list.php';
}
}
$adminStaffList = new adminStaffList();
$adminStaffList->index();
