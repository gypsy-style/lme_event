<?php
require_once('../vendor/autoload.php'); //LINE BOT SDKを読み込み
require_once('../../../../wp-load.php'); //WordPressの基本機能を読み込み
require_once('../line-members.php'); //LINE Connectを読み込み
require_once('../includes/html.php');
class adminStorePost
{


    public function index()
    {
        // echo '<pre>';
        // print_r($_POST);
        // echo '</pre>';exit;
        if(!isset($_POST['store_id'])) {
            echo '更新に失敗しました。<br><a href="./store_list.php">一覧に戻る</a>';
            exit;
        }
        $store_id = $_POST['store_id'];
        unset($_POST['store_id']);
        if (!$store_id) {
            echo '店舗パラメータが不明です。<br><a href="./store_list.php">一覧に戻る</a>';
            exit;
        }

        $status = isset($_POST['status']) ? $_POST['status'] : '';
        if($status == 1) {
            $richmenu = get_option('richmenu_3');
        }else {
            $richmenu = get_option('richmenu_2');
        }

        
        if ($richmenu) {
            $line_id = get_post_meta($store_id,'line_id',true);

            $args = [
                'post_type' => array('line_user'), //投稿タイプを指定
                'posts_per_page' => '-1', //取得する投稿件数を指定
                'meta_key' => 'line_id', //カスタムフィールドのキーを指定
                'meta_value' => $line_id, //カスタムフィールドの値を指定
                'orderby' => 'meta_value', //ソートの基準を指定
                'order' => 'asc' //ソート方法を指定（昇順：asc, 降順：desc）
            ];
            $the_query = new WP_Query($args);
    
            if ($the_query->have_posts()) {
                while ($the_query->have_posts()) {
                    $the_query->the_post();
                    $user_id = get_the_ID();
                }
                wp_reset_postdata();
            }
            
            update_post_meta($user_id, 'richmenu_id', $richmenu);
            lineconnectRichmenu::updateRichMenu($line_id, $richmenu);
        }

        foreach ($_POST as $name => $value) {
            update_post_meta($store_id, $name, $value);
        }
        if(!isset($_POST['status']))
        {
            update_post_meta($store_id,'status','');
        }

        header('Location: store_list.php');
        exit;
    }
}
$adminStoreEdit = new adminStorePost();
$adminStoreEdit->index();
