import * as fs from "fs";
import * as path from "path";

export type ValidationMode = "permissive" | "standard" | "strict";

export interface Config {
  port: number;
  validationMode: ValidationMode;
  issuerUrl: string;
  logLevel: string;
  jwtPrivateKey: string;
  jwtPublicKey: string;
  jwtAlgorithm: "RS256" | "HS256";
  keyId: string;
  ecPrivateKey: string;
  ecPublicKey: string;
  ecKeyId: string;
  environment: string;
}

// Simple key loading - keys are committed to repo
const loadKey = (filename: string): string => {
  try {
    const keyPath = path.join(__dirname, "..", "..", filename);
    return fs.readFileSync(keyPath, "utf8");
  } catch (error) {
    console.error(`Error loading ${filename}:`, error);
    return "";
  }
};

export const config: Config = {
  port: parseInt(process.env.PORT || "8080", 10),
  validationMode: (process.env.VALIDATION_MODE || "permissive") as ValidationMode,
  issuerUrl: process.env.ISSUER_URL || `http://localhost:${process.env.PORT || "8080"}`,
  logLevel: process.env.LOG_LEVEL || "info",
  environment: process.env.NODE_ENV || "development",

  // Load RSA keys for JWT signing (main tokens)
  jwtPrivateKey: process.env.JWT_PRIVATE_KEY || loadKey("mock-private.pem"),
  jwtPublicKey: process.env.JWT_PUBLIC_KEY || loadKey("mock-public.pem"),
  jwtAlgorithm: "RS256", // Use RS256 for proper OAuth/OIDC compliance
  keyId: "mock-key-1", // Key ID for JWKS

  // Load EC keys for coreIdentityJWT signing
  ecPrivateKey: loadKey("mock-ec-private.pem"),
  ecPublicKey: loadKey("mock-ec-public.pem"),
  ecKeyId: "mock-ec-key-1",
};

// Log configuration on startup
export function logConfig(): void {
  console.log("🚀 Mock GOV.UK Sign In Service Starting");
  console.log("================================");
  console.log(`Port: ${config.port}`);
  console.log(`Validation Mode: ${config.validationMode}`);
  console.log(`Issuer URL: ${config.issuerUrl}`);
  console.log(`Environment: ${config.environment}`);
  console.log("================================");

  if (config.validationMode === "permissive") {
    console.log("⚠️  PERMISSIVE MODE ACTIVE");
    console.log("   - Accepting any client credentials");
    console.log("   - Auto-fixing missing parameters");
    console.log("   - Not validating signatures");
    console.log("   - Perfect for local development!");
    console.log("================================");
  }
}
