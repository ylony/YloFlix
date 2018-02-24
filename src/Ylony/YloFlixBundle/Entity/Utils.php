<?php
/**
 * Created by IntelliJ IDEA.
 * User: hylow
 * Date: 27/06/2017
 * Time: 00:59
 */

namespace Ylony\YloFlixBundle\Entity;


use Ylony\YloFlixBundle\Controller\AppController;

class Utils
{
    public static $rootDir;

    public static function getSeasonFromStr($str)
    {
        $i = 0;
        while($i <= 99)
        {
            if (strripos($str, 'S'.$i) === FALSE) {
                $i++;
            }
            else{
                return $str;
            }
        }
        return FALSE;
    }

    /* Old function need to be updated but still working here  */

    public static function getStringInfo($str)
    {
        $i = 0; //check sttripos
        $i2 = 0; //generate name
        $cancel = 0;
        $end_str = array('showName' => '',
            'saison' => '',
            'episode' => '');
        $cut_str = explode('.', $str);
        if (empty($cut_str[$i]))
            print_r($str);
        while ($cancel === 0) {
            if (empty($cut_str[$i])) { // If cant find any stop
                return null;
            }
            $wait = strtoupper($cut_str[$i]);
            if(self::getSeasonFromStr($wait) === FALSE){
                $i++;
            }
            else{
                $cancel++;
            }
        }
        while ($i2 < $i) {
            $end_str['showName'] = $end_str['showName'] . ' ' . $cut_str[$i2];
            $i2++;
        }
        $end_str['showName'] = trim($end_str['showName']);
        $end_str['saison'] = substr($cut_str[$i], 1, 2);
        $end_str['episode'] = substr($cut_str[$i], 4, 2);
        return $end_str;
    }

    public static function getTitle($lign)
    {
        if($lign !== NULL)
        {
            $lign = explode('-', $lign);
            $lign = end($lign);
            $lign = explode('subtitles', $lign);
            $lign = $lign[0];
        }
        return trim($lign);
    }

    public static function getDownloadLink($lign)
    {
        if($lign !== NULL)
        {
            $lign = explode('"', $lign);
            $lign = $lign[9];
        }
        return $lign;
    }

    /**
     * Verifie si un fichier est vide ou non
     * @return true or false, true IF not empty
     * @param $file, path to the file
     */

    public static function checkEmptyFile($file)
    {
        if(!file_exists($file)){ return false; }
        $file = fopen($file, 'rb');
        if($file)
        {
            $line = fgets($file);
            return !empty($line);
        }
            return false;
    }

    public static function myStrPos($str, $keyword)
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

    public static function parse($keywords, $keywords2, $temp){
        $temp_open = fopen($temp, 'ab+');
        $thisline = NULL;
        $nextline = NULL;
        if($temp_open){
            $o = 0;
            while($lign = fgets($temp_open)){
                if ($o === 1){
                    if(self::myStrPos($lign, $keywords2)){
                        $thisline = $lign;
                        break;
                    }
                }
                else {
                    if(self::myStrPos($lign, $keywords)){
                        $o++;
                    }
                }
            }
            if($thisline !== NULL) {
                return $thisline;
            }
            return NULL;
        }
        new Log("Can't open the temp file : " . $temp, 'fail');
        return NULL;
    }

