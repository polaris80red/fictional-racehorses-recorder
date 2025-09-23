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
        ],
        self::Maintainer=>[
            'name'=>'メンテナ',
        ],
        self::Editor=>[
            'name'=>'編集者',
        ],
        self::Author=>[
            'name'=>'投稿者',
        ],
    ];
}
