export function renderLoginPage(params: {
  client_id: string;
  redirect_uri: string;
  state: string;
  nonce: string;
  vtr: string;
  claims: string;
}): string {
  return `
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mock GOV.UK Sign In</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: "GDS Transport", Arial, sans-serif;
            font-size: 19px;
            line-height: 1.5;
            color: #0b0c0c;
            background-color: #ffffff;
        }
        
        .govuk-header {
            background-color: #0b0c0c;
            color: white;
            padding: 20px 0;
            border-bottom: 10px solid #1d70b8;
        }
        
        .govuk-header-container {
            max-width: 960px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        .govuk-header-content {
            display: flex;
            align-items: center;
        }
        
        .govuk-header-logo {
            font-size: 30px;
            font-weight: bold;
            color: white;
            text-decoration: none;
            margin-right: 10px;
        }
        
        .govuk-tag {
            background-color: #f47738;
            color: white;
            padding: 2px 8px;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 1px;
        }
        
        .govuk-main-wrapper {
            max-width: 960px;
            margin: 0 auto;
            padding: 30px 15px;
        }
        
        .govuk-warning {
            background-color: #ffdd00;
            padding: 20px;
            margin-bottom: 30px;
            border-left: 5px solid #0b0c0c;
        }
        
        .govuk-warning h2 {
            margin-bottom: 10px;
        }
        
        h1 {
            font-size: 48px;
            font-weight: bold;
            margin-bottom: 30px;
        }
        
        .govuk-form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        input[type="email"],
        input[type="password"] {
            width: 100%;
            max-width: 500px;
            padding: 10px;
            font-size: 19px;
            border: 2px solid #0b0c0c;
        }
        
        .govuk-button {
            background-color: #00703c;
            color: white;
            padding: 12px 20px;
            font-size: 19px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            margin-top: 20px;
        }
        
        .govuk-button:hover {
            background-color: #005a30;
        }
        
        .test-users {
            background-color: #f3f2f1;
            padding: 20px;
            margin-top: 40px;
            border-left: 5px solid #1d70b8;
        }
        
        .test-users h2 {
            margin-bottom: 15px;
        }
        
        .test-users ul {
            list-style: none;
            padding-left: 0;
        }
        
        .test-users li {
            margin-bottom: 10px;
            font-family: monospace;
            background-color: white;
            padding: 5px 10px;
        }
        
        .test-users .description {
            color: #505a5f;
            font-family: "GDS Transport", Arial, sans-serif;
            font-size: 16px;
        }
        
        .debug-info {
            margin-top: 40px;
            padding: 20px;
            background-color: #f8f8f8;
            border: 1px solid #b1b4b6;
            font-size: 14px;
        }
        
        .debug-info h3 {
            margin-bottom: 10px;
        }
        
        .debug-info pre {
            background-color: white;
            padding: 10px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <header class="govuk-header">
        <div class="govuk-header-container">
            <div class="govuk-header-content">
                <a href="/" class="govuk-header-logo">GOV.UK</a>
                <span class="govuk-tag">MOCK</span>
            </div>
        </div>
    </header>

    <main class="govuk-main-wrapper">
        <div class="govuk-warning">
            <h2>⚠️ Mock Service</h2>
            <p>This is a mock GOV.UK Sign In service for testing purposes only.</p>
            <p>DO NOT enter real credentials.</p>
        </div>

        <h1>Sign in</h1>
        
        <form method="POST" action="/authorize">
            <!-- Hidden OAuth parameters -->
            <input type="hidden" name="client_id" value="${params.client_id}">
            <input type="hidden" name="redirect_uri" value="${params.redirect_uri}">
            <input type="hidden" name="state" value="${params.state}">
            <input type="hidden" name="nonce" value="${params.nonce}">
            <input type="hidden" name="vtr" value="${params.vtr}">
            <input type="hidden" name="claims" value="${params.claims}">
            
            <div class="govuk-form-group">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email" required 
                       placeholder="e.g., test.success@mock.gov">
            </div>
            
            <div class="govuk-form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" 
                       value="mock-password" 
                       placeholder="Any value accepted">
                <p style="color: #505a5f; font-size: 16px; margin-top: 5px;">
                    Password is ignored - only email determines the outcome
                </p>
            </div>
            
            <button type="submit" class="govuk-button" id="sign-in-button">
                Continue
            </button>
        </form>
        
        <div class="test-users">
            <h2>Test User Accounts</h2>
            <p>Use these email addresses to simulate different scenarios:</p>
            <ul>
                <li>
                    <strong>test.success@mock.gov</strong>
                    <span class="description">- Successful P2 verification</span>
                </li>
                <li>
                    <strong>test.denied@mock.gov</strong>
                    <span class="description">- Access denied</span>
                </li>
                <li>
                    <strong>test.p1only@mock.gov</strong>
                    <span class="description">- P1 level only (insufficient for VOL)</span>
                </li>
                <li>
                    <strong>test.cancel@mock.gov</strong>
                    <span class="description">- User cancellation</span>
                </li>
                <li>
                    <strong>john.smith.1990-01-15@mock.gov</strong>
                    <span class="description">- Dynamic user with specific data</span>
                </li>
            </ul>
            <p style="margin-top: 15px; font-size: 16px;">
                <em>Any other email will be accepted as a successful P2 verification.</em>
            </p>
        </div>
        
        <div class="debug-info">
            <h3>OAuth Request Details</h3>
            <pre>${JSON.stringify(
              {
                client_id: params.client_id,
                redirect_uri: params.redirect_uri,
                state: params.state ? params.state.substring(0, 50) + "..." : "none",
                nonce: params.nonce ? "present" : "none",
                vtr: params.vtr || "none",
              },
              null,
              2,
            )}</pre>
        </div>
    </main>
</body>
</html>
`;
}
