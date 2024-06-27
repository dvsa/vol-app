### sp_populate_misc_fees

#### Parameters:
- `p_user_id INT`: User ID of the person executing the procedure.

#### Record Selection Criteria for Deletion:
- **Entity:** `fee`
- **Association Filters:** Targets fee records that are not associated with any licence, IRFO goods vehicle permit, IRFO PSV authorization, bus registration, application, IRHP application, or task, ensuring only miscellaneous fees with no current relevant entity links are selected.
- **Date Filter:** Filters fees based on the `last_modified_on` or `created_on` date being older than the current date minus the retention period defined in the retention rules. This identifies fees that have not been updated or relevant for a specified time period.

#### Summary:
- This procedure identifies miscellaneous fee records that are unlinked to any major operational entity and whose modification or creation date is older than the defined retention period.
