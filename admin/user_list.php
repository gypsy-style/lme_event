<?php
require_once('../vendor/autoload.php'); //LINE BOT SDKを読み込み
require_once('../../../../wp-load.php'); //WordPressの基本機能を読み込み
require_once('../line-members.php'); //LINE Connectを読み込み
require_once('../includes/html.php');
require_once('./adminController.php');
class adminUserList extends adminController 
{

    public function index()
    {
        // if(!isset($_GET['store_id']) || empty($store_id))
        // {
        //     // 店舗一覧へリダイレクト
        // }
        // $store_id = $_GET['store_id'];
        // $paged = (get_query_var('paged')) ? get_query_var('paged') : 1; 
        $paged = isset($_GET['paged']) ? $_GET['paged'] : 1;
        $posts_per_page = 30;
        $search_term = isset($_POST['s']) ? sanitize_text_field($_POST['s']) : '';
        // 店舗一覧
        $args = array(
            'post_type' => array('line_user'), //投稿タイプを指定
            'posts_per_page' => $posts_per_page, //取得する投稿件数を指定
            'paged' => $paged,
            'orderby' => 'date', //投稿の日付を基準にソート
            'order' => 'desc', //最新の投稿を取得するために降順にソート
            's' => $search_term
        );
        // $store_name = get_post_meta($store_id,'store_name',true);
        $user_list_html = '';
        $the_query = new WP_Query($args);
        $counter = 0; // カウンタを初期化
        if ($the_query->have_posts()) {
            while ($the_query->have_posts()) {
                $the_query->the_post();
                $user_id = get_the_ID();
                $user_name = get_the_title($user_id);
                $line_id = get_post_meta($user_id, 'line_id', true);
                $point = get_post_meta($user_id, 'point', true);

                // カウンタが偶数の場合に 'tbd' クラスを追加
                $class = ($counter % 2 === 0) ? 'lma-user_box tbd' : 'lma-user_box';

                $user_list_html .= '<li>
            <div class="lma-user_box">
                <div class="user_info">
                    <h3 class="name">' . $user_name . '</h3>
                    <p class="line_id">' . $line_id . '</p>
                </div>
                <div class="user_point">
                    <b class="points"><span class="point">' . $point . '</span><small class="unit">pt</small></b>
                </div>
                <div class="lma-btn_box btn_min btn_gy">
                    <a class="lgy" href="user_point_history.php?type=user&user_id=' . $user_id . '">ポイント履歴</a>
                    <a class="bu" href="delete.php?type=user&user_id=' . $user_id . '">削除</a>
                </div>
            </div>
        </li>';

                $counter++; // カウンタをインクリメント
            }
            $pagination_base = get_pagenum_link(1);
            $pagination = paginate_links(array(
                'base' => $pagination_base . '%_%', // ベースURL
                'total' => $the_query->max_num_pages, // Total number of pages
                'current' => $paged, // Current page
                'format' => '?paged=%#%', // Pagination format
                'show_all' => false,
                'end_size' => 2,
                'mid_size' => 1,
                'prev_text' => __('&laquo; Previous'),
                'next_text' => __('Next &raquo;'),
            ));
        }
        // echo 'paged='.$paged;

        include './view/user_list.php';
    }
}
$adminUserList = new adminUserList();
$adminUserList->index();
