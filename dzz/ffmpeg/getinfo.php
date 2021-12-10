<?php
ignore_user_abort(true);
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
@set_time_limit(0);
ini_set('memory_limit', -1);
@ini_set('max_execution_time', 0);
$appids = [];
foreach(DB::fetch_all("select appid from %t where `type` = %d and getinfo = %d",array('pichome_vapp',1,1)) as $v){
    $appids[] = $v['appid'];
}
if(empty($appids)){
    exit('success');
}
$locked = true;
for($i=0;$i<1;$i++){
    $processname = 'DZZ_LOCK_PICHOMEGETINFO'.$i;
    if (!dzz_process::islocked($processname, 60*60)) {
        $locked=false;
        break;
    }
}
$processname = 'DZZ_LOCK_PICHOMEGETINFO'.$i;
$limit = 10;
$start=$i*$limit;
if (!dzz_process::islocked($processname, 60*60)) {
    $locked=false;
}
if ($locked) {
    exit(json_encode( array('error'=>'进程已被锁定请稍后再试')));
}
use dzz\ffmpeg\classes\info as info;

$info =new info;
foreach(DB::fetch_all("select * from %t where infostatus = 0 and appid in(%n) 
order by infodonum asc limit $start,$limit",array('pichome_ffmpeg_record',$appids)) as $v){
    $processname1 = 'PICHOMEGETINFO_'.$v['rid'];
    if (dzz_process::islocked($processname1, 60*5)) {
        continue;
    }
    $data = C::t('pichome_resources')->fetch_data_by_rid($v['rid']);

    if(empty($data)){
        C::t('pichome_ffmpeg_record')->delete($v['rid']);
        dzz_process::unlock($processname1);
        continue;
    }else{
        //如果信息执行次数大于三次，直接赋值为1，不再尝试获取
        if($v['infodonum'] > 3 && $v['infostatus'] == 0){
            $v['infostatus'] = 1;
            C::t('pichome_ffmpeg_record')->update($v['rid'],array('infostatus'=>1));
            dzz_process::unlock($processname1);
            continue;
        }
        //如果信息和缩略图标记为已生成，标记该文件信息状态为已获取
        if($v['thumbstatus'] == 1 && $v['infostatus'] == 1){
            C::t('pichome_resources_attr')->update($v['rid'],array('isget'=>1));
            dzz_process::unlock($processname1);
            continue;
        }
        DB::query("update %t set infodonum=infodonum+%d where rid = %s ", array('pichome_ffmpeg_record', 1, $data['rid']));
        $info->rundata($data);
        dzz_process::unlock($processname1);
    }
}
dzz_process::unlock($processname);
if(DB::result_first("select * from %t where infostatus = 0  ",array('pichome_ffmpeg_record'))){
    dfsockopen(getglobal('localurl') . 'index.php?mod=ffmpeg&op=getinfo', 0, '', '', false, '', 0.1);
}
exit('success');