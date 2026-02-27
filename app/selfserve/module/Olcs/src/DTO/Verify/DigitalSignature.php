<?php

namespace Olcs\DTO\Verify;

final class DigitalSignature
{
    public const string KEY_APPLICATION_ID = 'applicationId';
    public const string KEY_LVA = 'lva';
    public const string KEY_ROLE = 'role';
    public const string KEY_CONTINUATION_DETAIL_ID = 'continuationDetailId';
    public const string KEY_TRANSPORT_MANAGER_APPLICATION_ID = 'transportManagerApplicationId';
    public const string KEY_LICENCE_ID = 'licenceId';
    public const string KEY_VERIFY_ID = 'verifyId';

    private $applicationId;
    private $lva;
    private $role;
    private $continuationDetailId;
    private $transportManagerApplicationId;
    private $licenceId;
    private $verifyId;

    public function __construct(array $data)
    {
        $this->applicationId = $data[self::KEY_APPLICATION_ID] ?? null;
        $this->lva = $data[self::KEY_LVA] ?? null;
        $this->role = $data[self::KEY_ROLE] ?? null;
        $this->continuationDetailId = $data[self::KEY_CONTINUATION_DETAIL_ID] ?? null;
        $this->transportManagerApplicationId = $data[self::KEY_TRANSPORT_MANAGER_APPLICATION_ID] ?? null;
        $this->licenceId = $data[self::KEY_LICENCE_ID] ?? null;
        $this->verifyId = $data[self::KEY_VERIFY_ID] ?? null;
    }

    /**
     * @return mixed|null
     */
    public function getApplicationId()
    {
        return $this->applicationId;
    }

    /**
     * @return mixed|null
     */
    public function getLva()
    {
        return $this->lva;
    }

    /**
     * @return mixed|null
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @return mixed|null
     */
    public function getContinuationDetailId()
    {
        return $this->continuationDetailId;
    }

    /**
     * @return mixed|null
     */
    public function getTransportManagerApplicationId()
    {
        return $this->transportManagerApplicationId;
    }

    /**
     * @return mixed|null
     */
    public function getLicenceId()
    {
        return $this->licenceId;
    }

    /**
     * @return mixed|null
     */
    public function getVerifyId()
    {
        return $this->verifyId;
    }

    public function toArray(): array
    {
        return [
            self::KEY_APPLICATION_ID => $this->applicationId,
            self::KEY_LVA => $this->lva,
            self::KEY_ROLE => $this->role,
            self::KEY_CONTINUATION_DETAIL_ID => $this->continuationDetailId,
            self::KEY_TRANSPORT_MANAGER_APPLICATION_ID => $this->transportManagerApplicationId,
            self::KEY_LICENCE_ID => $this->licenceId,
            self::KEY_VERIFY_ID => $this->verifyId
        ];
    }
}
