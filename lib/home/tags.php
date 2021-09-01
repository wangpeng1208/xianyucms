<?php
//应用行为扩展定义文件
return [
    'module_init' =>[
        'app\\home\\behavior\\ReadHtmlCacheBehavior',
        'app\\home\\behavior\\SetTheme',
    ],
    'view_filter' => [
        'app\\home\\behavior\\WriteHtmlCacheBehavior',
    ],
];