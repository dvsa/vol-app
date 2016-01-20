<?php

/**
 * Bus Controller Trait
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Traits;

use Dvsa\Olcs\Transfer\Query\Bus\BusReg as BusRegDto;

/**
 * Bus Controller Trait
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
trait BusControllerTrait
{
    /**
     * bus reg new / variation and cancellation statuses
     *
     * @var array
     */
    protected $newVariationCancellationStatuses = [
        'breg_s_new',
        'breg_s_var',
        'breg_s_cancellation'
    ];

    /**
     * bus reg rejected statuses
     *
     * @var array
     */
    protected $rejectedStatuses = [
        'breg_s_admin',
        'breg_s_refused',
        'breg_s_cancelled',
        'breg_s_withdrawn'
    ];

    /**
     * Memoize Bus Reg details to prevent multiple backend calls with same id
     * @var array
     */
    protected $busRegDetailsCache = [];

    /**
     * returns array of new / variation / cancellation statuses
     *
     * @return array
     */
    public function getNewVariationCancellationStatuses()
    {
        return $this->newVariationCancellationStatuses;
    }

    /**
     * returns array of rejected statuses
     *
     * @return array
     */
    public function getRejectedStatuses()
    {
        return $this->rejectedStatuses;
    }

    /**
     * Gets the Bus Registration record
     *
     * @param int $id
     * @return array
     */
    public function getBusReg($id = null)
    {
        if (is_null($id)) {
            $id = $this->params()->fromRoute('busRegId');
        }

        if (isset($busRegDetailsCache[$id])) {
            return $busRegDetailsCache[$id];
        }

        $dto = new BusRegDto();
        $dto->exchangeArray(['id' => $id]);
        $response = $this->handleQuery($dto);

        if ($response->isNotFound()) {
            return $this->notFoundAction();
        }

        if ($response->isClientError() || $response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
            //this probably should end up on a different page...
        }

        if ($response->isOk()) {
            $result = $response->getResult();
            $busRegDetailsCache[$result['id']] = $result;
            return $result;
        }
    }

    /**
     * @param int $id
     * @return bool
     */
    public function isFromEbsr($id = null)
    {
        $busReg = $this->getBusReg($id);
        return (isset($busReg['isTxcApp']) && $busReg['isTxcApp'] == 'Y');
    }

    /**
     * @param int $id
     * @return bool
     */
    public function isLatestVariation($id = null)
    {
        $busReg = $this->getBusReg($id);
        return $busReg['isLatestVariation'];
    }

    /**
     * Gets the main navigation
     *
     * @return \Zend\Navigation\Navigation
     */
    public function getNavigation()
    {
        return $this->getServiceLocator()->get('Navigation');
    }

    /**
     * Gets the sub navigation
     *
     * @return \Zend\Navigation\Page\Mvc
     */
    public function getSubNavigation()
    {
        return $this->getNavigation()->findOneBy('id', $this->getSubNavRoute());
    }

    /**
     * Returns the sub nav route
     *
     * @return string
     */
    public function getSubNavRoute()
    {
        return $this->subNavRoute;
    }

    /**
     * Returns the section
     *
     * @return string
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * Returns the menu item
     *
     * @return string
     */
    public function getItem()
    {
        return $this->item;
    }
}
