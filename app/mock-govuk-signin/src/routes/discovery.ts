import { Router } from "express";
import { config } from "../config";

const router = Router();

router.get("/.well-known/openid-configuration", (req, res) => {
  const discovery = {
    issuer: config.issuerUrl,
    authorization_endpoint: `${config.issuerUrl}/authorize`,
    token_endpoint: `${config.issuerUrl}/token`,
    userinfo_endpoint: `${config.issuerUrl}/userinfo`,
    jwks_uri: `${config.issuerUrl}/jwks`,
    scopes_supported: ["openid", "email"],
    response_types_supported: ["code"],
    response_modes_supported: ["query"],
    grant_types_supported: ["authorization_code"],
    subject_types_supported: ["public"],
    id_token_signing_alg_values_supported: [config.jwtAlgorithm],
    token_endpoint_auth_methods_supported: ["client_secret_post", "client_secret_basic"],
    claims_supported: ["sub", "iss", "aud", "exp", "iat", "nonce", "https://vocab.account.gov.uk/v1/coreIdentityJWT"],

    // Custom field to identify this as a mock service
    _mock_service: {
      version: "1.0.0",
      validation_mode: config.validationMode,
      environment: config.environment,
      info_endpoint: `${config.issuerUrl}/mock-info`,
    },
  };

  res.json(discovery);
});

export default router;
