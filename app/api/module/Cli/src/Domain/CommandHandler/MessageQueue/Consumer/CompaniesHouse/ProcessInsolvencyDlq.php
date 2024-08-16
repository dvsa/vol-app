<?php

namespace Dvsa\Olcs\Cli\Domain\CommandHandler\MessageQueue\Consumer\CompaniesHouse;

class ProcessInsolvencyDlq extends AbstractProcessDlq
{
    /**
     * @inheritdoc
     *
     * @var string
     */
    protected $emailSubject = 'Companies House Insolvency process failure - list of those that failed';

    /**
     * @inheritdoc
     *
     * @var string
     */
    protected $queueType = ProcessInsolvency::class;
}
