<?php

namespace Common\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;

/**
 * @author Dmitry Golubev <d.e.golubev@gmail.com>
 */
class CaseEntityNrStatus implements FormatterPluginManagerInterface
{
    private const URL_TEMPLATE = '<a class="govuk-link" href="%s">%s</a>';

    private const TEMPLATE_LIC = '%s (%s)';

    private const TEMPLATE_APP = '%s (%s)<br />/%s (%s)';

    public function __construct(private UrlHelperService $urlHelper)
    {
    }

    /**
     * Return traffic area name
     *
     * @param array $data   Data
     * @param array $column Column data
     *
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        $typeId = $data['caseType']['id'];

        //  transport manager
        if ($typeId === \Common\RefData::CASE_TYPE_TM) {
            $tmId = $data['transportManager']['id'];

            return sprintf(
                self::URL_TEMPLATE,
                $this->urlHelper->fromRoute('transport-manager', ['transportManager' => $tmId]),
                $tmId
            );
        }

        //  licence
        $lic = $data['licence'];

        $licLink = sprintf(
            self::URL_TEMPLATE,
            $this->urlHelper->fromRoute('lva-licence', ['licence' => $lic['id']]),
            $lic['licNo']
        );

        $licStatus = $lic['status']['description'];

        if (
            $typeId === \Common\RefData::CASE_TYPE_LICENCE
            || $typeId === \Common\RefData::CASE_TYPE_IMPOUNDING
        ) {
            return sprintf(self::TEMPLATE_LIC, $licLink, $licStatus);
        }

        //  application
        $app = $data['application'];
        $appId = $app['id'];

        $appLink = sprintf(
            self::URL_TEMPLATE,
            $this->urlHelper->fromRoute('lva-application', ['application' => $appId]),
            $appId
        );

        $appStatus = $app['status']['description'];

        return sprintf(self::TEMPLATE_APP, $licLink, $licStatus, $appLink, $appStatus);
    }
}
