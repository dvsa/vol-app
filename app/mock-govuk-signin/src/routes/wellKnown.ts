import { Router } from "express";
import { config } from "../config";
import * as crypto from "crypto";
import { KeyObject } from "crypto";

const router = Router();

// Convert EC public key PEM to JWK format
function ecPublicKeyToJwk(publicKeyPem: string): any {
  try {
    const keyObject: KeyObject = crypto.createPublicKey(publicKeyPem);
    const jwk = keyObject.export({ format: "jwk" });

    // Add algorithm to the JWK
    return {
      ...jwk,
      alg: "ES256",
    };
  } catch (error) {
    console.error("Error converting EC public key to JWK:", error);
    // Return a fallback JWK for development
    return {
      kty: "EC",
      crv: "P-256",
      x: "mock-x-coordinate",
      y: "mock-y-coordinate",
      alg: "ES256",
    };
  }
}

// DID Document endpoint for coreIdentityJWT verification
router.get("/.well-known/did.json", (req, res) => {
  const jwk = ecPublicKeyToJwk(config.ecPublicKey);

  // Build DID URLs dynamically from issuerUrl for environment compatibility
  const issuerHost = new URL(config.issuerUrl).host;
  const didId = `did:web:${issuerHost}`;
  const keyId = `${didId}#${config.ecKeyId}`;

  const didDocument = {
    "@context": ["https://www.w3.org/ns/did/v1", "https://w3id.org/security/jwk/v1"],
    id: didId,
    assertionMethod: [
      {
        type: "JsonWebKey",
        id: keyId,
        controller: didId,
        publicKeyJwk: jwk,
      },
    ],
  };

  res.json(didDocument);
});

// OpenID Connect Discovery endpoint
router.get("/.well-known/openid-configuration", (req, res) => {
  const discovery = {
    issuer: config.issuerUrl,
    authorization_endpoint: `${config.issuerUrl}/authorize`,
    token_endpoint: `${config.issuerUrl}/token`,
    userinfo_endpoint: `${config.issuerUrl}/userinfo`,
    jwks_uri: `${config.issuerUrl}/jwks`,
    response_types_supported: ["code"],
    subject_types_supported: ["public"],
    id_token_signing_alg_values_supported: ["RS256"],
    scopes_supported: ["openid", "email", "profile"],
    token_endpoint_auth_methods_supported: ["client_secret_basic", "client_secret_post"],
    claims_supported: ["sub", "email", "email_verified", "name", "given_name", "family_name"],
  };

  res.json(discovery);
});

export default router;
