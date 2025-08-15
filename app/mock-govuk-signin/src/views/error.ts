export function renderErrorPage(title: string, errors: string[]): string {
  return `
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - Mock GOV.UK Sign In</title>
    <style>
        body {
            font-family: "GDS Transport", Arial, sans-serif;
            font-size: 19px;
            line-height: 1.5;
            color: #0b0c0c;
            background-color: #ffffff;
            margin: 0;
            padding: 20px;
        }
        
        .error-container {
            max-width: 960px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f3f2f1;
            border-left: 5px solid #d4351c;
        }
        
        h1 {
            color: #d4351c;
            margin-bottom: 20px;
        }
        
        ul {
            margin: 20px 0;
        }
        
        li {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>${title}</h1>
        <ul>
            ${errors.map((error) => `<li>${error}</li>`).join("")}
        </ul>
        <p>This is a mock service. In permissive mode, these errors would be auto-fixed.</p>
    </div>
</body>
</html>
`;
}
