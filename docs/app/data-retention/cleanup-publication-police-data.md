---
sidebar_position: 4
---

# Publication Police Data Cleanup

## `sp_cleanup_publication_police_data` Procedure Documentation

## Overview
The `sp_cleanup_publication_police_data` procedure is designed to remove orphaned records from the `publication_police_data` table that no longer have corresponding entries in the `publication_link` table. This helps in maintaining the integrity of the database by ensuring that data references remain consistent.

## Execution Details
- **Transaction Control:** The procedure executes within a transaction to ensure that all operations are atomic. If an error occurs during execution, changes are rolled back to maintain database consistency.
- **Error Handling:** Errors during execution will trigger a rollback and provide detailed error messages, including SQL state and error number, for debugging purposes.

## Process Steps
1. **Temporary Table Creation:** A temporary table `temp_publication_police_data_to_delete` is created to hold the IDs of records to be deleted.
2. **Identification of Orphaned Records:**
    - The procedure identifies records in `publication_police_data` that reference non-existent records in `publication_link`.
    - These records are inserted into the temporary table.
3. **Deletion Validation:**
    - Before deletion, the procedure calculates the expected number of records to remain post-deletion.
    - After deletion, it verifies that the count of remaining records matches expectations.
    - It checks for any remaining unwanted records and raises an error if mismatches are found.
4. **Record Deletion:** Records identified as orphaned are deleted from `publication_police_data`.

## Error Conditions
- If the actual number of remaining records does not match the expected number, an error is raised.
- If any records that were supposed to be deleted remain in the database, an error is raised.

## Usage
To execute the procedure manually as a dev, use the following SQL command:
```sql
CALL sp_cleanup_publication_police_data();
