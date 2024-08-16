<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsSystemAdmin;

return [
    QueryHandler\Template\AvailableTemplates::class => IsSystemAdmin::class,
    QueryHandler\Template\PreviewTemplateSource::class => IsSystemAdmin::class,
    QueryHandler\Template\TemplateSource::class => IsSystemAdmin::class,
    QueryHandler\Template\TemplateCategories::class => IsSystemAdmin::class,
    CommandHandler\Template\UpdateTemplateSource::class => IsSystemAdmin::class,
];
