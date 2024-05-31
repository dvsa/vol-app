### sp_populate_org_no_licence

#### Parameters:
- `p_user_id INT`: User ID of the person executing the procedure.

#### Record Selection Criteria for Deletion:
- **Entity:** `organisation`
- **Licence Association Filter:** Targets organisations that currently do not hold any licences. It excludes organisations that are associated with any record in the `licence` table.
- **Modification Date Filter:** Filters organisations based on the `last_modified_on` date being earlier than the current date minus the retention period defined.
- **IRFO Filter:** Excludes IRFO organisations by checking if `is_irfo=0`.
- **Custom Rule Filter:** Ensures organisations have not been marked for retention under any custom rules.

#### Summary:
- This procedure identifies organisations without any current licences and which have not been modified within the retention period defined by the rules table.
