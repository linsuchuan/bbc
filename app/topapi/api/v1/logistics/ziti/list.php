<?php

/**
 * topapi
 *
 * -- logistics.ziti.list
 * -- 根据用户收货地址，获取自提地址
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_logistics_ziti_list implements topapi_interface_api {

    /**
     * 接口作用说明
     */
    public $apiDescription = '根据用户收货地区，获取自提地址';

    public function setParams()
    {
        return  [
            'area_id' =>['type'=>'string','valid'=>'required', 'desc'=>'收货地区id','example'=>'110100,110101'],
        ];
    }

    public function handle($params)
    {
        return app::get('topapi')->rpcCall('logistics.ziti.list',$params);
    }
}
