<?php
require_once('../vendor/autoload.php'); //LINE BOT SDKを読み込み
require_once('../../../../wp-load.php'); //WordPressの基本機能を読み込み
require_once('../line-members.php'); //LINE Connectを読み込み
require_once('../includes/html.php');

// セッションを開始
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ユーザー名とパスワードを取得
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 認証用データを設定
    $credentials = array(
        'user_login'    => $username,
        'user_password' => $password,
        'remember'      => true
    );

    // WordPressの認証処理
    $user = wp_signon($credentials, false);

    if (is_wp_error($user)) {
        // 認証失敗
        echo 'ログインに失敗しました: ' . $user->get_error_message();
    } else {
        // 認証成功
        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = $user->ID;
        $_SESSION['user_name'] = $user->user_login;

        // リダイレクトまたは成功メッセージ
        // トップページへリダイレクト
        $index_url = plugins_url('line-members/admin/index.php');
        header('Location: '.$index_url);
        exit;
    }
}
include './view/login.php';