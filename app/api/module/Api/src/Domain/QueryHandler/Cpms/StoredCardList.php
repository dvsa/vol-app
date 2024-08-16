<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Cpms;

use Dvsa\Olcs\Api\Domain\CpmsAwareInterface;
use Dvsa\Olcs\Api\Domain\CpmsAwareTrait;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * StoredCardList
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class StoredCardList extends AbstractQueryHandler implements CpmsAwareInterface
{
    use CpmsAwareTrait;

    /**
     * @inheritdoc
     */
    public function handleQuery(QueryInterface $query)
    {
        $result = [];

        $data = $this->getCpmsService()->getListStoredCards($query->getIsNi());
        if (isset($data['items'])) {
            foreach ($data['items'] as $item) {
                $result[] = [
                    'maskedPan' => $item['mask_pan'],
                    'cardScheme' => $item['card_scheme'],
                    'cardReference' => $item['card_reference'],
                ];
            }
        }
        return [
            'result' => $result,
            'count' => count($result),
        ];
    }
}
