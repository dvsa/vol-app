### sp_populate_refused_with_var_zero

#### Parameters:
- `p_user_id INT`: User ID of the person executing the procedure.

#### Record Selection Criteria for Deletion:
- **Entity:** `bus_reg` (bus registration)
- **Status Filter:** Targets bus registrations where the status is 'breg_s_refused'.
- **Variation Number Filter:** Selects registrations that have a variation number of zero (`variation_no = 0`).
- **Event History Filter:** Includes a subquery that fetches the latest event datetime for events of type `1018` (Registration Refused), and filters out registrations where this occurred before the current date minus the specified retention period.

#### Summary:
- This procedure identifies bus registrations that have been refused, have no variations, and where the latest relevant event occurred prior to the retention period.
