export type ValidationMode = "permissive" | "standard" | "strict";

export interface Config {
  port: number;
  validationMode: ValidationMode;
  issuerUrl: string;
  logLevel: string;
  jwtPrivateKey: string;
  jwtPublicKey: string;
  jwtAlgorithm: "RS256" | "HS256";
  environment: string;
}

export const config: Config = {
  port: parseInt(process.env.PORT || "8080", 10),
  validationMode: (process.env.VALIDATION_MODE || "permissive") as ValidationMode,
  issuerUrl: process.env.ISSUER_URL || `http://localhost:${process.env.PORT || "8080"}`,
  logLevel: process.env.LOG_LEVEL || "info",
  environment: process.env.NODE_ENV || "development",

  // In permissive mode, we'll auto-generate keys if not provided
  jwtPrivateKey: process.env.JWT_PRIVATE_KEY || "mock-private-key-for-testing",
  jwtPublicKey: process.env.JWT_PUBLIC_KEY || "mock-public-key-for-testing",
  jwtAlgorithm: "HS256", // Use HS256 for simplicity in mock
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
