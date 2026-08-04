<?php

namespace Common\Service\Data;

use Common\Exception\DataServiceException;
use Common\Preference\Language as LanguagePreference;
use Dvsa\Olcs\Transfer\Query\RefData\RefDataList;

/**
 * Class RefData
 *
 * @package Common\Service\Data
 */
class RefData extends AbstractListDataService
{
    /** @var LanguagePreference */
    protected $languagePreferenceService;

    /**
     * Create service instance
     *
     *
     * @return RefData
     */
    public function __construct(RefDataServices $refDataServices)
    {
        parent::__construct($refDataServices->getAbstractListDataServiceServices());
        $this->languagePreferenceService = $refDataServices->getLanguagePreferenceService();
    }

    /**
     * Fetch list data
     *
     * @param string $category Category
     *
     * @return array
     * @throw DataServiceException
     */
    #[\Override]
    public function fetchListData($category = null)
    {
        if (is_null($this->getData($category))) {
            $params = [
                'refDataCategory' => $category,
                'language' => $this->languagePreferenceService->getPreference()
            ];
            $dtoData = RefDataList::create($params);
            $response = $this->handleQuery($dtoData);

            if (!$response->isOk()) {
                throw new DataServiceException('unknown-error');
            }

            $this->setData($category, false);

            if (isset($response->getResult()['results'])) {
                $this->setData($category, $response->getResult()['results']);
            }
        }

        return $this->getData($category);
    }
}
