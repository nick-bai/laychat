<?php
namespace curl;
/**
* Chrome	Mozilla/5.0 (Windows NT 6.1) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.47 Safari/536.11
* IE6		Mozilla/5.0 (Windows NT 6.1; rv:9.0.1) Gecko/20100101 Firefox/9.0.1
* FF		Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; CIBA; .NET CLR 2.0.50727)
*/
class CURL{
	const ITEM_URL=0;
	const ITEM_P=1;
	const ITEM_F=2;
	const ITEM_TRYED=3;
	const ITEM_FP=4;
	const ITEM_P_OPT=5;
	//thread limit
	public $limit=30;
	//try time(s) before curl failed
	public $maxTry=3;
	//user defined opt
	public $opt=array();
	//cache options
	public $cache=array('on'=>false,'dir'=>null,'expire'=>86400);
	//task callback,if taskpool is empty,this callback will be called,you can call CUrl::add() in callback
	public $task=null;

	//the real multi-thread num
	private $activeNum=0;
	//finished task in the queue
	private $queueNum=0;
	//finished task number,include failed task and cache
	private $finishNum=0;
	//The number of cache hit
	private $cacheNum=0;
	//completely failed task number
	private $failedNum=0;
	//task num has added
	private $taskNum=0;
	//all added task was saved here first
	private $taskPool=array();
	//running task(s)
	private $taskRunning=array();
	//failed task need to retry
	private $taskFailed=array();
	//total downloaded size,byte
	private $traffic=0;
	//handle of multi-thread curl
	private $mh=null;
	//time multi-thread start
	private $startTime=null;

	/**
	* running infomation
	*/
	function status($debug=false){
		if($debug){
			$s="finish:".($this->finishNum).'('.$this->cacheNum.')';
			$s.="  task:".$this->taskNum;
			$s.="  active:".$this->activeNum;
			$s.="  running:".count($this->taskRunning);
			$s.="  queue:".$this->queueNum;
			$s.="  failed:".$this->failedNum;
			$s.="  taskPool:".count($this->taskPool);
			echo $s."\n";
		}else{
			static $last=0;
			static $strlen=0;
			$now=time();
			//update status every 1 minute or all task finished
			if($now>$last or ($this->finishNum==$this->taskNum)){
				$last=$now;
				$timeSpent=$now-$this->startTime;
				if($timeSpent==0)
					$timeSpent=1;
				//percent
				$s=sprintf('%-.2f%%',round($this->finishNum/$this->taskNum,4)*100);
				//num
				$s.=sprintf('  %'.strlen($this->finishNum).'d/%-'.strlen($this->taskNum).'d(%-'.strlen($this->cacheNum).'d)',$this->finishNum,$this->taskNum,$this->cacheNum);
				//speed
				$speed=($this->finishNum-$this->cacheNum)/$timeSpent;
				$s.=sprintf('  %-d',$speed).'/s';
				//net speed
				$suffix='KB';
				$netSpeed=$this->traffic/1024/$timeSpent;
				if($netSpeed>1024){
					$suffix='MB';
					$netSpeed/=1024;
				}
				$s.=sprintf('  %-.2f'.$suffix.'/s',$netSpeed);
				//total size
				$suffix='KB';
				$size=$this->traffic/1024;
				if($size>1024){
					$suffix='MB';
					$size/=1024;
					if($size>1024){
						$suffix='GB';
						$size/=1024;
					}
				}
				$s.=sprintf('  %-.2f'.$suffix,$size);
				//estimated time of arrival
				if($speed==0){
					$str='--';
				}else{
					$eta=($this->taskNum-$this->finishNum)/$speed;
					$str=ceil($eta).'s';
					if($eta>3600){
						$str=ceil($eta/3600).'h'.ceil(($eta%3600)/60).'m';
					}elseif($eta>60){
						$str=ceil($eta/60).'m'.($eta%60).'s';
					}
				}
				$s.='  ETA '.$str;
				$len=strlen($s);
				echo "\r".$s;
				if($len>$strlen){
					$strlen=$len;
				}else{
					$t=$strlen-$len;
					//字符串缩短后清除后面的遗留文字并回退光标的位置
					echo str_pad('',$t).str_repeat(chr(8),$t);
				}
				if($this->finishNum==$this->taskNum)
					echo "\n";
			}
		}
	}

	/**
	* read interface
	*/
	function __get($name){
		return $this->$name;
	}

	/**
	* single thread download
	* single thread
	* @param mixed $url
	* @param mixed $file
	* @return boolean true or false
	*/
	function download($url,$file){
		$ch=$this->init($url,$file);
		//curl can create the last level directory
		$dir=dirname($file);
		if(!file_exists($dir))
			mkdir($dir,0777);
		curl_setopt($ch,CURLOPT_FILE,fopen($file,'w'));
		$r=curl_exec($ch);
		fclose($fp);
		if(curl_errno($ch)!==0){
			debug_print('errno: '.curl_errno($ch)."\nerr: ".curl_error($ch));
		}
		return $r;
	}

