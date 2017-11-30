<?php

namespace Ylony\YloFlixBundle\Controller;

use Doctrine\DBAL\Exception\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Ylony\YloFlixBundle\Entity\Episode;
use Ylony\YloFlixBundle\Entity\remoteIO;
use Ylony\YloFlixBundle\Entity\Utils;
use Ylony\YloFlixBundle\Entity\Log;


class AppController extends Controller
{
    /* Temp config*/
    public static $folder = "C:\Users\Desktop\Series/";
    public static $dl_folder = "C:\Users\Downloads/";
    public static $extAllowed = array('mp4', 'mkv', 'avi');
    public static $googleApiKey = '';
    public static $googleCxKey = '';
    public static $debug = true;
    public static $addicted = array('login' => 'addictedLogin', 'password' => 'addictedPassword');
    /* */


    public function indexAction()
    {
        $repository = $this->getDoctrine()->getManager()->getRepository('YlonyYloFlixBundle:Serie');
        $i = 0;
        $listSeries = $repository->findAll();
        return $this->render('YlonyYloFlixBundle:App:index.html.twig', array('series' => $listSeries, 'i' => $i,));
    }

    public function getAllEpisode($showId)
    {
        $repository = $this->getDoctrine()->getManager()->getRepository('YlonyYloFlixBundle:Episode');
        return $repository->findByShowid($showId);
    }

    public function getThisSerieInfo($showId)
    {
        $repository = $this->getDoctrine()->getManager()->getRepository('YlonyYloFlixBundle:Serie');
        return $repository->findOneById($showId);
    }

    public function checkIfSerieExist($showname)
    {
        $repository = $this->getDoctrine()->getManager()->getRepository('YlonyYloFlixBundle:Serie');
        return $repository->findOneByName(trim($showname));
    }

    public function checkIfEpisodeExist($show, $saison, $episode){
        $repository = $this->getDoctrine()->getManager()->getRepository('YlonyYloFlixBundle:Episode');
        return $repository->findOneBy(array('showid' => (int)$show->getId(), 'saison' => (int)$saison, 'episode' => (int)$episode));
    }

    public function convertToEpisode($element)
    {
        $elementInfo = Utils::getStringInfo($element);
        Utils::$rootDir = $this->get('kernel')->getRootDir() . '/../web';
        $show = $this->checkIfSerieExist($elementInfo['showName']);
        if($show !== null && $this->checkIfEpisodeExist($show, $elementInfo['saison'], $elementInfo['episode']) === null)
        {
            $array = array('title' => NULL, 'download_link' => NULL);
            $i = 0;
            while (empty($array['title']) || empty($array['download_link']))
            {
                $array = remoteIO::getOnlineData($show, $elementInfo['saison'], $elementInfo['episode']);
                if ($i >= 10)
                {
                    throw new \Symfony\Component\Form\Exception\InvalidArgumentException("Impossible de récupérer les données de l'épiode " . $elementInfo['saison'] . 'x' . $elementInfo['episode'] . ' sur internet.');
                }
                $i++;
                if (empty($array['title']) || empty($array['download_link']))
                {
                    new Log('getOnlineData sleeping array empty', 'fail');
                    sleep(5);
                }
            }
            $episode = new Episode();
            $episode->setEpisode($elementInfo['episode']);
            $episode->setSaison($elementInfo['saison']);
            $episode->setShowid($show->getId());
            $episode->setStr($element);
            $episode->setSublang('French'); // default
            $episode->setTitle($array['title']);
            $episode->setUrl($array['download_link']);
            $repository = $this->getDoctrine()->getManager();
            $repository->persist($episode); // Save to DB
            new Log('New episode : ' . $show->getName() . ' ' . $elementInfo['saison']. ' ' . $elementInfo['episode'], 'ok');
            return $episode;
        }

    }

    public function refreshThisShowFolder($showName)
    {
        set_time_limit(0); // Long task
        $path = self::$folder . $showName;
        if (!file_exists($path)) {
            throw new FileNotFoundException($path . " Don't exist or Access is denied");
        }
        if ($list = opendir($path)) {
            while (false !== ($serie = readdir($list))) {
                if ($serie !== '.' && $serie !== '..') {
                    $ext = explode('.', $serie);
                    $ext = end($ext);
                    if (in_array($ext, self::$extAllowed, true)) {
                        $this->convertToEpisode($serie);
                    }
                }
            }
            $repository = $this->getDoctrine()->getManager();
            $repository->flush(); // Save to DB
        }
    }

    public function viewShowAction($id, $fromDL = false)
    {
        $thisSerie = $this->getThisSerieInfo($id);
        $this->refreshThisShowFolder($thisSerie->getName());
        $listEpisodes = $this->getAllEpisode($id);
        return $this->render('YlonyYloFlixBundle:App:onShow.html.twig', array('episodes' => $listEpisodes, 'thisSerie' => $thisSerie, 'fromDL' => $fromDL));
    }

    public function downloadSubAction($id, $saison, $episode)
    {
        Utils::$rootDir = $this->get('kernel')->getRootDir() . '/../web';
        $show = $this->getThisSerieInfo($id);
        $element = $this->checkIfEpisodeExist($show, $saison, $episode);
        $file = './tmp/' . substr($element->getStr(),0,strlen($element->getStr())-4) . '.srt';
        if($element !== null){
            $remote = new remoteIO();
            $remote->login();
            $remote->getSub('http://www.addic7ed.com' . $element->getUrl(), $file);
            $remote->close();
            if(Utils::checkEmptyFile($file))
            {
                if(!rename($file, self::$folder.$show->getName() . '/' . substr($element->getStr(),0,strlen($element->getStr())-4) . '.srt'))
                {
                    throw new AccessDeniedException($file);
                }
                return $this->viewShowAction($id, true);
            }
            throw new InvalidArgumentException("Can't download the subtitle from the generated link");
        }
        throw new InvalidArgumentException("This episode don't exist in database.");
    }

    public function dashboardAction(){
        return $this->render('YlonyYloFlixBundle:App:dashboard.html.twig');
    }
}

?>
