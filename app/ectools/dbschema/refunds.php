<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

return array (
    'columns' => array (
        'refund_id' => array (
            'type' => 'string',
            'length' => 20,
            'required' => true,
            'width' => 110,
            'searchtype' => 'has',
            'filtertype' => 'yes',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
            'label' => app::get('ectools')->_('退款单号'),
            'comment' => app::get('ectools')->_('退款单号'),
        ),
        'money' => array (
            'type' => 'money',
            'default' => '0',
            'required' => true,
            'label' => app::get('ectools')->_('退款金额'),
            'comment' => app::get('ectools')->_('退款金额'),
        ),
        'cur_money' => array (
            'type' => 'money',
            'default' => '0',
            'required' => true,
            'label' => app::get('ectools')->_('退款金额'),
            'comment' => app::get('ectools')->_('退款金额'),
        ),
        'refund_bank' => array (
            'type' => 'string',
            'default' => '',
            'length' => 50,
            'width' => 110,
            'searchtype' => 'tequal',
            'filtertype' => 'normal',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
            'label' => app::get('ectools')->_('退款银行'),
            'comment' => app::get('ectools')->_('退款银行'),
        ),
        'refund_account' => array (
            'type' => 'string',
            'default' => '',
            'length' => 50,
            'width' => 110,
            'filtertype' => 'normal',
            'filterdefault' => true,
            'in_list' => true,
            'label' => app::get('ectools')->_('退款账号'),
            'comment' => app::get('ectools')->_('退款账号'),
        ),
        'refund_people' => array (
            'type' => 'string',
            'default' => '',
            'length' => 100,
            'width' => 75,
            'filtertype' => 'yes',
            'filterdefault' => true,
            'in_list' => true,
            'label' => app::get('ectools')->_('退款人'),
            'coment' => app::get('ectools')->_('退款人'),
        ),
        'receive_bank' => array (
            'type' => 'string',
            'default' => '',
            'length' => 50,
            'width' => 110,
            'searchtype' => 'tequal',
            'filtertype' => 'normal',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
            'label' => app::get('ectools')->_('收款银行'),
            'comment' => app::get('ectools')->_('收款银行'),
        ),
        'receive_account' => array (
            'type' => 'string',
            'default' => '',
            'length' => 50,
            'width' => 110,
            'filtertype' => 'normal',
            'filterdefault' => true,
            'in_list' => true,
            'label' => app::get('ectools')->_('收款账号'),
            'comment' => app::get('ectools')->_('收款账号'),
        ),
        'beneficiary' =>array (
            'type' => 'string',
            'default' => '',
            'length' => 50,
            'width' => 110,
            'filtertype' => 'normal',
            'in_list' => true,
            'label' => app::get('ectools')->_('收款人'),
            'comment' => app::get('ectools')->_('收款人'),
        ),
        'currency' => array (
            'type' => 'string',
            'length' => 10,
            'label' => app::get('ectools')->_('货币'),
            'comment' => app::get('ectools')->_('货币'),
            'width' => 75,
            'default' => 'CNY',
            'in_list' => true,
        ),
        'paycost' => array (
            'type' => 'money',
            'default' => '0',
            'label' => app::get('ectools')->_('支付网关费用'),
            'comment' => app::get('ectools')->_('支付网关费用'),
            'width' => 110,
            'in_list' => false,
        ),
        'status' => array (
            'type' => array (
                'ready' => app::get('ectools')->_('准备中'),
                'progress' => app::get('ectools')->_('处理中'),
                'succ' => app::get('ectools')->_('支付成功'),
                'failed' => app::get('ectools')->_('支付失败'),
                'cancel' => app::get('ectools')->_('取消'),
            ),
            'default' => 'ready',
            'required' => true,
            'label' => app::get('ectools')->_('支付状态'),
            'comment' => app::get('ectools')->_('支付状态'),
            'width' => 75,
            'filtertype' => 'yes',
            'filterdefault' => true,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'op_id' => array (
            'type' => 'table:account@desktop',
            'label' => app::get('ectools')->_('操作员'),
            'width' => 110,
            'filtertype' => 'normal',
            'in_list' => true,
            'default_in_list' => true,
        ),
        'rufund_type' => array (
            'type' => array (
                'online' => app::get('ectools')->_('在线退款'),
                'offline' => app::get('ectools')->_('线下退款'),
                'deposit' => app::get('ectools')->_('预存款退款'),
            ),
            'required' => true,
            'label' => app::get('ectools')->_('退款方式'),
            'comment' => app::get('ectools')->_('退款方式'),
            'width' => 110,
            'filtertype' => 'yes',
            'filterdefault' => true,
            'in_list' => true,
        ),
        'refunds_type' => array(//命名我也是晕了，傻傻分不清楚
            'type' => array(
                '0' => '售后申请退款',
                '1' => '取消订单退款',
                '2' => '拒收订单退款',
            ),
            'required' => true,
            'in_list' => true,
            'default_in_list' => true,
            'label' => app::get('sysaftersales')->_('退款类型'),
            'comment' => app::get('sysaftersales')->_('退款类型'),
        ),
        'aftersales_bn' => array (
            'type' => 'string',
            'length' => 32,
            'default' => '',
            'label' => app::get('ectools')->_('售后单号'),
            'width' => 140,
            'filtertype' => 'yes',
            'filterdefault' => true,
            'in_list' => false,
            'default_in_list' => false,
        ),
        'pay_app_id' => array (
            'type' => 'string',
            'length' => 100,
            'label' => app::get('ectools')->_('支付方式'),
            'required' => true,
            'default' => '',
            'in_list' => true,
            'default_in_list' => true,
        ),
        'created_time' => array (
            'type' => 'time',
            'label' => app::get('ectools')->_('支付开始时间'),
            'width' => 110,
            'filtertype' => 'time',
            'filterdefault' => true,
            'in_list' => true,
        ),
        'modified_time' => array (
            'type' => 'last_modify',
            'label' => app::get('ectools')->_('最后修改时间'),
            'comment' => app::get('ectools')->_('最后更新时间'),
            'in_list' => true,
            'default_in_list' => false,
        ),
        'finish_time' => array (
            'type' => 'time',
            'label' => app::get('ectools')->_('支付完成时间'),
            'width' => 110,
            'in_list' => true,
        ),
        'memo' => array (
            'type' => 'text',
            'comment' => app::get('ectools')->_('备注'),
            'label' => app::get('ectools')->_('备注'),
        ),
        'oid' => array (
            'type' => 'string',
            'length' => 30,
            'comment' => app::get('ectools')->_('交易子订单号'),
            'label' => app::get('ectools')->_('交易子订单号'),
        ),
        'tid' => array (
            'type' => 'string',
            'length' => 30,
            'in_list' => true,
            'default_in_list' => true,
            'searchtype' => 'tequal',
            'comment' => app::get('ectools')->_('交易主订单号'),
            'label' => app::get('ectools')->_('交易主订单号'),
        ),
        'refunds_id' => array(
            'type' => 'number',
            'required' => true,
            'comment' => app::get('sysaftersales')->_('退款主键(sysaftersales/refunds表主键)，第三方退款后使用'),
            'label' => app::get('sysaftersales')->_('sysaftersales/refunds表主键'),
        ),
        'return_fee' => array(
            'type' => 'money',
            'required' => true,
            'comment' => app::get('sysaftersales')->_('商家退款金额(含红包、积分抵扣金额)，第三方退款后使用'),
            'label' => app::get('sysaftersales')->_('商家退款金额'),
        ),
    ),
    'primary' => 'refund_id',
    'comment' => app::get('ectools')->_('退款单表'),
);
