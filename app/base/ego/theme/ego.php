<?php

function ecos_19bc18a74bdb584f352dad0669ed541584e52bc6()
{
	return true;
}

function ecos_cactus_theme_fix_theme_media($code)
{
	$from = array('/((?:background|src|href)\\s*=\\s*["|\'])(?:\\.\\/|\\.\\.\\/)?(images\\/.*?["|\'])/is', '/((?:background|background-image):\\s*?url\\()(?:\\.\\/|\\.\\.\\/)?(images\\/)/is');
	$themeUrl = kernel::get_themes_host_url();
	$to = array(sprintf('\\1%s\\2', $themeUrl . '/' . theme::getThemeName() . '/'), sprintf('\\1%s\\2', $themeUrl . '/' . theme::getThemeName() . '/'));
	return preg_replace($from, $to, $code);
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
