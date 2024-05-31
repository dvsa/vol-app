## Data Cleanup Process Overview

The `sp_dr_cleanup` systematically cleans up data across related tables in the database, based on the data retention rules. This procedure is integral to maintaining the integrity and performance of the system by removing outdated records, and helps remain compliant with data protection and retention legislation.

### Key Steps in the Cleanup Process

1. **Initialization:**
    - The procedure starts by checking system parameters to ensure that data deletion is not disabled. It sets some system parameters necessary for the run, including disabling foreign key checks and triggers for the remainder of the run.

2. **Transaction Management:**
    - A transaction is started to ensure that all changes are atomic and can be rolled back in case of any errors during the execution process. This is planned to be removed soon, to allow the DR process to run while the system remains online (albeit at a v.quiet time)

3. **Temporary Tables Creation:**
    - Temporary tables are created to manage the data that needs to be processed. These tables help isolate and track which records are to be deleted without affecting the live data during the cleanup process.

4. **Deletion Criteria Setup:**
    - Records eligible for deletion are identified based on the rules specified in the `data_retention` table and related `data_retention_rule`. This includes checking fields like `action_confirmation` and `deleted_date`.

5. **Dependency Management:**
    - The script handles dependencies carefully by ensuring that related records in dependent tables are also considered for deletion. This includes cascading effects where deleting a record in one table may affect records in multiple related tables.

6. **Record Deletion:**
    - Actual deletion of records occurs from various tables, based on the dependencies and relationships defined.

7. **Verification and Cleanup:**
    - After deletion, the script verifies the number of records deleted and ensures that the counts match the expected numbers. Temporary tables are used to compare pre and post-deletion record counts.

8. **Transaction Completion:**
    - If the `dry_run` parameter is set to `FALSE`, the changes are committed to the database. If `dry_run` is `TRUE`, all changes are rolled back, allowing the operation to be tested without affecting actual data.

9. **Finalization:**
    - The environment is reset by re-enabling foreign key checks and triggers. Temporary tables are dropped to clean up the session.

### Error Handling

- The procedure includes robust error handling mechanisms that catch SQL exceptions during execution. These exceptions trigger a rollback of the transaction, ensuring that the database state remains consistent and no partial updates are committed.
- The above mechanism will not be possible when running without transactions, some consideration should be given to the impact of this change.

### Usage

- This procedure can be executed with parameters to specify the user ID performing the cleanup, the limit on the number of records to process, and whether the operation should be a dry run.
