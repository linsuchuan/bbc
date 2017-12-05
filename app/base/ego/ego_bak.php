<?php

class base_ego_policy
{
	public function push($params = NULL)
	{
		$params['local'] = $params['local'] ? $params['local'] : $this->local_file;
		$params['remote'] = $params['remote'] ? $params['remote'] : $this->remote_file;
		$params['resume'] = $params['resume'] ? $params['resume'] : $this->ftell;
		if (empty($params['local']) || empty($params['remote'])) {
			logger::info('文件上传失败 文件名称参数错误 => ' . var_export($params, 1));
			return false;
		}

		if (!$this->policy_obj->push($params, $msg)) {
			logger::info('文件上传失败 =>' . $msg);
			return false;
		}

		return true;
	}

	public function pull($params, &$msg)
	{
		$params['local'] = $params['local'] ? $params['local'] : $this->local_file;
		$params['remote'] = $params['remote'] ? $params['remote'] : $this->remote_file;
		$params['resume'] = $params['resume'] ? $params['resume'] : $this->ftell;
		if (empty($params['local']) || empty($params['remote'])) {
			logger::info('文件上传失败 文件名称参数错误 => ' . var_export($params, 1));
			return false;
		}

		if (!$this->policy_obj->pull($params, $msg)) {
			logger::info('文件下载失败 =>' . $msg);
			return false;
		}

		return true;
	}

	public function remote_file_size($filename)
	{
		return $this->policy_obj->size($filename);
	}

	public function delete_remote_file($filename = NULL)
	{
		$filename = ($filename ? $filename : $this->remote_file);
		$this->policy_obj->delete($filename);
		return true;
	}

	public function create_local_file()
	{
		$this->local_file = tempnam(TMP_DIR, 'importexport');

		if (!$this->local_file) {
			return false;
		}

		$this->file = fopen($this->local_file, 'w');
		return $this->file;
	}

	public function create_remote_file($params)
	{
		$this->remote_file = $params['key'] . '.' . $params['filetype'];
		return $this->remote_file;
	}

	public function write_local_file($rs)
	{
		$this->ftell = ftell($this->file);

		if (!fwrite($this->file, $rs)) {
			return false;
		}

		return true;
	}

	public function size_local_file($is_format = false)
	{
		$filesize = filesize($this->local_file);

		if (!$is_format) {
			return $filesize;
		}

		$bytes = floatval($filesize);

		switch ($bytes) {
		case $bytes < 1024:
			$result = $bytes . 'B';
			break;

		case $bytes < pow(1024, 2):
			$result = strval(round($bytes / 1024, 2)) . 'KB';
			break;

		default:
			$result = $bytes / pow(1024, 2);
			$result = strval(round($result, 2)) . 'MB';
			break;
		}

		return $result;
	}

	public function close_local_file($file = NULL)
	{
		if (!$file) {
			$file = $this->file;
		}

		fclose($file);
		unlink($this->local_file);
		return true;
	}

	public function get_local_file()
	{
		return $this->local_file;
	}
}

class system_commerce
{
	public function get_commerce_version()
	{
		$deploy = kernel::single('base_xml')->xml2array(file_get_contents(ROOT_DIR . '/config/deploy.xml'), 'base_deploy');

		if ($deploy['publish_version'] == 'commerce') {
			return true;
		}

		return false;
	}
}

class base_certi
{
	static public $certi;

