<?php

/**
 * Request Cpms Report
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cpms;

use Dvsa\Olcs\Api\Domain\Command\Queue\Create as CreateQueueCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CpmsAwareInterface;
use Dvsa\Olcs\Api\Domain\CpmsAwareTrait;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Cpms\RequestReport as Cmd;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;

/**
 * Request Cpms Report
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class RequestReport extends AbstractCommandHandler implements CpmsAwareInterface
{
    use CpmsAwareTrait;

    /**
     * @param Cmd $command
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $start = new \DateTime($command->getStart());
        $end = new \DateTime($command->getEnd());
        $now = new DateTime('now');
        if ($end->format('Y-m-d') === $now->format('Y-m-d')) {
            $end->setTime($now->format('H'), $now->format('i'), $now->format('s'));
        } else {
            $end->setTime(23, 59, 59);
        }

        $data = $this->getCpmsService()->requestReport($command->getReportCode(), $start, $end);

        // if response code is not success then CPMS service rejected the request, return the message to client
        if ($data['code'] != \Dvsa\Olcs\Api\Service\CpmsHelperInterface::RESPONSE_SUCCESS) {
            throw new \Dvsa\Olcs\Api\Domain\Exception\BadRequestException($data['message']);
        }

        $queueCmd = CreateQueueCmd::create(
            [
                'type' => Queue::TYPE_CPMS_REPORT_DOWNLOAD,
                'status' => Queue::STATUS_QUEUED,
                'options' => json_encode(
                    [
                        'reference' => $data['reference'],
                        'name' => $command->getName()
                    ]
                ),
            ]
        );
        $result->merge($this->handleSideEffect($queueCmd));

        $result->addMessage('Report requested');
        $result->addId('cpmsReport', $data['reference']);

        return $result;
    }
}
