### sp_populate_licence_no_imp_unlicenced

#### Parameters:
- `p_user_id INT`: User ID of the person executing the procedure.

#### Record Selection Criteria for Deletion:
- **Entity:** `licence`
- **Organisation Filter:** Targets licences linked to organisations flagged as unlicensed (`is_unlicensed = TRUE`).
- **Licence Status Filter:** Selects licences with the status `'lsts_unlicenced'`.
- **Case Association Filter:** Excludes licences that are associated with any cases, specifically impounding cases.
- **Expiry Date Filter:** Filters licences based on their expiry date being earlier than the current date minus the retention period.

#### Summary:
- This procedure identifies unlicensed licences that are not involved in ongoing cases and whose expiry dates have passed beyond the designated retention period.