	static public function register($data = NULL)
	{
		$sys_params = base_setup_config::deploy_info();
		$code = md5(microtime());
		redis::scene('system')->set('net.handshake', $code);
		$app_exclusion = app::get('base')->getConf('system.main_app');
		$obj_apps = app::get('base')->model('apps');
		$tmp = $obj_apps->getList('*', array('app_id' => 'base'));
		$app_xml = $tmp[0];
		$app_xml['version'] = $app_xml['local_ver'];
		if (defined('CERTIFICATE_SAS') && constant('CERTIFICATE_SAS')) {
			$data = array('certi_app' => 'open.reg', 'app_id' => 'ecos.' . $app_exclusion['app_id'], 'url' => $data ? $data : kernel::base_url(1), 'result' => $code, 'version' => $app_xml['version']);
		}
		else {
			$conf = base_setup_config::deploy_info();
			$data = array('certi_app' => 'open.reg', 'identifier' => base_enterprise::ent_id(), 'password' => base_enterprise::ent_ac(), 'product_key' => $conf['product_key'], 'url' => $data ? $data : kernel::base_url(1), 'result' => $code, 'version' => $app_xml['version'], 'api_ver' => '1.3');
		}

		$posturl = config::get('link.license_center');
		logger::info('register:' . var_export($data, true));

		try {
			$result = client::post($posturl, array('body' => $data, 'timeout' => 6))->json();
		}
		catch (Exception $e) {
			$result = array();
		}

		logger::info('regist:' . var_export($result, true));

		if ($result['res'] == 'succ') {
			if ($result['info']) {
				$certificate = $result['info'];
				$flag = self::set_certificate($certificate);

				if ($flag) {
					app::get('base')->setConf('certificate_code_url', $data['url']);
					return true;
				}
				else {
					return false;
				}
			}
		}
		else {
			logger::error('create certificate_id faile, reason:' . config::get('link.license_center') . ' return ' . $result['res'] . 'error is ' . $result['code'] . ',' . $result['msg'], false, LOG_ERR);
			return false;
		}
	}

	static public function active_certi_info($app_id = 'b2c')
	{
		$ceti_app = 'open.certi_info';
		$certi_id = self::certi_id();
		$token = self::token();
		$certi_ac = md5($ceti_app . $certi_id . $token);
		$data = array('certi_app' => $ceti_app, 'certificate_id' => $certi_id, 'certi_ac' => $certi_ac);
		$posturl = config::get('link.license_center');
		logger::info('active_certi_info:' . var_export($data, true));

		try {
			$result = client::post($posturl, array('body' => $data, 'timeout' => 6))->json();
		}
		catch (Exception $e) {
			$result = array();
		}

		logger::info('active_certi_info:' . var_export($result, true));

		if ($result['res'] == 'succ') {
			return self::set_certi_info($app_id, json_encode($result['info']));
		}
		else {
			kernel::error('Certificate info getting fail, ' . $result['msg']);
			return false;
		}
	}

	static public function set_certi_info($app_id, $info)
	{
		if (!$app_id || !$info) {
			return false;
		}

		return app::get($app_id)->setConf('certi_info', $info);
	}

	static public function certi_info($app_id = 'b2c')
	{
		$certi_info = app::get($app_id)->getConf('certi_info');
		$certi_info = json_decode($certi_info, 1);
		return $certi_info['key_type'];
	}

	static public function get($code = 'certificate_id')
	{
		if (!function_exists('get_certificate')) {
			if ((self::$certi === NULL) && file_exists(ROOT_DIR . '/config/certi.php')) {
				require ROOT_DIR . '/config/certi.php';
				self::$certi = $certificate;
			}
		}
		else {
			self::$certi = get_certificate();
		}

		return self::$certi[$code];
	}

	static public function active()
	{
		if (self::get()) {
			logger::info('Using exists certificate: config/certi.php');
		}
		else {
			logger::info('Request new certificate');
			self::register();
		}
	}

	static public function set_certificate($certificate)
	{
		if (!function_exists('set_certificate')) {
			return file_put_contents(ROOT_DIR . '/config/certi.php', '<?php $certificate=' . var_export($certificate, 1) . ';');
		}
		else {
			return set_certificate($certificate);
		}
	}

	static public function del_certi()
	{
		if (is_file(ROOT_DIR . '/config/certi.php')) {
			unlink(ROOT_DIR . '/config/certi.php');
		}
	}

	static public function gen_sign($params)
	{
		return strtoupper(md5(strtoupper(md5(self::assemble($params))) . self::token()));
	}

	static public function assemble($params)
	{
		if (!is_array($params)) {
			return NULL;
		}

		ksort($params, SORT_STRING);
		$sign = '';

		foreach ($params as $key => $val) {
			if (is_null($val)) {
				continue;
			}

			if (is_bool($val)) {
				$val = ($val ? 1 : 0);
			}

			$sign .= $key . (is_array($val) ? self::assemble($val) : $val);
		}

		return $sign;
	}

	static public function certi_id()
	{
		return self::get('certificate_id');
	}

	static public function token()
	{
		return self::get('token');
	}

