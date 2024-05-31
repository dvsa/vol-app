### sp_populate_irfo_psv_auth_withdrawn_pending_refused_expired

#### Parameters:
- `p_user_id INT`: User ID of the person executing the procedure.

#### Record Selection Criteria for Deletion:
- **Entity:** `irfo_psv_auth`
- **Status Filter:** Selects records where the PSV authorization status is in one of the following states: 'irfo_auth_s_withdrawn' (withdrawn), 'irfo_auth_s_pending' (pending), 'irfo_auth_s_refused' (refused), 'irfo_auth_s_granted' (granted), or 'irfo_auth_s_cns' (consented).
- **No Expiry Date Filter:** Specifically targets authorizations that do not have an `expiry_date` set.
- **Creation Date Filter:** Filters authorizations based on the `created_on` date being older than the current date minus the retention period.

#### Summary:
- This procedure identifies IRFO PSV authorizations with specific statuses, particularly those without an expiry date and whose creation date is older than the current date minus the retention period.
