<?php

namespace Ylony\YloFlixBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('YlonyYloFlixBundle:Default:index.html.twig');
    }
}
