<?php

	return array(
		//'DEFAULT_C_LAYER'       =>  'Action', // 默认的控制器层名称
		//允许访问的模块列表
		'MODULE_ALLOW_LIST'    	=>  array('Home','Admin'),
		// 设置禁止访问的模块列表
		'MODULE_DENY_LIST'      =>  array('Common','Runtime','Service'),
		//默认模块
		'DEFAULT_MODULE'     	=> 'Home',

		//'SHOW_PAGE_TRACE'=>true,
		'URL_MODEL'=>2,

		//是否开启session
	    'SESSION_AUTO_START' 	=> true,
		//不区分大小写
		'URL_CASE_INSENSITIVE'  =>  true,
		'APP_AUTOLOAD_PATH' => '@.TagLib',
    	'TAGLIB_BUILD_IN' => 'Cx,Lists',


		//加载扩展配置文件
		'LOAD_EXT_CONFIG' =>'db,extend',
		
		//数据库缓存
		'DB_SQL_BUILD_CACHE' => true,
		'DATA_CACHE_TIME'=>60,
		'DB_SQL_BUILD_LENGTH' => 20, // SQL缓存的队列长度
		// 关闭字段缓存
		'DB_FIELDS_CACHE'		=>false,
		/* 日志设置 */
		// 默认不记录日志
		'LOG_RECORD' => true,
		// 只记录EMERG ALERT CRIT ERR 错误
		'LOG_LEVEL'  =>'EMERG,ALERT,CRIT,ERR',


 		'__ROOT__'      =>  __ROOT__,       // 当前网站地址
		'__APP__'       =>  __APP__,        // 当前应用地址
		'__MODULE__'    =>  __MODULE__,
		'__ACTION__'    =>  __ACTION__,     // 当前操作地址
		'__SELF__'      =>  __SELF__,       // 当前页面地址
		'__CONTROLLER__'=>  __CONTROLLER__,
		'__URL__'       =>  __CONTROLLER__,

	);
?>
