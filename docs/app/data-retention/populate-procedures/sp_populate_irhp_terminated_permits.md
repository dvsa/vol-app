### sp_populate_irhp_terminated_permits

#### Parameters:
- `p_user_id INT`: User ID of the person executing the procedure.

#### Record Selection Criteria for Deletion:
- **Entity:** `irhp_permit` and `irhp_application`
- **Status Filter for IRHP Permit:** Selects records where the IRHP permit status is 'irhp_permit_terminated'.
- **Date Filter for IRHP Permit:** Filters permits based on the `last_modified_on` date being older than the current date minus the retention period defined in the data retention rules.
- **Comprehensive Application Cleanup:** Also targets IRHP applications associated solely with terminated permits, ensuring applications that no longer have any active or non-terminated permits and were last modified more than 60 months ago are selected.

#### Summary:
- This procedure identifies terminated IRHP permits and their associated applications, selecting those permits and applications whose last modification dates are older than the defined retention periods.
