### sp_populate_tm_without_pi

#### Parameters:
- `p_user_id INT`: User ID of the person executing the procedure.

#### Record Selection Criteria for Deletion:
- **Entity:** `transport_manager`
- **Date Filter:** Targets transport managers whose last associated licence date is earlier than the current date minus the specified retention period (`v_retention_period`).
- **Case Involvement Filter:** Excludes transport managers who have been involved in public inquiries (`pi`) or have decisions from transport manager cases marked as 'tm_decision_rl' or 'tm_decision_rnl'.

#### Summary:
- This procedure identifies transport manager records that have not been involved in public inquiries or other decisions about thier their status, whose last licence date has surpassed the retention period.