	/**
	* single thread
	*
	* @param mixed $url
	* @return mixed curl_exec() result
	*/
	function read($url){
		if($this->cache['on']){
			$r=$this->cache($url);
			if(null!==$r)
				return $r;
		}
		$r=array();
		$ch=$this->init($url);
		$content=curl_exec($ch);
		if(curl_errno($ch)===0){
			$r['info']=curl_getinfo($ch);
			$r['content']=$content;
			if($this->cache['on'])
				$this->cache($url,$r);
		}else{
			$r['info'] = '';
			$r['content'] = '';
		}
		return $r;
	}

	/**
	* add a task to taskPool
	*
	* @param array $url $url[0] is url,$url[1] is file path if isset,$url[2] is curl option
	* @param array $p success callback,$p[0] is callback,$p[1] is param for the callback
	* @param array $f fail callback,$f[0] is callback,$f[1] is param for the callback
	*/
	function add($url=array(),$p=array(),$f=array()){
		//check
		if(!is_array($url) or empty($url[0])){
			var_dump($url);
			debug_print('url is invalid',E_USER_ERROR);
		}
		if(!is_array($p) or !is_array($f))
			debug_print('callback is not array',E_USER_ERROR);
		if(!isset($p[0]))
			debug_print('process callback is not set',E_USER_ERROR);
		if((isset($p[1]) and !is_array($p[1])) or (isset($f[1]) and !is_array($f[1]))){
			debug_print('callback function parameter must be an array',E_USER_ERROR);
		}
		//fix
		if(empty($url[1]))
			$url[1]=null;
		if(empty($url[2]))
			$url[2]=null;
		if(!isset($p[1]))
			$p[1]=array();
		if(isset($f[0]) and !isset($f[1]))
			$f[1]=array();
		$task=array();
		$task[self::ITEM_URL]=$url;
		$task[self::ITEM_P]=$p;
		$task[self::ITEM_P_OPT]=$url[2];
		$task[self::ITEM_F]=$f;
		$task[self::ITEM_TRYED]=0; //try times befroe complete failure
		$task[self::ITEM_FP]=null; //file handler for file download
		$this->taskPool[]=$task;
		$this->taskNum++;
	}

	/**
	* Perform the actual task(s).
	*/
	function go(){
		static $running=false;
		if($running)
			debug_print('CURL can only run one instance',E_USER_ERROR);
		$this->mh=curl_multi_init();
		//init
		for($i=0;$i<$this->limit;$i++)
			$this->addTask();
		$this->startTime=time();
		$running=true;
		do{
			$this->exec();
			//curl_multi_select mainly used for blocking
			curl_multi_select($this->mh);
			while($curlInfo = curl_multi_info_read($this->mh,$this->queueNum)){
				$ch=$curlInfo['handle'];
				$info=curl_getinfo($ch);
				$this->traffic+=$info['size_download'];
				$k=(int)$ch;
				$task=$this->taskRunning[$k];
				if(empty($task)){
					debug_print("can't get running task",E_USER_WARNING);
				}
				$callFail=false;
				if($curlInfo['result']==CURLE_OK){
					if(isset($task[self::ITEM_P])){
						$param=array();
						$param['info']=$info;
						if(!isset($task[self::ITEM_URL][1]))
							$param['content']=curl_multi_getcontent($ch);
						array_unshift($task[self::ITEM_P][1],$param);
					}
					//write cache
					if($this->cache['on'] and !isset($task[self::ITEM_URL][1]))
						$this->cache($task[self::ITEM_URL][0],$param);
				}else{
					if($task[self::ITEM_TRYED] >= $this->maxTry){
						$msg='curl error '.$curlInfo['result'].', '.curl_error($ch).', '.$info['url'];
						if(isset($task[self::ITEM_F][0])){
							array_unshift($task[self::ITEM_F][1],$msg);
							$callFail=true;
						}else{
							echo $msg."\n";
						}
						$this->failedNum++;
					}else{
						$task[self::ITEM_TRYED]++;
						$this->taskFailed[]=$task;
						$this->taskNum++;
					}
				}
				curl_multi_remove_handle($this->mh,$ch);
				curl_close($ch);
				if(isset($task[self::ITEM_FP]))
					fclose($task[self::ITEM_FP]);
				unset($this->taskRunning[$k]);
				$this->finishNum++;
				if($curlInfo['result']==CURLE_OK){
					call_user_func_array($task[self::ITEM_P][0],$task[self::ITEM_P][1]);
				}elseif($callFail){
					call_user_func_array($task[self::ITEM_F][0],$task[self::ITEM_F][1]);
				}
				$this->addTask();
				//so skilful,if $this->queueNum grow very fast there will be no efficiency lost,because outer $this->exec() won't be executed.
				$this->exec();
			}
		}while($this->activeNum || $this->queueNum || !empty($this->taskFailed) || !empty($this->taskRunning) || !empty($this->taskPool));
		unset($this->startTime);
		curl_multi_close($this->mh);
		$running=false;
	}

