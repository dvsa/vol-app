<?php
namespace Permits\Model;

use RuntimeException;
use Zend\Db\TableGateway\TableGatewayInterface;

class PermitTable
{
  private $tableGateway;

  public function __construct(TableGatewayInterface $tableGateway)
  {
    $this->tableGateway = $tableGateway;
  }

  public function fetchAll()
  {
    return $this->tableGateway->select();
  }

  public function getPermit($id)
  {
    $id = (int) $id;
    $rowset = $this->tableGateway->select(['id' => $id]);
    $row = $rowset->current();
    if (! $row) {
      throw new RuntimeException(sprintf(
        'Could not find row with identifier %d',
        $id
      ));
    }

    return $row;
  }

  public function savePermit(Permit $Permit)
  {
    $data = [
      'artist' => $Permit->artist,
      'title'  => $Permit->title,
    ];

    $id = (int) $Permit->id;

    if ($id === 0) {
      $this->tableGateway->insert($data);
      return;
    }

    if (! $this->getPermit($id)) {
      throw new RuntimeException(sprintf(
        'Cannot update Permit with identifier %d; does not exist',
        $id
      ));
    }

    $this->tableGateway->update($data, ['id' => $id]);
  }

  public function deletePermit($id)
  {
    $this->tableGateway->delete(['id' => (int) $id]);
  }
}