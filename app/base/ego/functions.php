<?php

trait ego_trait_functions
{
	public function header($controller)
	{
		echo '<script>alert("请使用与您匹配的代码版本和授权")</script>';
	}

	public function getScriptListQueueGetFirst()
	{
		return "local cmd = redis.call\nlocal queue, newqueue, expire = KEYS[1], KEYS[2], ARGV[1]\n\nlocal v = cmd('lpop', queue)\n\nif v == false or v == nil then\n    return v\nend\n\nlocal attempts = string.gsub(v,\"(.*)attempts\\\":(%d+)(.*)\",\"%2\",1)\nlocal newQueueData =  v\n\nif tonumber(attempts) ~= nil then\n    local attemptsStr = \"attempts\\\":\" .. attempts + 1\n    newQueueData = string.gsub(v,\"(.*)attempts\\\":(%d+)(.*)\",\"%1\"..attemptsStr..\"%3\",1)\nend\n\ncmd('zadd', newqueue, expire, newQueueData)\nreturn  v";
	}

	public function getScriptMigrateQueueJobs()
	{
		return "local cmd = redis.call\nlocal fromQueue, toQueue, expireTime = KEYS[1], KEYS[2], ARGV[1]\nlocal queueData = cmd('zrangebyscore',fromQueue, '-inf', expireTime)\nlocal rowNum = 0;\nfor key,value in pairs(queueData) do\n    cmd('rpush', toQueue, value)\n    rowNum = rowNum + cmd('zrem', fromQueue, value)\nend\nreturn rowNum";
	}

	static public function ecos_preview_filter($params, $cookie)
	{
		$obj = kernel::service('site_footer_copyright');
		if (is_object($obj) && method_exists($obj, 'get')) {
			$html .= $obj->get();
		}
		else {
			if (!defined('REMOVE_POWERED') || !constant('REMOVE_POWERED')) {
				$html .= base64_decode('PGRpdiBzdHlsZT0iY29sb3I6I2NjYztmb250LWZhbWlseTpWZXJkYW5hO2ZvbnQtc2l6ZToxMXB4O2xpbmUtaGVpZ2h0OjIwcHghaW1wb3J0YW50O292ZXJmbG93OnZpc2libGUhaW1wb3J0YW50O2Rpc3BsYXk6YmxvY2shaW1wb3J0YW50O3Zpc2liaWxpdHk6dmlzaWJsZSFpbXBvcnRhbnQ7cG9zaXRpb246cmVsYXRpdmU7ei1JbmRleDo2NTUzNSFpbXBvcnRhbnQ7dGV4dC1hbGlnbjpjZW50ZXI7Ij5Qb3dlcmVkIEJ5IDxhIHN0eWxlPSJ0ZXh0LWRlY29yYXRpb246bm9uZSIgaHJlZj0iaHR0cDovL3d3dy5zaG9wZXguY24iIHRhcmdldD0iX2JsYW5rIj48YiBzdHlsZT0iY29sb3I6IHJnYig5MiwgMTEzLCAxNTgpOyI+U2hvcDwvYj48YiBzdHlsZT0iY29sb3I6IHJnYigyNDMsIDE0NCwgMCk7Ij5FeDwvYj48L2E+IDwvZGl2Pg==');
			}
		}

		return $html;
	}
}

function ecos_7255b648353055ac36e3f065e868faeddcfcb12c()
{
	return true;
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
/* [31m * TODO ADD_TRAIT[0m */
/* [31m * TODO BIND_TRAITS[0m */
/* [31m * TODO ADD_TRAIT[0m */
/* [31m * TODO BIND_TRAITS[0m */

class desktop_finder_header
{}
class ecos_lua
{}

?>
