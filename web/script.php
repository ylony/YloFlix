<?php
use Ylony\YloFlixBundle\Entity\remoteIO;

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

	$argc = $_SERVER['argc'];
	$argv = $_SERVER['argv'];
	//echo $_SERVER['argc'];
	//print_r($_SERVER['argv']);
	if($argc < 3){
		echo "Invalid parameters.\nUses php script.php showName season episode \n or php script.php showName S01E01\n";
		exit;
	}
	/*
	while($i < $argc)
	{
		if(strpos(haystack, needle))
	}
	*/
	$argv = array_splice($argv, 1, $argc);
	$str = implode(' ', $argv);
	echo $str."\n";
	if(myStrPos(strtoupper($str), 'S0') === TRUE || myStrPos(strtoupper($str), 'S1') === TRUE)
	{		
		print_r(getStrInfo($str));
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
		print_r($strInfo);
	}
	$serie = remoteIO::getSerieOnlineData($strInfo['showName']);
	
	//echo $str;
?>