<?php

/**
 * Licence Controller Trait
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller\Traits;

use Common\RefData;

/**
 * Licence Controller Trait
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait LicenceControllerTrait
{
    /**
     * Get view with licence
     *
     * @param  array $variables
     * @return \Laminas\View\Model\ViewModel
     */
    protected function getViewWithLicence($variables = [])
    {
        $licence = $this->getLicence();
        if ($licence['goodsOrPsv']['id'] == RefData::LICENCE_CATEGORY_GOODS_VEHICLE) {
            $this->navigation->findOneBy('id', 'licence_bus')->setVisible(0);
        }

        $variables['licence'] = $licence;

        return $this->getView($variables);
    }

    /**
     * Gets the licence by ID.
     *
     * @param  integer $id
     * @return array
     */
    protected function getLicence($id = null)
    {
        if (is_null($id)) {
            $id = $this->params()->fromRoute('licence');
        }

        /* @var $response \Common\Service\Cqrs\Response */
        $response = $this->handleQuery(
            \Dvsa\Olcs\Transfer\Query\Licence\Licence::create(['id' => $id])
        );

        if (!$response->isOk()) {
            throw new \RuntimeException('Failed to get Licence data');
        }

        return $response->getResult();
    }
}
