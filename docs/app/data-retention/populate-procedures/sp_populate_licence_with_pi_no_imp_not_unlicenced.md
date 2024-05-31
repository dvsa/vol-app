### sp_populate_licence_with_pi_no_imp_not_unlicenced

#### Parameters:
- `p_user_id INT`: User ID of the person executing the procedure.

#### Summary:
- This procedure identifies goods vehicle licences involved in public inquiries but not impounding cases, which are not associated with unlicensed organisations, and whose expiry dates have passed beyond the retention period.

#### Record Selection Criteria for Deletion:
- **Entity:** `licence`
- **Public Inquiry Filter:** Selects licences that are involved in public inquiries.
- **Exclusion of Impounding Cases:** Excludes licences that are currently involved in any impounding cases, selecting only those with public inquiry cases.
- **Organisation Filter:** Targets licences linked to organisations that are not marked as unlicensed (`is_unlicensed = FALSE`).
- **Licence Category Filter:** Specifically targets licences categorised under 'lcat_gv' (goods vehicles).
- **Expiry Date Filter:** Filters licences based on their expiry date being earlier than the current date minus the retention period defined in the data retention rules.
