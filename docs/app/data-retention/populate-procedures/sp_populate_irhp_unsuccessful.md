### sp_populate_irhp_unsuccessful

#### Parameters:
- `p_user_id INT`: User ID of the person executing the procedure.

#### Record Selection Criteria for Deletion:
- **Entity:** `irhp_application`
- **Status Filter:** Selects records where the IRHP application status is 'permit_app_unsuccessful'.
- **Date Filter:** Filters applications based on the `last_modified_on` date being older than the current date minus the retention period defined in config.

#### Summary:
- This procedure identifies IRHP applications that were unsuccessful, selecting those whose last modification date is older than the defined retention period.