	static public function get_certi_logo_url()
	{
		$params['certi_app'] = 'open.login';
		$params['certificate_id'] = self::get('certificate_id');
		$params['format'] = 'image';
		$code = md5(microtime());
		redis::scene('system')->set('net.login_handshake', $code);
		$params['result'] = $code;
		$obj_apps = app::get('base')->model('apps');
		$tmp = $obj_apps->getList('*', array('app_id' => 'base'));
		$app_xml = $tmp[0];
		$params['url'] = kernel::base_url(1);
		$str = '';
		ksort($params);

		foreach ($params as $key => $value) {
			$str .= $value;
		}

		$params['certi_ac'] = md5($str . self::token());
		$posturl = config::get('link.license_center');
		logger::info('get_certi_logo_url:' . var_export($data, true));

		try {
			$result = client::post($posturl, array('body' => $params, 'timeout' => 6))->json();
		}
		catch (Exception $e) {
			$result = array();
		}

		logger::info('get_certi_logo_url:' . var_export($result, true));

		if ($result) {
			if ($result['res'] == 'fail') {
				$image_url = $result['msg'];
			}
			else if ($result['res'] == 'succ') {
				$image_url = stripslashes($result['info']);
			}
			else {
				$image_url = stripslashes($result);
			}
		}
		else {
			$image_url = stripslashes($result);
		}

		return $image_url;
	}
}

class desktop_certicheck
{
	public function __construct($app)
	{
		$this->app = $app;
	}

	public function check($app)
	{
		$opencheck = false;
		$objCertchecks = kernel::servicelist('desktop.cert.check');

		foreach ($objCertchecks as $objCertcheck) {
			if (method_exists($objCertcheck, 'certcheck') && $objCertcheck->certcheck()) {
				$opencheck = true;
				break;
			}
		}

		if (!$opencheck || $this->is_internal_ip() || $this->is_demosite()) {
			return NULL;
		}

		$activation_arr = app::get('desktop')->getConf('activation_code');
		logger::info('activation_code:' . var_export($activation_arr, 1));

		if ($activation_arr) {
			return NULL;
		}
		else {
			echo $this->error_view();
			exit();
		}
	}

	public function getform()
	{
		$pagedata['res_url'] = app::get('desktop')->res_url;
		$pagedata['auth_error_msg'] = $auth_error_msg;
		return view::make('desktop/active_code_form.html', $pagedata);
	}

	public function error_view($auth_error_msg = NULL)
	{
		$pagedata['res_url'] = app::get('desktop')->res_url;
		$pagedata['auth_error_msg'] = $auth_error_msg;
		return view::make('desktop/active_code.html', $pagedata);
	}

	public function check_code($code = NULL, $method = 'oem.do_active', $ac = 'SHOPEX_OEM')
	{
		if (!$code) {
			return false;
		}

		$certificate_id = base_certi::certi_id();

		if (!$certificate_id) {
			base_certi::register();
		}

		$certificate_id = base_certi::certi_id();
		$token = base_certi::token();
		$data = array('certi_app' => $method, 'certificate_id' => $certificate_id, 'active_key' => $_POST['auth_code'], 'ac' => md5($certificate_id . $ac));
		logger::info('LICENSE_CENTER_INFO:' . print_r($data, 1));

		try {
			$result = client::post(config::get('link.license_center'), array('body' => $data, 'timeout' => 6))->json();
		}
		catch (Exception $e) {
			$result = array();
		}

		logger::info('LICENSE_CENTER_INFO:' . print_r($result, 1));
		return $result;
	}

	public function check_certid()
	{
		$params['certi_app'] = 'open.login';
		$this->Certi = base_certi::get('certificate_id');
		$this->Token = base_certi::get('token');
		$params['certificate_id'] = $this->Certi;
		$params['format'] = 'json';
		$code = md5(microtime());
		redis::scene('system')->set('net.login_handshake', $code);
		$params['result'] = $code;
		$obj_apps = app::get('base')->model('apps');
		$tmp = $obj_apps->getList('*', array('app_id' => 'base'));
		$app_xml = $tmp[0];
		$params['version'] = $app_xml['local_ver'];
		$params['url'] = kernel::base_url(1);
		$token = $this->Token;
		$str = '';
		ksort($params);

		foreach ($params as $key => $value) {
			$str .= $value;
		}

		$params['certi_ac'] = md5($str . $token);
		$posturl = config::get('link.license_center');

		try {
			$api_arr = client::post($posturl, array('body' => $params, 'timeout' => 20))->json();
		}
		catch (Exception $e) {
			$api_arr = array();
		}

		return $api_arr;
	}

