<?php

class topwap_ctl_member_hongbao extends topwap_ctl_member {


    public function index()
    {
        $limit = 9;

        $isValid = input::get('is_valid','active');

        $apiParams = [
            'user_id' => userAuth::id(),
            'is_valid'=> $isValid,
            'page_no' => input::get('pages',1),
            'page_size' => $limit,
            'fields'=>'*'
        ];
        $hongbaoData = app::get('topwap')->rpcCall('user.hongbao.list.get', $apiParams);

        $pagedata['list'] = $hongbaoData['list'];
        $pagedata['is_valid'] = $isValid;

        $filter['pages'] = time();
        $filter['is_valid'] = $isValid;
        if($hongbaoData['pagers']['total']>0) $total = ceil($hongbaoData['pagers']['total']/$limit);
        $current = intval(input::get('pages',1));
        $current = $total < $current ? $total : $current;
        $pagedata['pagers'] = array(
            'link'=>url::action('topwap_ctl_member_hongbao@index',$filter),
            'current'=>$current,
            'total'=>$total,
            'use_app'=>'topwap',
            'token'=>$filter['pages'],
        );

        return $this->page('topwap/member/hongbao/index.html', $pagedata);
    }

    public function getHongbao()
    {
        $apiParams = [
            'user_id' => userAuth::id(),
                'hongbao_id' => input::get('hongbao_id'),
                'money' => input::get('money'),
                'hongbao_obtain_type' => 'userGet',
        ];

        try
        {
            $hongbaoData = app::get('topwap')->rpcCall('user.hongbao.get', $apiParams);
        }
        catch( LogicException $e )
        {
            $msg = $e->getMessage();
            return $this->splash('error',"",$msg,true);
        }
        catch( Exception $e)
        {
            $msg = '红包已领完';
            return $this->splash('error',"",$msg,true);
        }

        return $this->splash('success',"",'红包领取成功',true);
    }
}

