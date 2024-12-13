<?php
require_once('../vendor/autoload.php'); //LINE BOT SDKを読み込み
require_once('../../../../wp-load.php'); //WordPressの基本機能を読み込み
require_once('../line-members.php'); //LINE Connectを読み込み
require_once('../includes/html.php');
class getCsv
{

    public function index()
    {
        $tax_rate = 0.10;
        $point_rate = 0.01;

        $year = $_GET['year'] ?? date('Y');
        $month = $_GET['month'] ?? date('n');
        // 月の開始日と終了日を計算
        $first_day_of_month = date('Y-m-01', strtotime("$year-$month-01"));
        $last_day_of_month = date('Y-m-t', strtotime($first_day_of_month));
        $csv = [];
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

                $points_query = new WP_Query($args);
                // ポイント集計用変数
                $userGivenPoints = 0;
                $userReceivedPoints = 0;
                $adminGivenPoints = 0;
                $storePointsAmount = 0;
                $pointFeeWithTax = 0;
                if ($points_query->have_posts()) {
                    while ($points_query->have_posts()) {
                        $points_query->the_post();
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

                        // ポイントタイプに応じた集計
                        switch ($point_type) {
                            case '付与':
                                $userGivenPoints += $point_number;
                                $storePointsAmount += $price;
                                break;
                            case '使用':
                                $userReceivedPoints += $point_number;
                                break;
                            default:
                                break;
                        }
                    }
                    $adminGivenPoints = $userGivenPoints - $userReceivedPoints;
                    // $storePointsAmount = ceil($storePointsAmount / 1.1);
                    // $pointFee = $storePointsAmount * 0.01;
                    // $pointFee = ceil($pointFee);
                    // $pointFee = ($storePointsAmount / 1.1) * 0.01;
                    // $pointFee = ceil($pointFee);
                    // // 10%の税を足す
                    // $taxRate = 0.10;
                    // $pointFeeWithTax = $pointFee + ($pointFee * $taxRate);
                    // $pointFeeWithTax = number_format($pointFeeWithTax);
                    $pointWithoutTax = $storePointsAmount / (1 + $tax_rate);
            $pointFee = $pointWithoutTax * $point_rate;
            $pointFeeWithTax = $pointFee * (1 + $tax_rate);
            $pointFeeWithTax = round($pointFeeWithTax);
                    // $totalPoint = $adminGivenPoints + $pointFee;

                    // $pointFee = number_format($pointFee);
                    $storePointsAmount = number_format($storePointsAmount);
                }

                // CSV用のデータを配列に追加
                $csv[$counter]['store_name'] = $store_name;
                $csv[$counter]['userGivenPoints'] = $userGivenPoints;
                $csv[$counter]['userReceivedPoints'] = $userReceivedPoints;
                $csv[$counter]['adminGivenPoints'] = $adminGivenPoints;
                $csv[$counter]['storePointsAmount'] = $storePointsAmount;
                $csv[$counter]['pointFeeWithTax'] = $pointFeeWithTax;
                $counter++;
            }
        }

        // CSVの出力
        $file_name = 'wakuwaku_data_' . $year . '-' . $month . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $file_name . '"');
        $output = fopen('php://output', 'w');

        // ヘッダー行を出力
        fputcsv($output, ['店舗名', 'ユーザーに付与したポイント', 'ユーザーから付与されたポイント', '運営から付与するポイント', '店舗ポイント付与金額（税込）', 'ポイント手数料（税込）']);

        // CSVデータを出力
        foreach ($csv as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }
}
$adminIndex = new getCsv();
$adminIndex->index();
