import jwt from "jsonwebtoken";
import { config } from "../config";
import { UserScenario, CoreIdentity, UserInfo, NamePart } from "../types";

export class TokenService {
  private privateKey = config.jwtPrivateKey;
  private publicKey = config.jwtPublicKey;
  private algorithm = config.jwtAlgorithm;
  private issuer = config.issuerUrl;

  generateIdToken(clientId: string, nonce: string | undefined, scenario: UserScenario): string {
    const now = Math.floor(Date.now() / 1000);

    // Create core identity JWT
    const coreIdentityJWT = this.generateCoreIdentityJWT(scenario);

    const payload = {
      iss: this.issuer,
      sub: `mock-user-${scenario.email}`,
      aud: clientId,
      exp: now + 3600, // 1 hour
      iat: now,
      nonce: nonce,
      "https://vocab.account.gov.uk/v1/coreIdentityJWT": coreIdentityJWT,
    };

    return jwt.sign(payload, this.privateKey, { algorithm: this.algorithm });
  }

  generateAccessToken(scenario: UserScenario): string {
    const now = Math.floor(Date.now() / 1000);

    const payload = {
      iss: this.issuer,
      sub: `mock-user-${scenario.email}`,
      exp: now + 3600, // 1 hour
      iat: now,
      scope: "openid email",
    };

    return jwt.sign(payload, this.privateKey, { algorithm: this.algorithm });
  }

  generateCoreIdentityJWT(scenario: UserScenario): string {
    const now = Math.floor(Date.now() / 1000);

    const coreIdentityPayload = {
      iss: "https://identity.integration.account.gov.uk/",
      sub: `urn:uuid:${this.generateUUID()}`,
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

    return jwt.sign(coreIdentityPayload, this.privateKey, { algorithm: this.algorithm });
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

  createUserInfo(scenario: UserScenario): UserInfo {
    const coreIdentityJWT = this.generateCoreIdentityJWT(scenario);

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
    // For JWKS endpoint - simplified for mock service
    return {
      keys: [
        {
          kty: "RSA",
          use: "sig",
          kid: "mock-key-1",
          alg: this.algorithm,
          n: "mock-modulus", // In real implementation, extract from public key
          e: "AQAB",
        },
      ],
    };
  }
}

export const tokenService = new TokenService();
