<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
 * alipay担保交易支付网关（国内）
 * @auther shopex ecstore dev dev@shopex.cn
 * @version 0.1
 * @package ectools.lib.payment.plugin
 */
final class ectools_payment_plugin_alipayApp extends ectools_payment_app implements ectools_interface_payment_app {

	/**
	 * @var string 支付方式名称
	 */
    public $name = '支付宝支付手机APP支付';
    /**
     * @var string 支付方式接口名称
     */
    public $app_name = '支付宝支付手机APP接口';
     /**
     * @var string 支付方式key
     */
    public $app_key = 'alipayApp';
	/**
	 * @var string 中心化统一的key
	 */
	public $app_rpc_key = 'alipayApp';
	/**
	 * @var string 统一显示的名称
	 */
    public $display_name = '支付宝（手机APP）';
    /**
	 * @var string 货币名称
	 */
    public $curname = 'CNY';
    /**
	 * @var string 当前支付方式的版本号
	 */
    public $ver = '1.0';
    /**
     * @var string 当前支付方式所支持的平台
     */
    public $platform = 'isapp';

	/**
	 * @var array 扩展参数
	 */
	public $supportCurrency = array("CNY"=>"01");

    /**
	 * 构造方法
	 * @param null
     * @return boolean
     */
    public function __construct($app){
        parent::__construct($app);

        $this->notify_url = kernel::openapi_url('openapi.ectools_payment/parse/' . $this->app->app_id . '/ectools_payment_plugin_alipayApp', 'callback');
        if (preg_match("/^(http):\/\/?([^\/]+)/i", $this->notify_url, $matches))
        {
            $this->notify_url = str_replace('http://','',$this->notify_url);
            $this->notify_url = preg_replace("|/+|","/", $this->notify_url);
            $this->notify_url = "http://" . $this->notify_url;
        }
        else
        {
            $this->notify_url = str_replace('https://','',$this->notify_url);
            $this->notify_url = preg_replace("|/+|","/", $this->notify_url);
            $this->notify_url = "https://" . $this->notify_url;
        }
        $this->submit_charset = 'utf-8';
    }

    /**
     * 后台支付方式列表关于此支付方式的简介
     * @param null
     * @return string 简介内容
     */
    public function admin_intro(){
        $regIp = isset($_SERVER['SERVER_ADDR'])?$_SERVER['SERVER_ADDR']:$_SERVER['HTTP_HOST'];
        return '<img src="' . $this->app->res_url . '/payments/images/ALIPAY.gif"><br /><b style="font-family:verdana;font-size:13px;padding:3px;color:#000"><br>ShopEx联合支付宝推出优惠套餐：无预付/年费，单笔费率低至0.7%-1.2%，无流量限制。</b><div style="padding:10px 0 0 388px"><a  href="javascript:void(0)" onclick="document.ALIPAYFORM.submit();"><img src="' . $this->app->res_url . '/payments/images/alipaysq.png"></a></div><div>如果您已经和支付宝签约了其他套餐，同样可以点击上面申请按钮重新签约，即可享受新的套餐。<br>如果不需要更换套餐，请将签约合作者身份ID等信息在下面填写即可，<a href="http://www.shopex.cn/help/ShopEx48/help_shopex48-1235733634-11323.html" target="_blank">点击这里查看使用帮助</a><form name="ALIPAYFORM" method="GET" action="http://top.shopex.cn/recordpayagent.php" target="_blank"><input type="hidden" name="postmethod" value="GET"><input type="hidden" name="payagentname" value="支付宝"><input type="hidden" name="payagentkey" value="ALIPAY"><input type="hidden" name="market_type" value="from_agent_contract"><input type="hidden" name="customer_external_id" value="C433530444855584111X"><input type="hidden" name="pro_codes" value="6AECD60F4D75A7FB"><input type="hidden" name="regIp" value="'.$regIp.'"><input type="hidden" name="domain" value="'.url::route('topc').'"></form></div>';
    }

