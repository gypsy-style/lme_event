<?php
require_once('vendor/autoload.php'); //LINE BOT SDKを読み込み
require_once('../../../wp-load.php'); //WordPressの基本機能を読み込み
require_once('line-members.php'); //LINE Connectを読み込み
require_once('includes/html.php');

$enabled_coupon = get_option('enabled_coupon');
$not_exist_redirect = get_option('not_exist_redirect');

$show_banner = get_option('show_banner');

$categories = get_categories(array(
    'taxonomy' => 'event_category', // カテゴリータクソノミー
    'hide_empty' => false,    // 投稿がないカテゴリーも表示
));

?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">

    <meta name="robots" content="noindex,follow">
    <meta name="viewport" content="width=device-width,user-scalable=no">
    <meta name="format-detection" content="telephone=no" />

    <link href="./css/default.css" rel="stylesheet" media="all">
    <link href="./css/front.css" rel="stylesheet" media="all">

    <title>申し込み</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" type="text/javascript"></script>
    <script src="https://static.line-scdn.net/liff/edge/2.1/sdk.js"></script>

</head>

<body class="lmf-schedule_body cust">
    <div class="lmf-container">
        <div class="lmf-title_block">
            <h1 class="title">申し込み</h1>
        </div>
        <main class="lmf-main_contents">
            <section class="lmf-content">
                <div class="lmf-tab_wrapper">
                    <input type="radio" name="rank_tab" id="tab_check01" class="vibtn" checked>
                    <?php
                    if ($categories): ?>
                        <?php foreach ($categories as $index => $category):
                            $checked = ($index === 0) ? 'checked' : ''; // 最初の項目をデフォルトで選択
                        ?>
                            <input type="radio" name="rank_tab" id="tab_<?php echo esc_attr($category->slug); ?>" class="vibtn" <?php echo $checked; ?>>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <ul class="lmf-tab_list">
                        <li><label for="tab_check01">すべて</label></li>
                        <?php
                        foreach ($categories as $category) :
                        ?>
                            <li><label for="tab_<?= esc_attr($category->slug); ?>"><?= esc_html($category->name); ?></label></li>
                        <?php
                        endforeach;
                        ?>
                    </ul>
                    <div class="lmf-tab_area">
                        <section class="section">
                            <ul class="lmf-card_list">
                                <?php
                                // カテゴリーに紐づいたカスタム投稿 'event' を取得
                                $event_query = new WP_Query(array(
                                    'post_type' => 'event', // カスタム投稿タイプ
                                    'posts_per_page' => -1, // すべての投稿を取得
                                ));
                                if ($event_query->have_posts()):
                                    while ($event_query->have_posts()): $event_query->the_post();
                                        // タイトル、タグ、アイキャッチ、カスタムフィールドの取得
                                        $post_id = get_the_ID();
                                        $title = get_the_title();
                                        $tags = get_the_tags($post_id); // タグを取得
                                        $event_types = get_post_meta($post_id, 'event_types', true); // カスタムフィールド
                                        $formatted_event_types = '';
                                        if (!empty($event_types)) {
                                            $event_types_array = explode("\n", $event_types); // 改行で分割

                                            foreach ($event_types_array as $type) {
                                                $type = trim($type); // 不要な空白を削除
                                                if (!empty($type)) {
                                                    $formatted_event_types .= '<span class="icon">' . esc_html($type) . '</span>';
                                                }
                                            }
                                        }
                                        $event_date = get_post_meta($post_id, 'event_date', true); // カスタムフィールド
                                        $formatted_date = '';
                                        if (!empty($event_date)) {
                                            $date = new DateTime($event_date);
                                            // 曜日を日本語で表記する配列
                                            $weekdays = ['日曜日', '月曜日', '火曜日', '水曜日', '木曜日', '金曜日', '土曜日'];
                                            // 整形した日付を生成
                                            $formatted_date = $date->format('Y年n月j日') . '（' . $weekdays[$date->format('w')] . '）';
                                        }

                                ?>
                                        <li><a href="https://liff.line.me/2006629843-rzZ2l4Xb?event_id=<?=$post_id;?>">
                                                <p class="data_box"><?= $formatted_date; ?></p>
                                                <h3 class="name"><?= $title; ?></h3>
                                                <div class="lmf-icon_box"><?= $formatted_event_types; ?></div>
                                            </a></li>
                                    <?php endwhile;
                                    wp_reset_postdata();
                                else: ?>
                                    <p>このカテゴリーに関連するイベントはありません。</p>
                                <?php endif; ?>
                            </ul>
                        </section>
                        <?php
                        foreach ($categories as $category) :
                        ?>
                            <section class="section">
                                <ul class="lmf-card_list">
                                    <?php
                                    // カテゴリーに紐づいたカスタム投稿 'event' を取得
                                    $event_query = new WP_Query(array(
                                        'post_type' => 'event', // カスタム投稿タイプ
                                        'posts_per_page' => -1, // すべての投稿を取得
                                        'tax_query' => array(
                                            array(
                                                'taxonomy' => 'event_category',
                                                'field'    => 'slug',
                                                'terms'    => $category->slug,
                                            ),
                                        ),
                                    ));
                                    if ($event_query->have_posts()):
                                        while ($event_query->have_posts()): $event_query->the_post();
                                            // タイトル、タグ、アイキャッチ、カスタムフィールドの取得
                                            $post_id = get_the_ID();
                                            $title = get_the_title();
                                            $tags = get_the_tags($post_id); // タグを取得
                                            $event_types = get_post_meta($post_id, 'event_types', true); // カスタムフィールド
                                            $formatted_event_types = '';
                                            if (!empty($event_types)) {
                                                $event_types_array = explode("\n", $event_types); // 改行で分割

                                                foreach ($event_types_array as $type) {
                                                    $type = trim($type); // 不要な空白を削除
                                                    if (!empty($type)) {
                                                        $formatted_event_types .= '<span class="icon">' . esc_html($type) . '</span>';
                                                    }
                                                }
                                            }
                                            $event_date = get_post_meta($post_id, 'event_date', true); // カスタムフィールド
                                            $formatted_date = '';
                                            if (!empty($event_date)) {
                                                $date = new DateTime($event_date);
                                                // 曜日を日本語で表記する配列
                                                $weekdays = ['日曜日', '月曜日', '火曜日', '水曜日', '木曜日', '金曜日', '土曜日'];
                                                // 整形した日付を生成
                                                $formatted_date = $date->format('Y年n月j日') . '（' . $weekdays[$date->format('w')] . '）';
                                            }

                                    ?>
                                            <li><a href="https://liff.line.me/2006629843-rzZ2l4Xb?event_id=<?=$post_id;?>">
                                                    <p class="data_box"><?= $formatted_date; ?></p>
                                                    <h3 class="name"><?= $title; ?></h3>
                                                    <div class="lmf-icon_box"><?= $formatted_event_types; ?></div>
                                                </a></li>
                                        <?php endwhile;
                                        wp_reset_postdata();
                                    else: ?>
                                        <p>このカテゴリーに関連するイベントはありません。</p>
                                    <?php endif; ?>
                                </ul>
                            </section>
                        <?php
                        endforeach; ?>

                    </div>
                </div>
            </section>
        </main>
    </div><!-- /.lmf-container -->
</body>

</html>