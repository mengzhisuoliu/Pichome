<?php
/*
 * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
 * @license     https://www.oaooa.com/licenses/
 *
 * @link        https://www.oaooa.com
 * @author      zyx(zyx@oaooa.com)
 */

    
if (!defined('IN_OAOOA')) {//所有的php文件必须加上此句，防止被外部调用
	exit('Access Denied');
}
updatesession();
global $_G;
$operation = isset($_GET['operation']) ? trim($_GET['operation']) : '';
$opentype = isset($_GET['opentype']) ? trim($_GET['opentype']) : '';
$apptype = isset($_GET['apptype']) ? trim($_GET['apptype']) : '';
//$overt = getglobal('setting/overt');
if($operation == 'fetch'){
    if(!$patharr=Pdecode($_GET['path'])){
        exit(json_encode(array('status'=>2,'error'=>lang('no_perm'))));
    }
    $rid = $patharr['path'];
    $isshare = $patharr['isshare'];
    $perm = $patharr['perm'];
    $isadmin = $patharr['isadmin'];
    $ulevel = getglobal('pichomelevel') ? getglobal('pichomelevel') : 0;
	if (!$rid) {
        exit(json_encode(array('status'=>2,'error'=>lang('no_perm'))));
	}

	$resourcesdata = C::t('pichome_resources')->fetch_by_rid($rid,$isshare,1);

    $appdata = C::t('pichome_vapp')->fetch($resourcesdata['appid']);
    $viewperm = unserialize($appdata['view']);
    $overt = getglobal('setting/overt');

    if ($viewperm !== '1' && !$overt && !$overt = C::t('setting')->fetch('overt')) {
        Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
    }
    if($perm){
        $resourcesdata['download'] =perm::check('download2',$perm)?1:0;
        $resourcesdata['share'] =perm::check('share',$perm)?1:0;
        $resourcesdata['view'] =perm::check('read2',$perm)?1:0;
    }
   if((!isset($resourcesdata['view']) || !$resourcesdata['view']) && !$isshare && !C::t('pichome_vapp')->getpermbypermdata($appdata['view'],$resourcesdata['appid'])){
       exit(json_encode(array('status'=>2,'error'=>lang('no_perm'))));
   }
    if(!$resourcesdata['iniframe']){
        $resourcesdata['preview'] = C::t('thumb_preview')->fetchPreviewByRid($rid);
        $resourcesCover = ['spath'=>$resourcesdata['icondata'],'lpath'=>$resourcesdata['originalimg']];
        if($resourcesdata['preview']) array_unshift($resourcesdata['preview'],$resourcesCover);

    }

    $appdata = C::t('pichome_vapp')->fetch($resourcesdata['appid']);
    $data['fileds'] = unserialize($appdata['fileds']);
    //获取tab数据
    $tabstatus = 0;
    Hook::listen('checktab', $tabstatus);
    if($tabstatus){
        foreach($data['fileds'] as $v){
            if($v['type'] == 'tabgroup'){
                $gid =  intval(str_replace('tabgroup_','',$v['flag']));
                $tids = [];
                foreach(DB::fetch_all("select tid from %t where rid= %s and gid = %d",array('pichome_resourcestab',$rid,$gid)) as $val){
                    $tids[] = $val['tid'];
                }
                Hook::listen('gettab',$tids);
                $data[$v['flag']] = $tids;
            }
        }
    }
   $resourcesdata = array_merge($resourcesdata,$data);
    //增加浏览次数
    if($resourcesdata){
        addFileviewStats($rid,$isadmin);
    }
    Hook::listen('getDetailRighturl',$resourcesdata);
	if($isshare || $ulevel >= $resourcesdata['level']){
        exit(json_encode(array('status'=>1,'resourcesdata' => $resourcesdata,'sitename'=>$_G['setting']['sitename'])));
    }else{

        exit(json_encode(array('status'=>2,'error'=>lang('no_perm'))));
    }


}else{

    updatesession();
    $isadmin = $_GET['isadmin'] ? intval($_GET['isadmin']):0;
	$ismobile = helper_browser::ismobile();
	if ($ismobile) {
	    include template('mobile/page/details');
	} else {
        if ($apptype == 'libraryview') {
            include template('libraryview/pc/page/details');
        }else{
            include template('pc/page/index');
        }
		
	}
	
}

