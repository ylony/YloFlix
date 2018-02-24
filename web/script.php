<?php
use Ylony\YloFlixBundle\Entity\remoteIO;
use Ylony\YloFlixBundle\Entity\Episode;
use Ylony\YloFlixBundle\Entity\Serie;
use Ylony\YloFlixBundle\Entity\Utils;

$loader = require __DIR__.'/../vendor/autoload.php';

function getStrInfo($str)
    {
        $explode = explode(' ', $str);
        $i = 0;
        $showName = null;
        while(strripos(strtoupper($explode[$i]), 'S0') === FALSE && strripos(strtoupper($explode[$i]), 'S1') === FALSE){
        	$showName = $showName . ' ' . $explode[$i];
        	$i++;
        	if(empty($explode[$i])){
        		break;
        	}
        }
        $element = explode(' ', $str);
        $element = end($element);
        $saison = explode('E', $element);
        $episode = $saison['1'];
        $saison = $saison['0'];
        $saison = substr($saison, 1, strlen($saison));
        return array('showName' => $showName, 'saison' => $saison, 'episode' => $episode);
    } 
	function myStrPos($str, $keyword)
	{
		$i = 0;
		$j = strlen($str);
		$x = 0;
		while($i < $j)
		{
			if($str[$i] == $keyword[$x]){
				$x++;
			}
			else{
				$x = 0;
			}
			if($x == strlen($keyword)){
				return true;
			}
			$i++;
		}
		return false;
	}

	function searchAddicted($strInfo, $lang = "French"){
		$rand = mt_rand(1, 9999999);
		if(remoteIO::cacheGenerate('http://www.addic7ed.com/search.php?search=' . $strInfo['showName'] . '+s' . (int)$strInfo['saison'] . 'e' . (int)$strInfo['episode'] . '&Submit=Search', $rand)){
			$ligne1 = Utils::parse('<td width="50%"><div align="left"><span class="titulo">', '<small>', "./tmp/{$rand}.html");
			$ligne2 = Utils::parse('<td width="21%" class="language">'.$lang, '</strong>', "./tmp/{$rand}.html");
			unlink("./tmp/{$rand}.html");
			$array['title'] = trim(Utils::getTitle($ligne1));
			$array['download_link'] = trim(Utils::getDownloadLink($ligne2));
			return $array;
		}
		return null;
	}

	function getEpisodeData($strInfo)
	{
		$array = array('title' => NULL, 'download_link' => NULL);
		$i = 0;
		while (empty($array['title']) || empty($array['download_link']))
		{
		    $array = searchAddicted($strInfo);
		    if ($i >= 10)
		    {
		        exit("Impossible de récupérer les données de l'épiode " . $strInfo['saison'] . 'x' . $strInfo['episode'] . ' sur internet.');
		    }
		    $i++;
		    if (empty($array['title']) || empty($array['download_link']))
		    {
		        echo "getOnlineData sleeping array empty\n";
		        sleep(5);
		    }
		}
		$episode = new Episode();
		$episode->setEpisode($strInfo['episode']);
		$episode->setSaison($strInfo['saison']);
		//$episode->setShowid($show->getId());
		$episode->setStr("none.mkv");
		$episode->setSublang('French'); // default
		$episode->setTitle($array['title']);
		$episode->setUrl($array['download_link']);
		return $episode;
	}

	$argc = $_SERVER['argc'];
	$argv = $_SERVER['argv'];
	//echo $_SERVER['argc'];
	//print_r($_SERVER['argv']);
	/*if($argc < 3){
		echo "Invalid parameters.\nUses php script.php showName season episode \n or php script.php showName S01E01\n";
		exit;
	}*/
	/*
	while($i < $argc)
	{
		if(strpos(haystack, needle))
	}
	*/
	$argv = array_splice($argv, 1, $argc);
	$str = implode(' ', $argv);
	echo $str."\n";
	if($argc == 2){
		$strInfo = Utils::getStringInfo($str);
	}
	else{
		if(myStrPos(strtoupper($str), 'S0') === TRUE || myStrPos(strtoupper($str), 'S1') === TRUE)
		{		
			$strInfo = getStrInfo($str);
		}
		else
		{
			$i = 0;
			$showName = null;
			while($i < $argc - 3){
				$showName = $showName . $argv[$i];
				$i++;
			}
			$strInfo = array('showName' => $showName, 'saison' => $argv[$argc - 3], 'episode' => $argv[$argc - 2]);
		}
	}
	print_r($strInfo);
	$elEpisodo = getEpisodeData($strInfo);
	print_r($elEpisodo);
	$file = './tmp/' . substr($str,0,strlen($str)-4) . '.srt';
	$remote = new remoteIO();
	$remote->login();
	$remote->getSub('http://www.addic7ed.com' . $elEpisodo->getUrl(), $file);
	$remote->close();

	//echo $str;
?>