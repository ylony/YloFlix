<?php
/**
 * Created by IntelliJ IDEA.
 * User: hylow
 * Date: 07/07/2017
 * Time: 01:31
 */

namespace Ylony\YloFlixBundle\Entity;


use Ylony\YloFlixBundle\Controller\AppController;

class Log
{
    public function __construct($record, $type)
    {
        if(AppController::$debug)
        {
            $time = $this->GetCurrentTime();
            $color = $this->GetLogColor($type);
            $write = $this->write($record, $time, $color, $type);
            if($write === -1)
            {
                $rst = $this->CheckServer();
                if($rst === -1)
                {
                    echo "Problème interne du serveur can't record logs.";
                }
            }
        }
    }
    private function GetCurrentTime()
    {
        $day = '[' . date('d.m.y') . ']';
        $min = '[' . date('H:i:s') . ']';
        return $day . $min;
    }
    private function GetLogColor($type)
    {
        $color = array( 'SQL' => '#F6DC12',
                        'ok' => '#008000',
                        'fail' => '#D10000');
        return $color[$type];
    }
    private function write($record, $time, $color, $type)
    {
        $logs_folder = AppController::$logsFolder;
        $file = 'site.html';
        if(!file_exists($logs_folder))
        {
            $rst = mkdir($logs_folder);
            if(!$rst) {
                return -1;
            }
        }
        $log_file = fopen($logs_folder . $file, 'ab+');
        fwrite($log_file, '<tr><td>'. $time. '</td><td> <font color=' . $color . '>' . $record . "</font></td></tr>\n");
        return 0;
    }
    private function CheckServer()
    {
        $disk = disk_free_space('./');
        if ($disk<10000000)
        {
            echo 'Le disque dur est plein.';
            return 0;
        }
        return -1;
    }
}