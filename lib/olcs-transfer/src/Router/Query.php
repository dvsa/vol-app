<?php

namespace Dvsa\Olcs\Transfer\Router;

use Laminas\Router\Http\Method;

class Query extends Method
{
    #[\Override]
    public function assemble(array $params = [], array $options = [])
    {
        $mergedParams = array_merge($this->defaults, $params);

        if (count($mergedParams)) {
            $options['uri']->setQuery($mergedParams);
        }

        return parent::assemble($params, $options);
    }
}
