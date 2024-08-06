### sp_populate_withdrawn_with_var_zero

#### Parameters:
- `p_user_id INT`: User ID of the person executing the procedure.

#### Record Selection Criteria for Deletion:
- **Entity:** `bus_reg` (bus registration)
- **Status Filter:** Selects bus registrations where the status is 'breg_s_withdrawn'.
- **Variation Number Filter:** Targets registrations that have a variation number of zero (`variation_no = 0`),  selecting initial applications that were withdrawn without any subsequent variations or changes.
- **Event History Filter:** Includes a join to `event_history` to ensure that only bus registrations with specific events of type '1002' are selected, and only events occurring before the current date minus the retention period.

#### Summary:
- This procedure identifies bus registrations that have been withdrawn, have no variations, and have specific withdrawal-related events recorded before the retention period expiry.
