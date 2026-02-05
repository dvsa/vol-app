<?php

namespace Olcs\FormService\Form\Lva\OperatingCentre;

use Common\FormService\Form\Lva\OperatingCentre\CommonOperatingCentre;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Dvsa\Olcs\Utils\Helper\ValueHelper;
use Laminas\Form\Form;

/**
 * Lva Operating Centre
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LvaOperatingCentre extends CommonOperatingCentre
{
    public function __construct(
        FormHelperService $formHelper,
        protected TranslationHelperService $translator,
        protected UrlHelperService $urlHelper
    ) {
        $this->formHelper = $formHelper;
    }

    public const DEFAULT_ADVERT_TEMPLATE = 'default-guide-oc-advert-gb-new';

    /**
     * Alter form
     *
     * @param Form $form Form
     * @param array $params Lva object data
     *
     * @return void
     */
    #[\Override]
    public function alterForm(Form $form, array $params)
    {
        $isNi = $this->isNi($params);

        $this->setAdPlacedLabels($form, $isNi, $params['isVariation']);

        $this->setUploadLaterContent($form);

        parent::alterForm($form, $params);
    }

    /**
     * Set label text in depend from parameters
     *
     * @param Form $form Form
     * @param boolean $isNi Is NI
     * @param boolean $isVariation Is Variation
     *
     * @return void
     */
    protected function setAdPlacedLabels(Form $form, $isNi, $isVariation)
    {
        $advertisements = $form->get('advertisements');

        /** @var \Laminas\Form\Element\Radio $radio */
        $radio = $advertisements->get('radio');

        $guideName = 'advertising-your-operating-centre';

        if ($isNi) {
            $guideName .= '-ni';
        } else {
            $guideName .= '-gb';
        }

        if ($isVariation) {
            $guideName .= '-var';
        } else {
            $guideName .= '-new';
        }

        $templateIdentifier = !$isVariation && !$isNi ? static::DEFAULT_ADVERT_TEMPLATE : $guideName;
        $templateUrl = $this->getUrl()->fromRoute(
            'getfile',
            [
                'identifier' => base64_encode((string) $templateIdentifier)
            ],
            [
                'query' => [
                    'slug' => 1
                ]
            ]
        );
        $advertisements->setLabel('lva-operating-centre-radio-label');
        $advertisements->setOption('hint', 'lva-operating-centre-radio-hint');

        $guidance = $this->getTranslator()->translateReplace(
            'markup-lva-oc-ad-placed-label-selfserve',
            [
                $templateUrl,
                $this->getUrl()->fromRoute('guides/guide', ['guide' => $guideName])
            ]
        );
        $form->get('data')->get('guidance')->setValue($guidance);

        $valuesOptions = $radio->getValueOptions();

        $valuesOptions['adPlaced'] = 'lva-oc-adplaced-y-selfserve';
        $valuesOptions['adPlacedLater'] = 'lva-oc-adplaced-l-selfserve';

        $radio->setValueOptions($valuesOptions);
    }

    /**
     * Set Upload Later text for selfserve
     *
     * @param Form $form Form
     *
     * @return void
     */
    protected function setUploadLaterContent(Form $form)
    {
        $form->get('advertisements')->get('adPlacedLaterContent')->setValue('markup-lva-oc-ad-upload-later-text');
    }

    /**
     * Get Translator service
     *
     * @return \Common\Service\Helper\TranslationHelperService
     */
    protected function getTranslator()
    {
        return $this->translator;
    }

    /**
     * Get url service
     *
     * @return \Common\Service\Helper\UrlHelperService
     */
    protected function getUrl()
    {
        return $this->urlHelper;
    }

    /**
     * Define is Nothern Ireland
     *
     * @param array $params Lva object data
     *
     * @return bool
     */
    protected function isNi(array $params)
    {
        if (isset($params['niFlag'])) {
            return ValueHelper::isOn($params['niFlag']);
        }

        if (isset($params['trafficArea']['isNi'])) {
            return (bool)$params['trafficArea']['isNi'];
        }

        if (isset($params['licence']['trafficArea']['isNi'])) {
            return (bool)$params['licence']['trafficArea']['isNi'];
        }

        return false;
    }
}
