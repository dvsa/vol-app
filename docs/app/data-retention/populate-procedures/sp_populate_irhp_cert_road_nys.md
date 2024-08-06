### sp_populate_irhp_cert_road_nys

#### Parameters:
- `p_user_id INT`: User ID of the person executing the procedure.

#### Record Selection Criteria for Deletion:
- **Entity:** `irhp_application`
- **Status Filter:** Selects records where the IRHP application status is 'permit_app_nys' (not yet started).
- **Permit Type Filter:** Specifically targets applications for permit types with IDs `6` (Roadworthiness Cert Vehicle) and `7` (Roadworthiness Cert Trailer).
- **Date Filter:** Filters applications based on either the `last_modified_on` date or the `created_on` date being older than 12 months from the current date. This accounts for cases where the last modified date might be null.
#### Summary:
- This procedure identifies IRHP applications that are not yet started (status 'permit_app_nys') for specific permit types, selecting those whose last interaction or creation is older than 12 months.
