<?php
if(!defined('IN_OAOOA')) {
    exit('Access Denied');
}
class dzz_app extends dzz_base{

    var $sessionlife = 60*30;
    var $mem = null;

    var $session = null;

    var $config = array();

    var $var = array();

    var $cachelist = array();

    var $init_db = true;
    var $init_setting = true;
    var $init_user = true;
    var $init_session = true;
    var $init_cron = true;
    var $init_misc = true;
    var $init_mobile = false;
    var $init_hook = true;
    var $init_env = true;
    var $init_config = true;
    var $init_input= true;
    var $init_output = true;

    var $initated = false;

    var $superglobal = array(
        'GLOBALS' => 1,
        '_GET' => 1,
        '_POST' => 1,
        '_REQUEST' => 1,
        '_COOKIE' => 1,
        '_SERVER' => 1,
        '_ENV' => 1,
        '_FILES' => 1,
        '_config'=>1
    );

    public static function &instance($params=array()) {
        static $object;
        if(empty($object)) {
            $object = new self($params);
        }
        return $object;
    }

    public function __construct($params=array()) {
        foreach($params as $k=>$v){
            $this->$k = $v;
        }
        if($this->init_env){
            $this->_init_env();
        }
        if($this->init_config){
            $this->_init_config();
        }
        if($this->init_input){
            $this->_init_input();
        }
        if($this->init_output){
            $this->_init_output();
        }
        if($this->init_db){
            $this->_init_db();
        }
        if($this->init_hook){
            $this->_init_hook();
        }
		
    }

    public function init() {
        if(!$this->initated) {
			if($this->init_setting){
				$this->_init_setting();	
			} 
			if($this->init_user){
				 $this->_init_user();	
			} 
           if($this->init_session){
				 $this->_init_session();	
			} 
			if($this->init_cron){
				 $this->_init_cron();	
			} 
           if($this->init_misc){
				 $this->_init_misc();	
			} 
        }
        $this->initated = true;
    }

