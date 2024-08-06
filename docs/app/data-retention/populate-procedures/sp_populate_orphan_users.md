### sp_populate_orphan_users

#### Parameters:
- `p_user_id INT`: User ID of the person executing the procedure.

#### Record Selection Criteria for Deletion:
- **Entity:** `user`
- **Activity Filter:** Targets users who have not logged in since a specified period defined by `v_retention_period` months ago. This condition also accounts for users who have never logged in since their creation if `last_login_at` is NULL.
- **Association Filter:** Excludes users who are associated with any `organisation_user` entries, ensuring that only truly orphaned users are targeted. Further, users are excluded if they have associations with local authorities, partner contact details, teams, and certain specified user IDs (1 and 4, reserved for administrative or system accounts).
- **Expiry Date Filter:** Utilizes the `last_login_at` or `created_on` dates to determine if the user's record has surpassed the retention period without activity or association, qualifying them as stale or orphaned.

#### Summary:
- This procedure identifies users who are considered orphaned due to lack of login activity, absence of associations with organisations, teams, local authorities, or partners, and are not specially protected user IDs. 
