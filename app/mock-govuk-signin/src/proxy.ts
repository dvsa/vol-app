import express from "express";
import axios from "axios";

// API Gateway proxy for local development
const proxy = express();
proxy.use(express.json());
proxy.use(express.urlencoded({ extended: true }));

proxy.all("*", async (req, res) => {
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
    body: req.body ? JSON.stringify(req.body) : undefined,
    isBase64Encoded: false,
  };

  try {
    // Call RIE running in same container
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