    private function _init_env() {

        error_reporting(E_ERROR);
        /*if(PHP_VERSION < '5.3.0') {
            set_magic_quotes_runtime(0);
        }*/

        define('MAGIC_QUOTES_GPC', function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc());
        define('ICONV_ENABLE', function_exists('iconv'));
        define('MB_ENABLE', function_exists('mb_convert_encoding'));
        define('EXT_OBGZIP', function_exists('ob_gzhandler'));

        define('TIMESTAMP', time());
        $this->timezone_set();
        foreach ($GLOBALS as $key => $value) {
            if (!isset($this->superglobal[$key])) {
                $GLOBALS[$key] = null; unset($GLOBALS[$key]);
            }
        }
        if(!defined('DZZ_CORE_FUNCTION') && !@include(DZZ_ROOT.'./core/function/function_core.php')) {
            exit('function_core.php is missing');
        }

        if(function_exists('ini_get')) {
            $memorylimit = @ini_get('memory_limit');
            if($memorylimit && return_bytes($memorylimit) < 33554432 && function_exists('ini_set')) {
                ini_set('memory_limit', '128m');
            }
        }

        define('IS_ROBOT', checkrobot());


        global $_G;
        $_G = array(
            'uid' => 0,
            'username' => '',
            'adminid' => 0,
            'groupid' => 1,
            'sid' => '',
            'formhash' => '',
            'connectguest' => 0,
            'timestamp' => TIMESTAMP,
            'starttime' => microtime(true),
            'clientip' => $this->_get_client_ip(),
            'referer' => '',
            'charset' => '',
            'gzipcompress' => '',
            'authkey' => '',
            'timenow' => array(),


            'PHP_SELF' => '',
            'siteurl' => '',
            'localurl' => '',
            'siteroot' => '',
            'siteport' => '',


            'config' => array(),
            'setting' => array(),
            'member' => array(),
            'group' => array(),
            'cookie' => array(),
            'style' => array(),
            'cache' => array(),
            'session' => array(),
            'lang' => array(),

            'rssauth' => '',


        );

        $_G['PHP_SELF'] = dhtmlspecialchars($this->_get_script_url());
        $_G['basescript'] = CURSCRIPT.'php';
        $_G['basefilename'] = basename($_G['PHP_SELF']);
        $sitepath = substr($_G['PHP_SELF'], 0, strrpos($_G['PHP_SELF'], '/'));
        if(defined('IN_API')) {
            $sitepath = preg_replace("/\/api\/?.*?$/i", '', $sitepath);
        } elseif(defined('IN_ARCHIVER')) {
            $sitepath = preg_replace("/\/archiver/i", '', $sitepath);
        }
        $_G['isHTTPS'] = $this->is_HTTPS();//($_SERVER['HTTPS'] && strtolower($_SERVER['HTTPS']) != 'off') ? true : false;

        if(strpos($_SERVER['HTTP_HOST'],':')!==false){
            list($host,$siteport) = explode(':',$_SERVER['HTTP_HOST']);
            $_G['siteport']=':'.intval($siteport);
        }else{
            $_G['siteport'] = (empty($_SERVER['SERVER_PORT']) || $_SERVER['SERVER_PORT'] == '80' || $_SERVER['SERVER_PORT'] == '443' || $_SERVER['HTTP_X_FORWARDED_PORT'] == '443')? '' : ':'.$_SERVER['SERVER_PORT'];
        }
        $_G['siteurl'] = dhtmlspecialchars('http'.($_G['isHTTPS'] ? 's' : '').'://'.$_SERVER['HTTP_HOST'].$sitepath.'/');
     
		if(strpos($_SERVER['HTTP_HOST'],'127.')!==false && strpos($_SERVER['HTTP_HOST'],'localhost')!==false){
			$_G['localurl']=dhtmlspecialchars('http'.($_G['isHTTPS'] ? 's' : '').'://127.0.0.1'.$sitepath.'/');
		}else{
			$_G['localurl']=$_G['siteurl'];
		}
        $url = parse_url($_G['siteurl']);
        $_G['siteroot'] = isset($url['path']) ? $url['path'] : '';
       
        if(defined('SUB_DIR')) {
            $_G['siteurl'] = str_replace(SUB_DIR, '/', $_G['siteurl']);
            $_G['siteroot'] = str_replace(SUB_DIR, '/', $_G['siteroot']);
        }
        $_G['browser']=helper_browser::getbrowser();
        $_G['platform']=helper_browser::getplatform();
        $this->var = & $_G;


    }
    private function is_HTTPS(){
        if($_SERVER['HTTPS'] === 1){  //Apache
            return TRUE;
        }elseif($_SERVER['HTTPS'] === 'on'){ //IIS
            return TRUE;
        }elseif($_SERVER['SERVER_PORT'] == 443){ //其他
            return TRUE;
        }elseif($_SERVER['REQUEST_SCHEME'] == 'https'){ //其他
            return TRUE;
        }elseif(isset($_SERVER['HTTP_X_SCHEME']) && $_SERVER['HTTP_X_SCHEME'] == 'https'){ //其他, k8s集群nginx-ingress
            return TRUE;
		 }elseif(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'){ //其他
            return TRUE;
        }
        return FALSE;
    }
    private function _get_script_url() {
        if(!isset($this->var['PHP_SELF'])){
            $scriptName = basename($_SERVER['SCRIPT_FILENAME']);
            if(basename($_SERVER['SCRIPT_NAME']) === $scriptName) {
                $this->var['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];
            } else if(basename($_SERVER['PHP_SELF']) === $scriptName) {
                $this->var['PHP_SELF'] = $_SERVER['PHP_SELF'];
            } else if(isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $scriptName) {
                $this->var['PHP_SELF'] = $_SERVER['ORIG_SCRIPT_NAME'];
            } else if(($pos = strpos($_SERVER['PHP_SELF'],'/'.$scriptName)) !== false) {
                $this->var['PHP_SELF'] = substr($_SERVER['SCRIPT_NAME'],0,$pos).'/'.$scriptName;
            } else if(isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['SCRIPT_FILENAME'],$_SERVER['DOCUMENT_ROOT']) === 0) {
                $this->var['PHP_SELF'] = str_replace('\\','/',str_replace($_SERVER['DOCUMENT_ROOT'],'',$_SERVER['SCRIPT_FILENAME']));
                $this->var['PHP_SELF'][0] != '/' && ($this->var['PHP_SELF'] = '/'.$this->var['PHP_SELF']);
            } else {
                system_error('request_tainting');
            }
        }
        return $this->var['PHP_SELF'];
    }

