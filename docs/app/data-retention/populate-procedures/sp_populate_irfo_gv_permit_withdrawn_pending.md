### sp_populate_irfo_gv_permit_withdrawn_pending_refused_expired

#### Parameters:
- `p_user_id INT`: User ID of the person executing the procedure.

#### Record Selection Criteria for Deletion:
- **Entity:** `irfo_gv_permit`
- **Status Filter:** Selects records where the IRFO goods vehicle permit status is in one of the following states: 'irfo_auth_s_withdrawn' (withdrawn), 'irfo_perm_s_pending' (pending), or 'irfo_perm_s_refused' (refused).
- **No Expiry Date Filter:** Specifically targets permits that do not have an `expiry_date` set.
- **Creation Date Filter:** Filters permits based on the `created_on` date being older than the current date minus the retention period

#### Summary:
- This procedure identifies IRFO goods vehicle permits with specific statuses (withdrawn, pending, or refused) that lack an expiry date and whose creation date is older than the current date minus the retention period.
