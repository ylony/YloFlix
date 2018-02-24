<?php

use Ylony\YloFlixBundle\Entity\Utils;
use Ylony\YloFlixBundle\Entity\remoteIO;

$loader = require __DIR__.'/../vendor/autoload.php';


//$ligne1 = Utils::parse('<head>', 'content="', "./tmp/test.html");

//echo $ligne1."</br>";


//echo Utils::getTitle($ligne1);

$cache = remoteIO::cacheGenerate("http://www.addic7ed.com/serie/Marvel%27s_Agents_of_S.H.I.E.L.D./2/1/Shadows", 404);
Utils::optimiseFile("./tmp/404.html");

?>