### sp_populate_new_var_appl_bus_reg_expired

#### Parameters:
- `p_user_id INT`: User ID of the person executing the procedure.

#### Record Selection Criteria for Deletion:
- **Entity:** `bus_reg` (bus registration)
- **Status Filter:** Selects records where the bus registration status is 'breg_s_expired'
- **Expiry Date Filter:** Filters registrations based on the `end_date` being earlier than the current date minus the retention period defined in the data retention rules. 

#### Summary:
- This procedure identifies bus registrations that have expired and whose end date is older than the defined retention period.
