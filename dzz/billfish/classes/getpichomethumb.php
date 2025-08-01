<?php

namespace dzz\billfish\classes;

use \core as C;
use \DB as DB;
use \fmpeg as fmpeg;

class getpichomethumb
{

    public function run(&$data)
    {
        $thumbid = DB::result_first("select thumb from %t where appid = %s and rid = %s", array('billfish_record', $data['appid'], $data['rid']));
        $iscloud = false;
        $arr = explode(':', $data['apppath']);
        if($arr[1] && is_numeric($arr[1])){
            $iscloud = true;
        }else{
            $data['apppath'] = str_replace('/', BS, $data['apppath']);
           $data['apppath'] = str_replace('dzz::', '', $data['apppath']);
        }
        if(isset($data['version']) && $data['version'] >=30){
            $bid = DB::result_first("select bid from %t where rid = %s",array('billfish_record',$data['rid']));
            $thumbdir = dechex($bid);
            $thumbdir = (string) $thumbdir;
            if(strlen($thumbdir) < 2){
                $thumbdir =str_pad($thumbdir,2,0,STR_PAD_LEFT);
            }elseif(strlen($thumbdir) > 2){
                $thumbdir = substr($thumbdir,-2);
            }
            $pathdir = ($iscloud) ? \IO::getStream($data['apppath'].'/.bf/.preview/'.$thumbdir.'/'.$bid.'.small.webp'):$data['apppath'].BS.'.bf'.BS.'.preview'.BS.$thumbdir.BS.$bid.'.small.webp';
            return array('icon'=>$pathdir);
        }else{
            if (strlen($thumbid) < 9) {
                $thumbid = str_pad($thumbid,9,0,STR_PAD_LEFT);
            }
            $pathdir = $data['apppath'].BS.'.bf'.BS.'.preview';
            $thumbpatharr = $this->mbStrSplit($thumbid,3);
            array_pop($thumbpatharr);
            if($iscloud){
                $thumbpath = implode('/',$thumbpatharr);
                $pathdir = \IO::getStream($pathdir.'/'.$thumbpath.'/'.$thumbid.'.webp');

            }else{
                $thumbpath = implode(BS,$thumbpatharr);
                $pathdir = $pathdir.BS.$thumbpath.BS.$thumbid.'.webp';
            }

            return array('icon'=>$pathdir);
        }

    }

    function mbStrSplit ($string, $len=1) {
        $start = 0;
        $strlen = mb_strlen($string);
        while ($strlen) {
            $array[] = mb_substr($string,$start,$len,"utf8");
            $string = mb_substr($string, $len, $strlen,"utf8");
            $strlen = mb_strlen($string);
        }
        return $array;
    }


}