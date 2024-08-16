<?php

namespace Dvsa\Olcs\Cli\Domain\QueryHandler\DataRetention;

use RuntimeException;

/**
 * EscapeMysqlIdentifier Trait
 */
trait EscapeMysqlIdentifierTrait
{
    /**
     * Escapes an identifier for use in queries
     *
     * @param string $identifier Identifier string to escape
     * @return string
     * @throws RuntimeException
     */
    private function escapeMysqlIdentifier(string $identifier)
    {
        if (!preg_match('/^[_a-zA-Z0-9]+$/', $identifier)) {
            throw new RuntimeException("Bad identifier " . var_export($identifier, true));
        }
        return "`$identifier`";
    }
}
