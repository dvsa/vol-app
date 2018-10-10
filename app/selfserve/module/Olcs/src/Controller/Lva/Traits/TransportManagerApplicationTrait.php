<?php

namespace Olcs\Controller\Lva\Traits;

use Dvsa\Olcs\Transfer\Query\TransportManagerApplication\GetDetails;
use Zend\Mvc\MvcEvent;
use Dvsa\Olcs\Transfer\Command;
use Common\RefData;

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

    public function preDispatch()
    {
        return $this->redirectIfSectionNotCorrect();
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
     * @return string
     */
    protected function returnApplicationOrVariation(): string
    {
        if ($this->tma["application"]["isVariation"]) {
            return self::LVA_VAR;
        }
        return self::LVA_APP;
    }

    /**
     * Update TMA status
     *
     * @param int    $tmaId     TM application id
     * @param string $newStatus New status
     * @param int    $version   Version
     *
     * @return bool
     */
    protected function updateTmaStatus($tmaId, $newStatus, $version = null)
    {
        $command = $this->getServiceLocator()->get('TransferAnnotationBuilder')
            ->createCommand(
                Command\TransportManagerApplication\UpdateStatus::create(
                    ['id' => $tmaId, 'status' => $newStatus, 'version' => $version]
                )
            );
        /* @var $response \Common\Service\Cqrs\Response */
        $response = $this->getServiceLocator()->get('CommandService')->send($command);

        return $response->isOk();
    }

    /**
     * Redirect if user tries to access un-permitted route
     *
     * @return mixed
     */
    private function redirectIfSectionNotCorrect()
    {
        if (!$this->isUserPermitted($this->tma)) {
            if ($this->isGranted(RefData::PERMISSION_SELFSERVE_TM_DASHBOARD) &&
                !$this->isGranted(RefData::PERMISSION_SELFSERVE_LVA)) {
                return $this->redirect()->toRoute('dashboard');
            } else {
                return $this->redirect()->toRoute(
                    "lva-{$this->lva}/transport_managers",
                    ['application' => $this->tma["application"]["id"]]
                );
            }
        }
    }
}
