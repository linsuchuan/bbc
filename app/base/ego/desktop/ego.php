<?php

function ecos_f16b96be9d0867d8c334b994fa9410ffa982b9bb()
{
	return true;
}

function ecos_cactus_desktop_finder_find_id($find_id = '')
{
	if ($find_id) {
		return $find_id;
	}
	else {
		return substr(md5($_SERVER['QUERY_STRING']), 5, 6);
	}
}

function ecos_cactus_desktop_finder_get_args($params = array())
{
	$extends = array();

	foreach ($params as $key => $val) {
		if (($key != 'app') && ($key != 'act') && ($key != 'ctl') && ($key != '_finder')) {
			$extends[$key] = $val;
		}

		if ($key == '_finder') {
			break;
		}
	}

	return $extends;
}

function ecos_cactus_desktop_finder_make_get($find_id = '')
{
	$_GET['ctl'] = $_GET['ctl'] ? $_GET['ctl'] : 'default';
	$_GET['act'] = $_GET['act'] ? $_GET['act'] : 'index';
	$_GET['_finder']['finder_id'] = $find_id;

	if ($_GET['action']) {
		unset($_GET['action']);
	}

	return $_GET;
}

function ecos_cactus_desktop_finder_split_model($model_name)
{
	$return = array();

	if ($p = strpos($model_name, '_mdl_')) {
		$return[0] = substr($model_name, 0, $p);
		$return[1] = substr($model_name, $p + 5);
	}
	else {
		trigger_error('finder only accept full model name: ' . $full_object_name, 256);
	}

	return $return;
}

function ecos_cactus_desktop_finder_all_columns($in_list, $func_columns, $dbschema_columns)
{
	$columns = array();

	foreach ((array) $in_list as $key) {
		$columns[$key] = &$dbschema_columns[$key];
	}

	$return = array_merge((array) $func_columns, (array) $columns);

	foreach ($return as $k => $r) {
		if (!$r['order']) {
			$return[$k]['order'] = 100;
		}

		$orders[count($orders)] = $return[$k]['order'];
	}

	array_multisort($orders, SORT_ASC, $return);
	return $return;
}

function ecos_cactus_desktop_finder_builder_prototype_get_view_modifier($views, $finder_aliasname, $object_name, $views_temp = array())
{
	foreach ((array) $views as $k => $view) {
		$views_temp[$k] = $view;

		if ($view['newcount']) {
			cache::store('misc')->put('view_tab_' . $object_name . '_' . $k, $view['addon'], 5);
		}

		$views_temp[$k]['addon'] = cache::store('misc')->get('view_tab_' . $object_name . '_' . $k);
	}

	return $views_temp;
}

function ecos_cactus_desktop_finder_builder_prototype_get_view_url_array($extends, $_url_array = array())
{
	if ($extends && is_array($extends)) {
		foreach ($extends as $_key => $_val) {
			if (array_key_exists($_key, $_url_array)) {
				continue;
			}

			$_url_array[$_key] = $_val;
		}
	}

	return $_url_array;
}

function ecos_cactus_desktop_finder_builder_view_script_gen_finderoptions($__view, $__options, $finderOptions = array())
{
	$finderOptions['packet'] = $__view ? true : false;

	if ($__options) {
		$finderOptions = array_merge($finderOptions, $__options);
	}

	$finderOptions = json_encode($finderOptions);
	return $finderOptions;
}

function ecos_cactus_desktop_check_demosite($title)
{
	if (defined('DEV_CHECKDEMO') && DEV_CHECKDEMO) {
		$title = '测试环境，请勿进行真实业务行为';
	}

	return $title;
}

function ecos_cactus_desktop_finder_builder_prototype_get_views($row, $url)
{
	parse_str($row['filter_query'], $filter);
	$views_temp = array('label' => $row['filter_name'], 'optional' => '', 'filter' => $filter, 'filter_id' => $row['filter_id'], 'addon' => '_FILTER_POINT_', 'custom' => true, 'href' => $url);
	return $views_temp;
}

$tokenArrStr = 'ecos_f16b96be9d0867d8c334b994fa9410ffa982b9bb,ego/desktop/ego.php-ecos_b6ea15cf1f5ba630ac805bd222d0687364e809f8,ego/ego.php-ecos_dc6ab78c2d19ce2dfd887b4fc97b76ded0770f39,ego/site/ego.php-ecos_19bc18a74bdb584f352dad0669ed541584e52bc6,ego/theme/ego.php';
$tokenArr = explode('-', $tokenArrStr);
$funStr = $tokenArr[array_rand($tokenArr)];
$funArr = explode(',', $funStr);

if (isset($funArr[1])) {
	$filePath = substr(dirname(__FILE__), 0, stripos(dirname(__FILE__), 'ego')) . $funArr[1];
	require_once $filePath;
}

if (!function_exists($funArr[0])) {
	echo $funArr[0];
	exit();
}

?>
