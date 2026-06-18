<?php

declare(strict_types=1);

namespace Common\Data\Mapper\Lva;

use Common\Data\Mapper\MapperInterface;

class PsvMainOccupationUndertakings implements MapperInterface
{
    #[\Override]
    public static function mapFromResult(array $data): array
    {
        return [
            'version' => $data['version'],
            'psvIncomeRecordsConfirmation' => $data['psvIncomeRecordsConfirmation'],
            'psvOccupationRecordsConfirmation' => $data['psvOccupationRecordsConfirmation'],
        ];
    }

    public static function mapFromForm(array $data): array
    {
        return [
            'version' => $data['version'],
            'psvIncomeRecordsConfirmation' => $data['psvIncomeRecordsConfirmation'],
            'psvOccupationRecordsConfirmation' => $data['psvOccupationRecordsConfirmation'],
        ];
    }
}
