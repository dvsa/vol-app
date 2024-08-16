<?php

namespace Olcs\View\Builder;

use Laminas\View\Model\ViewModel;
use Olcs\View\Model\ViewModel as OlcsViewModel;

/**
 * Class Builder
 * @package Olcs\View\Builder
 */
final class Builder extends AbstractBuilder
{
    /**
     * @param $headerViewTemplate
     * @param string $baseTemplate
     */
    public function __construct(private OlcsViewModel $layout)
    {
    }

    public function setLeft(ViewModel $left)
    {
        $this->layout->setLeft($left);
        return $this;
    }

    /**
     * Extend the render view method
     *
     * @param string|\Laminas\View\Model\ViewModel $view
     * @param string|null $pageTitle
     * @param string|null $pageSubTitle
     * @return \Laminas\View\Model\ViewModel
     */
    protected function decorateView(ViewModel $view)
    {
        $this->layout->setContent($view);
        return $this->layout;
    }
}
