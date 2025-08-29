<?php

/**
 * Created by PhpStorm.
 * User: sawadakeisuke
 * Date: 5/16/22
 * Time: 6:12 AM
 */
//require_once('../extensions/custom_fields.php');
require_once(plugin_dir_path(plugin_dir_path(__FILE__)) . 'extensions/custom_fields.php');
class settingEvent
{
    const LABEL = 'イベント';
    const POST_TYPE = 'event';

    static $fields = [
        'event_image' => 'イベント画像', // 画像
        'event_subtitle' => 'イベントサブタイトル', // テキスト
        'event_date' => '開催日時', // 日付（xxxx年xx月xx日 xx時xx分）
        'event_date_override' => '開催日時の上書き', // 日付（xxxx年xx月xx日 xx時xx分）
        'event_time' => '開催時間', // テキスト
        'event_venue' => '会場', // テキスト
        'event_address' => '会場住所', // テキストエリア
        'event_map' => 'Google MAP', // テキスト

        'event_committee' => '担当委員会', // 担当委員会
        'event_chairperson' => '委員長名', // 委員長名
        'contact_phone' => '問い合わせ先電話番号', // 電話番号
        'entry_fee' => '参加費', // テキスト
        'speaker_name' => '講師名', // テキスト
        'speaker_profile' => '講師プロフィール', // テキストエリア
        'event_types' => 'イベントタイプ', // テキスト
        'checkin_event_types' => '参加集計用意項目', // テキスト
        'event_checkin' => 'イベントチェックインURL', // テキスト
        'event_stop_entry' => '受付終了', // チェックボックス
    ];

    static $tag_fields = [
        'icon_color' => 'アイコン色'
    ];

    /**
     * LINEユーザーのカスタム投稿タイプ作成
     */
    static function set_event_post_type()
    {
        $label = self::LABEL;
        $post_type = self::POST_TYPE;
        register_post_type(
            $post_type,
            array(
                'label' => $label,
                'labels' => array(
                    'add_new' => '新規' . $label . '追加',
                    'edit_item' => $label . 'の編集',
                    'view_item' => $label . 'を表示',
                    'search_items' => $label . 'を検索',
                    'not_found' => $label . 'は見つかりませんでした。',
                    'not_found_in_trash' => 'ゴミ箱に' . $label . 'はありませんでした。',
                ),
                'public' => true,
                'description' => 'カスタム投稿タイプ「' . $label . '」の説明文です。',
                'hierarchicla' => false,
                'has_archive' => true,
                'capability_type' => 'post',
                'show_in_rest' => false,
                'supports' => array(
                    'title',
                    'editor',
                    'thumbnail',
                    'excerpt',
                    'custom-fields',
                    'revisions'
                ),
                'taxonomies' => array('event_category', 'event_tag'), // カテゴリーとタグを追加
                'menu_position' => 5,
            )
        );
    }

    static function register_taxonomies()
    {
        // カテゴリーの登録
        register_taxonomy(
            'event_category',
            self::POST_TYPE,
            array(
                'labels' => array(
                    'name' => 'イベントカテゴリー',
                    'singular_name' => 'イベントカテゴリー',
                    'search_items' => 'カテゴリーを検索',
                    'all_items' => 'すべてのカテゴリー',
                    'parent_item' => '親カテゴリー',
                    'parent_item_colon' => '親カテゴリー:',
                    'edit_item' => 'カテゴリーを編集',
                    'update_item' => 'カテゴリーを更新',
                    'add_new_item' => '新しいカテゴリーを追加',
                    'new_item_name' => '新しいカテゴリー名',
                    'menu_name' => 'カテゴリー',
                ),
                'hierarchical' => true, // カテゴリーは階層構造を持つ
                'show_ui' => true,
                'show_admin_column' => true,
                'show_in_rest' => true,
            )
        );

        // タグの登録
        register_taxonomy(
            'event_tag',
            self::POST_TYPE,
            array(
                'labels' => array(
                    'name' => 'イベントタグ',
                    'singular_name' => 'イベントタグ',
                    'search_items' => 'タグを検索',
                    'all_items' => 'すべてのタグ',
                    'edit_item' => 'タグを編集',
                    'update_item' => 'タグを更新',
                    'add_new_item' => '新しいタグを追加',
                    'new_item_name' => '新しいタグ名',
                    'menu_name' => 'タグ',
                ),
                'hierarchical' => false, // タグは階層構造を持たない
                'show_ui' => true,
                'show_admin_column' => true,
                'show_in_rest' => true,
            )
        );
    }

