### sp_populate_irfo_operator_expired

#### Parameters:
- `p_user_id INT`: User ID of the person executing the procedure.

#### Record Selection Criteria for Deletion:
- **Entity:** `organisation`
- **Type Filter:** Focuses on organisations of type 'org_t_ir', IRFO Operators.
- **Creation Date Filter:** Selects organisations based on the `created_on` date being older than the current date minus the retention period fron the data retention rules.
- **Association Filters:** Ensures that the organisations are not currently associated with any IRFO PSV authorizations, IRFO goods vehicle permits, or licenses.

#### Summary:
- This procedure targets IRFO operator organisations that are no longer linked to any active permits, authorizations, or licenses and whose creation date is older than the specified retention period. It stages these organization records for deletion.
