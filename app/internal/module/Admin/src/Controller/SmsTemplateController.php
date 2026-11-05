<?php

namespace Admin\Controller;

/**
 * SMS Management (VOL-7542): the same list/edit/preview screens as Email Templates, locked
 * to format = sms. There is no format filter, and because the rows are never markdown the
 * parent's Markdown hint and Send test button are already switched off.
 */
class SmsTemplateController extends TemplateController
{
    protected $tableName = 'admin-sms-templates';

    protected string $templateRoute = 'admin-dashboard/admin-sms-templates';

    protected $filterForm = '';

    #[\Override]
    protected function modifyListQueryParameters($parameters)
    {
        $parameters['emailTemplateCategory'] = null;
        // Whatever is in the query string, only SMS rows are listed.
        $parameters['format'] = 'sms';
        return $parameters;
    }
}
