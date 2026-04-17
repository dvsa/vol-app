<?php

namespace Dvsa\Olcs\Db\Controller;

use Dvsa\Olcs\Db\Exceptions\SearchDateFilterParseException;
use Laminas\Http\PhpEnvironment\Response;
use Dvsa\Olcs\Db\Service\Search\Search;

/**
 * Class SearchController
 *
 * @package Olcs\Db\Controller
 */
class SearchController extends AbstractController
{
    public function __construct(private readonly Search $elasticSearchService)
    {
    }
    /**
     * Get list from search
     *
     * @return \Laminas\Http\Response $response http response
     */
    #[\Override]
    public function getList()
    {
        $params = array_merge((array)$this->params()->fromRoute(), (array)$this->params()->fromQuery());

        $indices = explode('|', (string) $params['index']);

        if (isset($params['filters']) && !empty($params['filters']) && is_array($params['filters'])) {
            $this->elasticSearchService->setFilters($params['filters'], $params['filterTypes'] ?? []);
        }
        if (!empty($params['dateRanges']) && is_array($params['dateRanges'])) {
            try {
                $this->elasticSearchService->setDateRanges($params['dateRanges']);
            } catch (SearchDateFilterParseException $dateException) {
                return $this->respond(
                    Response::STATUS_CODE_500,
                    'invalid date filter criteria',
                    ['error' => $dateException->getDateField()]
                );
            }
        }

        if (!empty($params['sort'])) {
            $this->elasticSearchService->setSort($params['sort']);
        }

        if (!empty($params['order'])) {
            $this->elasticSearchService->setOrder($params['order']);
        }

        $resultSet = $this->elasticSearchService->search(
            $params['q'],
            $indices,
            $params['page'],
            $params['limit']
        );

        return $this->respond(Response::STATUS_CODE_200, 'Results found', $resultSet);
    }
}
