### sp_populate_cancelled_reg

#### Parameters:
- `p_user_id INT`: User ID of the person executing the procedure.

#### Record Selection Criteria for Deletion:
- **Entity:** `bus_reg`
- **Status Filter:** Selects records where the bus registration status is `'breg_s_cancelled'`.
- **Date Filter:** Filters registrations based on the `effective_date` being older than the current date minus the retention period defined for this rule.

#### Summary:
- This procedure targets records in the `bus_reg` table that have been cancelled, specifically those whose effective date is older than the current date minus the retention period.
