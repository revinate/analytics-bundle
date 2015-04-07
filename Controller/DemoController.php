<?php

namespace Revinate\AnalyticsBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


use Symfony\Component\Routing\Annotation\Route;

class DemoController extends Controller {

    /**
     * @Route("/api-new/revinate/analytics/demo")
     * @Method({"GET"})
     */
    public function demoAction() {
        return $this->render('RevinateAnalyticsBundle::demo.html.twig');
    }
}