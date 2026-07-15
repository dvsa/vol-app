<?php

namespace Common\Controller\Lva\Traits;

use Common\Controller\Lva\AbstractSafetyController;
use Dvsa\Olcs\Transfer\Command\Application\DeleteWorkshop;
use Dvsa\Olcs\Transfer\Command\Application\UpdateSafety;
use Dvsa\Olcs\Transfer\Query\Application\Safety;

/**
 * Application Safety Controller Trait
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait ApplicationSafetyControllerTrait
{
    /**
     * Save
     *
     * @param array $data    Form Data
     * @param bool  $partial Is partial post
     *
     * @return \Common\Service\Cqrs\Response
     * @inheritdoc
     */
    protected function save($data, $partial)
    {
        $dtoData = $data['application'];
        $dtoData['id'] = $this->getApplicationId();
        $dtoData['partial'] = $partial;
        $dtoData['licence'] = $data['licence'];
        $dtoData['licence']['id'] = $this->getLicenceId();

        return $this->handleCommand(UpdateSafety::create($dtoData));
    }

    /**
     * Delete selected workshops
     *
     * @param array $ids Identifiers
     *
     * @return \Common\Service\Cqrs\Response
     * @inheritdoc
     */
    protected function deleteWorkshops($ids)
    {
        $data = [
            'application' => $this->getApplicationId(),
            'ids' => $ids
        ];

        return $this->handleCommand(DeleteWorkshop::create($data));
    }

    /**
     * Get Safety Data
     *
     * @param bool $noCache No Cache
     *
     * @return array
     */
    protected function getSafetyData($noCache = false)
    {
        if (is_null($this->safetyData) || $noCache) {
            $request = $this->getRequest();
            $query = $request->isPost() ? $request->getPost('query') : $request->getQuery();
            $params = [
                'id' => $this->getApplicationId()
            ];
            $params['page'] = $query['page'] ?? 1;

            $params['limit'] = $query['limit'] ?? AbstractSafetyController::DEFAULT_TABLE_RECORDS_COUNT;

            $response = $this->handleQuery(Safety::create($params));

            if (!$response->isOk()) {
                return $this->notFoundAction();
            }

            $application = $response->getResult();
            $this->safetyData = $application;

            $this->canHaveTrailers = $application['canHaveTrailers'];
            $this->isShowTrailers = $application['isShowTrailers'];
            $this->workshops = $application['workshops'];
        }

        return $this->safetyData;
    }
}
