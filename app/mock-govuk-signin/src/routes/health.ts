import { Router } from "express";
import { config } from "../config";
import { storageService } from "../services/storageService";
import { userService } from "../services/userService";

const router = Router();

router.get("/health", (req, res) => {
  res.json({
    status: "healthy",
    timestamp: new Date().toISOString(),
    mode: config.validationMode,
  });
});

router.get("/mock-info", (req, res) => {
  res.json({
    service: "Mock GOV.UK Sign In",
    version: "1.0.0",
    environment: config.environment,
    validation_mode: config.validationMode,
    issuer_url: config.issuerUrl,
    endpoints: {
      discovery: "/.well-known/openid-configuration",
      authorize: "/authorize",
      token: "/token",
      userinfo: "/userinfo",
      jwks: "/jwks",
    },
    test_users: userService.getTestUsersInfo(),
    stats: storageService.getStats(),
    mode_description: {
      permissive: "Accepts any credentials, auto-fixes missing parameters",
      standard: "Validates parameters exist but continues with warnings",
      strict: "Full validation, rejects invalid requests",
    }[config.validationMode],
    note:
      config.validationMode === "permissive"
        ? "⚠️  Running in PERMISSIVE mode - perfect for local development!"
        : `Running in ${config.validationMode.toUpperCase()} mode`,
  });
});

export default router;
