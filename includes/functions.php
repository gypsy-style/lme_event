<?php
// 絵文字を特定の文字に置き換える関数
function replace_emoji($text, $replacement = '') {
    return preg_replace('/[^\x{0000}-\x{007F}\x{0080}-\x{FFFF}]/u', $replacement, $text);
}

function get_weekdays(){
    return ['日曜日', '月曜日', '火曜日', '水曜日', '木曜日', '金曜日', '土曜日'];
}