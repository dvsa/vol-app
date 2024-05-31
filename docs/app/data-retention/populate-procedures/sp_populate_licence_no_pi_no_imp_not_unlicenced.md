### sp_populate_licence_no_pi_no_imp_not_unlicenced

#### Parameters:
- `p_user_id INT`: User ID of the person executing the procedure.

#### Record Selection Criteria for Deletion:
- **Entity:** `licence`
- **Exclusion of Cases:** Excludes licences that are currently involved in any public inquiry (`pi`) or impounding cases.
- **Organisation Filter:** Targets licences linked to organisations that are not marked as unlicensed (`is_unlicensed = FALSE`).
- **Licence Category Filter:** Specifically targets licences categorised under 'lcat_gv' (goods vehicles).
- **Expiry Date Filter:** Filters licences based on their expiry date being earlier than the current date minus the retention period defined in the data retention rules. 

#### Summary:
- This procedure identifies licences not involved in public inquiries or impounding cases, which are not marked as unlicensed, specifically within the GV category, and whose expiry dates have passed beyond the retention period.
