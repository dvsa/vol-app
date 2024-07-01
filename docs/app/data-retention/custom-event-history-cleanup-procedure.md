---
sidebar_position: 5
---
# Custom Event History Cleanup

## `sp_custom_event_history_cleanup` Procedure Overview

## Purpose
The `sp_custom_event_history_cleanup` stored procedure is designed to cleanse the `event_history` table by removing entries that are no longer linked to any active records across various entities. This procedure helps maintain data integrity and reduces storage overhead by eliminating orphaned records.

## Parameters
- **`p_user_id` (INT):** The ID of the user performing the cleanup operation.
- **`p_limit` (INT):** The maximum number of records to delete in one execution.

## Behavior
- **Error Handling:** If an SQL exception occurs during execution, the procedure will rollback any changes, log the error with a detailed message, and halt.
- **Conditional Execution:** The procedure checks if it is enabled through a configuration in the `data_retention_rule` table. If it is disabled, an error is raised, and no deletion occurs.

## Deletion Criteria
Records in the `event_history` table will be deleted if all the following fields are `NULL`, indicating that they are not linked to any existing entities:
- `licence_id`
- `application_id`
- `transport_manager_id`
- `organisation_id`
- `case_id`
- `bus_reg_id`
- `account_id`
- `task_id`
- `irhp_application_id`
- `entity_pk`
- `entity_type`

## Execution
To execute the procedure manually, a dev can use the following SQL command:
```sql
CALL sp_custom_event_history_cleanup(@user_id, @limit);
