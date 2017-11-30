<?php
/**
 * Created by IntelliJ IDEA.
 * User: hylow
 * Date: 06/07/2017
 * Time: 23:51
 */

namespace Ylony\YloFlixBundle\Controller;


use Ylony\YloFlixBundle\Entity\remoteIO;
use Ylony\YloFlixBundle\Entity\Utils;

class DashboardController extends AppController
{
    public function refreshAction(){
        $newShow = Utils::refresh();
        $i = 0;
        while(!empty($newShow[$i])){
            $newShow[$i]['pics'] = remoteIO::getOnlinePictures($newShow[$i]);
            $i++;
        }
        return $this->render('YlonyYloFlixBundle:App:dashboardRefresh.html.twig', array('newShow' => $newShow));
    }
}