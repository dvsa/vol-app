# RFC: Simplify Database Objects Management

## Summary
Move management of database views, stored procedures and triggers from Liquibase to a simpler file-based approach in vol-app that only updates objects when their content changes.

## Motivation
- Reduce complexity by removing Liquibase dependency
- Simplify deployment process
- Utilize vol-app as single source of truth

## Detailed Design

### Directory Structure
Move SQL files from migrations repo into vol-app:
```
vol-app/
└── sql/
    ├── procedures/
    │   ├── sp_populate_irhp_expired.sql
    │   └── ...
    ├── views/
    │   ├── vw_active_licenses.sql
    │   └── ...
    └── triggers/
        ├── tr_address_ad.sql
        └── ...
```

### State Tracking
Add table to track deployed state:
```sql
CREATE TABLE db_objects_state (
    filepath VARCHAR(255) PRIMARY KEY,
    file_hash CHAR(64) NOT NULL,    -- SHA-256 of file content
    last_updated DATETIME NOT NULL
);
```

### Deployment Process
1. Scan SQL directories for files
2. For each file:
    - Calculate SHA-256 hash of file content
    - Compare against stored hash
    - If different or not present, execute SQL file
    - Update state table with new hash
3. Execute in dependency order:
    1. Views (may depend on other views)
    2. Stored Procedures
    3. Triggers

### Implementation

Over-simplified example implementation:
```php
class DatabaseObjectManager
{
    public function sync(): void
    {
        $views = $this->scanDirectory('sql/views');
        $procedures = $this->scanDirectory('sql/procedures'); 
        $triggers = $this->scanDirectory('sql/triggers');

        // Deploy in order
        $this->processBatch($views);
        $this->processBatch($procedures);
        $this->processBatch($triggers);
    }

    private function processBatch(array $files): void 
    {
        foreach ($files as $file) {
            $currentHash = hash_file('sha256', $file);
            $storedHash = $this->getStoredHash($file);

            if ($currentHash !== $storedHash) {
                $this->executeSqlFile($file);
                $this->updateHash($file, $currentHash);
            }
        }
    }
}
```

### Example Usage
```bash
$ ./vendor/bin/laminas db:sync-objects

Scanning SQL files...
→ Found 382 objects
  - 156 procedures
  - 89 views
  - 137 triggers

Processing changes...
→ 2 changes detected:
  [UPDATE] procedures/sp_populate_irhp_expired.sql
  [UPDATE] views/vw_vehicle_history.sql
  [SKIP] 380 unchanged objects

Complete!
```

## Migration Path

1. **Initial Setup**
    - Create state tracking table
    - Move SQL files from olcs-etl repo to vol-app
    - Add DatabaseObjectManager class
    - Add console command

2. **Testing**
    - Run against dev database
    - Verify all objects deploy correctly
    - Test changes trigger updates
    - Verify unchanged objects skipped

3. **Production Migration**
    - Deploy tracking table
    - Initial population from current state
    - Remove Liquibase configuration
    - Switch to new system

## Benefits

1. **Simplicity**
    - No utility shell scripts
    - No complex XML changesets
    - Simple file-based tracking
    - Clear deployment process

2**Reliability**
    - Direct file content hashing
    - Clear state tracking
    - Explicit dependencies

4. **Developer Experience**
    - SQL would live in application repo
    - Simple file changes
    - Clear deployment status
    - Easy to test changes

## Drawbacks

1. **Migration Effort**
    - Need to move files
    - Need to re-run every .sql file to test execution via PHP manager code
    - Careful production transition

2. **Dependency Management**
    - Not sure if we need to maintain a particular order when executing .sql files - TBC
