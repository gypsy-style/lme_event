<?php
require_once('../vendor/autoload.php'); //LINE BOT SDKを読み込み
require_once('../../../../wp-load.php'); //WordPressの基本機能を読み込み
require_once('../line-members.php'); //LINE Connectを読み込み
require_once('../includes/html.php');
require_once('./adminController.php');
class adminStoreDetail extends adminController
{

    public function index()
    {
        if (!isset($_GET['store_id']) || empty($_GET['store_id'])) {
            // 店舗一覧へリダイレクト
            exit;
        }

        $store_id = $_GET['store_id'];
        $store_name = get_post_meta($store_id, 'store_name', true);

        // 年月を取得 (指定がない場合は現在の年月)
        $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
        $month = isset($_GET['month']) ? intval($_GET['month']) : date('m');

        // 月の開始日と終了日を計算
        $first_day_of_month = date('Y-m-01', strtotime("$year-$month-01"));
        $last_day_of_month = date('Y-m-t', strtotime($first_day_of_month));

        $tax_rate = 0.10;
        $point_rate = 0.01;

        // ユーザーに付与したポイント
        $userGivenPoints = 0;

        // ユーザーから付与されたポイント
        $userReceivedPoints = 0;

        // 運営から付与するポイント
        $adminGivenPoints = 0;

        // 店舗ポイント付与金額
        $storePointsAmount = 0;

        $pointFee = 0;

        // ポイント手数料（税込）
        $pointFeeWithTax = 0;

        // 月間フィルタをクエリに追加
        $args = array(
            'post_type' => array('point_history'), //投稿タイプを指定
            'posts_per_page' => '-1', //取得する投稿件数を指定
            'meta_key' => 'store_id', //カスタムフィールドのキーを指定
            'meta_value' => $store_id, //カスタムフィールドの値を指定
            'date_query' => array(
                array(
                    'after' => $first_day_of_month,
                    'before' => $last_day_of_month,
                    'inclusive' => true,
                ),
            ),
            'orderby' => 'date', //投稿の日付を基準にソート
            'order' => 'desc' //最新の投稿を取得するために降順にソート
        );

        $the_query = new WP_Query($args);

        if ($the_query->have_posts()) {
            while ($the_query->have_posts()) {
                $the_query->the_post();
                $point_history_id = get_the_ID();
                $point_type = get_post_meta($point_history_id, 'point_type', true);
                $price = get_post_meta($point_history_id, 'price', true);
                $point_number = get_post_meta($point_history_id, 'point_number', true);

                if (is_numeric($point_number)) {
                    $point_number = floatval($point_number);
                } else {
                    $point_number = 0;
                }

                if (is_numeric($price)) {
                    $price = floatval($price);
                } else {
                    $price = 0;
                }


                switch ($point_type) {
                    case '付与':
                        $userGivenPoints += $point_number;
                        $storePointsAmount += $price;
                        break;
                    case '使用':
                        $userReceivedPoints += $point_number;
                        break;
                    case '運営':
                        $adminGivenPoints += $point_number;
                        break;
                }
            }

            $adminGivenPoints = $userGivenPoints - $userReceivedPoints;
            // // $pointFee = $storePointsAmount * 0.01;
            // $pointFee = ($storePointsAmount / 1.1) * 0.01;
            // $pointFee = ceil($pointFee);
            $pointWithoutTax = $storePointsAmount / (1 + $tax_rate);
            $pointFee = $pointWithoutTax * $point_rate;
            $pointFeeWithTax = $pointFee * (1 + $tax_rate);
            $pointFeeWithTax = round($pointFeeWithTax);
            // 10%の税を足す

            // $pointFeeWithTax = $pointFee + ($pointFee * $taxRate);
            // $pointFeeWithTax = number_format($pointFeeWithTax);
            // $totalPoint = $adminGivenPoints + $pointFee;

            $pointFee = number_format($pointFee);
            $storePointsAmount = number_format($storePointsAmount);
        }

        // 現在の月の前月と次月を計算
        $prev_month = strtotime("-1 month", strtotime("$year-$month-01"));
        $next_month = strtotime("+1 month", strtotime("$year-$month-01"));

        $prev_year = date('Y', $prev_month);
        $prev_month_number = date('m', $prev_month);

        $next_year = date('Y', $next_month);
        $next_month_number = date('m', $next_month);

        include './view/store_detail.php'; // ビューを表示
    }
}
$adminStoreDetail = new adminStoreDetail();
$adminStoreDetail->index();
