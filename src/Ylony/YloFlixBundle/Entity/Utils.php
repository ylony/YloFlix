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
            if (strripos($wait, 'S0') === FALSE) {
                if (strripos($wait, 'S1') === FALSE) {
                    $i++;
                } else {
                    $cancel++;
                }
            } else {
                $cancel++;
            }
        }
        while ($i2 < $i) {
            $end_str['showName'] = $end_str['showName'] . ' ' . $cut_str[$i2];
            $i2++;
        }
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
            $lign = explode('<', $lign);
            $lign = $lign[0];
        }
        return $lign;
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

    public static function parse($keywords, $keywords2, $temp){
        $temp_open = fopen($temp, 'ab+');
        $thisline = NULL;
        $nextline = NULL;
        if($temp_open){
            $o = 0;
            while($lign = fgets($temp_open)){
                if ($o === 1){
                    if(strpos($lign, $keywords2)){
                        $thisline = $lign;
                        break;
                    }
                }
                else {
                    if(strpos($lign, $keywords)){
                        $o++;
                    }
                }
            }
            if($thisline !== NULL) {
                return $thisline;
            }
            return NULL;
        }
        //echo "Impossible d'ouvrir le fichier temporaire";
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
                //echo 'Impossible de scanner le dossier';
                return false;
            }
        } else {
            //echo "Le dossier série lié à la configuration n'existe pas.";
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
                //echo 'cant open map file';
                return false;
            }
        } else {
            //echo 'map file inexistant';
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
                        $newShow[$j] = $str_info['showName'];
                        $j++;
                    }
                }
                else {
                    //echo "Can't mkdir";
                }
            }
            $nomfichier = explode('/', $list[$i]);
            $source = $dl_folder . $list[$i];
            $dest = $folder . trim($name) . '/' . trim(end($nomfichier));
            $rst = rename(trim($source), trim($dest));
            if ($rst) {
                //echo 'Moved ' . $source . ' to ' . $dest . '</br>';
            } else {
                //echo 'Impossible de déplacer ' . $source . ' a ' . $dest;
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
        //echo "Le dossier series lié à la configuration n'existe pas ou est vide.";
        return null;
    }
}