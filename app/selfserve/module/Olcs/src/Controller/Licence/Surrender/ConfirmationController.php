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
    protected $dataSourceConfig = [
    ];

    public function indexAction()
    {
        $params = $this->getViewVariables();
        $this->updateSurrender(RefData::SURRENDER_STATUS_SUBMITTED);
        return $this->renderView($params);
    }

    private function getSignatureFullName($surrender)
    {
        $names = [];
        $attributes = json_decode($surrender["digitalSignature"]["attributes"]);
        $names[] = $attributes->firstname ?? '';
        $names[] = $attributes->surname ?? '';

        return implode(' ', $names);
    }

    private function getSignatureDate($surrender)
    {
        $unixTimeStamp = strtotime($surrender["digitalSignature"]['createdOn']);
        return date("j M Y", $unixTimeStamp);
    }

    private function returnDashboardLink(): string
    {
        return $this->url()->fromRoute('dashboard');
    }

    protected function getSurrender()
    {
        $result = $this->handleQuery(GetSignature::create(
            ['id' => $this->licenceId]
        ));
        return $result->getResult();
    }

    /**
     * @return array
     *
     */
    protected function getViewVariables(): array
    {
        /** @var $translator TranslationHelperService */
        $translator = $this->getServiceLocator()->get('Helper\Translation');
        $surrender = $this->getSurrender();
        return [
            'content' => $translator->translateReplace(
                'markup-licence-surrender-confirmation',
                [
                    $surrender['licence']['licNo'],
                    $this->getSignatureFullName($surrender),
                    $this->getSignatureDate($surrender),
                    $this->returnDashboardLink()
                ]
            ),
            'backLink' => $this->returnDashboardLink()
        ];
    }
}
