# RFC-010: Ephemeral Environments - Developer Experience

## Summary

Define the developer workflow, interface, and processes for creating, managing, and using ephemeral environments in the VOL application development lifecycle.

## Problem

While RFC-009 addresses the technical implementation of ephemeral environments, the developer experience needs careful design to ensure:

1. **Usability**: The process for creating and managing environments must be intuitive and frictionless for development and QA team members
2. **Visibility**: Teams need clear visibility into environment status, availability, and ownership
3. **Communication**: Changes to environments must be effectively communicated across teams
4. **Governance**: Appropriate controls must be in place to manage resource usage and cleanup
5. **Documentation**: Developers need clear guidance on best practices and troubleshooting

## Proposal

Create a comprehensive developer experience for ephemeral environments suitable for all team members to use.

### Trigger Mechanisms

Three ways of spinning up an ephemeral environment are envisioned:

#### 1. Pull Request Labels

-   When a developer creates a PR and adds an `ephemeral-env` label, an environment is automatically provisioned
-   The environment updates automatically with new commits to the PR
-   Environment is destroyed when PR is merged/closed or label is removed

**Benefits**:

-   Seamless integration with code review process
-   Clear visibility of environment status in PR

#### 2. Manual Workflow Dispatch

-   GitHub workflow with form for environment parameters:
    -   Branch name
    -   Environment name
    -   TTL (time-to-live)
-   Suitable for creating environments without PRs

**Benefits**:

-   Flexibility for testing branches without PRs
-   Support for demonstration environments
-   Custom TTL settings

#### 3. CLI Package

-   Using the proposed `@vol-app/ephemeral-env` package directly from the command line:
    ```
    npm run ephemeral -- create --branch=feature/new-payment-system
    ```

**Benefits**:

-   Quick to implement, no web UI to design, and no hosting needed vs a web dashboard for environment status/management
-   Command-line workflow similar to existing refresh local script
-   Leverages the existing AWS and github authenticaion available on developers systems in their ENV
-   Efficient for developers already working in terminal

### User Interfaces

#### 1. CLI Extensions

Extend the workspace with a new package `@vol-app/ephemeral-env` that provides command-line tools for managing ephemeral environments, building on our existing authentication env vars:

```
npm run ephemeral -- [command] [options]
```

**Core Commands:**

-   **list**: Display all active ephemeral environments with status, expiry, and URLs

    ```
    npm run ephemeral -- list [--json]
    ```

-   **create**: Provision a new ephemeral environment

    ```
    npm run ephemeral -- create --name=[name] --branch=[branch] --ttl=[days] [--pr=[pr-number]]
    ```

-   **destroy**: Remove an ephemeral environment and all its resources

    ```
    npm run ephemeral -- destroy [name] [--force]
    ```

-   **info**: Show detailed information about a specific environment

    ```
    npm run ephemeral -- info [name]
    ```

-   **exec**: Connect to a container in the environment (similar to existing ECS helper)

    ```
    npm run ephemeral -- exec [name] [service]
    ```

-   **extend**: Extend the TTL of an existing environment
    ```
    npm run ephemeral -- extend [name] [days]
    ```

**Key Features:**

-   Integration with existing AWS & Github authentication
-   Interactive prompts when parameters are missing
-   Rich console output with status colours and formatting
-   JSON output option for scriptability
-   Similar to existing vol developer tools

**Benefits:**

-   Command-line workflow for advanced users
-   Self-service environment management
-   Reduced context switching (stay in terminal)
-   Automation opportunities through scripting
-   Leverages developers' existing workflow patterns
-   Some chunks of the typescript/js code or patterns may be re-useable to make a web dashboard in future

The CLI tooling will be implemented as a TypeScript package within the workspace, following the same patterns as our existing local refresh tool.

#### 2. PR Integration

Enhance GitHub PR experience with:

-   Status checks for environment deployment
-   Comments with environment details and URLs
-   Environment deletion confirmation
-   Automatic PR updates on environment status changes

#### 3. Dashboard Web App (Future Enhancement)

In a future phase, create a simple web dashboard showing:

-   Active environments
-   Creation date and expiration
-   Owner/associated PR
-   Resource utilisation
-   Access URLs
-   Actions (extend, destroy, refresh)

**Benefits**:

-   Accessible to non-technical team members who do not have a local stack installed or AWS / Github creds available
-   Central visibility of all environments
-   Quick access to environment URLs
-   Resource usage monitoring

### Workflow Examples

#### Developer Creating Feature Environment

1. Developer creates a feature branch: `git checkout -b feature/new-payment-system`
2. Makes changes and pushes: `git push origin feature/new-payment-system`
3. Creates a PR on GitHub from this branch to main
4. Adds the `ephemeral-env` label to the PR
5. Automatic comment appears in PR with environment details:

    ```
    🚀 Ephemeral environment deployed!
    URL: https://feature-new-payment-system.ephemeral.olcs.dev-dvsacloud.uk
    Expires: In 7 days (05/25/2025)

    Services:
    - API: https://api.feature-new-payment-system.ephemeral.olcs.dev-dvsacloud.uk
    - Self-service: https://ssweb.feature-new-payment-system.ephemeral.olcs.dev-dvsacloud.uk
    - Internal: https://iuweb.feature-new-payment-system.ephemeral.olcs.dev-dvsacloud.uk
    ```

6. When done, removes the label or closes the PR

