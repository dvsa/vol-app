import express from "express";
import cors from "cors";
import discoveryRouter from "./routes/discovery";
import authorizeRouter from "./routes/authorize";
import tokenRouter from "./routes/token";
import userinfoRouter from "./routes/userinfo";
import jwksRouter from "./routes/jwks";
import healthRouter from "./routes/health";

export function createApp(): express.Application {
  const app = express();

  // Middleware
  app.use(cors());
  app.use(express.json());
  app.use(express.urlencoded({ extended: true }));

  // Logging middleware
  app.use((req, res, next) => {
    console.log(`${new Date().toISOString()} ${req.method} ${req.path}`);
    next();
  });

  // Routes
  app.use(discoveryRouter);
  app.use(authorizeRouter);
  app.use(tokenRouter);
  app.use(userinfoRouter);
  app.use(jwksRouter);
  app.use(healthRouter);

  // Root endpoint
  app.get("/", (req, res) => {
    res.redirect("/mock-info");
  });

  // 404 handler
  app.use((req, res) => {
    res.status(404).json({
      error: "Not Found",
      message: `Endpoint ${req.path} not found`,
      available_endpoints: [
        "/.well-known/openid-configuration",
        "/authorize",
        "/token",
        "/userinfo",
        "/jwks",
        "/health",
        "/mock-info",
      ],
    });
  });

  // Error handler
  app.use((err: Error, req: express.Request, res: express.Response, next: express.NextFunction) => {
    console.error("Error:", err);
    res.status(500).json({
      error: "Internal Server Error",
      message: err.message,
    });
  });

  return app;
}
