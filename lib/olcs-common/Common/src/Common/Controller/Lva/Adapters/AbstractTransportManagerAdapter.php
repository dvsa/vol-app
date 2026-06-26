<?php

namespace Common\Controller\Lva\Adapters;

use Common\Controller\Lva\Interfaces\TransportManagerAdapterInterface;
use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Cqrs\Query\CachingQueryService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder as TransferAnnotationBuilder;
use Psr\Container\ContainerInterface;

abstract class AbstractTransportManagerAdapter extends AbstractControllerAwareAdapter implements
    TransportManagerAdapterInterface
{
    public const SORT_LAST_FIRST_NAME = 1;

    //  sort: Last, First Name, 'A' action (ASC)
    public const SORT_LAST_FIRST_NAME_NEW_AT_END = 2;

    /** @var TransferAnnotationBuilder */
    protected $transferAnnotationBuilder;

    /** @var CachingQueryService */
    protected $querySrv;

    /** @var CommandService */
    protected $commandSrv;

    protected $tableSortMethod = self::SORT_LAST_FIRST_NAME;

    /**
     * AbstractTransportManagerAdapter constructor.
     *
     * @param TransferAnnotationBuilder $transferAnnotationBuilder annotation builder
     * @param CachingQueryService       $querySrv                  caching query service
     * @param CommandService            $commandSrv                command service
     * @param ContainerInterface        $container                 container
     *
     * @return void
     */
    public function __construct(
        TransferAnnotationBuilder $transferAnnotationBuilder,
        CachingQueryService $querySrv,
        CommandService $commandSrv,
        ContainerInterface $container
    ) {
        $this->transferAnnotationBuilder = $transferAnnotationBuilder;
        $this->querySrv = $querySrv;
        $this->commandSrv = $commandSrv;
        parent::__construct($container);
    }

    /**
     * Get the table
     *
     * @param string $template table being prepared
     *
     * @return \Common\Service\Table\TableBuilder
     */
    #[\Override]
    public function getTable($template = 'lva-transport-manangers')
    {
        return $this->container->get(TableFactory::class)->prepareTable($template);
    }

    /**
     * Is this licence required to have at least one Transport Manager
     *
     * @return boolean
     */
    #[\Override]
    public function mustHaveAtLeastOneTm()
    {
        return false;
    }

    /**
     * Add any messages to the page
     *
     * @param int $licenceId licence id
     *
     * @return void
     */
    #[\Override]
    public function addMessages($licenceId)
    {
    }

    /**
     * Map array data from the Backend into arrays for CRUD tables
     *
     * @param array $applicationTms array of Transport Manager Applications
     * @param array $licenceTms     array of Transport Manager Licences
     *
     * @return array
     */
    protected function mapResultForTable(array $applicationTms, array $licenceTms = [])
    {
        $mappedData = [];

        // add each TM from the licence
        foreach ($licenceTms as $tml) {
            $id = $tml['tmid'];
            $mappedData[$id] = [
                // Transport Manager Licence ID
                'id' => 'L' . $tml['id'],
                'name' => [
                    'forename' => $tml['forename'],
                    'familyName' => $tml['familyName'],
                ],
                'status' => null,
                'email' => $tml['emailAddress'],
                'dob' => $tml['birthDate'],
                'transportManager' => [
                    'id' => $id
                ],
                'action' => 'E',
            ];
        }

        // add each TM from the application/variation
        foreach ($applicationTms as $tma) {
            $id = $tma['tmid'];
            $mappedData[$id . 'a'] = [
                'id' => $tma['id'],
                'name' => [
                    'familyName' => $tma['familyName'],
                    'forename' => $tma['forename']
                ],
                'status' => [
                    'id' => $tma['tmasid'],
                    'description' => $tma['tmasdesc']
                ],
                'email' => $tma['emailAddress'],
                'dob' => $tma['birthDate'],
                'action' => $tma['action'],
                'transportManager' => [
                    'id' => $id
                ]
            ];
            // update the licence TM's if they have been updated
            switch ($tma['action']) {
                case 'U':
                    // Mark original as the current
                    $mappedData[$id]['action'] = 'C';
                    break;
                case 'D':
                    // Remove the original so that just the Delete version appears
                    unset($mappedData[$id]);
                    break;
            }
        }

        return $this->sortResultForTable($mappedData, $this->tableSortMethod);
    }

    /**
     * Method sort a table data with specified method
     *
     * @param array $data   Array of Transport Managers
     * @param null  $method Sort method
     *
     * @return array
     */
    protected function sortResultForTable(array $data, $method = null)
    {
        if ($method === self::SORT_LAST_FIRST_NAME) {
            usort($data, fn(array $a, array $b): int => $this::sortCmpByName($a, $b));

            return $data;
        }

        if ($method === self::SORT_LAST_FIRST_NAME_NEW_AT_END) {
            usort($data, fn(array $a, array $b): int => $this::sortCmpByNameAndNewAtEnd($a, $b));

            return $data;
        }

        return $data;
    }

    /**
     * Comparison function for sorting a table by Last and First name (ASC)
     *
     * @param array $a first entry to sort
     * @param array $b second entry to sort
     *
     * @return int
     */
    private function sortCmpByName($a, $b)
    {
        $keyA = strtolower($a['name']['familyName'] . $a['name']['forename']);
        $keyB = strtolower($b['name']['familyName'] . $b['name']['forename']);

        return strnatcmp($keyA, $keyB);
    }

    /**
     * Comparison function to provider sort:
     *  - new items (action ='A') located at the end;
     *  - all item sorted by Last and First name (ASC),
     *
     * @param array $a first entry to sort
     * @param array $b second entry to sort
     *
     * @return int
     */
    private function sortCmpByNameAndNewAtEnd($a, $b)
    {
        $isNewA = (int)($a['action'] === 'A');
        $isNewB = (int)($b['action'] === 'A');

        if ($isNewA !== $isNewB) {
            return ($isNewA < $isNewB ? -1 : 1);
        }

        return $this->sortCmpByName($a, $b);
    }

    /**
     * get number of rows in the data
     *
     * @param int $applicationId application id
     * @param int $licenceId     licence id
     *
     * @return int
     */
    #[\Override]
    public function getNumberOfRows($applicationId, $licenceId)
    {
        return count($this->getTableData($applicationId, $licenceId));
    }
}
