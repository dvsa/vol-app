<?php

/**
 * Cpms Report Download Queue Consumer
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Cli\Service\Queue\Consumer\Cpms;

use Dvsa\Olcs\Api\Domain\Exception\Exception as DomainException;
use Dvsa\Olcs\Api\Domain\Exception\NotReadyException;
use Dvsa\Olcs\Api\Domain\QueryHandlerManager;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Cli\Service\Queue\Consumer\AbstractConsumer;
use Dvsa\Olcs\Cli\Service\Queue\Consumer\AbstractConsumerServices;
use Dvsa\Olcs\Transfer\Command\Cpms\DownloadReport as DownloadReportCmd;
use Dvsa\Olcs\Transfer\Query\Cpms\ReportStatus as ReportStatusQry;

/**
 * Cpms Report Download Queue Consumer
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ReportDownload extends AbstractConsumer
{
    public const MAX_ATTEMPTS = 10;

    /** @var QueryHandlerManager */
    protected $queryHandlerManager;

    /**
     * Create service instance
     *
     * @param AbstractConsumerServices $abstractConsumerServices
     * @param QueryHandlerManager $queryHandlerManager
     *
     * @return ReportDownload
     */
    public function __construct(
        AbstractConsumerServices $abstractConsumerServices,
        QueryHandlerManager $queryHandlerManager
    ) {
        parent::__construct($abstractConsumerServices);
        $this->queryHandlerManager = $queryHandlerManager;
    }

    /**
     * Process the message item
     *
     * @param QueueEntity $item queue item
     *
     * @return string
     */
    public function processMessage(QueueEntity $item)
    {
        if ($item->getAttempts() > self::MAX_ATTEMPTS) {
            return $this->failed($item, QueueEntity::ERR_MAX_ATTEMPTS);
        }

        $options = (array) json_decode($item->getOptions());
        $reference = $options['reference'];

        $query = ReportStatusQry::create(['reference' => $reference]);

        try {
            $result = $this->queryHandlerManager->handleQuery($query);
        } catch (\Exception $e) {
            return $this->handleException($e, $item);
        }

        $msg = vsprintf(
            'Download using reference %s and token %s and extension %s',
            [$reference, $result['token'], $result['extension']]
        );

        $extension = $result['extension'] ? ('.' . $result['extension']) : '';
        $filename = $options['name'] . $extension;
        $command = DownloadReportCmd::create(
            [
                'reference' => $reference,
                'token'     => $result['token'],
                'filename'  => $filename,
                'user'      => $item->getCreatedBy()->getId()
            ]
        );
        try {
            $downloadResult = $this->handleCommand($command);
        } catch (\Exception $e) {
            return $this->handleException($e, $item);
        }

        $messages = array_merge([$msg], $downloadResult->getMessages());

        return $this->success($item, implode('|', $messages));
    }

    /**
     * handle exception
     *
     * @param \Exception  $e    the exception
     * @param QueueEntity $item queue item
     *
     * @return string
     */
    protected function handleException(\Exception $e, QueueEntity $item)
    {
        if ($e instanceof NotReadyException) {
            return $this->retry($item, $e->getRetryAfter(), $e->getMessage());
        }

        if ($e instanceof DomainException) {
            $message = !empty($e->getMessages()) ? implode(', ', $e->getMessages()) : $e->getMessage();
            return $this->failed($item, $message);
        }

        return $this->failed($item, $e->getMessage());
    }
}
