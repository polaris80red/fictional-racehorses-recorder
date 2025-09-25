<?php
/**
 * 権限・役割クラス
 */
class Role {
    const Administrator=1;
    const Maintainer=2;
    const Editor=3;
    const Author=4;

    const RoleInfoList=[
        self::Administrator=>[
            'name'=>'管理者',
            'caption'=>"管理者",
        ],
        self::Maintainer=>[
            'name'=>'メンテナ',
            'caption'=>"マスタ管理が可能",
        ],
        self::Editor=>[
            'name'=>'編集者',
            'caption'=>"他ユーザーが登録したレコードも管理可能",
        ],
        self::Author=>[
            'name'=>'投稿者',
            'caption'=>"自分が登録した馬とその戦績、および自分が登録したレースのみ編集可。レース削除不可",
        ],
    ];
}
