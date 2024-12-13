<?php
// WordPressの環境を読み込む
require_once('../../../../wp-load.php'); //WordPressの基本機能を読み込み

// セッションを開始
session_start();

// セッションを削除
session_unset();
session_destroy();

// WordPressのログアウト処理
wp_logout();

// ログアウト後にリダイレクト
$login_url = plugins_url('line-members/admin/login.php');
header('Location: ' . $login_url);
exit;
