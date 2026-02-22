<?php

namespace Dvsa\Olcs\Api\Controller;

use Doctrine\ORM\OptimisticLockException;
use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Dvsa\Olcs\Api\Domain\Exception;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Dvsa\Olcs\Api\Domain\QueryHandlerManager;
use Olcs\Logging\Log\Logger;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractRestfulController;

/**
 * Generic Controller
 * @method \Dvsa\Olcs\Api\Mvc\Controller\Plugin\Response response()
 */
class GenericController extends AbstractRestfulController
{
    public function __construct(private readonly QueryHandlerManager $queryHandlerManager, private readonly CommandHandlerManager $commandHandlerManager)
    {
    }

    /**
     * Get data by passed Query Fqcl
     *
     * @param int $id Identifier (ignorred)
     *
     * @return Response|Response\Stream|\Laminas\View\Model\JsonModel
     */
    #[\Override]
    public function get($id)
    {
        $dto = $this->params('dto');

        try {
            $result = $this->handleQuery($dto);
            return $this->response()->singleResult($result);
        } catch (Exception\NotFoundException) {
            return $this->response()->notFound();
        } catch (Exception\NotReadyException $ex) {
            return $this->response()->notReady($ex->getRetryAfter());
        } catch (Exception\RestResponseException $ex) {
            return $this->response()->error($ex->getCode(), $ex->getMessages());
        } catch (Exception\ForbiddenException $ex) {
            return $this->response()->error(403, $ex->getMessages());
        } catch (Exception\Exception $ex) {
            return $this->response()->error(400, $ex->getMessages());
        } catch (\Exception $ex) {
            Logger::logException($ex, \Laminas\Log\Logger::ERR);
            return $this->response()->error(500, [$ex->getMessage()]);
        }
    }

    /**
     * Get list of data by passed Query Fqcl
     *
     * @return Response|\Laminas\View\Model\JsonModel
     */
    #[\Override]
    public function getList()
    {
        $dto = $this->params('dto');

        try {
            $result = $this->handleQuery($dto);

            if ($result instanceof Response\Stream) {
                return $this->response()->streamResult($result);
            } elseif ($result instanceof Result || !isset($result['result'])) {
                // we sometimes still get a single result if we're not retrieving by id
                return $this->response()->singleResult($result);
            }

            $count = $result['count'] ?? null;
            $results = $result['result'];
            $countUnfiltered = $result['count-unfiltered'] ?? $count;
            unset($result['count'], $result['result'], $result['count-unfiltered']);

            return $this->response()->multipleResults($count, $results, $countUnfiltered, $result);
        } catch (Exception\NotFoundException) {
            return $this->response()->notFound();
        } catch (Exception\NotReadyException $ex) {
            return $this->response()->notReady($ex->getRetryAfter());
        } catch (Exception\ForbiddenException $ex) {
            return $this->response()->error(403, $ex->getMessages());
        } catch (Exception\Exception $ex) {
            return $this->response()->error(400, $ex->getMessages());
        } catch (\Exception $ex) {
            Logger::logException($ex, \Laminas\Log\Logger::ERR);
            return $this->response()->error(500, [$ex->getMessage()]);
        }
    }

    /**
     * Update by passed Command Fqcl
     *
     * @param mixed $id   Ignored
     * @param mixed $data Ignored
     *
     * @inheritdoc
     * @return Response|\Laminas\View\Model\JsonModel
     */
    #[\Override]
    public function update($id, $data)
    {
        $dto = $this->params('dto');

        try {
            $result = $this->handleCommand($dto);
            return $this->response()->successfulUpdate($result);
        } catch (OptimisticLockException $ex) {
            return $this->response()->error(409, [$ex->getMessage()]);
        } catch (Exception\VersionConflictException $ex) {
            return $this->response()->error(409, $ex->getMessages());
        } catch (Exception\NotFoundException) {
            return $this->response()->notFound();
        } catch (Exception\RestResponseException $ex) {
            return $this->response()->error($ex->getCode(), $ex->getMessages());
        } catch (Exception\ForbiddenException $ex) {
            return $this->response()->error(403, $ex->getMessages());
        } catch (Exception\Exception $ex) {
            return $this->response()->error(400, $ex->getMessages());
        } catch (\Exception $ex) {
            Logger::logException($ex, \Laminas\Log\Logger::ERR);
            return $this->response()->error(500, [$ex->getMessage()]);
        }
    }