    /**
     * カスタムフィールド追加
     * @return void
     */
    static function create_event_custom_fields()
    {
        $post_type = self::POST_TYPE;
        $fields = self::$fields;

        foreach ($fields as $key => $label) {
            add_meta_box(
                $key, // セクションID
                $label, // タイトル
                ['settingEvent', 'render_field_' . $key], // 出力関数
                $post_type,
                'normal'
            );
        }
    }

    /**
     * 各フィールドの出力関数
     */
    static function render_field_event_image($post)
    {
        $image_id = get_post_meta($post->ID, 'event_image', true); // 保存された画像のIDを取得
        $image_url = $image_id ? wp_get_attachment_url($image_id) : ''; // 画像URLを取得
        // echo 'image='.$image_id;
?>
        <div>
            <input type="hidden" id="event_image_hidden" name="event_image" value="<?= esc_attr($image_id); ?>">
            <button type="button" class="button" id="upload_event_image">画像を選択</button>
            <button type="button" class="button" id="remove_event_image" style="display: <?= $image_url ? 'inline-block' : 'none'; ?>;">画像を削除</button>
        </div>
        <div id="event_image_preview" style="margin-top: 10px;">
            <?php if ($image_url): ?>
                <img src="<?= esc_url($image_url); ?>" alt="イベント画像" style="max-width: 100%; height: auto;">
            <?php endif; ?>
        </div>
    <?php
    }

    static function render_field_event_subtitle($post)
    {
        $value = get_post_meta($post->ID, 'event_subtitle', true);
    ?>

        <input type="text" id="event_subtitle" name="event_subtitle" value="<?= esc_attr($value); ?>" class="large-text">
    <?php
    }

    static function render_field_event_date($post)
    {
        $value = get_post_meta($post->ID, 'event_date', true); // 保存された値を取得
    ?>

        <input type="date" id="event_date" name="event_date" value="<?= esc_attr($value); ?>">
    <?php
    }

    static function render_field_event_date_override($post)
    {
        $value = get_post_meta($post->ID, 'event_date_override', true);
    ?>

        <input type="text" id="event_date_override" name="event_date_override" value="<?= esc_attr($value); ?>" class="large-text">
    <?php
    }

    static function render_field_event_time($post)
    {
        $value = get_post_meta($post->ID, 'event_time', true);
    ?>

        <textarea id="event_time" name="event_time" rows="4" cols="50"><?= esc_textarea($value); ?></textarea>
    <?php
    }

    static function render_field_event_venue($post)
    {
        $value = get_post_meta($post->ID, 'event_venue', true);
    ?>

        <input type="text" id="event_venue" name="event_venue" value="<?= esc_attr($value); ?>">
    <?php
    }

    static function render_field_event_address($post)
    {
        $value = get_post_meta($post->ID, 'event_address', true);
    ?>

        <input type="text" id="event_address" name="event_address" value="<?= esc_attr($value); ?>" class="large-text">

    <?php
    }

    static function render_field_event_map($post)
    {
        $value = get_post_meta($post->ID, 'event_map', true);
    ?>

        <input type="text" id="event_map" name="event_map" value="<?= esc_attr($value); ?>" class="large-text">

    <?php
    }

    static function render_field_event_committee($post)
    {
        $value = get_post_meta($post->ID, 'event_committee', true);
    ?>

        <input type="text" id="event_committee" name="event_committee" value="<?= esc_attr($value); ?>">
    <?php
    }

    static function render_field_event_chairperson($post)
    {
        $value = get_post_meta($post->ID, 'event_chairperson', true);
    ?>

        <input type="text" id="event_chairperson" name="event_chairperson" value="<?= esc_attr($value); ?>">
    <?php
    }

    static function render_field_contact_phone($post)
    {
        $value = get_post_meta($post->ID, 'contact_phone', true);
    ?>

        <input type="text" id="contact_phone" name="contact_phone" value="<?= esc_attr($value); ?>" placeholder="例: 03-1234-5678">
    <?php
    }

    static function render_field_entry_fee($post)
    {
        $value = get_post_meta($post->ID, 'entry_fee', true);
    ?>

        <input type="text" id="entry_fee" name="entry_fee" value="<?= esc_attr($value); ?>" placeholder="例: ¥1000">
    <?php
    }

    static function render_field_speaker_name($post)
    {
        $value = get_post_meta($post->ID, 'speaker_name', true);
    ?>

        <input type="text" id="speaker_name" name="speaker_name" value="<?= esc_attr($value); ?>" placeholder="">
    <?php
    }

    static function render_field_speaker_profile($post)
    {
        $value = get_post_meta($post->ID, 'speaker_profile', true);
    ?>

        <textarea id="speaker_profile" name="speaker_profile" rows="4" cols="50" class="large-text"><?= esc_textarea($value); ?></textarea>
    <?php
    }