     /**
     * 后台配置参数设置
     * @param null
     * @return array 配置参数列表
     */
    public function setting(){
        return array(
            'pay_name'=>array(
                'title'=>app::get('ectools')->_('支付方式名称'),
                'type'=>'string',
                'validate_type' => 'required',
            ),
            'mer_id'=>array(
                'title'=>app::get('ectools')->_('合作者身份(parterID)'),
                'type'=>'string',
                'validate_type' => 'required',
            ),
            'mer_key'=>array(
                'title'=>app::get('ectools')->_('交易安全校验码(key)'),
                'type'=>'string',
                'validate_type' => 'required',
            ),
            'seller_account_name'=>array(
                'title'=>app::get('ectools')->_('支付宝账号'),
                'type'=>'string',
                'validate_type' => 'required',
            ),
            'order_by' =>array(
                'title'=>app::get('ectools')->_('排序'),
                'type'=>'string',
                'label'=>app::get('ectools')->_('整数值越小,显示越靠前,默认值为1'),
            ),
            'support_cur'=>array(
                'title'=>app::get('ectools')->_('支持币种'),
                'type'=>'text hidden cur',
                'options'=>$this->arrayCurrencyOptions,
            ),
            'pay_fee'=>array(
                'title'=>app::get('ectools')->_('交易费率'),
                'type'=>'pecentage',
                'validate_type' => 'number',
            ),
            //                     'pay_brief'=>array(
            //                         'title'=>app::get('ectools')->_('支付方式简介'),
            //                          'type'=>'textarea',
            //                     ),
            'pay_desc'=>array(
                'title'=>app::get('ectools')->_('描述'),
                'type'=>'html',
                'includeBase' => true,
            ),
            'pay_type'=>array(
                'title'=>app::get('ectools')->_('支付类型(是否在线支付)'),
                'type'=>'hidden',
                'name' => 'pay_type',
            ),
            'status'=>array(
                'title'=>app::get('ectools')->_('是否开启此支付方式'),
                'type'=>'radio',
                'options'=>array('false'=>app::get('ectools')->_('否'),'true'=>app::get('ectools')->_('是')),
                'name' => 'status',
            ),
        );
    }
    /**
     * 前台支付方式列表关于此支付方式的简介
     * @param null
     * @return string 简介内容
     */
    public function intro(){
        return app::get('ectools')->_('支付宝（中国）网络技术有限公司是国内领先的独立第三方支付平台，由阿里巴巴集团创办。支付宝致力于为中国电子商务提供“简单、安全、快速”的在线支付解决方案。').'
<a target="_blank" href="https://www.alipay.com/static/utoj/utojindex.htm">'.app::get('ectools')->_('如何使用支付宝支付？').'</a>';
    }

	/**
     * 提交支付信息的接口
     * @param array 提交信息的数组
     * @return mixed false or null
     */
    public function dopay($payment)
	{
        $mer_id = trim($this->getConf('mer_id', __CLASS__));
        $mer_key = trim($this->getConf('mer_key', __CLASS__));
        $seller_account_name = trim($this->getConf('seller_account_name', __CLASS__));

        $subject = $payment['account'].$payment['payment_id'];
        $subject = str_replace("'",'`',trim($subject));
        $subject = str_replace('"','`',$subject);
        $orderDetail = str_replace("'",'`',trim($orderDetail));
        $orderDetail = str_replace('"','`',$orderDetail);

        $virtual_method = $this->getConf('virtual_method', __CLASS__);#$config['virtual_method'];
        $real_method = $this->getConf('real_method', __CLASS__);#$config['real_method'];

        $parameter = array(
            'service'        => 'mobile.securitypay.pay',                        // 必填，接口名称，固定值
            'partner'        => $mer_id,                            // 必填，合作商户号
            '_input_charset' => 'UTF-8',                                         // 必填，参数编码字符集
            'out_trade_no'   => $payment['payment_id'],                          // 必填，商户网站唯一订单号
            'subject'        => $payment['item_title'].'...',                    // 必填，商品名称
            'payment_type'   => '1',                                             // 必填，支付类型
            'seller_id'      => $seller_account_name,                                         // 必填，卖家支付宝账号
            'total_fee'      => number_format($payment['cur_money'],2,".",""),   // 必填，总金额，取值范围为[0.01,100000000.00]
            'body'           => $payment['item_title'].'...',                    // 必填，商品详情
            'notify_url'     => $this->notify_url,                               // 可选，服务器异步通知页面路径
        );

        //签名
        $orderInfo = $this->createLinkstring($parameter);
        $sign = $this->md5Sign($orderInfo, $mer_key);

        echo $orderInfo.'&sign="'.$sign.'"&sign_type="MD5"';
        exit;
    }

