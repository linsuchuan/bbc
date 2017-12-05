<?php
/**
 * topapi
 *
 * -- trade.create
 * -- 创建订单
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_trade_create implements topapi_interface_api {

    /**
     * api接口的名称
     * @var string
     */
    public $apiDescription = '创建订单';

    /**
     * 定义API传入的应用级参数
     * @return string payment_type 支付类型（online or offline）
     * @return string payment_id 支付单号（仅在payment_type为online时出现）
     */
    public function setParams()
    {
        //接口传入的参数
        $return = array(
            'mode'          => ['type'=>'string',  'valid'=>'required|in:cart,fastbuy', 'desc'=>'购物车类型', 'example'=>'cart'],
            'md5_cart_info' => ['type'=>'string', 'valid'=>'required', 'desc'=>'购物车数据校验'],
            'addr_id'       => ['type'=>'string', 'valid'=>'required', 'desc'=>'收货地址', 'msg'=>'请选择收货地址'],
            'taxPackage'       => ['type'=>'string', 'valid'=>'', 'desc'=>'海关验证身份证号码'],
            'payment_type'  => ['type'=>'string', 'valid'=>'required', 'desc'=>'支付方式', 'msg'=>'请选择支付方式'],
            'source_from'   => ['type'=>'string', 'valid'=>'in:pc,wap', 'desc'=>'使用平台 pc电脑端 wap手机端'],
            'shipping_type' => ['type'=>'jsonArray', 'valid'=>'required', 'example'=>'', 'desc'=>'每个店铺的配送方式 [{"shop_id":"3","type":"express"}]', 'params' => [
                'shop_id' => ['type'=>'string',  'valid'=>'required', 'example'=>'', 'desc'=>'店铺ID'],
                'type'    => ['type'=>'string',  'valid'=>'required', 'example'=>'', 'desc'=>'配送方式'],
            ]],
            'mark' => ['type'=>'jsonArray', 'valid'=>'', 'desc'=>'买家留言', 'params'=>[
                'shop_id' => ['type'=>'string',  'valid'=>'required', 'desc'=>'店铺ID'],
                'memo'    => ['type'=>'string',  'valid'=>'required', 'desc'=>'对应店铺买家留言'],
            ]],
            'invoice_type'    => ['type'=>'string', 'valid'=>'required:in,normal,vat,notuse','desc'=>'发票类型 normal普通发票，vat 增值税发票, notuse 不需要发票'],
            'invoice_content' => ['type'=>'jsonObject', 'valid'=>'required_if:invoice_type,normal,vat', 'desc'=>'发票数据内容', 'params'=>array(
                'title'                 => ['type'=>'string', 'valid'=>'required_if:invoice_type,normal|in:individual,unit', 'desc'=>'发票抬头类型 individual 个人,unit 企业'],
                'content'               => ['type'=>'string', 'valid'=>'required_if:invoice_type,normal', 'desc'=>'发票抬头'],
                'company_name'          => ['type'=>'string', 'valid'=>'required_if:invoice_type,vat', 'desc'=>'公司名称',     'msg'=>'请输入公司名称'],
                'company_address'       => ['type'=>'string', 'valid'=>'required_if:invoice_type,vat', 'desc'=>'公司地址',     'msg'=>'请输入公司地址'],
                'registration_number'   => ['type'=>'string', 'valid'=>'required_if:invoice_type,vat', 'desc'=>'纳税人识别号', 'msg'=>'请输入纳税人识别号'],
                'bankname'              => ['type'=>'string', 'valid'=>'required_if:invoice_type,vat', 'desc'=>'开户银行',     'msg'=>'请输入开户银行'],
                'bankaccount'           => ['type'=>'string', 'valid'=>'required_if:invoice_type,vat', 'desc'=>'开户银行帐号', 'msg'=>'请输入开户银行帐号'],
                'company_phone'         => ['type'=>'string', 'valid'=>'required_if:invoice_type,vat', 'desc'=>'公司电话',     'msg'=>'请输入公司电话'],
            )],
            'use_points' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'desc'=>'使用的积分值'],
        );

        return $return;
    }

    /**
     * @return string payment_type 在线支付或者线下支付
     * @return string payment_id 支付单号
     */
    public function handle($params)
    {
        $params = $this->__checkCartInfo($params);

        $invoiceContent = $params['invoice_content'];
        unset($params['invoice_content']);

        //是否需要发票
        if( $params['invoice_type'] == 'notuse' )
        {
            $params['need_invoice'] = 0;
        }
        else
        {
            $params['need_invoice'] = 1;
        }

        if( $params['invoice_type'] == 'vat' )
        {
            $params['invoice_vat'] = $invoiceContent;
        }
        else
        {
            $params['invoice_content'] = $invoiceContent['content'];
            $params['invoice_title'] = $invoiceContent['title'];
        }

        if( $params['mark'] )
        {
            $mark = $params['mark']; unset($params['mark']);
            foreach($mark as $row)
            {
                $params['mark'][$row['shop_id']] = $row['memo'];
            }
        }

        $params['user_name'] = app::get('topapi')->rpcCall('user.get.account.name', ['user_id'=>$params['user_id']]);
        $params['shipping_type'] = $this->__preShipping($params['shipping_type']);
        $params['source_from'] = $params['source_from'] ?: 'wap';
        $createFlag = app::get('topapi')->rpcCall('trade.create',$params);

        $paymentType = $params['payment_type'];
        if($paymentType == 'online')
        {
            $requestParamsForPayment['tid'] = $createFlag;
            $requestParamsForPayment['user_id'] = $params['user_id'];
            $requestParamsForPayment['user_name'] = $params['user_name'];

            $paymentId = kernel::single('topapi_payment')->getPaymentId($requestParamsForPayment);
        }

        return ['payment_type'=>$paymentType, 'payment_id'=>$paymentId];
    }

    private function __checkCartInfo($params)
    {
        $cartFilter['mode'] = $params['mode'];
        $cartFilter['needInvalid'] = false;
        $cartFilter['platform'] = $params['source_from'];
        $md5CartFilter = array('user_id'=>$params['user_id'], 'platform'=>$params['source_from'], 'mode'=>$cartFilter['mode'], 'checked'=>1);
        $cartInfo = app::get('topapi')->rpcCall('trade.cart.getBasicCartInfo', $md5CartFilter);

        // 校验购物车是否为空
        if( !$cartInfo )
        {
            $msg = app::get('topapi')->_("购物车信息为空或者未选择商品");
            throw new \LogicException($msg);
        }

        // 校验购物车是否发生变化
        $md5CartInfo = md5(serialize(utils::array_ksort_recursive($cartInfo, SORT_STRING)));
        if( $params['md5_cart_info'] != $md5CartInfo )
        {
            $msg = app::get('topapi')->_("购物车数据发生变化，请刷新后确认提交");
            throw new \LogicException($msg);
        }

        unset($params['md5_cart_info']);

        return $params;
    }

    private function __preShipping($shipping)
    {
        $shopIds = array_column($shipping,'shop_id');
        $shopIds = array_unique($shopIds);
        $shopData = app::get('topapi')->rpcCall('shop.get.list', ['shop_id'=>implode(',',$shopIds),'fields'=>'shop_id,shop_type']);
        $shopData = array_bind_key($shopData,'shop_id');

        foreach( $shipping as $row)
        {
            $shopId = $row['shop_id'];

            //判断是否支持货到付款支付方式
            if( $params['payment_type'] == 'offline' )
            {
                if(($shopData[$shopId]['shop_type'] != "self") || ($shopData[$shopId]['shop_type'] == "self" && $ifOpenOffline == "false"))
                {
                    $msg = app::get('topapi')->_($shopData[$shopId]['shopname'].'不支持货到付款');
                    throw new \LogicException($msg);
                }
            }

            $newShipping .= $shopId.':'.$row['type'].';';
        }

        return $newShipping;
    }

    private function __checkUserAddr($postData){
        if(!$postData['addr_id'])
        {
            $msg .= app::get('topm')->_("请先确认收货地址");
            throw new \LogicException($msg);
        }
        else
        {
            //20160909苏添加海关添加身份证
            if(isset($postData['taxPackage']) && !empty($postData['taxPackage'])){
                $user_addrs = app::get('sysuser')->model('user_addrs');
                $user_addrs->update(array('id_card_num'=>$postData['taxPackage']),array('addr_id'=>$postData['addr_id'],'user_id'=>userAuth::id()));
            }elseif(isset($postData['taxPackage']) && empty($postData['taxPackage'])){
                throw new \LogicException('身份证号码不能为空');
            }

            $addr = app::get('topm')->rpcCall('user.address.info',array('addr_id'=>$postData['addr_id'],'user_id'=>userAuth::id()));
            list($regions,$region_id) = explode(':',$addr['area']);
            list($state,$city,$district) = explode('/',$regions);

            if (!$state )
            {
                $msg .= app::get('topm')->_("收货地区不能为空！")."<br />";
            }

            if (!$addr['addr'])
            {
                $msg .= app::get('topm')->_("收货地址不能为空！")."<br />";
            }

            if (!$addr['name'])
            {
                $msg .= app::get('topm')->_("收货人姓名不能为空！")."<br />";
            }

            if (!$addr['mobile'] && !$addr['phone'])
            {
                $msg .= app::get('topm')->_("手机或电话必填其一！")."<br />";
            }

            if (strpos($msg, '<br />') !== false)
            {
                $msg = substr($msg, 0, strlen($msg) - 6);
            }
            if($msg)
            {
                throw new \LogicException($msg);
            }
        }
    }
}

