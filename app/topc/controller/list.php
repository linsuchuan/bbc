<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topc_ctl_list extends topc_controller {

    /**
     * 每页搜索多少个商品
     */
    public $limit = 20;

    /**
     * 最多搜索前100页的商品
     */
    public $maxPages = 100;


    public function index()
    {
        $this->setLayoutFlag('gallery');
        $objLibFilter = kernel::single('topc_item_filter');
        $postdata = input::get();


        $params = $objLibFilter->decode($postdata);
        //echo '<pre>';print_r($params);exit();
        $params['use_platform'] = '0,1';
        //判断自营  自营是1，非自营是0
        if($params['is_selfshop']=='1')
        {
            $pagedata['isself'] = '0';
        }
        else
        {
            $pagedata['isself'] = '1';
        }
        //如果不是从分类进入，并且没有关键字搜索则不能进入列表页
        $params['search_keywords'] = parseSearchKeyWord(trim($params['search_keywords']));
        if( empty($params['cat_id']) && empty($params['search_keywords']) )
        {
            return redirect::back();
        }

        //默认图片
        $pagedata['image_default_id'] = kernel::single('image_data_image')->getImageSetting('item');

        //搜索或者筛选获取商品
        $searchParams = $this->__preFilter($params);

        //面包屑数据
        $breadcrumb = array();
        if($searchParams['cat_id'] )
        {
            $cat = app::get('topc')->rpcCall('category.cat.get.data',array('cat_id'=>intval($searchParams['cat_id'])));
            $breadcrumb = array(
                ['url'=>url::action('topc_ctl_topics@index',array('cat_id'=>$cat['lv1']['cat_id'])),'title'=>$cat['lv1']['cat_name']],
                ['url'=>'','title'=>$cat['lv2']['cat_name']],
                ['url'=>url::action('topc_ctl_list@index',array('cat_id'=>$cat['lv3']['cat_id'])),'title'=>$cat['lv3']['cat_name']],
            );
            if($searchParams['brand_id'])
            {
                $brands = app::get('topc')->rpcCall('category.brand.get.list',array('brand_id'=>$searchParams['brand_id'],'fields'=>'brand_id,brand_name'));
                $title = (count($brands) >1) ? "品牌：" : '';
                foreach($brands as $brand)
                {
                    $title .= $brand['brand_name']."、";
                }
                $title = rtrim($title,"、");
                $breadcrumb[] = ['url'=>'','title'=>$title];
            }
        }

        if($searchParams['search_keywords'])
        {
            $breadcrumb = array(
                ['url'=>'','title'=>'全部商品'],
                ['url'=>'','title'=>$searchParams['search_keywords']],
            );
        }
        $pagedata['breadcrumb'] = $breadcrumb;

        $searchParams['fields'] = 'item_id,title,image_default_id,price,promotion';
        //$itemsList = app::get('topc')->rpcCall('item.search',$searchParams);
        try
        {
            $itemsList = app::get('topc')->rpcCall('item.search',$searchParams);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }
        //检测是否有参加团购活动
        if($itemsList['list'])
        {
            $itemsList['list'] = array_bind_key($itemsList['list'],'item_id');
            $itemIds = array_keys($itemsList['list']);
            $activityParams['item_id'] = implode(',',$itemIds);
            $activityParams['status'] = 'agree';
            $activityParams['end_time'] = 'bthan';
            $activityParams['start_time'] = 'sthan';
            $activityParams['fields'] = 'activity_id,item_id,activity_tag,price,activity_price';
            $activityItemList = app::get('topc')->rpcCall('promotion.activity.item.list',$activityParams);
            if($activityItemList['list'])
            {
                foreach($activityItemList['list'] as $key=>$value)
                {
                    $itemsList['list'][$value['item_id']]['activity'] = $value;
                    $itemsList['list'][$value['item_id']]['activity_price'] = $value['activity_price'];
                }
            }
        }

        //根据条件搜索出最多商品的分类，进行显示渐进式筛选项
        $filterItems = app::get('topc')->rpcCall('item.search.filterItems',$params);
        //渐进式筛选的数据
        $pagedata['screen'] = $filterItems;
        $pagedata['items'] = $itemsList['list'];//根据条件搜索出的商品
        $pagedata['count'] = $itemsList['total_found']; //根据条件搜索到的总数

        //已有的搜索条件
        $tmpFilter = $params;
        unset($tmpFilter['pages']);
        $pagedata['filter'] = $objLibFilter->encode($tmpFilter);

        //分页
        $pagedata['pagers'] = $this->__pages($params['pages'], $pagedata['count'], $pagedata['filter']);
        //已选择的搜索条件
        $pagedata['activeFilter'] = $params;
        return $this->page('topc/list/index.html', $pagedata);
    }

    /**
     * 将post过的数据转换为搜索需要的参数
     *
     * @param array $params
     */
    private function __preFilter($params)
    {
        $searchParams = $params;
        $searchParams['page_no'] = ($params['pages'] >=1 || $params['pages'] <= 100) ? $params['pages'] : 1;
        $searchParams['page_size'] = $this->limit;

        $searchParams['approve_status'] = 'onsale';
        $searchParams['buildExcerpts'] = true;

        if( $searchParams['brand_id'] && is_array($searchParams['brand_id']) )
        {
            $searchParams['brand_id'] = implode(',', $searchParams['brand_id']);
        }

        if( $searchParams['prop_index'] && is_array($searchParams['prop_index']) )
        {
            $searchParams['prop_index'] = implode(',', $searchParams['prop_index']);
        }

        //排序
        if( !$params['orderBy'] )
        {
            $params['orderBy'] = 'sold_quantity desc';
        }
        $searchparams['orderBy'] = $params['orderBy'];

        return $searchParams;
    }

    /**
     * 分页处理
     * @param int $current 当前页
     * @return int $total  总页数
     * @return array $filter 查询条件
     *
     * @return $pagers
     */
    private function __pages($current, $total, $filter)
    {
        //处理翻页数据
        $current = ($current || $current <= 100 ) ? $current : 1;
        $filter['pages'] = time();

        if( $total > 0 ) $totalPage = ceil($total/$this->limit);
        $pagers = array(
            'link'=>url::action('topc_ctl_list@index',$filter),
            'current'=>$current,
            'total'=>$totalPage,
            'token'=>time(),
        );
        return $pagers;
    }

}

