<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Document;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Service;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class PrintLetter extends AbstractQueryHandler
{
    protected $repoServiceName = 'Document';

    /**
     * Handle query
     *
     * @param \Dvsa\Olcs\Transfer\Query\Document\PrintLetter $query Query
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var Entity\Doc\Document $doc */
        $doc = $this->getRepo()->fetchUsingId($query);

        /** @var Service\Document\PrintLetter $srv */
        $srv = $this->getContainer()->get(Service\Document\PrintLetter::class);

        return [
            'flags' => [
                TransferCmd\Document\PrintLetter::METHOD_EMAIL => $srv->canEmail($doc),
                TransferCmd\Document\PrintLetter::METHOD_PRINT_AND_POST => $srv->canPrint($doc),
            ],
        ];
    }
}
