<?php
/**
 * Created by PhpStorm.
 * User: shaunhare
 * Date: 2018-11-27
 * Time: 10:44
 */

namespace Olcs\Controller\Licence\Surrender;

use Common\RefData;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Query\Surrender\GetSignature;

class ConfirmationController extends AbstractSurrenderController
{
    protected $pageTemplate = 'pages/confirmation';

    public function indexAction()
    {
        $params = $this->getViewVariables();
        $this->updateSurrender(RefData::SURRENDER_STATUS_SUBMITTED);
        return $this->renderView($params);
    }

    private function getSignatureFullName()
    {
        $names = [];
        $attributes = json_decode($this->getSurrender()["digitalSignature"]["attributes"]);
        $names[] = $attributes->firstname ?? '';
        $names[] = $attributes->surname ?? '';

        return implode(' ', $names);
    }

    private function getSignatureDate()
    {
        $unixTimeStamp = strtotime($this->getSurrender()["digitalSignature"]['createdOn']);
        return date("j M Y", $unixTimeStamp);
    }

    private function returnDashboardLink(): string
    {
        return $this->url()->fromRoute('dashboard');
    }

    protected function getSurrender()
    {
        $surrender = GetSignature::create(
            ['id' => $this->licenceId]
        );
        return $surrender;
    }

    /**
     * @return array
     *
     */
    protected function getViewVariables(): array
    {
        /** @var $translator TranslationHelperService */
        $translator = $this->getServiceLocator()->get('Helper\Translation');
        return [
            'content' => $translator->translateReplace(
                'markup-licence-surrender-confirmation',
                [
                    $this->licence['licNo'],
                    $this->getSignatureFullName(),
                    $this->getSignatureDate(),
                    $this->returnDashboardLink()
                ]
            ),
            'backLink' => $this->returnDashboardLink()
        ];
    }
}
