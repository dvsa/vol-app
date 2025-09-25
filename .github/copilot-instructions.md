# GitHub Copilot Code Review Instructions

## Review Coverage

**IMPORTANT**: Please review ALL files in the pull request, even if there are more than 31 files. Continue reviewing until you have examined every changed file.

## OLCS Entity Generator Context

This project uses an automated Doctrine entity generator that creates entity classes from database schema. Understanding this context is crucial for effective code review.

### File Types to Review

#### 1. Auto-Generated Abstract Entity Files (`Abstract*.php`)

These files are automatically generated and **SHOULD BE REVIEWED** for errors:

**Check for:**

- ✅ Correct Doctrine annotation syntax (e.g., `@ORM\Table(name="table_name")` not `@ORM\Table(name="table_name" * )`)
- ✅ Valid PHP syntax and formatting
- ✅ Proper property types and default values
- ✅ Correct pluralization of collection properties (ManyToMany should be plural: `$grounds`, `$actionTypes`)
- ✅ Proper getter/setter method names matching property names
- ✅ Consistent annotation formatting
- ✅ Proper use of nullable/non-nullable annotations

**Files Pattern**: `/app/api/module/Api/src/Entity/*/Abstract*.php`

#### 2. Concrete Entity Files (`[EntityName].php`)

These files contain business logic and are **NOT modified** by the generator:

- Only created if they don't exist
- Never overwritten once they exist
- Should rarely appear in entity generator PRs

#### 3. Generator Code

**Always review thoroughly:**

- Generator logic: `/app/api/module/Cli/src/Service/EntityGenerator/`
- Templates: `/app/api/module/Cli/src/Service/EntityGenerator/Templates/`
- Configuration: `/app/api/data/db/EntityConfig.php`

## OLCS-Specific Patterns (These are EXPECTED/CORRECT)

### Database & Entity Patterns

- **Reference tables without AUTO_INCREMENT**: Some tables (e.g., `bus_service_type`, `sub_category`) intentionally have `$id = 0` defaults without `@ORM\GeneratedValue` - this is correct for lookup tables
- **OLCS field types**: `yesno`, `yesnonull` are custom Doctrine types specific to this project
- **Legacy OLBS columns**: Tables may have `olbs_key`, `olbs_type`, etc. - these are migration artifacts

### Property Naming

- **ManyToMany collections**: Should be plural (`$grounds`, `$actionTypes`, `$variationReasons`)
- **EntityConfig overrides**: Property names are often defined in EntityConfig.php `inversedBy` configuration
- **Collection initialization**: Properties are initialized in `initCollections()` method

### Annotation Patterns

- **SoftDeleteable**: Many entities use `@Gedmo\SoftDeleteable(fieldName="deletedDate")`
- **Blameable**: Created/modified tracking with `@Gedmo\Blameable`
- **Complex table annotations**: Tables with indexes/constraints have multi-line `@ORM\Table` annotations

## Common Issues to Flag

### Syntax Errors

- Malformed Doctrine annotations
- Extra characters in annotations (like `* )` instead of `)`)
- Invalid PHP syntax

### Logic Errors

- Singular collection property names (should be plural)
- Missing or incorrect annotation parameters
- Inconsistent property initialization

### Generator Issues

- Template syntax errors
- Incorrect property name resolution
- Missing annotation generation

## Review Priority

1. **High**: Syntax errors, malformed annotations, incorrect property pluralization
2. **Medium**: Inconsistent formatting, missing documentation
3. **Low**: Style preferences (the generator follows established patterns)

Remember: Focus on catching errors in the generated code while understanding that many patterns that might seem unusual are intentional design choices for this legacy system.
