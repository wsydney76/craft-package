<?php

namespace wsydney76\package\controllers;

use craft\web\Controller;
use wsydney76\contentoverview\models\Settings;
use wsydney76\contentoverview\Plugin;
use wsydney76\package\models\Page;

class PageController extends Controller
{
    public $defaultAction = 'get-page';


    public function actionGetPage()
    {
        /** @var Settings $settings */
        $settings = Plugin::getInstance()->getSettings();

        $page = new Page();

        return $this->view->renderPageTemplate('contentoverview/index.twig', [
            'page' => $page,
            'settings' => $settings
        ]);
    }
}