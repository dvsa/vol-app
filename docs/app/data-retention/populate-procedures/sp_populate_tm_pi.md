### sp_populate_tm_pi

#### Parameters:
- `p_user_id INT`: User ID of the person executing the procedure.

#### Record Selection Criteria for Deletion:
- **Entity:** `transport_manager`
- **Date Filter:** Selects transport managers whose last associated licence date is earlier than the current date minus the specified retention period (`v_retention_period`).
- **Case Involvement Filter:** Includes transport managers who have been involved in public inquiries (`pi`) specifically related to transport manager cases (`case_t_tm`), or have decisions from transport manager cases marked as 'tm_decision_rl' (revoked licence) or 'tm_decision_rnl' (revoked no licence).

#### Summary:
- This procedure identifies transport manager records that have regulatory issues, and last licence date is beyond the retention period.