#### QA Team Member Testing a Feature

1. QA identifies the environment they need to test
2. If needed, extends the TTL using the CLI:
    ```
    npm run ephemeral -- extend feature-new-payment-system 3
    ```
3. Completes testing and adds results to the PR
4. Optionally destroys the environment when done:
    ```
    npm run ephemeral -- destroy feature-new-payment-system
    ```

#### Demo Environment for Stakeholders

1. Team lead uses manual workflow dispatch
2. Specifies:
    - Branch: `feature/new-ui`
    - Name: `demo-2025-05-18`
    - TTL: 2 days
3. Shares environment URL with stakeholders
4. Environment auto-destroys after demo

### CI/CD and Testing Workflows

Unlike the standard progressive deployment pipeline (DEV → INT → PREP → PROD), ephemeral environments will use a dedicated CI/CD workflow that combines automated and manual testing approaches.

#### 1. Automated Testing

When a developer pushes changes to a feature branch with an ephemeral environment:

-   **Validation**: Code linting and unit tests run automatically
-   **Deployment**: Changes are automatically deployed to the ephemeral environment
-   **Smoke Tests**: Basic functionality tests run to verify core features work

Example notifications in PR:

```
✅ Code validation passed
🚀 Changes deployed to environment: feature-123
🧪 Smoke tests passed (6 tests, 0 failures)
```

#### 2. Manual Test Triggers

Developers and QA can trigger additional test suites through:

-   The CLI tool: `npm run ephemeral -- run-tests feature-branch regression`
-   Workflow dispatch interface with test suite selection

#### 3. Test Suite Options

-   **Smoke**: Quick validation of critical paths (5-10 minutes)
-   **Regression**: Comprehensive test suite (30-60 minutes)
-   **Performance**: NFT Gatling testing for specific features

#### 4. Results Integration

Test results are integrated back into:

-   PR comments with summary and links to detailed reports
-   CLI tool output with test summary information

This hybrid approach gives developers immediate feedback through automated tests while allowing QA teams to run deeper validation when needed, all without impacting other environments.

### Access Management

1. **Authentication**:
    - Reuse existing Cognito user pools
    - Standard test accounts across all environments

### Documentation and Training

1. **Developer Guide**:
    - Environment creation workflows
    - Best practices for usage
    - Troubleshooting steps
    - Resource limitations

### Governance

1. **Resource Management**:

    - Limit of 10 concurrent environments ?
    - Default TTL of 7 days (extendable) ?
    - Approvals needed to spin up envs? Who?
    - Automated cleanup of expired environments

2. **Notifications**:
    - Creation confirmation
    - Expiration warnings (48h, 24h, 4h before)

## Implementation

### Phase 1: Core User Experience

-   Implement GitHub PR label trigger
-   Create basic PR status and comments
-   Develop initial documentation

### Phase 2: Advanced Interaction

-   Implement PR comment commands
-   Create manual workflow dispatch form
-   Extend CLI with environment commands

### Phase 3: Dashboard & Visibility

-   Implement notifications
-   Create usage reporting

### Phase 4: Training & Adoption

-   Create training materials
-   Conduct team training sessions
-   Gather feedback and refine process

Future: Develop web status dashboard

## Implementation Tickets

Here are the key developer experience implementation tasks needed to realize ephemeral environments:

### Developer Experience

**Extend CLI tools for environment management**  
Add new commands to existing CLI tooling that allow developers to manage ephemeral environments from the command line. This includes implementing commands for listing, creating, destroying, and modifying environments. The CLI extension should provide consistent output formats, robust error handling, and comprehensive help documentation.

**Create documentation for ephemeral environments**  
Develop comprehensive documentation covering all aspects of ephemeral environments. This includes architectural overviews, usage guides for different team roles, troubleshooting information, best practices, and limitations. Documentation should be published in an accessible format and include examples, diagrams, and practical workflows.

**Implement PR comment bot for environment actions**  
Create a bot that allows for controlling ephemeral environments via PR comments. This involves developing the comment parsing logic, implementing security controls, handling command execution, and providing feedback through PR comments. The bot should understand commands like "/deploy-env", "/destroy-env", and "/extend-env" with appropriate parameters.

### Rollout and Training

**Create training materials for ephemeral environments**  
Develop comprehensive training materials including documentation, video tutorials, and hands-on exercises. These materials should cover all aspects of ephemeral environment usage including creation, management, testing workflows, and troubleshooting. Different versions should be tailored to the needs of developers, QA engineers, and other stakeholders.

**Conduct pilot with selected development team**  
Roll out ephemeral environments to a single development team for initial validation in a real-world context. This involves providing dedicated support, collecting detailed feedback, monitoring for issues, and iterating on the implementation based on the team's experience. Success criteria should be clearly defined and measured throughout the pilot.

**Full rollout to all development teams**  
Expand availability of ephemeral environments to all development teams, supported by documentation, training sessions, and office hours for questions. This includes communicating the rollout plan, scheduling training sessions, monitoring adoption metrics, and establishing ongoing support channels. The rollout should be phased to ensure adequate support for each team.

## Success Criteria

1. Developers can create environments quickly on demand
2. Environment provisioning is reliable
3. Environments are properly cleaned up when no longer needed

By creating an intuitive and flexible developer experience for ephemeral environments, we will significantly improve the development and testing workflow for the VOL application, leavibg work streams blocked less often increasing productivity.
