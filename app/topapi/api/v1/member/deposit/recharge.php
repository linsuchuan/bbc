<?php
/**
 * topapi
 *
 * -- member.deposit.detail
 * -- 会员预存款明细
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_member_deposit_recharge implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '预存款充值';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'pay_app_id' => ['type'=>'string','valid'=>'required', 'desc'=>'支付方式id', 'msg'=>'请选择支付方式'],
            'money'      => ['type'=>'string','valid'=>'required', 'desc'=>'充值金额', 'msg'=>'请输入充值金额'],
        ];
    }

    /**
     */
    public function handle($params)
    {
        $rechargeParams = [];
        $rechargeParams['pay_app_id'] = $params['pay_app_id'];
        $rechargeParams['platform'] = 'app';
        $rechargeParams['money'] = $params['money'];
        $rechargeParams['user_id'] = $params['user_id'];
        $userInfo = app::get('topapi')->rpcCall('user.get.info', ['user_id'=>$params['user_id']]);
        $rechargeParams['user_name'] = $userInfo['login_account'];

        $result = app::get('topwap')->rpcCall('payment.deposit.recharge', $rechargeParams);
        echo $result;exit;
        return $result;
    }
}