    public static function generate_map($dir)
    {
        $dl_folder = AppController::$dl_folder;
        $result = array();
        if (file_exists($dir)) {
            $cdir = scandir($dir, 0);
            if ($cdir) {
                foreach ($cdir as $key => $value) {
                    if (!in_array($value, array('.', '..'), true)) {
                        if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
                            $result[$value] = self::generate_map($dir . '/' . $value);
                        } else {
                            $result[] = $value;
                            $path = $dir . '/' . $value;
                            $map_file = fopen('./tmp/map_file.txt', 'ab');
                            $count = strlen($dl_folder) + 1;
                            $path = substr($path, $count);
                            $ext = explode('.', $path);
                            $ext = end($ext);
                            if (in_array($ext, AppController::$extAllowed, true)) {
                                fwrite($map_file, $path . "\n");
                            }
                            fclose($map_file);
                        }
                    }
                }
            } else {
                new Log("Can't scan the dir : " . $dir, 'fail');
                return false;
            }
        } else {
            new Log("The show folder doesn't exist : " . $dir, 'fail');
            return false;
        }
    }

    public static function file_memory()
    {
        $list = array();
        $i = 0;
        if (file_exists('./tmp/map_file.txt')) {
            $map = fopen('./tmp/map_file.txt', 'rb');
            if ($map) {
                while ($path = fgets($map)) {
                    $list[$i] = $path;
                    $i++;
                }
                fclose($map);
                unlink('./tmp/map_file.txt');
            } else {
                new Log("Can't open map file", 'fail');
                return false;
            }
        } else {
            new Log('Map file not found', 'fail');
            return false;
        }
        return $list;
    }

    public static function c_sort($list)
    {
        $folder = AppController::$folder;
        $dl_folder = AppController::$dl_folder;
        $i = 0;
        $j = 0;
        $str_info = array();
        $newShow = array();
        //print_r($list);
        while (!empty($list[$i])) {
            //echo $list[$i];
            $str_info = Utils::getStringInfo($list[$i]);
            $name = $str_info['showName'];
            $name = explode('/', $name);
            $name = end($name);
            if (!file_exists($folder . trim($str_info['showName']))) {
                if (!empty($str_info['showName'])) {
                    if(mkdir($folder . trim($str_info['showName']))){
                        $newShow[$j] = trim($str_info['showName']);
                        $j++;
                    }
                }
                else {
                    new Log("Can't mkdir : " . $folder . trim($str_info['showName']), 'fail');
                }
            }
            $nomfichier = explode('/', $list[$i]);
            $source = $dl_folder . $list[$i];
            $dest = $folder . trim($name) . '/' . trim(end($nomfichier));
            $rst = rename(trim($source), trim($dest));
            if ($rst) {
                new Log('Moved ' . $source . ' to ' . $dest . '</br>', 'ok');
            } else {
                new Log('Impossible de déplacer ' . $source . ' a ' . $dest, 'fail');
            }
            $i++;
        }
        return $newShow;
    }

    public static function refresh()
    {
        $dl_folder = AppController::$dl_folder;
        self::generate_map($dl_folder);
        $list = self::file_memory();
        if (!empty($list))
        {
            return self::c_sort($list);
        }
        new Log("Le dossier series lié à la configuration n'existe pas ou est vide.", 'fail');
        return null;
    }

    public static function getLogs(){
        return file_get_contents(AppController::$logsFolder . 'site.html');
    }

    public static function convertItemsToPics($jsonItems){
        $i = 0;
        $pics = array();
        while(!empty($jsonItems[$i])){
            $pics[$i] = $jsonItems[$i]['link'];
            $i++;
        }
        return $pics;
    }

    public static function optimiseFile($tmp)
    {
        $file = fopen($tmp, "a+");
        $newPath = $tmp . ".opt";
        $fileTmp = fopen($newPath, "a+");
        if($file && $fileTmp){
            while($ligne = fgets($file)){
                if(self::myStrPos($ligne, '<meta name="description" content="')){
                    fwrite($fileTmp, $ligne);
                }
                if(self::myStrPos($ligne, '<div id="container95m">')){
                    while(self::myStrPos($ligne, '</div>') == FALSE){
                        fwrite($fileTmp, $ligne);
                        $ligne = fgets($file);
                    }
                }
            }
            fclose($file);
            fclose($fileTmp);
            return $newPath;
        }
        else{
            throw new FileNotFoundException("Can't optimise the temporary file, can't open it");
        }
    }

}