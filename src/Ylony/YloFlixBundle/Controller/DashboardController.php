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
use Ylony\YloFlixBundle\Entity\Serie;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Exception\InvalidArgumentException;

class DashboardController extends Controller
{
    public function refreshAction(Request $request){
    	$formBuilder = $this->get('form.factory')->createBuilder();
    	$form = $formBuilder->getForm();
    	if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
	        $newShow = Utils::refresh();
	        $i = 0;
	        while(!empty($newShow[$i])){
	        	$pics = Utils::convertItemsToPics(remoteIO::getOnlinePictures($newShow[$i]));
	            $newShow[$i] = array('showName' => $newShow[$i], 'pics' => $pics);
	            $i++;
	        }
	    }
        return $this->render('YlonyYloFlixBundle:App:dashboardRefresh.html.twig', array('form' => $form->createView()));
    }

    public function addShowAction(Request $request){
	    $show = new Serie();
	    $formBuilder = $this->get('form.factory')->createBuilder(FormType::class, $show);
	    $formBuilder
	      ->add('name',      TextType::class)
	      ->add('pic',		 FileType::class)
	    ;
	    $form = $formBuilder->getForm();
	    $pics = null;
	    if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {    
	    	$id = remoteIO::getSerieOnlineData($show->getName());
	    	if(empty($id)){
	    		throw new InvalidArgumentException('Show not found online try the manual method');
	    	}
	    	$show->setUrl('http://www.addic7ed.com/show/' . $id);
	    	$pics = Utils::convertItemsToPics(remoteIO::getOnlinePictures($show->getName()));
	    	return $this->render('YlonyYloFlixBundle:App:dashboardAdd.html.twig', array(
		      'form' => $form->createView(),
		      'pics' => $pics
		    ));
	    }

	    return $this->render('YlonyYloFlixBundle:App:dashboardAdd.html.twig', array(
	      'form' => $form->createView(),
	      'pics' => $pics
	    ));
    }

    public function viewLogsAction(){
    	$logs = Utils::getLogs();
    	return $this->render('YlonyYloFlixBundle:App:dashboardviewLogs.html.twig', array('logs' => $logs));
    }
}