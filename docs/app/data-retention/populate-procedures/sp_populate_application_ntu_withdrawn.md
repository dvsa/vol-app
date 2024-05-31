### sp_populate_application_ntu_withdrawn

#### Parameters:
- `p_user_id INT`: User ID of the person executing the procedure.

#### Record Selection Criteria for Deletion:
- **Entity:** `application`
- **Status Filter:** Selects records where the application status is either `'apsts_ntu'` (not taken up) or `'apsts_withdrawn'` (withdrawn by the applicant).
- **Event Filter:** Only considers records where the last event related to the application is of type ID `5` or `16`.
- **Date Filter:** Filters applications based on the date of the last relevant event being older than the current date minus the retention period defined.

#### Summary:
- This procedure focuses on records from the `application` table with statuses of not taken up or withdrawn, considering only those where the last significant event occurred before the current date minus the retention period.
