<?php
    if (!defined('IN_OAOOA')) {
        exit('Access Denied');
    }
    @ignore_user_abort(true);
    @set_time_limit(0);
    @ini_set('memory_limit', -1);
    @ini_set('max_execution_time', 0);
    
    $appid = isset($_GET['appid']) ? trim($_GET['appid']):0;
    $processname = 'DZZ_EXPORTCHECKFILE_LOCK_'.$appid;
    dzz_process::unlock($processname);
    $locked = true;
    if (!dzz_process::islocked($processname, 60*5)) {
        $locked=false;
    }
    
    if ($locked) {
        exit(json_encode( array('error'=>'进程已被锁定请稍后再试')));
    }
    $force = isset($_GET['force']) ? intval($_GET['force']):0;
    $data = C::t('pichome_vapp')->fetch($appid);
    if(!$data) exit(json_encode(array('error'=>'no data')));
    if($data['type'] == 0 && $data['state'] == '2'){
        include_once dzz_libfile('eagleexport');
        $eagleexport = new eagleexport($data);
        $return = $eagleexport->execCheckFile();
    }
    dzz_process::unlock($processname);
    