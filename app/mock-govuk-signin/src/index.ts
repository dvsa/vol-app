import express from "express";
import axios from "axios";
import { createApp } from "./app";
import { config, logConfig } from "./config";

// Check if we're in local proxy mode
if (process.env.LOCAL_PROXY === "true") {
  // Local development: API Gateway proxy mode
  const proxy = express();
  proxy.use(express.json());
  proxy.use(express.urlencoded({ extended: true }));

  proxy.all("*", async (req, res) => {
    // Handle body based on content type
    let body: string | undefined;
    if (req.body) {
      const contentType = req.get("content-type") || "";
      if (contentType.includes("application/x-www-form-urlencoded")) {
        // For form data, send as URL-encoded string
        body = new URLSearchParams(req.body).toString();
      } else {
        // For JSON and other types, stringify
        body = JSON.stringify(req.body);
      }
    }

    const lambdaEvent = {
      version: "2.0",
      routeKey: "$default",
      rawPath: req.path,
      rawQueryString: req.url.split("?")[1] || "",
      headers: req.headers,
      requestContext: {
        http: {
          method: req.method,
          path: req.path,
          protocol: req.protocol,
          sourceIp: req.ip,
          userAgent: req.get("user-agent"),
        },
        accountId: "anonymous",
        apiId: "local",
        domainName: req.hostname,
        domainPrefix: "local",
        requestId: Math.random().toString(36).substring(7),
        routeKey: "$default",
        stage: "$default",
        time: new Date().toISOString(),
        timeEpoch: Date.now(),
      },
      body: body,
      isBase64Encoded: false,
    };

    try {
      // Call RIE running in same container (use 127.0.0.1 for reliability)
      const response = await axios.post("http://127.0.0.1:8080/2015-03-31/functions/function/invocations", lambdaEvent);

      res.status(response.data.statusCode || 200);
      Object.entries(response.data.headers || {}).forEach(([key, value]) => {
        res.set(key, value as string);
      });
      res.send(response.data.body);
    } catch (error) {
      console.error("Proxy error:", error);
      res.status(502).send("Bad Gateway");
    }
  });

  proxy.listen(80, () => {
    console.log("🚀 API Gateway proxy running on port 80 → Lambda RIE on 8080");
  });
} else if (!process.env.AWS_LAMBDA_RUNTIME_API && !process.env.AWS_LAMBDA_FUNCTION_NAME) {
  // Direct Express mode for backward compatibility (only if not in Lambda)
  const app = createApp();
  app.listen(config.port, () => {
    logConfig();
    console.log(`
🌐 Mock service ready at: http://localhost:${config.port}
📋 View test users at: http://localhost:${config.port}/mock-info
    `);
  });
}
