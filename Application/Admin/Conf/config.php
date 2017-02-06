<?php
return array(

	//资源列表
	'TMPL_PARSE_STRING'=>array(
		'__PUBLIC__'=> MODULE_PATH.'Public',
		'__JS__'=> __ROOT__.'/Public/js',
        '__PLUG__'=> __ROOT__.'/Public/plug',
		'__CSS__'=>__ROOT__.'/Public/css',
		'__IMAGES__'=> __ROOT__.'/Public/img',
        '__METRONIC_CSS__'=> __ROOT__.'/Public/media/css',
        '__METRONIC_JS__'=> __ROOT__.'/Public/media/js',
        '__METRONIC_IMG__'=> __ROOT__.'/Public/media/image',

	),
    //SESSION前缀
    'SESSION_PREFIX'=>'Admin',
	//伪静态后缀
	'URL_HTML_SUFFIX'=>'',
	//默认分页
	'PAGE_SIZE'=>20,
	//默认错误跳转对应的模板文件
	'TMPL_ACTION_ERROR' => 'Common:error',
	//默认成功跳转对应的模板文件
	//'TMPL_ACTION_SUCCESS' => 'Common:success'
);