    /**
     * Replace an entire resource collection by passed Command Fqcl
     *
     * @param mixed $data Ignored
     *
     * @return Response|\Laminas\View\Model\JsonModel
     */
    #[\Override]
    public function replaceList($data)
    {
        $dto = $this->params('dto');

        try {
            $result = $this->handleCommand($dto);
            return $this->response()->successfulUpdate($result);
        } catch (Exception\VersionConflictException  $ex) {
            return $this->response()->error(409, $ex->getMessages());
        } catch (Exception\NotFoundException) {
            return $this->response()->notFound();
        } catch (Exception\Exception $ex) {
            return $this->response()->error(400, $ex->getMessages());
        } catch (\Exception $ex) {
            Logger::logException($ex, \Laminas\Log\Logger::ERR);
            return $this->response()->error(500, [$ex->getMessage()]);
        }
    }

    /**
     * Create a new resource by passed Command Fqcl
     *
     * @param mixed $data Ignored as we use DTO parameter
     *
     * @return Response|\Laminas\View\Model\JsonModel
     */
    #[\Override]
    public function create($data)
    {
        $dto = $this->params('dto');

        try {
            $result = $this->handleCommand($dto);
            return $this->response()->successfulCreate($result);
        } catch (Exception\RestResponseException $ex) {
            return $this->response()->error($ex->getCode(), $ex->getMessages());
        } catch (Exception\ForbiddenException $ex) {
            return $this->response()->error(403, $ex->getMessages());
        } catch (Exception\Exception $ex) {
            return $this->response()->error(400, $ex->getMessages());
        } catch (\Exception $ex) {
            Logger::logException($ex, \Laminas\Log\Logger::ERR);
            return $this->response()->error(500, [$ex->getMessage()]);
        }
    }

    /**
     * Delete a resource by passed Command Fqcl
     *
     * @param mixed $id Ignored
     *
     * @return Response|\Laminas\View\Model\JsonModel
     */
    #[\Override]
    public function delete($id)
    {
        $dto = $this->params('dto');

        try {
            $result = $this->handleCommand($dto);
            return $this->response()->successfulUpdate($result);
        } catch (Exception\NotFoundException) {
            return $this->response()->notFound();
        } catch (Exception\ForbiddenException $ex) {
            return $this->response()->error(403, $ex->getMessages());
        } catch (Exception\Exception $ex) {
            return $this->response()->error(400, $ex->getMessages());
        } catch (\Exception $ex) {
            Logger::logException($ex, \Laminas\Log\Logger::ERR);
            return $this->response()->error(500, [$ex->getMessage()]);
        }
    }

    /**
     * Delete a resources by passed Command Fqcl
     *
     * @param mixed $data Ignored
     *
     * @return Response|\Laminas\View\Model\JsonModel
     */
    #[\Override]
    public function deleteList($data = null)
    {
        $dto = $this->params('dto');

        try {
            $result = $this->handleCommand($dto);
            return $this->response()->successfulUpdate($result);
        } catch (Exception\NotFoundException) {
            return $this->response()->notFound();
        } catch (Exception\ForbiddenException $ex) {
            return $this->response()->error(403, $ex->getMessages());
        } catch (Exception\Exception $ex) {
            return $this->response()->error(400, $ex->getMessages());
        } catch (\Exception $ex) {
            Logger::logException($ex, \Laminas\Log\Logger::ERR);
            return $this->response()->error(500, [$ex->getMessage()]);
        }
    }

    /**
     * Execute Query
     *
     * @param string $dto Query Fqcl
     *
     * @return Result|array
     */
    protected function handleQuery($dto)
    {
        return $this->queryHandlerManager->handleQuery($dto);
    }

    /**
     * Execute Query
     *
     * @param string $dto Command Fqcl
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    protected function handleCommand($dto)
    {
        return $this->commandHandlerManager->handleCommand($dto);
    }
}
