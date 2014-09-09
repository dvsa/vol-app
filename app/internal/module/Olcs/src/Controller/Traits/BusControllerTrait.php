<?php

/**
 * Bus Controller Trait
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Traits;

/**
 * Bus Controller Trait
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
trait BusControllerTrait
{
    public function getNavigation()
    {
        return $this->getServiceLocator()->get('Navigation');
    }

    public function getSubNavigation()
    {
        return $this->getNavigation()->findOneBy('id', $this->getSubNavRoute());
    }

    public function getSubNavRoute()
    {
        return $this->subNavRoute;
    }

    public function getSection()
    {
        return $this->section;
    }

    public function getLayoutFile()
    {
        return $this->layoutFile;
    }
}
