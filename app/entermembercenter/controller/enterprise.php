<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class entermembercenter_ctl_enterprise extends desktop_controller{

    function index(){

        $this->entid = base_enterprise::ent_id();
        $this->ent_email = base_enterprise::ent_email();
        if(empty($this->entid) ||empty($this->ent_email)){
            $pagedata['enterprise'] = false;
        }else{
            $pagedata['enterprise'] = true;
        }
        $pagedata['entid'] = $this->entid;
		$pagedata['ent_email'] = $this->ent_email;
        $pagedata['debug'] = false;

        return $this->page('entermembercenter/enterprise.html', $pagedata);
    }

    function upLicense(){
    	if ( $_FILES ){
    		if ( $_FILES['enterprise']['name'] ){
	    		$fileName = explode( '.', $_FILES['enterprise']['name'] );
	    		if ( 'CER' != $fileName['1'] ){
	    		    echo "<script>parent.MessageBox.error('".app::get('entermembercenter')->_('"企业帐号格式不对"')."');</script>";
	    		    return;
	    		}
	    		else {
			        $content = file_get_contents($_FILES['enterprise']['tmp_name']);
			        list($entid,$ent_ac,$ent_email) = explode('|||',$content);
			        $result = base_enterprise::set_enterprise_info(array('ent_id'=>$entid,'ent_ac'=>$ent_ac,'ent_email'=>$ent_email));
			        if(!$result){
			            header("Content-type:text/html; charset=utf-8");
			            echo "<script>parent.MessageBox.error('".app::get('entermembercenter')->_('"企业帐号重置失败,请先上传文件"')."');</script>";
			        }else{
						// 删除证书和node_id.
						base_certi::del_certi();
			            header("Content-type:text/html; charset=utf-8");
			            echo "<script>parent.MessageBox.success('".app::get('entermembercenter')->_('"企业帐号上传成功"')."');</script>";
			        }
	    		}
    		}
    		else {
    		    echo "<script>parent.MessageBox.error('".app::get('entermembercenter')->_('"请选择要上传的文件"')."');</script>";
    		}
    	}
    }
    function download(){

        $this->fileName = 'enterprise.CER';
        $this->ent_id = base_enterprise::ent_id();
        $this->ent_ac = base_enterprise::ent_ac();
		$this->ent_email = base_enterprise::ent_email();

        $content = $this->ent_id . '|||' . $this->ent_ac . '|||' . $this->ent_email;
        $response = response::make($content, 200);
        $response->header('Content-type','application/octet-stream;charset=utf-8');
        $response->header('Content-type','application/force-download');
        $response->header('Content-Disposition','filename='.$this->fileName);
        return $response;
    }
}

