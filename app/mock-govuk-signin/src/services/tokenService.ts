import jwt from "jsonwebtoken";
import { config } from "../config";
import { UserScenario, CoreIdentity, UserInfo, NamePart } from "../types";

export class TokenService {
  private privateKey = config.jwtPrivateKey;
  private publicKey = config.jwtPublicKey;
  private algorithm = config.jwtAlgorithm;
  private ecPrivateKey = config.ecPrivateKey;
  private ecPublicKey = config.ecPublicKey;
  private issuer = config.issuerUrl;

  generateIdToken(clientId: string, nonce: string | undefined, scenario: UserScenario): string {
    const now = Math.floor(Date.now() / 1000);
    const idTokenSub = `mock-user-${scenario.email}`;

    // Create core identity JWT
    const coreIdentityJWT = this.generateCoreIdentityJWT(scenario, clientId, idTokenSub);

    const payload = {
      iss: this.issuer,
      sub: idTokenSub,
      aud: clientId,
      exp: now + 3600, // 1 hour
      iat: now,
      nonce: nonce,
      "https://vocab.account.gov.uk/v1/coreIdentityJWT": coreIdentityJWT,
    };

    return jwt.sign(payload, this.privateKey, {
      algorithm: this.algorithm,
      keyid: config.keyId,
    });
  }

  generateAccessToken(scenario: UserScenario, clientId: string): string {
    const now = Math.floor(Date.now() / 1000);

    const payload = {
      // Standard OAuth claims that VOL validates
      iss: this.issuer,
      sub: `mock-user-${scenario.email}`,
      aud: clientId,
      client_id: clientId,
      exp: now + 3600, // 1 hour
      iat: now,
      scope: "openid email",

      // Mock-specific data (VOL ignores these)
      // These allow us to be stateless - the token carries its own context
      mock_email: scenario.email,
      mock_scenario: scenario,
    };

    return jwt.sign(payload, this.privateKey, {
      algorithm: this.algorithm,
      keyid: config.keyId,
    });
  }

  generateCoreIdentityJWT(scenario: UserScenario, clientId: string, idTokenSub: string): string {
    const now = Math.floor(Date.now() / 1000);

    const coreIdentityPayload = {
      iss: this.issuer, // Use our mock service URL as issuer
      sub: idTokenSub, // Must match the ID token's sub
      aud: clientId, // Must match the client_id
      nbf: now,
      exp: now + 3600,
      vot: scenario.vot,
      vtm: "https://oidc.integration.account.gov.uk/trustmark",
      vc: {
        type: ["VerifiableCredential", "IdentityCheckCredential"],
        credentialSubject: {
          name: [
            {
              validUntil: null,
              nameParts: this.generateNameParts(scenario),
            },
          ],
          birthDate: [
            {
              value: scenario.birthDate,
            },
          ],
        },
      },
    };

    // Sign with EC key using ES256 algorithm
    // Build kid dynamically from issuerUrl for environment compatibility
    const issuerHost = new URL(this.issuer).host;
    const kid = `did:web:${issuerHost}#${config.ecKeyId}`;

    return jwt.sign(coreIdentityPayload, this.ecPrivateKey, {
      algorithm: "ES256",
      keyid: kid,
    });
  }

  generateNameParts(scenario: UserScenario): NamePart[] {
    const parts: NamePart[] = [];

    // Split first name by spaces for multiple given names
    const givenNames = scenario.firstName.split(" ");
    for (const name of givenNames) {
      parts.push({
        type: "GivenName",
        value: name,
      });
    }

    // Add family name
    parts.push({
      type: "FamilyName",
      value: scenario.familyName,
    });

    return parts;
  }

  createUserInfo(scenario: UserScenario, clientId: string, sub: string): UserInfo {
    const coreIdentityJWT = this.generateCoreIdentityJWT(scenario, clientId, sub);

    // Decode the JWT to get the payload
    const decoded = jwt.decode(coreIdentityJWT) as any;

    return {
      "https://vocab.account.gov.uk/v1/coreIdentityJWT": coreIdentityJWT,
      "https://vocab.account.gov.uk/v1/coreIdentityJWT:decoded": {
        vot: decoded.vot,
        vc: decoded.vc,
      },
    };
  }

  verifyToken(token: string): any {
    try {
      return jwt.verify(token, this.publicKey, { algorithms: [this.algorithm] });
    } catch (error) {
      console.error("Token verification failed:", error);
      return null;
    }
  }

  private generateUUID(): string {
    // Simple UUID v4 generation
    return "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g, (c) => {
      const r = (Math.random() * 16) | 0;
      const v = c === "x" ? r : (r & 0x3) | 0x8;
      return v.toString(16);
    });
  }

  getPublicKey(): any {
    // Convert PEM to JWK format for JWKS endpoint
    const crypto = require("crypto");

    try {
      // Parse the public key
      const keyObject = crypto.createPublicKey(this.publicKey);
      const keyExport = keyObject.export({ format: "jwk" });

      return {
        keys: [
          {
            ...keyExport,
            kid: config.keyId,
            use: "sig",
            alg: this.algorithm,
          },
        ],
      };
    } catch (error) {
      console.error("Error converting public key to JWK:", error);
      // Fallback for development
      return {
        keys: [
          {
            kty: "RSA",
            use: "sig",
            kid: config.keyId,
            alg: this.algorithm,
            n: "mock-modulus",
            e: "AQAB",
          },
        ],
      };
    }
  }
}

export const tokenService = new TokenService();
