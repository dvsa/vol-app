### sp_populate_licence_with_imp_not_unlicenced

#### Parameters:
- `p_user_id INT`: User ID of the person executing the procedure.

#### Summary:
- This procedure identifies licences within the goods vehicles category that have been involved in impounding cases and are not associated with unlicensed organisations. It targets those whose expiry dates have passed beyond the designated retention period.

#### Record Selection Criteria for Deletion:
- **Entity:** `licence`
- **Impounding Cases Filter:** Selects licences that are associated with impounding cases.
- **Organisation Filter:** Targets licences linked to organisations that are not marked as unlicensed (`is_unlicensed = FALSE`).
- **Licence Category Filter:** Specifically targets licences categorised under 'lcat_gv' (goods vehicles).
- **Expiry Date Filter:** Filters licences based on their expiry date being earlier than the current date minus the defined retention period.