    static function render_field_event_types($post)
    {
        $value = get_post_meta($post->ID, 'event_types', true);
    ?>
        <p>カンマ区切り</p>
        <input type="text" id="event_types" name="event_types" class="large-text" value="<?= esc_attr($value); ?>">
    <?php
    }

    static function render_field_checkin_event_types($post)
    {
        $value = get_post_meta($post->ID, 'checkin_event_types', true);
    ?>
        <p>カンマ区切り</p>
        <input type="text" id="checkin_event_types" name="checkin_event_types" class="large-text" value="<?= esc_attr($value); ?>">
    <?php
    }

    static function render_field_event_checkin($post)
    {
        $liff_id_event_checkin = get_option('liff_id_event_checkin');
        $checkin_url = 'https://liff.line.me/' . $liff_id_event_checkin . '/?event_id=' . $post->ID;

    ?>
        <input type="text" disabled id="event_checkin" name="event_checkin" class="large-text" value="<?= esc_url($checkin_url); ?>">
    <?php
    }

    static function render_field_event_stop_entry($post)
    {
        $value = get_post_meta($post->ID, 'event_stop_entry', true);

    ?>
        <fieldset>
            <label>
                <input type="checkbox" name="event_stop_entry" value="1" <?= $value == 1 ? 'checked' : ''; ?>>
            </label>
        </fieldset>
        <?php
    }

    /**
     * カスタムフィールド保存
     * @param mixed $post_ID
     * @return void
     */
    static function save_custom_fields($post_ID)
    {
        // ゴミ箱に入れられた場合は処理をスキップ
        if (get_post_status($post_ID) === 'trash') {
            return;
        }
        $fields = self::$fields;
        foreach ($fields as $key => $label) {
            if (isset($_POST[$key])) {
                if ($key === 'event_checkin') {
                    // event_checkinはデータ更新なし
                    continue;
                } elseif ($key === 'event_image') {
                    update_post_meta($post_ID, $key, intval($_POST[$key])); // 画像IDは整数として保存
                } else {
                    if ($key === 'event_time') {
                        // テキストエリア用のサニタイズ
                        update_post_meta($post_ID, $key, sanitize_textarea_field($_POST[$key]));
                    } else {
                        update_post_meta($post_ID, $key, sanitize_text_field($_POST[$key]));
                    }
                }
            } elseif (array_key_exists($key, $_POST)) {
                // 明示的に空が送られてきた場合のみ削除
                delete_post_meta($post_ID, $key);
            } else {
                // クイック編集などで未送信なら何もしない
                continue;
            }
        }
    }


    // タグフィールド
    static function add_event_tag_columns($columns)
    {
        $columns['custom_field_key'] = 'カスタムフィールド';
        return $columns;
    }

    // タグ編集画面にカスタムフィールドを追加
    static function add_event_tag_custom_fields($term)
    {
        $fields = self::$tag_fields;
        foreach ($fields as $key => $name) {
            $value = get_term_meta($term->term_id, $key, true);
        ?>
            <tr class="form-field">
                <th scope="row" valign="top">
                    <label for="<?= $key; ?>"><?= $name; ?></label>
                </th>
                <td>
                    <input type="text" name="<?= $key; ?>" id="<?= $key; ?>" value="<?php echo esc_attr($value); ?>">
                </td>
            </tr>
        <?php
        }
    }

    static function add_event_tag_custom_fields_create()
    {
        $fields = self::$tag_fields;
        foreach ($fields as $key => $name) {
        ?>
            <div class="form-field">
                <label for="<?= $key; ?>"><?= $name; ?></label>
                <input type="text" name="<?= $key; ?>" id="<?= $key; ?>">
            </div>
<?php
        }
    }

    // カスタムフィールドの保存
    static function save_event_tag_custom_fields($term_id)
    {
        $fields = self::$tag_fields;
        foreach ($fields as $key => $name) {
            if (isset($_POST[$key])) {
                update_term_meta($term_id, $key, sanitize_text_field($_POST[$key]));
            }
        }
    }

    // static function create_event_tag_custom_fields()
    // {
    //     $fields = self::$tag_fields;
    //     foreach($fields as $key => $name) {
    //         register_rest_field('event_tag', $key, [
    //             'get_callback'    => function ($term,$key) {
    //                 return get_term_meta($term['id'], $key, true);
    //             },
    //             'update_callback' => function ($value, $term) {
    //                 return update_term_meta($term->term_id, 'custom_field_key', sanitize_text_field($value));
    //             },
    //             'schema'          => null,
    //         ]);
    //     }
    // }


}
