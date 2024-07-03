### sp_populate_admin_cancelled

#### Parameters:
- `p_user_id INT`: User ID of the person executing the procedure.

#### Record Selection Criteria for Deletion:
- **Entity:** `bus_reg`
- **Status Filter:** Selects records where the `bus_reg_status_id` is `'breg_s_admin'` which is "admin cancelled" status 
- **Date Filter:** Selects records where the `effective_date` is older than the current date minus the retention period.


#### Summary:
- This procedure deletes records from the `bus_reg` table where the `bus_reg_status_id` is `'breg_s_admin'` and the `effective_date` is older than the current date minus the retention period defined in the data retention rules.
