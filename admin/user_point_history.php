<?php
require_once('../vendor/autoload.php'); //LINE BOT SDKを読み込み
require_once('../../../../wp-load.php'); //WordPressの基本機能を読み込み
require_once('../line-members.php'); //LINE Connectを読み込み
require_once('../includes/html.php');
require_once('./adminController.php');
class adminUserPointHistory extends adminController
{

    public function index()
{
    if(!isset($_GET['user_id']) || empty($_GET['user_id']))
    {
        // 店舗一覧へリダイレクト
    }
    $user_id = $_GET['user_id'];
    // 店舗一覧
    $args = array(
        'post_type' => array('point_history'), //投稿タイプを指定
        'posts_per_page' => '-1', //取得する投稿件数を指定
        'meta_key' => 'user_id', //カスタムフィールドのキーを指定
            'meta_value' => $user_id, //カスタムフィールドの値を指定
        'orderby' => 'date', //投稿の日付を基準にソート
        'order' => 'desc' //最新の投稿を取得するために降順にソート
    );
    $user_name = get_the_title($user_id);
    $html = '';
    $the_query = new WP_Query($args);
    $counter = 0; // カウンタを初期化
    if ($the_query->have_posts()) {
        while ($the_query->have_posts()) {
            
            $the_query->the_post();
            $store_name = get_the_title();
            $point_history_id = get_the_ID();
            $point_number = get_post_meta($point_history_id, 'point_number', true);
            $price = get_post_meta($point_history_id, 'price', true);
            $point_rate = get_post_meta($point_history_id, 'point_rate', true);
            $point_type = get_post_meta($point_history_id, 'point_type', true);
            $point_class = '';
            if($point_type == '付与')
            {
                $point_class='give';

            }elseif($point_type == '利用')
            {
                $point_class='use';
            }


            // カウンタが偶数の場合に 'tbd' クラスを追加
            $class = ($counter % 2 === 0) ? 'lma-user_box tbd' : 'lma-user_box';

            $html .= '<li>
            <div class="lma-user_box">
                <div class="user_info">
                    <h3 class="name">'.$store_name.'</h3>
                </div>
                <div class="user_point">
                    <b class="points"><span class="point">'.$point_number.'</span><small class="unit">pt</small></b>
                </div>
                <div class="lma-btn_box btn_list '.$point_class.'"><span class="point_type">'.$point_type.'</span></div>
            </div>
        </li>';

            $counter++; // カウンタをインクリメント
        }
    }

    include './view/user_point_history.php';
}
}
$adminStaffList = new adminUserPointHistory();
$adminStaffList->index();
