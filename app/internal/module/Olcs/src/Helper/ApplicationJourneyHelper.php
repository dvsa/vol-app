<?php

/**
 * ApplicationJourney Helper
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Helper;

use Zend\View\Model\ViewModel;

/**
 * ApplicationJourney Helper
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationJourneyHelper
{
    /**
     * Setup the layouts and then render
     *
     * @param ViewModel $content
     * @return ViewModel
     */
    public function render($content)
    {
        $header = $this->renderPageHeader();

        $layout = new ViewModel();
        $layout->setTemplate('layout/application');
        $layout->addChild($content, 'content');

        return $this->renderBaseLayout($header, $layout);
    }

    /**
     * Render the base layout
     *
     * @param ViewModel $header
     * @param ViewModel $layout
     * @return ViewModel
     */
    protected function renderBaseLayout($header, $layout)
    {
        $base = new ViewModel();
        $base->setTemplate('layout/base')
            ->setTerminal(true)
            ->setVariables($layout->getVariables())
            ->addChild($header, 'header')
            ->addChild($layout, 'content');

        return $base;
    }

    /**
     * Render the page header
     *
     * @return ViewModel
     */
    protected function renderPageHeader()
    {
        $header = new ViewModel(
            array(
                'pageTitle' => 'Licence number/Application number',
                'pageSubTitle' => 'Company name ltd'
            )
        );

        $header->setTemplate('layout/partials/header');

        return $header;
    }
}
