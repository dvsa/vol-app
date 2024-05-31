### sp_populate_queue_table_items_to_delete

#### Parameters:
- `p_user_id INT`: User ID of the person executing the procedure.

#### Record Selection Criteria for Deletion:
- **Entity:** `queue`
- **Status Filter:** Targets queue items that have a status of 'que_sts_complete'.
- **Date Filter:** Utilizes the `last_modified_on` or `created_on` date to check if the queue item's record is older than the retention period . The filter checks:
    - If `last_modified_on` is NULL, then it compares the `created_on` date.
    - Otherwise, it uses the `last_modified_on` date.

#### Summary:
- This procedure identifies completed queue items that have not been updated or interacted with past the specified retention period.
