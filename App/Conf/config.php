<?php
/*
 * config.php
 * cnxzcxy <cnxzcxy@gmail.com>
 * Wed Nov 09 03:07:32 GMT 2011 
 */
if (IS_SAE) {
	return array(
    'DB_TYPE'=>'mysql',
  	'DB_HOST'=>SAE_MYSQL_HOST_M,
  	'DB_NAME'=>SAE_MYSQL_DB,
  	'DB_USER'=>SAE_MYSQL_USER,
  	'DB_PWD'=>SAE_MYSQL_PASS,
  	'DB_PORT'=>SAE_MYSQL_PORT,
  	'DB_PREFIX'=>'ipb_',
  	'STORAGE_DOMAIN'=>'ipaobu',
  	'WB_AKEY'=>1559177051,
		'WB_SKEY'=>'*',
		'SITE_URL'=>'*',
		'RENREN_API_KEY'=>'*',
		'RENREN_API_SKEY'=>'*',
		'URL_MODEL'=>2,
  );
}else{
  return array(
    'DB_TYPE'=>'mysql',
  	'DB_HOST'=>'localhost',
  	'DB_NAME'=>'ipaobu',
  	'DB_USER'=>'root',
  	'DB_PWD'=>'',
  	'DB_PREFIX'=>'ipb_',
		'APP_DEBUG'=>false,
		'TMPL_CACHE_ON'=>false,
		'WB_AKEY'=>*,
		'WB_SKEY'=>'*',
		'SITE_URL'=>'*',
		'RENREN_API_KEY'=>'*',
		'RENREN_API_SKEY'=>'*',
  );  
}
?>