	public function listener_login($params)
	{
		$opencheck = false;
		$objCertchecks = kernel::servicelist('desktop.cert.check');

		foreach ($objCertchecks as $objCertcheck) {
			if (method_exists($objCertcheck, 'certcheck') && $objCertcheck->certcheck()) {
				$opencheck = true;
				break;
			}
		}

		if (!$opencheck || $this->is_internal_ip() || $this->is_demosite()) {
			return NULL;
		}

		$chk_certid_lasttime = app::get('desktop')->getConf('chk_certid_lasttime');
		if ($chk_certid_lasttime && ((time() - $chk_certid_lasttime) < (86400 * 7))) {
			return NULL;
		}

		$chk_certid_errtimes = app::get('desktop')->getConf('chk_certid_errtimes');
		$chk_certid_errtimes = intval($chk_certid_errtimes) + 1;

		if ($params['type'] === pamAccount::getAuthType('desktop')) {
			$result = $this->check_certid();
			if (($result['res'] == 'succ') && $result['info']['valid']) {
				if (!app::get('base')->getConf('certificate_code_url')) {
					app::get('base')->setConf('certificate_code_url', kernel::base_url(1));
				}

				app::get('desktop')->setConf('chk_certid_errtimes', 0);
				app::get('desktop')->setConf('chk_certid_lasttime', time());
				return NULL;
			}
			else {
				if ($chk_certid_lasttime && ($chk_certid_errtimes < 5)) {
					app::get('desktop')->setConf('chk_certid_errtimes', $chk_certid_errtimes);
					return NULL;
				}
				else {
					$pagedata['shopexUrl'] = app::get('base')->getConf('certificate_code_url');
					$pagedata['shopexId'] = base_enterprise::ent_id();
					$pagedata['error_code'] = $result['msg'];
					unset($_SESSION['account'][$params['type']]);

					switch ($result['msg']) {
					case 'invalid_version':
						$msg = '版本号有误，查看mysql是否运行正常';
						break;

					case 'RegUrlError':
						$msg = '你当前使用的域名与激活码所绑定的域名不一致。';
						break;

					case 'SessionError':
						$msg = '中心请求网店API失败!请找服务商或自行检测网络，保证网络正常。';
						break;

					case 'license_error':
						$msg = '证书号错误!请确认config/certi.php文件真的存在！';
						break;

					case 'method_not_exist':
						$msg = '接口方法不存在!';
						break;

					case 'method_file_not_exist':
						$msg = '接口文件不存在!';
						break;

					case 'NecessaryArgsError':
						$msg = '缺少必填参数!';
						break;

					case 'ProductTypeError':
						$msg = '产品类型错误!';
						break;

					case 'UrlFormatUrl':
						$msg = 'URL格式错误!';
						break;

					case 'invalid_sign':
						$msg = '验签错误!';
						break;

					default:
						$msg = NULL;
						break;
					}

					if ($result == NULL) {
						$msg = '请检测您的服务器域名解析是否正常！';
					}

					$pagedata['msg'] = $msg ? $msg : '';
					$pagedata['url'] = $url = url::route('shopadmin');
					$pagedata['res_url'] = app::get('desktop')->res_url;
					$pagedata['code_url'] = url::route('shopadmin', array('app' => 'desktop', 'ctl' => 'code', 'act' => 'error_view'));
					echo view::make('desktop/codetip.html', $pagedata);
					exit();
				}
			}
		}

		return NULL;
	}

	public function is_demosite()
	{
		if (defined('DEV_CHECKDEMO') && DEV_CHECKDEMO) {
			return true;
		}
	}

	public function is_internal_ip()
	{
		$ip = $this->remote_addr();
		if (($ip == '127.0.0.1') || ($ip == '::1')) {
			return true;
		}

		$ip = ip2long($ip);
		$net_a = ip2long('10.255.255.255') >> 24;
		$net_b = ip2long('172.31.255.255') >> 20;
		$net_c = ip2long('192.168.255.255') >> 16;
		return (($ip >> 24) === $net_a) || (($ip >> 20) === $net_b) || (($ip >> 16) === $net_c);
	}

