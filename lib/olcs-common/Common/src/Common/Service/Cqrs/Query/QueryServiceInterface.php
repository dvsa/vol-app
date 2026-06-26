<?php

namespace Common\Service\Cqrs\Query;

use Common\Service\Cqrs\Response;
use Dvsa\Olcs\Transfer\Query\QueryContainerInterface;

/**
 * QueryServiceInterface
 */
interface QueryServiceInterface
{
    /**
     * Send a query and return the response
     *
     * @param QueryContainerInterface $query query
     *
     * @return Response
     */
    public function send(QueryContainerInterface $query);

    /**
     * Set RecoverHttpClientException
     *
     * @param bool $value value
     *
     * @return void
     */
    public function setRecoverHttpClientException($value);

    /**
     * Get RecoverHttpClientException
     *
     * @return bool
     */
    public function getRecoverHttpClientException();
}
