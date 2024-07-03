### sp_populate_irfo_psv_auth_expired

#### Parameters:
- `p_user_id INT`: User ID of the person executing the procedure.

#### Record Selection Criteria for Deletion:
- **Entity:** `irfo_psv_auth`
- **Expiry Filter:** Selects records where the `expiry_date` of the PSV authorization is older than the current date minus the retention period parameter.

#### Summary:
- This procedure identifies expired IRFO PSV authorizations, selecting those whose expiry date is older than the current date minus the retention period.