	/**
	* curl_multi_exec()
	*/
	private function exec(){
		while(curl_multi_exec($this->mh, $this->activeNum)===CURLM_CALL_MULTI_PERFORM){}
	}

	/**
	* add a task to curl
	*/
	private function addTask(){
		$c=$this->limit-count($this->taskRunning);
		while($c>0){
			$task=array();
			//search failed first
			if(!empty($this->taskFailed)){
				$task=array_pop($this->taskFailed);
			}else{
				if(0<$left=(int)($this->limit-count($this->taskPool)) and isset($this->task)){
					while($left-->0){
						call_user_func($this->task);
						if(count($this->taskPool)>=$this->limit)
							break;
					}
				}
				if(!empty($this->taskPool))
					$task=array_pop($this->taskPool);
			}
			$cache=null;
			if(!empty($task)){
				if($this->cache['on']==true and !isset($task[self::ITEM_URL][1])){
					$cache=$this->cache($task[self::ITEM_URL][0]);
					if(null!==$cache){
						array_unshift($task[self::ITEM_P][1],$cache);
						$this->finishNum++;
						$this->cacheNum++;
						call_user_func_array($task[1][0],$task[self::ITEM_P][1]);
					}
				}
				if(!$cache){
					$ch=$this->init($task[self::ITEM_URL][0]);
					if(is_resource($ch)){
						//is a download task?
						if(isset($task[self::ITEM_URL][1])){
							//curl can create the last level directory
							$dir=dirname($task[self::ITEM_URL][1]);
							if(!file_exists($dir))
								mkdir($dir,0777);
							$task[self::ITEM_FP]=fopen($task[self::ITEM_URL][1],'w');
							curl_setopt($ch,CURLOPT_FILE,$task[self::ITEM_FP]);
						}
						//single task curl option
						if(isset($task[self::ITEM_P_OPT])){
							foreach($task[self::ITEM_P_OPT] as $k=>$v)
								curl_setopt($ch,$k,$v);
						}
						curl_multi_add_handle($this->mh,$ch);
						$this->taskRunning[(int)$ch]=$task;
					}else{
						debug_print('$ch is not resource,curl_init failed.',E_USER_WARNING);
					}
				}
			}
			if(!$cache)
				$c--;
		}
	}

	/**
	* set or get file cache
	*
	* @param mixed $key
	* @param mixed $content
	* @return return content or false if read,true or false if write
	*/
	private function cache($url,$content=null){
		$key=md5($url);
		if(!isset($this->cache['dir']))
			debug_print('Cache dir is not defined',E_USER_ERROR);
		$dir=$this->cache['dir'].DIRECTORY_SEPARATOR.substr($key,0,3);
		$file=$dir.DIRECTORY_SEPARATOR.substr($key,3);
		if(!isset($content)){
			if(file_exists($file)){
				if((time()-filemtime($file)) < $this->cache['expire']){
					return unserialize(file_get_contents($file));
				}else{
					unlink($file);
				}
			}
		}else{
			$r=false;
			//检查主目录是否存在
			if(!is_dir($this->cache['dir'])){
				debug_print("Cache dir doesn't exists",E_USER_ERROR);
			}else{
				$dir=dirname($file);
				if(!file_exists($dir) and !mkdir($dir,0777))
					debug_print("Create dir failed",E_USER_WARNING);
				$content=serialize($content);
				if(file_put_contents($file,$content,LOCK_EX))
					$r=true;
				else
					debug_print('Write cache file failed',E_USER_WARNING);
			}
			return $r;
		}
	}

	private function init($url){
		$ch=curl_init();
		$opt=array();
		$opt[CURLOPT_URL]=$url;
		$opt[CURLOPT_HEADER]=false;
		$opt[CURLOPT_CONNECTTIMEOUT]=15;
		$opt[CURLOPT_TIMEOUT]=300;
		$opt[CURLOPT_AUTOREFERER]=true;
		$opt[CURLOPT_USERAGENT]='Mozilla/5.0 (Windows NT 6.1) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.47 Safari/536.11';
		$opt[CURLOPT_RETURNTRANSFER]=true;
		$opt[CURLOPT_FOLLOWLOCATION]=true;
		$opt[CURLOPT_MAXREDIRS]=10;
		//user defined opt
		if(!empty($this->opt))
			foreach($this->opt as $k=>$v)
				$opt[$k]=$v;
		curl_setopt_array($ch,$opt);
		return $ch;
	}
}