<?php
/**
 * Created by PhpStorm.
 * User: sawadakeisuke
 * Date: 9/5/22
 * Time: 4:53 PM
 * keyはname属性
 * typeの直（text,textarea,select,birthday_y,birthday_m）
 * titleは日本語項目名
 * is_profileはプロフィールに表示させるかどうか
 */
class custom_fields {
    static $custom_fields = [
		
		'line_id'=>[
            'type'=>'text',
            'title'=>'LINE ID',
            'is_profile'=>false,
            'is_editable'=>false
        ],
		
		
        'richmenu_id'=>[
            'type'=>'richmenu_id',
            'title'=>'リッチメニュー',
            'is_profile'=>false,
            'is_editable'=>false
        ],

        'address'=>[
            'type'=>'text',
            'title'=>'住所',
            'is_profile'=>true,
            'is_editable'=>true
        ],

        'tel'=>[
            'type'=>'text',
            'title'=>'電話番号',
            'is_profile'=>true,
            'is_editable'=>true
        ],
		
		
		'sex'=>[
            'type'=>'select',
            'title'=>'性別',
            'options'=>[
                '男性',
                '女性',
                'その他',
            ],
            'is_profile'=>true,
            'is_editable'=>false
        ],

    ];
}
