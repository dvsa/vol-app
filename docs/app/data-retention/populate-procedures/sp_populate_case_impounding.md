### sp_populate_case_impounding

#### Parameters:
- `p_user_id INT`: User ID of the person executing the procedure.

#### Record Selection Criteria for Deletion:
- **Entity:** `cases`
- **Closure Filter:** Selects case records where the `closed_date` is older than the current date minus the retention period defined for this rule.

#### Summary:
- This procedure identifies cases related to impounding that have been closed and whose closed date is older than the current date minus the retention period.
