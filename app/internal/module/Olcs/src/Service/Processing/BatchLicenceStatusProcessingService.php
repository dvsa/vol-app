<?php

/**
 * Batch process queued licence status changes
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
namespace Olcs\Service\Processing;

use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\Console\Adapter\AdapterInterface as ConsoleAdapter;
use Common\Service\Entity\LicenceEntityService;

/**
 * Batch process queued licence status changes
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class BatchLicenceStatusProcessingService implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * Console adapter to output info (if set)
     * @var \Zend\Console\Adapter\AdapterInterface
     */
    private $consoleAdapter;

    /**
     * Set the console adapter
     *
     * @param ConsoleAdapter $adapter
     */
    public function setConsoleAdapter(ConsoleAdapter $adapter)
    {
        $this->consoleAdapter = $adapter;
    }

    /**
     * Get the console adapter
     *
     * @return ConsoleAdapter
     */
    public function getConsoleAdapter()
    {
        return $this->consoleAdapter;
    }

    /**
     * Output a line to the console adapter
     *
     * @param string $text  Text to output
     * @param int    $color One of the \Zend\Console\ColorInterface constants
     *
     * @return void
     */
    private function outputLine($text, $color = null)
    {
        if ($this->getConsoleAdapter()) {
            $this->getConsoleAdapter()->writeLine($text, $color);
        }
    }

    /**
     * Process licence rule status changes to revoke, curtail and suspend
     *
     * @return void
     */
    public function processToRevokeCurtailSuspend()
    {
        $licenceStatusRuleService = $this->getServiceLocator()->get('Entity\LicenceStatusRule');
        $licenceService = $this->getServiceLocator()->get('Entity\Licence');
        $dateTime = $this->getServiceLocator()->get('Helper\Date')->getDate('Y-m-d H:i:s');

        $licencesToAction = $licenceStatusRuleService->getLicencesToRevokeCurtailSuspend();
        $this->outputLine(
            sprintf('%s %d rules to process', __FUNCTION__, count($licencesToAction)),
            \Zend\Console\ColorInterface::BLUE
        );
        foreach ($licencesToAction as $row) {
            $this->outputLine(sprintf('Actioning rule id %d', $row['id']), \Zend\Console\ColorInterface::LIGHT_CYAN);

            // if licence is not valid, then continue
            if ($row['licence']['status']['id'] !== LicenceEntityService::LICENCE_STATUS_VALID) {
                $this->outputLine(
                    sprintf(
                        'Licence id %d is not active, it is "%s"',
                        $row['licence']['id'],
                        $row['licence']['status']['description']
                    ),
                    \Zend\Console\ColorInterface::YELLOW
                );
                continue;
            }
            // update licence status
            $this->outputLine(
                sprintf('Updating licence %d to status %s', $row['licence']['id'], $row['licenceStatus']['id'])
            );
            $licenceService->forceUpdate($row['licence']['id'], ['status' => $row['licenceStatus']['id']]);

            // update rule start processed date
            $this->outputLine(
                sprintf('Updating licence rule %d to startProcessedDate %s', $row['id'], $dateTime)
            );
            $licenceStatusRuleService->forceUpdate($row['id'], ['startProcessedDate' => $dateTime]);
        }
    }

    /**
     * Process licence rule status back to valid
     *
     * @return void
     */
    public function processToValid()
    {
        $licenceStatusRuleService = $this->getServiceLocator()->get('Entity\LicenceStatusRule');
        $licenceService = $this->getServiceLocator()->get('Entity\Licence');
        $vehicleService = $this->getServiceLocator()->get('Entity\Vehicle');
        $dateTime = $this->getServiceLocator()->get('Helper\Date')->getDate('Y-m-d H:i:s');

        $licencesToAction = $licenceStatusRuleService->getLicencesToValid();
        $this->outputLine(
            sprintf('%s %d rules to process', __FUNCTION__, count($licencesToAction)),
            \Zend\Console\ColorInterface::BLUE
        );
        foreach ($licencesToAction as $row) {
            $this->outputLine(sprintf('Actioning rule id %d', $row['id']), \Zend\Console\ColorInterface::LIGHT_CYAN);

            // if licence is not curtailed or suspened then continue
            if (
                $row['licence']['status']['id'] !== LicenceEntityService::LICENCE_STATUS_CURTAILED &&
                $row['licence']['status']['id'] !== LicenceEntityService::LICENCE_STATUS_SUSPENDED
                ) {
                $this->outputLine(
                    sprintf(
                        'Licence id %d is not curtailed or suspended, it is "%s"',
                        $row['licence']['id'],
                        $row['licence']['status']['description']
                    ),
                    \Zend\Console\ColorInterface::YELLOW
                );
                continue;
            }

            // update section26 on licenced vehicles
            foreach ($row['licence']['licenceVehicles'] as $licencedVehicle) {
                // check vehicle relationship is there (it is missing in test data)
                if ($licencedVehicle['vehicle']) {
                    $this->outputLine(
                        sprintf('Updating vehicle %d set section26 = 0', $licencedVehicle['vehicle']['id'])
                    );
                    $vehicleService->forceUpdate($licencedVehicle['vehicle']['id'], ['section26' => 0]);
                }
            }

            // update licence status
            $this->outputLine(
                sprintf(
                    'Updating licence %d to status %s',
                    $row['licence']['id'],
                    LicenceEntityService::LICENCE_STATUS_VALID
                )
            );
            $licenceService->forceUpdate(
                $row['licence']['id'],
                ['status' => LicenceEntityService::LICENCE_STATUS_VALID]
            );

            // update rule start processed date
            $this->outputLine(
                sprintf('Updating licence rule %d to endProcessedDate %s', $row['id'], $dateTime)
            );
            $licenceStatusRuleService->forceUpdate($row['id'], ['endProcessedDate' => $dateTime]);
        }
    }
}
