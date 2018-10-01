<?php

namespace Olcs\Controller\Lva\Traits;

use Dvsa\Olcs\Transfer\Query\TransportManagerApplication\GetDetails;
use Zend\Mvc\MvcEvent;

trait TransportManagerApplicationTrait
{
    protected $tma;

    public function onDispatch(MvcEvent $e)
    {
        $tmaId = (int)$this->params('child_id');
        $this->tma = $this->getTransportManagerApplication($tmaId);
        $this->lva = $this->returnApplicationOrVariation();
        parent::onDispatch($e);
    }

    /**
     * getTransportManagerApplication
     *
     * @param int $transportManagerApplicationId
     *
     * @return array|mixed
     */
    protected function getTransportManagerApplication($transportManagerApplicationId): array
    {
        $transportManagerApplication = $this->handleQuery(
            GetDetails::create(['id' => $transportManagerApplicationId])
        )->getResult();
        return $transportManagerApplication;
    }

    /**
     * getTmName
     *
     * @param array $transportManagerApplication
     *
     * @return string
     */
    protected function getTmName()
    {
        return trim(
            $this->tma['transportManager']['homeCd']['person']['forename'] . ' '
            . $this->tma['transportManager']['homeCd']['person']['familyName']
        );
    }

    /**
     * Returns "application" or "variation"
     *
     * @param array $tma
     *
     * @return string
     */
    protected function returnApplicationOrVariation(): string
    {
        if ($this->tma["application"]["isVariation"]) {
            return self::LVA_VAR;
        }
        return self::LVA_APP;
    }
}
