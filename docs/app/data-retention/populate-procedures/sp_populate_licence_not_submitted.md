### sp_populate_licence_not_submitted

#### Parameters:
- `p_user_id INT`: User ID of the person executing the procedure.

#### Summary:
- This procedure identifies licences that have not been submitted and are not associated with any applications. It targets those whose expiry dates have passed beyond the retention period

#### Record Selection Criteria for Deletion:
- **Entity:** `licence`
- **Status Filter:** Selects licences with the status `'lsts_not_submitted'`.
- **Expiry Date Filter:** Filters licences based on their expiry date being earlier than the current date minus the retention period.
- **Application Association Filter:** Excludes licences that are associated with any applications, ensuring only those without any submitted applications are selected.
