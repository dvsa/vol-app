<?php

namespace Common\Service\Data;

use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Cqrs\Query\CachingQueryService;
use Common\Service\Cqrs\Response;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;

/**
 * Abstract data service class
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
abstract class AbstractDataService
{
    /**
     * @var array
     */
    protected $data = [];

    /** @var AnnotationBuilder */
    protected $transferAnnotationBuilder;

    /** @var CachingQueryService */
    protected $queryService;

    /** @var CommandService */
    protected $commandService;

    public function __construct(AbstractDataServiceServices $abstractDataServiceServices)
    {
        $this->transferAnnotationBuilder = $abstractDataServiceServices->getTransferAnnotationBuilder();
        $this->queryService = $abstractDataServiceServices->getQueryService();
        $this->commandService = $abstractDataServiceServices->getCommandService();
    }

    /**
     * Handle query
     *
     * @param string $dtoData Query dto
     *
     * @return Response
     */
    protected function handleQuery($dtoData)
    {
        $query = $this->transferAnnotationBuilder->createQuery($dtoData);

        return $this->queryService->send($query);
    }

    /**
     * Handle command
     *
     * @param string $dtoData Command dto
     *
     * @return Response
     */
    protected function handleCommand($dtoData)
    {
        $command = $this->transferAnnotationBuilder->createCommand($dtoData);

        return $this->commandService->send($command);
    }

    /**
     * Format result
     *
     * @param array $result Result
     *
     * @return array
     */
    protected function formatResult($result)
    {
        // For backwards compatibility we need to return result with keys starting with uppercase letters
        return [
            'Results' => $result['results'],
            'Count' => $result['count']
        ];
    }

    /**
     * Set data
     *
     * @param string $key  Key
     * @param mixed  $data Data
     *
     * @return $this
     */
    public function setData($key, mixed $data)
    {
        $this->data[$key] = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @param string $key Key
     *
     * @return mixed|null
     */
    public function getData($key)
    {
        return $this->data[$key] ?? null;
    }
}
