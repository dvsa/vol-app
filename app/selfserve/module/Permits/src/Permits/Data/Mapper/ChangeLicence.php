<?php

namespace Permits\Data\Mapper;

use Common\Service\Helper\TranslationHelperService;
use Permits\Controller\Config\DataSource\LicencesAvailable as LicencesAvailableDataSource;

/**
 * Change Licence mapper
 */
class ChangeLicence
{
    const CHANGE_LICENCE_LABEL = 'permits.form.change_licence.label';

    /** @var TranslationHelperService */
    private $translator;

    /**
     * Create service instance
     *
     * @param TranslationHelperService $translator
     *
     * @return ChangeLicence
     */
    public function __construct(TranslationHelperService $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Map for form options
     *
     * @param array $data
     * @param mixed $form
     *
     * @return array
     */
    public function mapForFormOptions(array $data, $form): array
    {
        $mapData = $data[LicencesAvailableDataSource::DATA_KEY];

        $confirmChangeLabel = $this->translator->translateReplace(
            self::CHANGE_LICENCE_LABEL,
            [$mapData['eligibleLicences'][$data['licence']]['licNo']]
        );

        $form->get('fields')->get('ConfirmChange')->setLabel(
            $confirmChangeLabel
        );

        $form->setData(['fields' => ['licence' => $data['licence']]]);

        return $data;
    }
}
