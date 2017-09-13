<?php

namespace Olcs\FormService\Form\Lva\OperatingCentre;

use Common\FormService\Form\Lva\OperatingCentre\CommonOperatingCentre;
use Common\View\Helper\ReturnToAddress;
use Dvsa\Olcs\Utils\Helper\ValueHelper;
use Zend\Form\Form;

/**
 * Lva Operating Centre
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LvaOperatingCentre extends CommonOperatingCentre
{
    /** @var \Common\Service\Helper\TranslationHelperService */
    protected $translator;

    /** @var \Common\Service\Helper\UrlHelperService */
    protected $url;

    /**
     * Alter form
     *
     * @param Form  $form   Form
     * @param array $params Lva object data
     *
     * @return void
     */
    public function alterForm(Form $form, array $params)
    {
        $isNi = $this->isNi($params);

        $this->setSendByPostContent($form, $isNi, $params);

        $this->setAdPlacedLabels($form, $isNi, $params['isVariation']);

        $this->setUploadLaterContent($form);

        parent::alterForm($form, $params);
    }

    /**
     * Set label text in depend from parameters
     *
     * @param Form    $form        Form
     * @param boolean $isNi        Is NI
     * @param boolean $isVariation Is Variation
     *
     * @return void
     */
    protected function setAdPlacedLabels(Form $form, $isNi, $isVariation)
    {
        /** @var \Zend\Form\Element\Radio $radio */
        $radio = $form->get('advertisements')->get('radio');

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

        $label = $this->getTranslator()->translateReplace(
            'markup-lva-oc-ad-placed-label-selfserve',
            [
                $this->getUrl()->fromRoute('guides/guide', ['guide' => $guideName])
            ]
        );

        $radio->setLabel($label);

        $valuesOptions = $radio->getValueOptions();

        $valuesOptions['adPlaced'] = 'lva-oc-adplaced-y-selfserve';
        $valuesOptions['adSendByPost'] = 'lva-oc-adplaced-n-selfserve';
        $valuesOptions['adPlacedLater'] = 'lva-oc-adplaced-l-selfserve';

        $radio->setValueOptions($valuesOptions);
    }

    /**
     * Set Send By Post address in depend from parameters
     *
     * @param Form    $form   Form
     * @param boolean $isNi   Is NI
     * @param array   $params Lva object data
     *
     * @return void
     */
    protected function setSendByPostContent(Form $form, $isNi, $params)
    {
        /** @var \Common\Form\Elements\Types\HtmlTranslated $adSendByPost */
        $adSendByPost = $form->get('advertisements')->get('adSendByPostContent');

        if (empty($params['licNo'])) {
            $reference = '';
        } else {
            if (isset($params['applicationId'])) {
                $reference = ': <b>' . $params['licNo'] . '/' . $params['applicationId'] . '</b>';
            } else {
                $reference = ': <b>' . $params['licNo'] . '</b>';
            }
        }

        $value = $this->getTranslator()->translateReplace(
            'markup-lva-oc-ad-send-by-post-text',
            [
                ReturnToAddress::getAddress($isNi, '<br />'),
                $reference
            ]
        );

        $adSendByPost->setValue($value);
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
        if ($this->translator === null) {
            $this->translator = $this->getServiceLocator()->get('Helper\Translation');
        }

        return $this->translator;
    }

    /**
     * Get url service
     *
     * @return \Common\Service\Helper\UrlHelperService
     */
    protected function getUrl()
    {
        if ($this->url === null) {
            $this->url = $this->getServiceLocator()->get('Helper\Url');
        }

        return $this->url;
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
