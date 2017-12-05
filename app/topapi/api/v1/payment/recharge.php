<?php
/**
 * topapi
 *
 * -- payment.pay.do
 * -- 获取会员基本信息
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_payment_recharge implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '去支付';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'pay_app_id' => ['type'=>'string','valid'=>'required', 'description'=>'支付方式', 'msg'=>'请输入支付单号'],
            'money' => ['type'=>'float','valid'=>'required', 'description'=>'预存款支付密码', 'msg'=>'请输入支付单号'],
            ];
    }

    /**
     */
    public function handle($params)
    {

        $rechargeRequestParams = [];
        if($params['pay_app_id'] == 'deposit')
        {
            throw new LogicException(app::get('topapi')->_('请勿使用预存款充值'));

        }
        $rechargeRequestParams['pay_app_id'] = $params['pay_app_id'];
        $this->checkoutAmount($params['money']);
        $rechargeRequestParams['money'] = $params['money'];
        $rechargeRequestParams['platform'] = 'app';
        $rechargeRequestParams['user_id'] = $params['user_id'];

        $userInfo = app::get('topapi')->rpcCall('user.get.info', ['user_id'=>$params['user_id']]);
        $rechargeRequestParams['user_name'] = $userInfo['login_account'];

        $ret = app::get('topwap')->rpcCall('payment.deposit.recharge', $rechargeRequestParams);

        return $ret;
    }

    private function checkoutAmount($amount)
    {

        if( !is_numeric($amount) )
            throw new LogicException('充值金额必须为数字');

        if( $amount <= 0 )
            throw new LogicException('充值金额必须大于0');

        if( $amount >= 10000000)
            throw new LogicException('请勿充值过大的金额');

        if(  ( (int)($amount*100) ) != ($amount * 100)  )
            throw new LogicException('充值金额的最小单位不得小于分');

    }

}

