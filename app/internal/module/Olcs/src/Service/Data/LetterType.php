<?php

namespace Olcs\Service\Data;

use Common\Exception\DataServiceException;
use Dvsa\Olcs\Transfer\Query\LetterType\GetList as GetListQry;

/**
 * LetterType Data Service
 * Provides active letter types for dropdown selection in admin UI
 */
class LetterType extends AbstractPublicDataServiceServices
{
  protected static $sort = "name";
  protected static $order = "ASC";

  /**
   * Fetch list data
   *
   * @param array $context Parameters
   * @return array
   * @throws DataServiceException
   */
  public function fetchListData($context = null)
  {
    // Check cache first
    $data = (array) $this->getData("letterTypes");
    if (count($data) > 0) {
      return $data;
    }

    // Fetch only active letter types for admin dropdown
    $params = [
      "sort" => self::$sort,
      "order" => self::$order,
      "isActive" => true,
    ];

    $response = $this->handleQuery(GetListQry::create($params));

    if (!$response->isOk()) {
      throw new DataServiceException("unknown-error");
    }

    $result = $response->getResult();
    $this->setData("letterTypes", $result["results"] ?? []);

    return $this->getData("letterTypes");
  }

  /**
   * Format data for dropdown
   *
   * @param array $data Data
   * @return array
   */
  public function formatData(array $data)
  {
    $optionData = [];
    foreach ($data as $datum) {
      // Format: "Name - Description" or just "Name" if no description
      $label = $datum["name"];
      if (!empty($datum["description"])) {
        $label .= " - " . $datum["description"];
      }
      $optionData[$datum["id"]] = $label;
    }
    return $optionData;
  }
}
