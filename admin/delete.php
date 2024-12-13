<?php
require_once('../vendor/autoload.php'); //LINE BOT SDKを読み込み
require_once('../../../../wp-load.php'); //WordPressの基本機能を読み込み
require_once('../line-members.php'); //LINE Connectを読み込み
require_once('../includes/html.php');
class delete
{


    public function index()
    {
        $type = $_GET['type'] ?? '';
        
        switch($type) {
            case 'store':
                $store_id = $_GET['store_id'] ?? '';
                $this->deleteStore($store_id);
                break;
            case 'user':
                $user_id = $_GET['user_id'] ?? '';
                $this->deleteUser($user_id);
                break;
            case 'storeUser':
                $store_user_id = $_GET['store_user_id'] ?? '';
                $this->deleteStoreUser($store_user_id);
                break;
            default:
                $this->error();
            break;
        }
        
    }

    private function deleteStore($store_id)
    {
        if(!$store_id) {
            $this->error();
        }
        $result = $this->delete($store_id,'store');
        if(!$result) {
            $this->error();
        }
        header('Location: ./store_list.php');
    }

    private function deleteUser($user_id)
    {
        if(!$user_id) {
            $this->error();
        }
        $result = $this->delete($user_id,'line_user');
        if(!$result) {
            $this->error();
        }
        header('Location: ./user_list.php');

    }

    private function deleteStoreUser($store_user_id)
    {
        if(!$store_user_id) {
            $this->error();
        }
        $result = $this->delete($store_user_id,'storeUser');
        if(!$result) {
            $this->error();
        }
        header('Location: ./staff_list.php');
    }

    private function delete($post_id,$post_type)
    {
        if (get_post_type($post_id) == $post_type) { // カスタム投稿タイプを確認
            $result = wp_trash_post($post_id);
            return $result;
        }
        return false;
    }

    private function error()
    {
        echo '削除できませんでした';
        exit;
    }
}
$adminStoreEdit = new delete();
$adminStoreEdit->index();
