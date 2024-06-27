### sp_populate_irfo_gv_permit_expired

#### Parameters:
- `p_user_id INT`: User ID executing the procedure.

#### Record Selection Criteria for Deletion:
- **Entity:** `irfo_gv_permit`
- **Expiry Filter:** Selects records where the `expiry_date` of the permit is older than the current date minus the retention period.

#### Summary:
- This procedure targets expired IRFO goods vehicle permits, populating rows where the expiry date is older than the current date minus the retention period.
