### sp_populate_document_financial_reports

#### Parameters:
- `p_user_id INT`: User ID of the person executing the procedure.

#### Record Selection Criteria for Deletion:
- **Entity:** `document`
- **Sub-category Filter:** Selects records specifically in the sub-category with ID `180`, which is `Financial reports` .
- **Association Filters:** Ensures the documents considered for deletion are not associated with any licences, applications, cases, transport managers, operating centres, IRFO organisations, submissions, statements, or continuation details.
- **Date Filter:** Filters documents based on the last modification or creation date being older than the current date minus the retention period from the data retention rules table.

#### Summary:
- Targets documents classified under a specific financial report sub-category, which are not linked to any major entity (eg licences or applications), and whose last modification or creation date is older than the retention period.
