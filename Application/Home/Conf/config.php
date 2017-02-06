<?php
return array(
    //剪短路由配置
    'URL_ROUTER_ON'   => true, //开启路由

    'URL_ROUTE_RULES' => array( //定义路由规则
        '/^(\w+)_(\d+)$/'  		            =>'News/index?id=:1&p=:2',
        '/^(\w+)$/'							=>'News/index?id=:1',
		'/detail\/(\d+)$/'				    =>'News/detail?aid=:1',
        '/^(\w+)\/(\w+)$/'				    =>'News/index?id=:1&cid=:2',
    ),
    'PAGE_SIZE'=>5,
	//静态缓存
	'HTML_CACHE_ON'=>false,
	'HTML_PATH'=>'Html',
	'HTML_FILE_SUFFIX'=>'.shtml',
	'HTML_CACHE_TIME'=>60*5,
	
	//加载自定义标签
	'TAGLIB_LOAD'               => true,
	'APP_AUTOLOAD_PATH'         =>'@.TagLib',
	'TAGLIB_BUILD_IN'           =>'Cx,Lists',

	'HTML_CACHE_RULES'=> array(
		'index:index'=>array('index'),
		'news:index'=>array('{id}{p}'),
		'news:detail'=>array('article_{aid}')
	),
    //资源列表
    'TMPL_PARSE_STRING'=>array(
        '__JS__'=> __ROOT__.'/Public/js',
        '__PLUG__'=> __ROOT__.'/Public/plug',
        '__CSS__'=>__ROOT__.'/Public/css',
        '__IMAGES__'=> __ROOT__.'/Public/img',
        '__FILES__'=> __ROOT__.'/Uploads/files'
    ),
);