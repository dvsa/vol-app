<?php

/**
 * Cpms Report Status
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Cpms;

use Dvsa\Olcs\Api\Domain\CpmsAwareInterface;
use Dvsa\Olcs\Api\Domain\CpmsAwareTrait;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Exception\NotReadyException;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;

/**
 * Cpms Report Status
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ReportStatus extends AbstractQueryHandler implements CpmsAwareInterface
{
    use CpmsAwareTrait;

    public function handleQuery(QueryInterface $query)
    {
        $data = $this->getCpmsService()->getReportStatus($query->getReference());

        if (empty($data)) {
            throw new NotFoundException();
        }

        if (isset($data['completed']) && $data['completed']) {
            return [
                // we only really need to return token and extension, we
                // already know what the download url will be based on the reference
                'completed' => $data['completed'],
                'token' => $this->parseToken($data),
                'extension' => $data['file_extension'] ?? null,
            ];
        }

        // If report is not ready throw a NotReadyException which results in a 503 response
        $ex = new NotReadyException("Report is not ready yet");
        $ex->setRetryAfter(60); // seconds
        throw $ex;
    }

    /**
     * @param array $data
     * @return string
     */
    private function parseToken($data)
    {
        $downloadUrl = $data['download_url'];
        $queryString = parse_url((string) $downloadUrl, PHP_URL_QUERY);
        $vars = [];
        parse_str($queryString, $vars);
        return $vars['token'];
    }
}
