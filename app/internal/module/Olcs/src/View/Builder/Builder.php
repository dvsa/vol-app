<?php

namespace Olcs\View\Builder;

use Zend\View\Model\ViewModel;
use Olcs\View\Model\ViewModel as OlcsViewModel;

/**
 * Class Builder
 * @package Olcs\View\Builder
 */
final class Builder extends AbstractBuilder
{
    /**
     * @var OlcsViewModel
     */
    private $layout;

    /**
     * @param $headerViewTemplate
     * @param string $baseTemplate
     */
    public function __construct(OlcsViewModel $viewModel)
    {
        $this->layout = $viewModel;
    }

    public function setLeft(ViewModel $left)
    {
        $this->layout->setLeft($left);
        return $this;
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
        $this->layout->setContent($view);
        return $this->layout;
    }
}
