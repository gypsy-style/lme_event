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
        'event_status' => 'ステータス', // dropdown (有効、無効)
        'event_subtitle' => 'イベントサブタイトル', // テキスト
        'event_date' => '開催日時', // 日付（xxxx年xx月xx日 xx時xx分）
        'event_time' => '開催時間', // テキスト
        'event_venue' => '会場', // テキスト
        'event_address' => '会場住所', // テキストエリア
        'contact_phone' => '問い合わせ先電話番号', // 電話番号
        'contact_email' => '問い合わせ先メールアドレス', // メールアドレス
        'entry_fee' => '参加費', // テキスト
        'event_types' => 'イベントタイプ', // テキスト
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
    static function render_field_event_status($post)
    {
        $value = get_post_meta($post->ID, 'event_status', true);
        ?>
        <label for="event_status">ステータス</label>
        <select id="event_status" name="event_status">
            <option value="active" <?php selected($value, 'active'); ?>>有効</option>
            <option value="inactive" <?php selected($value, 'inactive'); ?>>無効</option>
        </select>
        <?php
    }

    static function render_field_event_subtitle($post)
    {
        $value = get_post_meta($post->ID, 'event_subtitle', true);
        ?>
        <label for="event_subtitle">イベントサブタイトル</label>
        <input type="text" id="event_subtitle" name="event_subtitle" value="<?= esc_attr($value); ?>">
        <?php
    }

    static function render_field_event_date($post)
    {
        $value = get_post_meta($post->ID, 'event_date', true);
        ?>
        <label for="event_date">開催日時</label>
        <input type="datetime-local" id="event_date" name="event_date" value="<?= esc_attr($value); ?>">
        <?php
    }

    static function render_field_event_time($post)
    {
        $value = get_post_meta($post->ID, 'event_time', true);
        ?>
        <label for="event_time">開催時間</label>
        <input type="text" id="event_time" name="event_time" value="<?= esc_attr($value); ?>">
        <?php
    }

    static function render_field_event_venue($post)
    {
        $value = get_post_meta($post->ID, 'event_venue', true);
        ?>
        <label for="event_venue">会場</label>
        <input type="text" id="event_venue" name="event_venue" value="<?= esc_attr($value); ?>">
        <?php
    }

    static function render_field_event_address($post)
    {
        $value = get_post_meta($post->ID, 'event_address', true);
        ?>
        <label for="event_address">会場住所</label>
        <textarea id="event_address" name="event_address" rows="4" cols="50"><?= esc_textarea($value); ?></textarea>
        <?php
    }

    static function render_field_contact_phone($post)
    {
        $value = get_post_meta($post->ID, 'contact_phone', true);
        ?>
        <label for="contact_phone">問い合わせ先電話番号</label>
        <input type="text" id="contact_phone" name="contact_phone" value="<?= esc_attr($value); ?>" placeholder="例: 03-1234-5678">
        <?php
    }

    static function render_field_contact_email($post)
    {
        $value = get_post_meta($post->ID, 'contact_email', true);
        ?>
        <label for="contact_email">問い合わせ先メールアドレス</label>
        <input type="email" id="contact_email" name="contact_email" value="<?= esc_attr($value); ?>" placeholder="例: example@example.com">
        <?php
    }

    static function render_field_entry_fee($post)
    {
        $value = get_post_meta($post->ID, 'entry_fee', true);
        ?>
        <label for="entry_fee">参加費</label>
        <input type="text" id="entry_fee" name="entry_fee" value="<?= esc_attr($value); ?>" placeholder="例: ¥1000">
        <?php
    }

    static function render_field_event_types($post)
    {
        $value = get_post_meta($post->ID, 'event_types', true);
        ?>
        <label for="event_types">イベントタイプ</label>
        <textarea id="event_types" name="event_types" rows="4" cols="50"><?= esc_textarea($value); ?></textarea>
        <?php
    }

    /**
     * カスタムフィールド保存
     * @param mixed $post_ID
     * @return void
     */
    static function save_custom_fields($post_ID)
    {
        $fields = self::$fields;
        foreach ($fields as $key => $label) {
            if (isset($_POST[$key])) {
                update_post_meta($post_ID, $key, sanitize_text_field($_POST[$key]));
            }
        }
    }
}