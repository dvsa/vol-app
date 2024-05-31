### sp_populate_irhp_replaced_permits

#### Parameters:
- `p_user_id INT`: User ID of the person executing the procedure.

#### Record Selection Criteria for Deletion:
- **Entity:** `irhp_permit`
- **Status Filter:** Selects records where the IRHP permit status is 'irhp_permit_ceased'.
- **Expiry Date Filter:** Filters permits based on the `expiry_date`  being earlier than the current date minus the retention period.
#### Summary:
- This procedure targets IRHP permits that have been ceased or replaced, selecting those whose expiry date is older than the defined retention period.
