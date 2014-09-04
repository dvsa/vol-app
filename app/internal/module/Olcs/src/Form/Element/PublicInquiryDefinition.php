<?php

namespace Olcs\Form\Element;

use Common\Form\Element\DynamicSelect;

class PublicInquiryDefinition extends DynamicSelect
{
    protected $serviceName = 'PublicInquiryDefinition';

    /**
     * @var \Olcs\Service\Data\Licence
     */
    protected $licenceService;

    /**
     * @param \Olcs\Service\Data\Licence $licenceService
     */
    public function setLicenceService($licenceService)
    {
        $this->licenceService = $licenceService;
    }

    /**
     * @return \Olcs\Service\Data\Licence
     */
    public function getLicenceService()
    {
        return $this->licenceService;
    }

    /**
     * This method can probably be extracted/abstracted somewhere as several things need it
     * (impoundings, reasons, definitions etc) (move to licence service??)
     *
     * @TO-DO lct-gv string should probably use a global constant or get it from ref data some how.
     * @return array|string
     */
    public function getContext()
    {
        //get the /default/ licence (controller should have already set the id for us)
        $licence = $this->getLicenceService()->fetchLicenceData();

        return [
            'ni' => $licence['niFlag'],
            'goods_or_psv' => ($licence['goodsOrPsv']['id'] == 'lcat_gv') ? 'goods': 'psv'
        ];
    }

    public function getValueOptions()
    {
        if (empty($this->valueOptions)) {
            $refDataService = $this->getDataService();
            $this->valueOptions = $refDataService->fetchListOptions($this->getContext(), $this->useGroups());
        }

        return $this->valueOptions;
    }

}