<?php

declare(strict_types=1);

namespace CommonTest\Common\Controller\Traits\Stubs;

use Common\Controller\Traits\CompanySearch;

class CompanySearchStub
{
    use CompanySearch;

    public $form;

    public $stubResponse;

    public function handleQuery(\Dvsa\Olcs\Transfer\Query\CompaniesHouse\ByNumber $dto)
    {
        $this->stubResponse->dto = $dto;
        return $this->stubResponse;
    }

    public function renderForm($form)
    {
        $this->form = $form;
        return $form;
    }
}