    private function _init_input() {
        if (isset($_GET['GLOBALS']) ||isset($_POST['GLOBALS']) ||  isset($_COOKIE['GLOBALS']) || isset($_FILES['GLOBALS'])) {
            system_error('request_tainting');
        }

        if(MAGIC_QUOTES_GPC) {
            $_GET = dstripslashes($_GET);
            $_POST = dstripslashes($_POST);
            $_COOKIE = dstripslashes($_COOKIE);
        }

        $prelength = strlen($this->config['cookie']['cookiepre']);
        foreach($_COOKIE as $key => $val) {
            if(substr($key, 0, $prelength) == $this->config['cookie']['cookiepre']) {
                $this->var['cookie'][substr($key, $prelength)] = $val;
            }
        }


        if($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST)) {
            $_GET = array_merge($_GET, $_POST);
        }

        if(isset($_GET['page'])) {
            $_GET['page'] = rawurlencode($_GET['page']);
        }
        if(isset($_GET['mod'])){
            $_GET['mod'] = (str_replace(array('<','>',".","%","'",'"','}','{',',','/','\\'),'',$_GET['mod']));
        }
        if(isset($_GET['op'])){
            $_GET['op'] = (str_replace(array('<','>',".","%","'",'"','}','{',',','/','\\'),'',$_GET['op']));
        }
        if(!(!empty($_GET['handlekey']) && preg_match('/^\w+$/', $_GET['handlekey']))) {
            unset($_GET['handlekey']);
        }

        if(!empty($this->var['config']['input']['compatible'])) {
            foreach($_GET as $k => $v) {
                $this->var['gp_'.$k] = daddslashes($v);
            }
        }

