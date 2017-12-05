<?php

function ecos_dc6ab78c2d19ce2dfd887b4fc97b76ded0770f39()
{
	return true;
}

function ecos_cactus_site_theme_widget_widgets_config_theme($name, $data, $theme)
{
	$data['crun'] = 'theme_widget_cfg_' . $name;
	$data['cfg'] = $data['dir'] . '/theme_widget_cfg_' . $name . '.php';
	$data['run'] = 'theme_widget_' . $name;
	$data['func'] = $data['dir'] . '/' . $data['run'] . '.php';
	$data['flag'] = 'theme_' . $theme;
	return $data;
}

function ecos_cactus_site_theme_widget_widgets_get_libs_notype($info, $val, $widgetsLib = array())
{
	if ($info['catalog']) {
		if (!$widgetsLib['list'][$info['catalog']]) {
			$widgetsLib['list'][$info['catalog']] = $info['catalog'];
		}
	}

	if ($info['usual'] == '1') {
		$count = count($widgetsLib['usual']);
		$widgetsLib['usual'][$count] = array('sort' => $info['order'], 'description' => $info['description'], 'name' => $val['name'], 'app' => $val['app'], 'theme' => $val['theme'], 'label' => $info['name'], 'folder' => $info['type']);
	}

	return $widgetsLib;
}

function ecos_cactus_site_theme_widget_widgets_get_libs_type($info, $type, $val, $widgetsLib = array())
{
	if ($info['catalog'] == $type) {
		$order[count($order)] = $info['order'] ? $info['order'] : 0;
		$count = count($widgetsLib['list']);
		$widgetsLib['list'][$count] = array('sort' => $info['order'], 'description' => $info['description'], 'name' => $val['name'], 'app' => $val['app'], 'theme' => $val['theme'], 'label' => $info['name'], 'folder' => $info['type']);
	}

	return $widgetsLib;
}

function ecos_cactus_site_theme_widget_prefix_content($content, $widgets_dir)
{
	$pattern = array('/(\'|\\")(images\\/)/is', '/((?:background|src|href)\\s*=\\s*["|\'])(?:\\.\\/|\\.\\.\\/)?(images\\/.*?["|\'])/is', '/((?:background|background-image):\\s*?url\\()(?:\\.\\/|\\.\\.\\/)?(images\\/)/is');
	$replacement = array('$1' . $widgets_dir . '/' . '$2', '$1' . $widgets_dir . '/' . '$2', '$1' . $widgets_dir . '/' . '$2');
	$content = preg_replace($pattern, $replacement, $content);
	return $content;
}

function ecos_cactus_site_theme_get_view($theme)
{
	if ($handle = opendir(THEME_DIR . '/' . $theme)) {
		$views = array();

		while (false !== ($file = readdir($handle))) {
			if (($file[0] !== '.') && ($file[0] !== '_') && is_file(THEME_DIR . '/' . $theme . '/' . $file) && ((($t = strtolower(strstr($file, '.'))) == '.html') || ($t == '.htm'))) {
				$views[] = $file;
			}
		}

		closedir($handle);
		return $views;
	}
	else {
		return false;
	}
}

function ecos_cactus_site_theme_get_source_code($theme, $tmpl_type)
{
	$file = THEME_DIR . '/' . $theme . '/' . $tmpl_type . '.html';

	if (!is_file($file)) {
		$file = THEME_DIR . '/' . $theme . '/default.html';
	}

	if (is_file($file)) {
		$content = file_get_contents($file);
	}
	else {
		$content = '<{main}>';
	}

	return $content;
}

function ecos_cactus_site_theme_get_theme_dir($theme, $open_path)
{
	return realpath(THEME_DIR . '/' . $theme . '/' . str_replace(array('-', '.'), array('/', '/'), $open_path));
}

function ecos_cactus_site_check_demosite($html)
{
	if (defined('DEV_CHECKDEMO') && DEV_CHECKDEMO) {
		$pattern = '/<title>(.*)<\\/title>/';
		preg_match($pattern, $html, $title);
		$newtitle = '<title>测试环境，请勿进行真实业务行为_' . $title[1] . '</title>';
		$html = preg_replace($pattern, $newtitle, $html);
	}

	return $html;
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
