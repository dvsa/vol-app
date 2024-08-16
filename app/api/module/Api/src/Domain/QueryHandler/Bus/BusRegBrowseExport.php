<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Bus;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\BusRegBrowseView;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Laminas\Http\Response;

/**
 * BusRegBrowseExport
 */
class BusRegBrowseExport extends AbstractQueryHandler
{
    protected $repoServiceName = 'BusRegBrowseView';

    private $columnsToExport = [
        'trafficAreaId' => 'Traffic area id',
        'trafficAreaName' => 'Traffic area',
        'name' => 'Organisation name',
        'address' => 'Address',
        'licNo' => 'Licence number',
        'licStatus' => 'Licence status',
        'regNo' => 'Reg No',
        'brStatus' => 'Bus Reg status',
        'busServiceType' => 'Bus service type',
        'variationNo' => 'Variation no',
        'receivedDate' => 'Received date',
        'effectiveDate' => 'Effective date',
        'endDate' => 'End date',
        'serviceNo' => 'Service no',
        'startPoint' => 'Start point',
        'finishPoint' => 'Finish point',
        'via' => 'Via',
        'otherDetails' => 'Other details',
        'acceptedDate' => 'Accepted date',
        'eventDescription' => 'Event description',
        'eventRegistrationStatus' => 'Event registration status',
    ];

    /**
     * Export
     *
     * @param QueryInterface $query Query
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var BusRegBrowseView $repo */
        $repo = $this->getRepo();

        $acceptedDate = $query->getAcceptedDate();

        $iterableResult = $repo->fetchForExport(
            array_keys($this->columnsToExport),
            $acceptedDate,
            $query->getTrafficAreas(),
            $query->getStatus()
        );

        $fp = fopen('php://temp', 'w+');

        // add column names
        fputcsv($fp, $this->columnsToExport);

        $hasData = false;
        while (false !== ($row = $iterableResult->next())) {
            fputcsv($fp, current($row));
            $hasData = true;
        }

        if (!$hasData) {
            throw new NotFoundException();
        }

        rewind($fp);
        $fstats = fstat($fp);

        $size = $fstats['size'];

        $filename = 'Bus_registration_decisions_' . $acceptedDate . '.csv';

        $response = new \Laminas\Http\Response\Stream();
        $response->setStatusCode(Response::STATUS_CODE_200);

        $response->setStream($fp);
        $response->setStreamName($filename);

        $headers = $response->getHeaders();
        $headers->addHeaders(
            [
                'Content-Type' => 'text/csv',
                'Content-Length' => $size,
                'Content-Disposition: attachment; filename="' . $filename . '"'
            ]
        );

        return $response;
    }
}
