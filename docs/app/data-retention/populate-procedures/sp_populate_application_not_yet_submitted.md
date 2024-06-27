### sp_populate_application_not_yet_submitted

#### Parameters:
- `p_user_id INT`: User ID of the person executing the procedure.

#### Record Selection Criteria for Deletion:
- **Entity:** `application`
- **Status Filter:** Selects records where the application status is `'apsts_not_submitted'`.
- **Date Filter:** Selects records based on the `last_modified_on` or `created_on` date being older than the current date minus the retention period.

#### Summary:
- This procedure targets records in the `application` table where the status is `'apsts_not_submitted'` and either the last modified date or creation date is older than the current date minus the retention period.
