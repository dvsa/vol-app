### sp_populate_irhp_cancelled_withdrawn

#### Parameters:
- `p_user_id INT`: User ID of the person executing the procedure.

#### Record Selection Criteria for Deletion:
- **Entity:** `irhp_application`
- **Status Filter:** Selects records where the IRHP application status is either 'permit_app_cancelled' (cancelled) or 'permit_app_withdrawn' (withdrawn).
- **Date Filter:** Filters applications based on the `last_modified_on` date being older than the current date minus the retention period.
#### Summary:
- This procedure identifies cancelled or withdrawn IRHP applications, selecting those whose last modification date is older than the current date minus the retention period
