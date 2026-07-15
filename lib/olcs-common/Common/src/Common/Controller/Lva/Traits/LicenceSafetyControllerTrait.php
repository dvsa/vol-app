<?php

namespace Common\Controller\Lva\Traits;

use Common\Controller\Lva\AbstractSafetyController;
use Dvsa\Olcs\Transfer\Command\Licence\UpdateSafety;
use Dvsa\Olcs\Transfer\Command\Workshop\DeleteWorkshop;
use Dvsa\Olcs\Transfer\Query\Licence\Safety;
use Laminas\Form\Form;

/**
 * Licence Safety Controller Trait
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait LicenceSafetyControllerTrait
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
        $dtoData = $data['licence'];
        $dtoData['id'] = $this->getLicenceId();

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
            'ids' => $ids,
            'licence' => $this->getIdentifier()
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
                'id' => $this->getLicenceId()
            ];
            $params['page'] = $query['page'] ?? 1;

            $params['limit'] = $query['limit'] ?? AbstractSafetyController::DEFAULT_TABLE_RECORDS_COUNT;

            $response = $this->handleQuery(Safety::create($params));

            if (!$response->isOk()) {
                return $this->notFoundAction();
            }

            $licence = $response->getResult();

            $this->canHaveTrailers = $licence['canHaveTrailers'];
            $this->isShowTrailers = $licence['isShowTrailers'];
            $this->workshops = $licence['workshops'];

            $this->safetyData = [
                'version' => null,
                'safetyConfirmation' => null,
                'isMaintenanceSuitable' => $licence['isMaintenanceSuitable'],
                'licence' => $licence,
                'safetyDocuments' => $licence['safetyDocuments']
            ];
        }

        return $this->safetyData;
    }

    /**
     * Alter the form depending on the LVA type
     *
     * @param \Laminas\Form\FormInterface $form Form
     * @param array                    $data Api/Form data
     *
     * @return void
     */
    protected function alterFormForLva(Form $form, $data = null)
    {
        $formHelper = $this->formHelper;
        $formHelper->remove($form, 'application->safetyConfirmation');
    }
}
