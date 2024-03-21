<?php

/**
 * View Model
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\View\Model;

use Laminas\View\Model\ViewModel as LaminasViewModel;

/**
 * View Model
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ViewModel extends LaminasViewModel
{
    protected $terminate = true;

    protected $template = 'layout/base';

    protected $variables = [
        'contentTitle' => null,
        'pageTitle' => null,
        'pageSubTitle' => null,
        'horizontalNavigationId' => null
    ];

    public function setHorizontalNavigationId($id)
    {
        $this->horizontalNavigationId = $id;
    }

    public function setContent(LaminasViewModel $content)
    {
        $this->addChild($content, 'content');
    }

    public function setRight(LaminasViewModel $right)
    {
        $this->addChild($right, 'right');
    }

    public function setLeft(LaminasViewModel $left)
    {
        $this->addChild($left, 'left');
    }

    public function clearLeft()
    {
        $children = $this->getChildrenByCaptureTo('left');

        /** @var LaminasViewModel $child */
        foreach ($children as $child) {
            $child->setCaptureTo('void');
        }
    }

    public function setContentTitle($title)
    {
        $this->contentTitle = $title;
    }

    public function setPageTitle($title)
    {
        $this->pageTitle = $title;
    }

    public function setPageSubTitle($subTitle)
    {
        $this->pageSubTitle = $subTitle;
    }

    public function setIsAjax($boolean)
    {
        $this->template = 'layout/' . ($boolean ? 'ajax' : 'base');
    }
}
