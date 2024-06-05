---
sidebar_position: 1
---
# Data Retention System Overview

The Data Retention system is designed to manage the lifecycle of records in compliance with regulatory and internal data management policies. This system uses a set of "populate rules" to determine when records are eligible for deletion, ensuring data is handled securely and efficiently.

### Key Components

#### Data Retention Rules

- **Table:** `data_retention_rule`
- **Description:** Stores the rules that define how data retention is handled for different types of entity. Each rule specifies the retention period, the maximum dataset size for consideration in a single run, and whether the rule is currently enabled.
- **Key Fields:**
    - `id`: Unique identifier for the rule.
    - `description`: A brief description of what the rule does.
    - `retention_period`: How long (in months) to keep the data after record closure.
    - `max_data_set`: Limits the number of rows affected in a single operation.
    - `is_enabled`: Indicates if the rule is active.
    - `action_type`: Defines the type of action (e.g., automatic deletion, manual review).
    - `populate_procedure`: The stored procedure associated with populating data for this rule.

#### Data Retention Control Table

- **Table:** `data_retention`
- **Description:** Records information about data eligible for retention actions. It is populated by the stored procedures indexed in the  `data_retention_rule` table,
- **Key Fields:**
    - `entity_name`, `entity_pk`: Identify the database record to delete.
    - `data_retention_rule_id`: Links to the applicable retention rule that populated the record.
    - `action_confirmation`: Indicates whether the deletion action is confirmed by an admin user.
    - `assigned_to`: User ID for accountability.
    - `next_review_date`: When the record should next be reviewed.
    - `actioned_date`: When the deletion took place for this record.

### Process Flow

1. **Rule Execution:**
    - Stored procedures defined per rule in `populate_procedure` are executed regularly. These procedures evaluate records against their respective retention rules and populate the `data_retention` table with records eligible for deletion or review.

2. **Admin UI Interaction:**
    - An administrative UI in the `olcs-internal` web app allows users to manage retention rules and review records flagged for manual action.

3. **Automated and Manual Deletion:**
    - Records flagged for automatic deletion are processed in scheduled runs of the `sp_dr_cleanup` stored procedure, which handles the deletion of data and cleanup of any dependent records.
    - Records requiring manual approval are not deleted in these runs until manually reviewed and confirmed through the admin UI, then deleted in subsequent cleanup runs.

### Security and Compliance

- The system is designed to ensure compliance with data protection regulations by allowing records to be deleted after per-entity retention periods have expired, and allows for review and manual intervention when necessary.

### Conclusion

The Data Retention system provides a framework for managing the lifecycle of data within the organisation. By automating data cleanup the system helps maintain data hygiene and regulatory compliance efficiently.
