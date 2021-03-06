<?php

namespace Fievel\WebSpider\Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {
        if ($request->isMethod('post')) {
            return new JsonResponse(['message' => 'Hello World!<br/>This is a test for Spiders in POST!']);
        }

        return $this->render('FievelWebSpiderBundle:Default:index.html.twig');
    }
}