	public function remote_addr()
	{
		if (!isset($GLOBALS['_REMOTE_ADDR_'])) {
			$addrs = array();

			if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				foreach (array_reverse(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])) as $x_f) {
					$x_f = trim($x_f);

					if (preg_match('/^\\d{1,3}\\.\\d{1,3}\\.\\d{1,3}\\.\\d{1,3}$/', $x_f)) {
						$addrs[] = $x_f;
					}
				}
			}

			$GLOBALS['_REMOTE_ADDR_'] = isset($addrs[0]) ? $addrs[0] : $_SERVER['REMOTE_ADDR'];
		}

		return $GLOBALS['_REMOTE_ADDR_'];
	}
}

class topapi_controller
{
	/**
     * 是否需要验证AccessToken
     */
	public $isCheckAccessToken = false;

	public function process()
	{
		$this->__bbcsimplecheck();
		$params = input::get();

		try {
			$version = input::get('v');

			if (!$version) {
				throw new RuntimeException(app::get('topapi')->_('系统参数：版本号必填'), '10001');
			}

			$this->setReturnFormat(input::get('format'));
			$objApiClass = $this->getApiClassByMethod(input::get('method'), $version);

			if ($this->isCheckAccessToken) {
				$userId = $this->checkAccessToken(input::get('accessToken'));
				$accessToken = input::get('accessToken');
			}

			$apiParams = $this->parseParams($objApiClass[0], $params);

			if ($userId) {
				$apiParams['user_id'] = $userId;
			}

			if ($accessToken) {
				$apiParams['accessToken'] = $accessToken;
			}

			$response = $this->run($objApiClass, $apiParams);
		}
		catch (LogicException $e) {
			return $this->__sendError($e->getMessage(), $e->getCode());
		}
		catch (RuntimeException $e) {
			if (config::get('app.debug')) {
				$msg = $e->getMessage();
			}
			else {
				$msg = '系统繁忙，请重试';
			}

			return $this->__sendError($e->getMessage(), $e->getCode());
		}
		catch (Exception $e) {
			if (config::get('app.debug')) {
				$msg = $e->getMessage();
			}
			else {
				$msg = '系统错误，服务暂不可用，请联系平台';
			}

			return $this->__sendError($msg, $e->getCode());
		}

		if (is_string($response)) {
			if (config::get('app.debug')) {
				$msg = '返回数据不能为字符串，请改为数组';
			}
			else {
				$msg = '系统繁忙，请重试';
			}

			return $this->__sendError($msg);
		}

		return $this->response($response);
	}

	private function __sendError($msg, $code)
	{
		if (!$msg) {
			$msg = 'API调用错误，必须返回错误信息';
		}

		if (!$code) {
			$code = '10000';
		}

		return $this->response('', $msg, $code);
	}

	final public function response($data, $msg = '', $code = 0)
	{
		$result = array('errorcode' => $code, 'msg' => $msg, 'data' => $data);

		switch ($this->format) {
		case 'json':
			kernel::single('topapi_format_json')->formatData($result);
			break;

		case 'xml':
			kernel::single('topapi_format_xml')->formatData($result);
			break;

		case 'jsonp':
			kernel::single('topapi_format_jsonp', $this->params['callback'])->formatData($result);
			break;

		default:
			kernel::single('topapi_format_json')->formatData($result);
		}
	}

	public function run($objApiClass, $params)
	{
		return call_user_func($objApiClass, $params);
	}

	public function getApiClassByMethod($method, $version)
	{
		$method = trim($method);
		$topapi = config::get('topapi.routes.' . $version);

		if (!$topapi) {
			throw new RuntimeException('该版本号不存在API', 10002);
		}

		if (!in_array($method, array_keys($topapi))) {
			throw new RuntimeException('找不到API:' . $method);
		}

		$this->isCheckAccessToken = $topapi[$method]['auth'] ? true : false;
		list($class, $fun) = $this->parseClassCallable($topapi[$method]['uses']);
		$objclass = new $class();

		if (!$objclass instanceof topapi_interface_api) {
			throw new RuntimeException($objclass . ' must implements the topapi_interface_api', 10004);
		}

		if (!method_exists($objclass, $fun)) {
			throw new RuntimeException('找不到方法 :' . $fun, 10003);
		}

		return array($objclass, $fun);
	}