        $this->var['mod'] = empty($_GET['mod']) ? '' : dhtmlspecialchars($_GET['mod']);
        $this->var['op'] = empty($_GET['op']) ? '' : dhtmlspecialchars($_GET['op']);
        $this->var['inajax'] = empty($_GET['inajax']) ? 0 : (empty($this->var['config']['output']['ajaxvalidate']) ? 1 : ($_SERVER['REQUEST_METHOD'] == 'GET' && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' || $_SERVER['REQUEST_METHOD'] == 'POST' ? 1 : 0));
        $this->var['page'] = empty($_GET['page']) ? 1 : max(1, intval($_GET['page']));
        $this->var['sid'] = $this->var['cookie']['sid'] = isset($this->var['cookie']['sid']) ? dhtmlspecialchars($this->var['cookie']['sid']) : '';

        if(empty($this->var['cookie']['saltkey'])) {
            $this->var['cookie']['saltkey'] = random(8);
            dsetcookie('saltkey', $this->var['cookie']['saltkey'], 86400 * 30, 1, 1);
        }
        $this->var['authkey'] = md5($this->var['config']['security']['authkey'].$this->var['cookie']['saltkey']);

    }

    private function _init_config() {
        global $_config;
        $data=array(
            "config_read"=>"core\dzz\config",
        );
        Hook::import($data);
        Hook::listen("config_read",$_GET);
        if(empty($_config)) {
            if(!file_exists(DZZ_ROOT.'./data/install.lock')) {
                header('location: install');
                exit;
            } else {
                system_error('config_notfound');
            }
        }

        //设置默认语言；
       // setglobal('language',$_config['output']['language']);

        //系统编码配置
        if (strtoupper(substr(PHP_OS, 0,3)) === 'WIN') {
            $config['system_os']='windows';
            if(!$_config['system_charset']) $_config['system_charset']='gbk';
        } else {
            $config['system_os']='linux';
            if(!$_config['system_charset']) $_config['system_charset']='utf-8';
        }
        if(empty($_config['security']['authkey'])) {
            $_config['security']['authkey'] = md5($_config['cookie']['cookiepre'].$_config['db'][1]['dbname']);
        }

        if(empty($_config['debug'])) {
            define('DZZ_DEBUG', false);
            error_reporting(0);
        } elseif($_config['debug'] === 1 || $_config['debug'] === 2 || !empty($_REQUEST['debug']) && $_REQUEST['debug'] === $_config['debug']) {
            define('DZZ_DEBUG', true);
            error_reporting(E_ERROR);
            if($_config['debug'] === 2) {
                error_reporting(E_ALL);
            }
        } else {
            define('DZZ_DEBUG', false);
            error_reporting(0);
        }
        define('STATICURL', !empty($_config['output']['staticurl']) ? $_config['output']['staticurl'] : 'static/');
        $this->var['staticurl'] = STATICURL;

        $this->config = & $_config;

        $this->var['config'] = & $_config;

        if(substr($_config['cookie']['cookiepath'], 0, 1) != '/') {
            $this->var['config']['cookie']['cookiepath'] = '/'.$this->var['config']['cookie']['cookiepath'];
        }
        $this->var['config']['cookie']['cookiepre'] = $this->var['config']['cookie']['cookiepre'].substr(md5($this->var['config']['cookie']['cookiepath'].'|'.$this->var['config']['cookie']['cookiedomain']), 0, 4).'_';
		
		
    }

    private function _init_output() {

        if($this->config['security']['urlxssdefend'] && $_SERVER['REQUEST_METHOD'] == 'GET' && !empty($_SERVER['REQUEST_URI'])) {
            $this->_xss_check();
        }

        if($this->config['security']['attackevasive'] && (!defined('CURSCRIPT') || !in_array($this->var['mod'], array('seccode', 'secqaa', 'swfupload')) && !defined('DISABLEDEFENSE'))) {
            require_once libfile('function/security');
        }

        if(!empty($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') === false) {
            $this->config['output']['gzip'] = false;
        }

        $allowgzip = $this->config['output']['gzip'] && empty($this->var['inajax']) && EXT_OBGZIP;
        setglobal('gzipcompress', $allowgzip);

        if(!ob_start($allowgzip ? 'ob_gzhandler' : null)) {
            ob_start();
        }

        setglobal('charset', $this->config['output']['charset']);
        define('CHARSET', $this->config['output']['charset']);
        if($this->config['output']['forceheader']) {
            @header('Content-Type: text/html; charset='.CHARSET);
        }
		if($this->config['localurl']){
			 setglobal('localurl', $this->config['localurl']);
		}

    }

    public function reject_robot() {
        if(IS_ROBOT) {
            exit(header("HTTP/1.1 403 Forbidden"));
        }
    }

    private function _xss_check() {

        static $check = array('"', '>', '<', '\'', 'CONTENT-TRANSFER-ENCODING');

        if($_SERVER['REQUEST_METHOD'] == 'GET' ) {
            $temp = $_SERVER['REQUEST_URI'];
        } elseif(empty ($_GET['formhash'])) {
            $temp = $_SERVER['REQUEST_URI'].file_get_contents('php://input');
        } else {
            $temp = '';
        }

        if(!empty($temp)) {
            $temp = strtoupper(urldecode(urldecode($temp)));
            foreach ($check as $str) {
                if(strpos($temp, $str) !== false) {
                    system_error('request_tainting');
                }
            }
        }

        return true;
    }


    private function _get_client_ip() {
        $ip = $_SERVER['REMOTE_ADDR'];
        if (isset($_SERVER['HTTP_CLIENT_IP']) && filter_var( $_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)){
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
            $ips=explode(',',$_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach ($ips as $xip) {
                if(filter_var( $xip, FILTER_VALIDATE_IP) && !filter_var( $xip, FILTER_FLAG_NO_PRIV_RANGE)){
                    $ip = $xip;
                    break;
                }
            }
        }
        return $ip;
    }

    private function _init_db() {
        if($this->init_db) {
            $driver = function_exists('mysqli_connect') ? 'db_driver_mysqli' : 'db_driver_mysql';
            if(getglobal('config/db/slave')) {
                $driver = function_exists('mysqli_connect') ? 'db_driver_mysqli_slave' : 'db_driver_mysql_slave';
            }
            DB::init($driver, $this->config['db']);
        }
    }

    private function _init_session() {

        $sessionclose = !empty($this->var['setting']['sessionclose']);
        $this->session = $sessionclose ? new dzz_session_close() : new dzz_session();

        if($this->init_session)	{
            $this->session->init($this->var['cookie']['sid'], $this->var['clientip'], $this->var['uid']);
            $this->var['sid'] = $this->session->sid;
            $this->var['session'] = $this->session->var;
			
            if(!empty($this->var['sid']) && $this->var['sid'] != $this->var['cookie']['sid']) {
                dsetcookie('sid', $this->var['sid'], 86400);
            }

            if($this->session->isnew) {
                if(ipbanned($this->var['clientip'])) {
                    $this->session->set('groupid', 6);
                }
            }

            if($this->session->get('groupid') == 6) {
                $this->var['member']['groupid'] = 6;
                sysmessage('user_banned');
            }
            if($this->var['uid'] && !$sessionclose && ($this->session->isnew || ($this->session->get('lastactivity') + 60) < TIMESTAMP)) {
				Hook::listen("core_session",$this->session);
                $this->session->set('lastactivity', TIMESTAMP);
				$this->session->update();
                if($this->session->isnew) {
                    if($this->var['member']['lastip'] && $this->var['member']['lastvisit']) {
                        dsetcookie('lip', $this->var['member']['lastip'].','.$this->var['member']['lastvisit']);
                    }

                    C::t('user_status')->update($this->var['uid'], array('lastip' => $this->var['clientip'], 'lastvisit' => TIMESTAMP));
                }
            }
        }
    }
    private function check_session($user){
        if(!$this->config['userlogin']['checksession']) return 3;
        $cpaccess=0;
        $session = C::t('admincp_session')->fetch($user['uid'], $user['groupid']);
        if(empty($session)) {
            $cpaccess = 1;
        } elseif ($session && empty($session['uid'])) {
            $cpaccess = 1;
        } elseif ($this->config['userlogin']['checksession'] && ($session['dateline'] < (TIMESTAMP - $this->config['userlogin']['checksession']))) {
            $cpaccess = 1;
        } elseif ($this->config['userlogin']['checkip'] && ($session['ip'] != $this->var['clientip'])) {
            $cpaccess = 1;

        } elseif ($session['errorcount'] >= 0 && $session['errorcount'] <= 3) {
            $cpaccess = 2;

        } elseif ($session['errorcount'] == -1) {
            $cpaccess = 3;
        }
        if($cpaccess == 1) {
            C::t('admincp_session')->delete_by_uid($user['uid'],$user['groupid'], $this->sessionlife);
            C::t('admincp_session')->insert(array(
                'uid' => $user['uid'],
                'adminid' => $user['adminid'],
                'panel' => $user['groupid'],
                'ip' => $this->var['clientip'],
                'dateline' => TIMESTAMP,
                'errorcount' => 0,
            ));
        } elseif ($cpaccess == 3) {
            C::t('admincp_session')->update_by_uid($user['uid'], $user['groupid'], array('dateline' => TIMESTAMP, 'ip' => $this->var['clientip'], 'errorcount' => -1));
        }
        return $cpaccess;
    }
    private function _init_user() {
        if($this->init_user) {
            if($auth = getglobal('auth', 'cookie')) {
                $auth = daddslashes(explode("\t", authcode($auth, 'DECODE')));
            }
            list($dzz_pw, $dzz_uid) = empty($auth) || count($auth) < 2 ? array('', '') : $auth;

            if($dzz_uid) {
                $user = getuserbyuid($dzz_uid, 1);
            }

            if(!empty($user) && $user['password'] == $dzz_pw && ($user['status']<1 || $user['uid']==1)) {//加上判断用户是否被停用

                if($this->check_session($user)==3){
                    $this->var['member'] = $user;
                }else{
                    $user = array();
                    $this->_init_guest();
                }
            } else {
                $user = array();
                $this->_init_guest();
            }
            $this->cachelist[] = 'usergroup_'.$this->var['member']['groupid'];
        } else {
            $this->_init_guest();
        }
        setglobal('groupid', getglobal('groupid', 'member'));
        !empty($this->cachelist) && loadcache($this->cachelist);
        if($this->var['member'] && $this->var['group']['radminid'] == 0 && $this->var['member']['adminid'] > 0 && $this->var['member']['groupid'] != $this->var['member']['adminid'] && !empty($this->var['cache']['admingroup_'.$this->var['member']['adminid']])) {
            $this->var['group'] = array_merge($this->var['group'], $this->var['cache']['admingroup_'.$this->var['member']['adminid']]);
        }

        if(empty($this->var['cookie']['lastvisit'])) {
            $this->var['member']['lastvisit'] = TIMESTAMP - 3600;
            dsetcookie('lastvisit', TIMESTAMP - 3600, 86400 * 30);
        } else {
            $this->var['member']['lastvisit'] = $this->var['cookie']['lastvisit'];
        }

        setglobal('uid', getglobal('uid', 'member'));
        if($this->var['member']['adminid']){
            setglobal('pichomelevel',5);
        }else{
           $pichomelevel =  C::t('user_setting')->fetch_by_skey('pichomelevel',getglobal('uid', 'member'));
            $pichomelevel = $pichomelevel ? intval($pichomelevel):0;
            setglobal('pichomelevel',$pichomelevel);
        }
        $settinglanglist = getglobal('setting/language_list');
        $defaultlang = getglobal('setting/defaultlang');
        $moreLanguageState = getglobal('setting/moreLanguageState');
        if(empty($settinglanglist)){
           if(!$settinglanglist = C::t('setting')->fetch('language_list',1)) {
               $settinglanglist = array(
                   'zh-CN' => array(
                       'langflag' => 'zh-CN',
                       'isdefault' => 1,
                       'name' => '简体中文',
                       'elementflag' => 'zh-cn',
                       'langval' => '简体中文',
                       'icon' => 'dzz/lang/images/w80/zh-CN.png',
                       'elementflagCamel' => 'ElementPlusLocaleZhCn'
                   )
               );
               $defaultlang='zh-CN';
               $moreLanguageState  = 0;
           }
        }

        if(!$defaultlang){
            $defaultlang = C::t('setting')->fetch('defaultlang');
        }
        if(!$moreLanguageState){
            $moreLanguageState = C::t('setting')->fetch('moreLanguageState');
        }
        setglobal('moreLanguageState',$moreLanguageState);
        setglobal('language_list',$settinglanglist);
        setglobal('defaultlang',$defaultlang);

        //设置语言；
		$langlist=$settinglanglist;
		if($language=getcookie('language')){
			
		}else if($this->var['member']['language']){
            $language=$this->var['member']['language'];
        }
		if(!isset($langlist[$language])){
            $language=checkLanguage($langlist,$defaultlang);
        }
        setglobal('language',$language);
        setglobal('username', getglobal('username', 'member'));
        setglobal('adminid', getglobal('adminid', 'member'));
        setglobal('groupid', getglobal('groupid', 'member'));
    }

    private function _init_guest() {
        $username = '';
        $groupid = 7;

        setglobal('member', array( 'uid' => 0, 'username' => $username, 'adminid' => 0, 'groupid' => $groupid, 'credits' => 0, 'timeoffset' => 9999));
    }

    private function _init_cron() {
        $ext = empty($this->config['remote']['on']) || empty($this->config['remote']['cron']);
        if($this->init_cron && $this->init_setting && $ext) {
            if($this->var['cache']['cronnextrun'] <= TIMESTAMP) {
                dzz_cron::run();
            }
        }
    }

    private function _init_misc() {
        if(!$this->init_misc) {
            return false;
        }
        lang('core');

        if($this->init_setting && $this->init_user) {
            if(!isset($this->var['member']['timeoffset']) || $this->var['member']['timeoffset'] == 9999 || $this->var['member']['timeoffset'] === '') {
                $this->var['member']['timeoffset'] = $this->var['setting']['timeoffset'];
            }
        }

        $timeoffset = $this->init_setting ? $this->var['member']['timeoffset'] : $this->var['setting']['timeoffset'];
        $this->var['timenow'] = array(
            'time' => dgmdate(TIMESTAMP),
            'offset' => $timeoffset >= 0 ? ($timeoffset == 0 ? '' : '+'.$timeoffset) : $timeoffset
        );
        $this->timezone_set($timeoffset);

        $this->var['formhash'] = formhash();
        define('FORMHASH', $this->var['formhash']);

        if($this->init_user) {
            $allowvisitflag = in_array(CURSCRIPT, array('user')) || defined('ALLOWGUEST') && ALLOWGUEST;

            if(isset($this->var['member']['status']) && $this->var['member']['status'] == -1 && !$allowvisitflag) {
                showmessage('user_banned');
            }
        }

        if(isset($this->var['setting']['ipaccess']) && $this->var['setting']['ipaccess'] && !ipaccess($this->var['clientip'], $this->var['setting']['ipaccess'])) {
            showmessage('user_banned');
        }

        if($this->var['setting']['bbclosed']) {
            if($this->var['member']['adminid']==1) { //系统管理员允许访问
            } elseif(in_array(CURSCRIPT, array('admin', 'user', 'api')) || defined('ALLOWGUEST') && ALLOWGUEST) {
            } else {
                $closedreason = C::t('setting')->fetch('closedreason');
                $closedreason = str_replace(':', '&#58;', $closedreason);
                dheader("Location: user.php?mod=login");

            }
        }
        if(isset($this->var['setting']['nocacheheaders']) && $this->var['setting']['nocacheheaders']) {
            @header("Expires: -1");
            @header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
            @header("Pragma: no-cache");
        }

        $lastact = TIMESTAMP."\t".dhtmlspecialchars(basename($this->var['PHP_SELF']))."\t".dhtmlspecialchars($this->var['mod']);
        dsetcookie('lastact', $lastact, 86400);
        setglobal('currenturl_encode', base64_encode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']));
		
		
    }

    private function _init_setting() {
		global $_G;
        if($this->init_setting) {
            if(empty($this->var['setting'])) {
                $this->cachelist[] = 'setting';
            }
            if(!isset($this->var['cache']['cronnextrun'])) {
                $this->cachelist[] = 'cronnextrun';
            }
        }
		
        !empty($this->cachelist) && loadcache($this->cachelist);
        if(!is_array($this->var['setting'])) {
            $this->var['setting'] =C::t('setting')->fetch_all();
        }
        if($ismobile=helper_browser::ismobile()) define('IN_MOBILE',$ismobile);
        define('VERHASH',isset($this->var['setting']['verhash'])?$this->var['setting']['verhash']:random(3));
    }

    //初始化之前导入数据库钩子
    private function _init_hook(){
        $tagfile = CACHE_DIR . BS . 'tags' . EXT;
        $data = array();
        if (file_exists($tagfile)) {//文件存在则导入文件
            $data=include $tagfile;
            //if(is_array($data)) $data=array_unique($data);
        }
        if($data){
            Hook::import($data);
        }else{
            foreach(DB::fetch_all("SELECT name,addons FROM %t where `status`='1' ORDER BY priority DESC",array('hooks')) as $value) {
                $addons = $value['addons'];//同一个挂载点下多个钩子改为多条记录
                Hook::add($value['name'],$addons);
            }
            //写入缓存文件
            $data = Hook::get();
            @file_put_contents($tagfile,"<?php \t\n return ".var_export($data,true).";");
        }

    }

    public function timezone_set($timeoffset = 0) {
        if(function_exists('date_default_timezone_set')) {
            @date_default_timezone_set('Etc/GMT'.($timeoffset > 0 ? '-' : '+').(abs($timeoffset)));
        }
    }

}