<?php
class creroHtmlCache {
	static $htmlcacheexpires=7;
	static $cachedat=Array();
	public function __construct($htmlcache_expires){
		$this::$htmlcacheexpires=floatval($htmlcache_expires);
		if (file_exists('./htmlcache/cached.dat')){
			$this::$cachedat=unserialize(file_get_contents('./htmlcache/cached.dat'));
		}
		
	//cleanup old pages
	
	$cachedkeys=array_keys($this::$cachedat);
	
	foreach ($cachedkeys as $cachedkey){
		
		$cachedpage=$this::$cachedat[$cachedkey];
		
		if ((floatval($cachedpage['expires'])
				+floatval($this::$htmlcacheexpires*3600))
				<floatval(microtime(true))){
					unlink('./htmlcache/cached/'.$cachedpage['expires'].'.html');
					unset($this::$cachedat[$cachedkey]);
				}
		
		}
	$this->saveCacheDatToDisk();
	
	}
	public function hasPageExpired($cachedkey){
		if (array_key_exists($cachedkey, $this::$cachedat))
			{
			$cachedpage=$this::$cachedat[$cachedkey];
			if (
					(floatval($cachedpage['expires'])
				+floatval($this::$htmlcacheexpires*3600))
				<floatval(microtime(true))
			
				){
					return true;
					
				}
			else {
				
				return false;
			}
		}
		else {
			return true;
		}
	}
	
	public function cachePage($cachedkey, $htmlCode){
		$willexpire=microtime(true);
		if (file_put_contents('./htmlcache/cached/'.floatval($willexpire).'.html', $htmlCode)){
			$pagedat=Array();
			$pagedat['expires']=''.floatval($willexpire).'';
			$this::$cachedat[$cachedkey]=$pagedat;
			return $this->saveCacheDatToDisk();
			
		}
		
	}
	public function getCachedPage($cachedkey){
		$page=$this::$cachedat[$cachedkey];
		return file_get_contents('./htmlcache/cached/'.$page['expires'].'.html');
	}
	
	
	private function saveCacheDatToDisk(){
			return file_put_contents('./htmlcache/cached.dat', serialize ($this::$cachedat));
		
		
	}
}

?>
