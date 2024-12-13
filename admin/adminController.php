<?php
class adminController
{
    public function __construct()
    {
        // セッションを開始
        session_start();

        // ログイン確認
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            // 未ログインの場合はログインページにリダイレクト
            $login_url = plugins_url('line-members/admin/login.php');
            header('Location: ' . $login_url);
            exit;
        }
    }
}