    /**
	 * 校验方法
	 * @param null
	 * @return boolean
	 */
    public function is_fields_valiad(){
        return true;
    }

	/**
	 * 支付后返回后处理的事件的动作
	 * @params array - 所有返回的参数，包括POST和GET
	 * @return null
	 */
    public function callback(&$recv)
	{
        #键名与pay_setting中设置的一致
        $mer_id = trim( $this->getConf('mer_id', __CLASS__) );
        $mer_id = $mer_id;
        $mer_key = trim( $this->getConf('mer_key', __CLASS__) );
        $mer_key = $mer_key;

        if($this->is_return_vaild($recv,$mer_key,$this->sec_id)){
            $ret['payment_id'] = $recv['out_trade_no'];
            $ret['account'] = $mer_id;
            $ret['bank'] = app::get('ectools')->_('支付宝应用');
            $ret['pay_account'] = app::get('ectools')->_('付款帐号');
            $ret['currency'] = 'CNY';
            $ret['money'] = $recv['total_fee'];
            $ret['paycost'] = '0.000';
            $ret['cur_money'] = $recv['total_fee'];
            $ret['trade_no'] = $recv['trade_no'];
            $ret['t_payed'] = strtotime($recv['notify_time']) ? strtotime($recv['notify_time']) : time();
            $ret['pay_app_id'] = "alipayApp";
            $ret['pay_type'] = 'online';
            $ret['memo'] = $recv['body'];

            if($recv['trade_status'] == 'TRADE_SUCCESS') {
                $ret['status'] = 'succ';
            }else {
                $ret['status'] =  'failed';
            }
        }else{
            $message = 'Invalid Sign';
            $ret['status'] = 'invalid';
        }

        return $ret;
    }


    /**
     * 生成签名
     * @param mixed $form 包含签名数据的数组
     * @param mixed $key 签名用到的私钥
     * @access private
     * @return string
     */
    public function _get_mac($key){
        ksort($this->fields);
        reset($this->fields);
        $mac= "";
        foreach($this->fields as $k=>$v){
            $mac .= "&{$k}={$v}";
        }
        $mac = substr($mac,1);
        $mac = md5($mac.$key);  //验证信息
        return $mac;
    }

    /**
     * 检验返回数据合法性
     * @param mixed $form 包含签名数据的数组
     * @param mixed $key 签名用到的私钥
     * @access private
     * @return boolean
     */
    public function is_return_vaild($form,$key)
	{
        ksort($form);
        foreach($form as $k=>$v){
            if($k!='sign'&&$k!='sign_type'){
                $signstr .= "&$k=$v";
            }
        }

        $signstr = ltrim($signstr,"&");
        $signstr = $signstr.$key;

        if($form['sign']==md5($signstr)){
            return true;
        }
        #记录返回失败的情况
        logger::error(app::get('ectools')->_('支付单号：') . $form['out_trade_no'] . app::get('ectools')->_('签名验证不通过，请确认！')."\n");
        logger::error(app::get('ectools')->_('本地产生的加密串：') . $signstr);
        logger::error(app::get('ectools')->_('支付宝传递打过来的签名串：') . $form['sign']);
		$str_xml .= "<alipayform>";
        foreach ($form as $key=>$value)
        {
            $str_xml .= "<$key>" . $value . "</$key>";
        }
        $str_xml .= "</alipayform>";

        return false;
    }

    // 对签名字符串转义
    public function createLinkstring($para) {
        $arg  = "";
        while (list ($key, $val) = each ($para)) {
            $arg.=$key.'="'.$val.'"&';
        }
        //去掉最后一个&字符
        $arg = substr($arg,0,count($arg)-2);
        //如果存在转义字符，那么去掉转义
        if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
        return $arg;
    }

    // 签名生成订单信息--MD5加密方式
    public function md5Sign($data, $key)
    {
        return md5($data.$key);
    }

    public function gen_form()
    {

        return '';
    }


}
