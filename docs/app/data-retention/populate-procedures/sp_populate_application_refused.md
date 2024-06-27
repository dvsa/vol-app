### sp_populate_application_refused

#### Parameters:
- `p_user_id INT`: User ID of the person executing the procedure.

#### Record Selection Criteria for Deletion:
- **Entity:** `application`
- **Status Filter:** Selects records where the application status is `'apsts_refused'`.
- **Variation Filter:** Considers only applications that are not variations (`is_variation = 0`).
- **Date Filter:** Filters applications based on the `refused_date` being older than the current date minus the retention period.

#### Summary:
- This procedure targets records in the `application` table that have been refused and are not variations, considering those whose refusal date is older than the current date minus the retention period.
