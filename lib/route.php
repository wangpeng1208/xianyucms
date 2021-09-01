<?php
return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[vod]'     => [
	    'show'   => ['home/vod/show', ['method' => 'get']],
		'type'   => ['home/vod/type', ['method' => 'get']],
        'read'   => ['home/vod/read', ['method' => 'get']],
        'play'   => ['home/vod/play', ['method' => 'get']],
		'filmtime'=> ['home/vod/filmtime', ['method' => 'get']],
		'news'  => ['home/vod/news', ['method' => 'get']],
    ],
	'[news]'     => [
		'show'   => ['home/news/show', ['method' => 'get']],
        'read'   => ['home/news/read', ['method' => 'get']],
    ],
    '[star]'     => [
		'show'   => ['home/star/show', ['method' => 'get']],
		'type'   => ['home/star/type', ['method' => 'get']],
        'read'   => ['home/star/read', ['method' => 'get']],
		'work'   => ['home/star/work', ['method' => 'get']],
		'hz'   => ['home/star/hz', ['method' => 'get']],
		'role'   => ['home/star/role', ['method' => 'get']],
        'info'   => ['home/star/info', ['method' => 'get']],
		'news'   => ['home/star/news', ['method' => 'get']],
    ],
	'[story]'     => [
		'show'   => ['home/story/show', ['method' => 'get']],
        'read'   => ['home/story/read', ['method' => 'get']],
    ],
	'[role]'     => [
		'show'   => ['home/role/show', ['method' => 'get']],
        'read'   => ['home/role/read', ['method' => 'get']],
    ],
	'[actor]'     => [
	    'show'   => ['home/actor/show', ['method' => 'get']],
        'read'   => ['home/actor/read', ['method' => 'get']],
    ],	
	'[tv]'     => [
	    'show'   => ['home/tv/show', ['method' => 'get']],
        'read'   => ['home/tv/read', ['method' => 'get']],
    ],
	'[special]'     => [
	    'show'   => ['home/special/show', ['method' => 'get']],
        'read'   => ['home/special/read', ['method' => 'get']],
    ],
	'[up]'     => [
	    'show'   => ['home/up/show', ['method' => 'get']],
    ],	
	'[tag]'     => [
	    'show'   => ['home/tag/show', ['method' => 'get']],
    ],		
	'[my]'     => [
	    'show'   => ['home/my/show', ['method' => 'get']],
    ],	
	'[map]'     => [
	    'show'   => ['home/map/show', ['method' => 'get']],
    ],
	'[gb]'     => [
	    'show'   => ['home/gb/show', ['method' => 'get']],
		'add'   => ['home/gb/add', ['method' => 'post']],
    ],
	'[search]'     => [
	    'index'   => ['home/search/index'],
    ],	
	'[updown]'     => [
	    'show'   => ['home/updown/show', ['method' => 'get']],
    ],
	'[hits]'     => [
	    'show'   => ['home/updown/hits', ['method' => 'get']],
    ],

	
];