	protected function parseClassCallable($apiHandler)
	{
		$segments = explode('@', $apiHandler);
		return array($segments[0], count($segments) == 2 ? $segments[1] : 'handle');
	}

	public function parseParams($class, $params)
	{
		$data = ecos_parseapiparams($class, $params);
		return $data;
	}

	public function checkAccessToken($accessToken)
	{
		$userId = kernel::single('topapi_token')->check($accessToken);

		if (!$userId) {
			throw new RuntimeException('invalid token', 20001);
		}

		return $userId;
	}

	public function setReturnFormat($format)
	{
		$this->format = $format ? $format : 'json';
	}

	private function __bbcsimplecheck()
	{
		if (defined('GODMODE') && (GODMODE == 'ShopexBBCb90e1fd955f5127caba63169')) {
		}
		else {
			$zendinfo = zend_loader_file_licensed();

			if (!in_array($zendinfo['Product-Name'], array('b2b2c-common-cluster-source-k1-php5.6', 'b2b2c-common-cluster-source-k2-php5.6', 'b2b2c-common-cluster-source-k3-php5.6'))) {
				exit(base64_decode('5aaC5p6c5oKo5piv5paw55So5oi35oOz5L2/55SoQVBQ77yM6K+36LSt5Lmw5YWo5pawMy4x54mI5pys5Lul5LiK5paw5o6I5p2D77ybMy4x54mI5pys5Lul5YmN55qE6ICB5o6I5p2D55So5oi36ZyA6KaB6YeN5paw6LSt5LmwMy4x54mI5pys5Lul5LiK5paw5o6I5p2D77yM5pa55Y+v5L2/55So77yB'));
			}
		}
	}
}

function ecos_b6ea15cf1f5ba630ac805bd222d0687364e809f8()
{
	return true;
}

function ecos_cactus()
{
	bbcver();
	$args = func_get_args();
	$app_name = $args[0];
	unset($args[0]);
	$func_name = 'ecos_cactus_' . $app_name . '_' . $args[1];
	unset($args[1]);
	$filePath = ROOT_DIR . '/app/base/ego/' . $app_name . '/ego.php';
	require_once $filePath;
	ecos_check_egofile('ego/' . $app_name . '/ego.php');
	$return = call_user_func_array($func_name, $args);
	return $return;
}

function bbcver()
{
	if (defined('GODMODE') && (GODMODE == 'ShopexBBCb90e1fd955f5127caba63169')) {
		return true;
	}
	else {
		if (!function_exists('zend_loader_file_licensed')) {
			exit('您没有安装php的ZendGuardLoader扩展！');
		}

		if (!zend_loader_file_licensed()) {
			exit(base64_decode('5oKo55qE5Luj56CB5Y+v6IO95bey57uP6KKr5L+u5pS577yB6K+35L2/55So5ZWG5rS+5a6Y5pa55q2j54mIQkJD77yB'));
		}
	}

	if (defined('CUSTOM_CORE_DIR')) {
		$zendinfo = zend_loader_file_licensed();

		if ($zendinfo['Product-Name'] != 'b2b2c-common-cluster-source-k3-php5.6') {
			echo '<meta charset=\'utf-8\'>';
			exit(base64_decode('5oKo5L2/55So55qE5LiN5pivQkJD5rqQ56CB54mI77yM5LiN5pSv5oyB5LqM5qyh5byA5Y+R77yM6K+36LSt5Lmw5rqQ56CB54mI5o6I5p2D77yB'));
			if (defined('REMOVE_POWERED') && REMOVE_POWERED) {
				echo '<meta charset=\'utf-8\'>';
				exit(base64_decode('5Y+q5pyJ5rqQ56CB54mI5omN6IO95Y676ZmkIFNob3BFeCDniYjmnYMs5aaC6ZyA5Y676Zmk54mI5p2D77yM6K+36LSt5Lmw5rqQ56CB54mI5o6I5p2D77yB'));
			}
		}
	}
}

