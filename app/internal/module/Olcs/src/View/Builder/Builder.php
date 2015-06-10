<?php

namespace Olcs\View\Builder;

use Zend\View\Model\ViewModel;

final class Builder extends AbstractBuilder
{
    private $headerViewTemplate;

    /**
     * decoded by request->isXmlHttpRequest()
     * base or ajax
     * @var string
     */
    private $baseTemplate;

    public function __construct($headerViewTemplate, $baseTemplate = 'base')
    {
        $this->headerViewTemplate = $headerViewTemplate;
        $this->baseTemplate = $baseTemplate;
    }

    /**
     * Extend the render view method
     *
     * @param string|\Zend\View\Model\ViewModel $view
     * @param string|null $pageTitle
     * @param string|null $pageSubTitle
     * @return \Zend\View\Model\ViewModel
     */
    protected function decorateView(ViewModel $view)
    {
        // every page has a header, so no conditional logic needed here
        $header = new ViewModel();
        $header->setTemplate($this->headerViewTemplate);

        // we always inherit from the same base layout, unless the request
        // was asynchronous in which case we render a much simpler wrapper,
        // but one which will include any inline JS we need
        // note that if templates don't want this behaviour they can either
        // mark themselves as terminal, or simply not opt-in to this helper
        $base = new ViewModel();
        $base->setTemplate('layout/' . $this->baseTemplate)
            ->setTerminal(true)
            ->addChild($header, 'header')
            ->addChild($view, 'content');

        return $base;
    }
}
