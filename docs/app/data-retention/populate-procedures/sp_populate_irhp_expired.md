### sp_populate_irhp_expired

#### Parameters:
- `p_user_id INT`: User ID of the person executing the procedure.

#### Record Selection Criteria for Deletion:
- **Entity:** `irhp_permit` and `irhp_application`
- **Status Filter for IRHP Permit:** Selects records where the IRHP permit status is 'irhp_permit_expired'.
- **Status Filter for IRHP Application:** Selects records where the IRHP application status is 'permit_app_expired'.
- **Date Filter for IRHP Permit:** Filters permits based on the `last_modified_on` date being older than the current date minus the retention window.
- **Date Filter for IRHP Application:** Filters applications based on the `last_modified_on` date being older than 60 months from the current date.

#### Summary:
- This procedure identifies expired IRHP permits and applications, selecting those permits whose last modification date is older than the defined retention period and applications whose last modification date is older than 60 months.