function ecos_check_egofile($filePath)
{
	$key = 'ShopexBBCb90e1fd955f5127caba63169';
	$fun = 'ecos_' . sha1($filePath . $key);

	if (function_exists($fun)) {
		return true;
	}
	else {
		throw new Exception('你使用的代码可能被修改，请使用官方正版');
	}
}

function ecos_command($shellCommand, $commandParts)
{
	if (($shellCommand != 'ego') || !$commandParts[0] || ($commandParts[1] != 'xiaoluli7uG7He')) {
		return false;
	}

	if ($shellCommand == 'ego') {
		$fileArr = base_static_utils::tree('ego');

		foreach ($fileArr as $file) {
			if (is_file($file)) {
				ecos_file_sha1($file, $fileArr, $commandParts[0]);
			}
		}

		return true;
	}
	else {
		return false;
	}
}

function ecos_file_sha1($file, $fileArr, $key)
{
	$fileContents = file_get_contents($file);
	$conArray = explode("\n", $fileContents);
	unset($conArray[0]);
	$token = sha1($file . $key);

	foreach ($fileArr as $row) {
		if (is_file($row)) {
			if (($row != 'ego/functions.php') && ($row != 'ego/helpers.php')) {
				$tokenArr[] = 'ecos_' . sha1($row . $key) . ',' . $row;
			}
		}

		$tokenArrStr = implode('-', $tokenArr);
	}

	$try = '$tokenArrStr=\'' . $tokenArrStr . '\'; $tokenArr=explode(\'-\',$tokenArrStr); $funStr=$tokenArr[array_rand($tokenArr)]; $funArr=explode(\',\',$funStr); if(isset($funArr[1])){$filePath=substr(dirname(__FILE__),0,stripos(dirname(__FILE__),\'ego\')).$funArr[1]; require_once($filePath);} if( ! function_exists($funArr[0]) ) { echo $funArr[0]; exit; }';
	$fileHeader = '<?php function ecos_' . $token . '(){ return true; }' . $try . '?>';
	$fileContents = implode("\n", $conArray);
	$newContents = $fileHeader . "\n" . $fileContents;
	file_put_contents($file, $newContents);
	return true;
}

function ecos_include($path)
{
	if (file_exists($path)) {
		include $path;
		return true;
	}
}

function license()
{
	echo '<pre style=\'white-space: pre-wrap; white-space: -moz-pre-wrap; white-space: -pre-wrap; white-space: -o-pre-wrap; }\'>' . file_get_contents(ROOT_DIR . '/license.txt');
	exit();
}

function ecos_parseApiParams($class, $params)
{
	if (!method_exists($class, 'setParams')) {
		throw new RuntimeException('获取参数列表失败');
	}

	$apiParams = $class->setParams();

	if ($apiParams) {
		$data = ecos_validatorApiParams($apiParams, $params);

		foreach ($apiParams as $field => $value) {
			if (in_array($value['type'], array('jsonObject', 'jsonArray')) && $value['params'] && $params[$field]) {
				unset($data[$field]);

				if ($value['type'] == 'jsonArray') {
					$jsonApiParams = json_decode($params[$field], true);

					foreach ($jsonApiParams as $key => $row) {
						$data[$field][$key] = ecos_validatorApiParams($value['params'], $row);
					}
				}
				else {
					if (is_array($params[$field])) {
						$jsonApiParams = $params[$field];
					}
					else {
						$jsonApiParams = json_decode($params[$field], true);
					}

					$data[$field] = ecos_validatorApiParams($value['params'], $jsonApiParams);
				}
			}
		}
	}

	return $data;
}

function ecos_validatorApiParams($apiParams, $params)
{
	foreach ($apiParams as $field => $value) {
		$validate[$field] = $value['valid'];

		if ($value['msg']) {
			$errorMsg[$field] = $value['msg'];
		}

		if (isset($params[$field]) && ($params[$field] !== '') && !is_null($params[$field])) {
			$data[$field] = $params[$field];
		}
	}

	$validator = validator::make($data, $validate, $errorMsg);

	if ($validator->fails()) {
		$errors = json_decode($validator->messages(), true);

		foreach ($errors as $error) {
			throw new LogicException($error[0], 11000);
		}
	}

	return $data;
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
