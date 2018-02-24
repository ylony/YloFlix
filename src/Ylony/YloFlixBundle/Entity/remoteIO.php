<?php

namespace Ylony\YloFlixBundle\Entity;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\FileNotFoundException;
use Ylony\YloFlixBundle\Controller\AppController;

class remoteIO {

	private $handle, $rand, $login = true;
	public function __construct()
	{
        $addicted = AppController::$addicted;
        $gen_cookie = self::grabACookie();
        if($gen_cookie === false)
        {
		  $this->rand = mt_rand(1, 999999);
		  $gen_cookie = Utils::$rootDir . '/tmp/RIO'.$this->rand.'.cookie';
          $this->login = false;
        }
		$url        = 'http://www.addic7ed.com/dologin.php';
		$cookieFile = $gen_cookie;
		$userAgent  = 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:11.0) Gecko/20100101 Firefox/11.0';
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
		curl_setopt($ch, CURLOPT_COOKIEJAR,  $cookieFile);
		curl_setopt($ch, CURLOPT_USERAGENT,  $userAgent);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER,    1);
		$post = array('username'  => $addicted['login'],
		              'password' => $addicted['password'],
		              'submit' => 'Log+in');
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
		$this->handle = $ch;
	}

	public function login()
	{
        if(!$this->login){
            $html = curl_exec($this->handle);
            new Log('Login Succesfully to addicted', 'ok');
            if(!empty($html)){
                $this->login = true;
                return true;                
            }
            else{
                new Log("Can't login to addited", 'fail');
            }    
        }		
		return false;
	}

	public function getSub($url, $file)
	{
		curl_setopt($this->handle, CURLOPT_URL, $url);
		curl_setopt($this->handle, CURLOPT_POST, 0);
		curl_setopt($this->handle, CURLOPT_POSTFIELDS, null);
		$html = curl_exec($this->handle);
		if(strpos($html, 'Daily Download count exceeded')) {
            throw new AccessDeniedHttpException("Daily Download count exceeded on Addicted server for your IP\n");
        }
        $fichier = fopen($file, 'ab+');
        fwrite($fichier, $html);
        fclose($fichier);
	}

	public function close(){
		curl_close($this->handle);
	}

    public static function download($remote_file, $local_file)
    {
        set_time_limit(0);
        $fp = fopen($local_file, 'ab+');
        $ch = curl_init(str_replace(' ', '%20', $remote_file));
        curl_setopt($ch, CURLOPT_TIMEOUT, 50);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
        sleep(1); //Avoid flooding
        return $local_file;
    }

    public static function download2($remoteFile, $localFile)
    {
        $remote = new remoteIO();
        $remote->login();
        $remote->getSub($remoteFile, $localFile);
        $remote->close();
        return $localFile;
    }

    public static function cacheGenerate($link, $rand)
    {
        if (!file_exists('./tmp/')) {
            mkdir("./tmp/");
        }
        if (file_exists("./tmp/{$rand}.html")) {
            unlink("./tmp/{$rand}.html");
        }
        $generatedFile = remoteIO::download2($link, "./tmp/{$rand}.html");
        if(!Utils::checkEmptyFile($generatedFile))
        {
            new Log("Can't generate a cache from " . $link, 'fail');
            return false;
        }
        new Log("Succesfully cached " . $link, 'ok');
        return true;
    }

    public static function getOnlineData($show, $saison, $episode, $lang = 'French')
    {
        $array = array('title' => null, 'download_link' => null);
        $rand = mt_rand(1, 9999999);
        $link = $show->getUrl();
        $addId = explode('/', $link);
        $addId = end($addId);
        if(remoteIO::cacheGenerate('http://www.addic7ed.com/re_episode.php?ep=' . $addId . '-' . (int)$saison . 'x' . (int)$episode, $rand)){
            $ligne1 = Utils::parse('<head>', 'content="', "./tmp/{$rand}.html");
            $ligne2 = Utils::parse('<td width="21%" class="language">'.$lang, '</strong>', "./tmp/{$rand}.html");
            unlink("./tmp/{$rand}.html");
            $array['title'] = trim(Utils::getTitle($ligne1));
            $array['download_link'] = trim(Utils::getDownloadLink($ligne2));
        }
        new Log('Parsing results : ' . $array['title'] . ' ' . $array['download_link'], 'fail');
        return $array;
    }

    public static function getSerieOnlineData($showName)
    {
        $rand = mt_rand(0, 99999999);
        if(remoteIO::cacheGenerate('www.addic7ed.com', $rand)){
            $ligne = Utils::parse('<option', $showName, './tmp/' . $rand . '.html');
            if(empty($ligne))
            {
                new Log("Parsing return an empty result. Pattern : " . $showName . " on file : ./tmp/ " . $rand . ".html", "fail");
                return null;
            }
            $ligne = explode($showName, $ligne);
            $ligne = explode('<', $ligne[0]);
            $ligne = end($ligne);
            $ligne = explode('"', $ligne);
            $showid = $ligne[1];
            unlink('./tmp/' . $rand . '.html');
            return $showid;
        }
        return null;
    }

    public static function getOnlinePictures($showName){
        $showName = str_replace(' ', '+', $showName);
        $json = json_decode(file_get_contents('https://www.googleapis.com/customsearch/v1?key=' . AppController::$googleApiKey . '&cx='. AppController::$googleCxKey .'&q='. $showName .'+show&searchType=image&fileType=jpg&imgSize=xlarge&alt=json'), true);
        return $json['items'];
    }

    public static function grabACookie(){
        $tmpFolder = scandir('./tmp');
        $cookieExpire = 15 * 60;
        $i = 0;
        while(!empty($tmpFolder[$i]))
        {
            $ext = explode('.', $tmpFolder[$i]);
            $ext = end($ext);
            if($ext == 'cookie')
            {
                $makeTime = filectime(realpath('./tmp/' . $tmpFolder[$i]));
                $currentTime = time();
                if($currentTime - $makeTime < $cookieExpire - 10)
                {
                    new Log('cookie found : '. './tmp/' . $tmpFolder[$i], 'ok');
                    return realpath('./tmp/' . $tmpFolder[$i]); // Grab one already logged
                }
                unlink(realpath('./tmp/' . $tmpFolder[$i]));
            }
            $i++;
        }
        new Log('no cookie available', 'fail');
        return false; // No cookie found need to generate a new one 
    }

}