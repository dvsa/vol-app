<?php

namespace Dvsa\Olcs\Cli\Domain\CommandHandler\MessageQueue\Consumer\CompaniesHouse;

class CompanyProfileDlq extends AbstractProcessDlq
{
    /**
     * @inheritdoc
     *
     * @var string
     */
    protected $emailSubject = 'Companies House Company Profile process failure - list of those that failed';

    /**
     * @inheritdoc
     *
     * @var string
     */
    protected $queueType = CompanyProfile::class;
}
