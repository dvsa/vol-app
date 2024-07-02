---
sidebar_position: 3
---
# Organisation-User Cleanup

## `sp_cleanup_organisation_user` Procedure Documentation

## Important Note
This procedure does not currently seem to be invoked by the olcs-backend D&DR command handlers or referenced in the data_retention_rule table as a custom procedure. Has it ever been used? Is it Still required?

## Overview
The `sp_cleanup_organisation_user` procedure maintains data integrity within the `organisation_user` table by removing records that are linked to users marked as deleted.

## Execution Details
- **Transaction Control:** The procedure is wrapped in a transaction to ensure all or nothing execution. If an error occurs, it triggers a rollback to revert all changes.
- **Error Handling:** Comprehensive error handling is implemented to capture and report SQL errors. These errors are logged with detailed messages, including the error code and SQL state.

## Process Steps
1. **Temporary Table Preparation:**
    - A temporary table, `temp_organisation_user_to_delete`, is created to store the IDs of records to be deleted.
2. **Identifying Orphaned Records:**
    - The procedure searches for `organisation_user` entries that refer to users with a non-null `deleted_date`, indicating they have been marked as deleted.
    - These IDs are collected in the temporary table.
3. **Deletion Operation:**
    - Records identified for deletion are removed from the `organisation_user` table.
4. **Validation of Operation:**
    - The procedure validates the deletion operation by comparing the expected number of records post-deletion with the actual count.
    - If discrepancies are found, an error is raised.
5. **Cleanup and Finalization:**
    - Temporary tables are dropped to clean up the session.
    - A success message is displayed if the operation completes without errors.

## Error Conditions
- **Mismatch in Record Counts:** If the actual count of remaining records does not match the expected count, an error is raised.
- **Existence of Orphaned Records:** If any records that should have been deleted remain in the database, an error is raised.

## Execution Command
The procedure can be run manually using the following SQL:
```sql
CALL sp_cleanup_organisation_user();
