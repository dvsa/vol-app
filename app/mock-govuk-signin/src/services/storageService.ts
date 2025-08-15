import jwt from "jsonwebtoken";
import { config } from "../config";
import { AuthCode } from "../types";

/**
 * Stateless storage service that encodes all state in JWTs
 * This allows the mock service to run in Lambda without any persistent storage
 */
export class StorageService {
  private privateKey = config.jwtPrivateKey;
  private publicKey = config.jwtPublicKey;
  private algorithm = config.jwtAlgorithm;

  /**
   * Generate a self-contained authorization code JWT
   * VOL never validates this, just passes it back to us
   */
  storeAuthCode(authCode: AuthCode): void {
    // No-op: auth code is already self-contained
    // The code itself IS the storage
    console.log(`📝 Created stateless auth code for ${authCode.email}`);
  }

  /**
   * Generate authorization code as a JWT containing all needed data
   */
  generateAuthCode(): string {
    // This will be called by authorize route, which will encode the data
    // For now, return a placeholder that will be replaced
    return "placeholder";
  }

  /**
   * Create auth code JWT with embedded data
   */
  createAuthCode(authCode: Omit<AuthCode, "code" | "createdAt">): string {
    const now = Math.floor(Date.now() / 1000);

    const payload = {
      type: "auth_code",
      email: authCode.email,
      scenario: authCode.scenario,
      clientId: authCode.clientId,
      nonce: authCode.nonce,
      redirectUri: authCode.redirectUri,
      state: authCode.state,
      iat: now,
      exp: now + 600, // 10 minutes
    };

    // Sign with our private key
    return jwt.sign(payload, this.privateKey, { algorithm: this.algorithm });
  }

  /**
   * Decode and validate an authorization code JWT
   */
  consumeAuthCode(code: string): AuthCode | undefined {
    try {
      const decoded = jwt.verify(code, this.publicKey, {
        algorithms: [this.algorithm],
      }) as any;

      // Check it's an auth code
      if (decoded.type !== "auth_code") {
        console.log(`❌ Invalid code type: ${decoded.type}`);
        return undefined;
      }

      console.log(`✅ Consumed stateless auth code for ${decoded.email}`);

      // JWT.verify already checks expiration, return as AuthCode format
      return {
        code: code,
        email: decoded.email,
        scenario: decoded.scenario,
        clientId: decoded.clientId,
        nonce: decoded.nonce,
        redirectUri: decoded.redirectUri,
        state: decoded.state,
        createdAt: new Date(decoded.iat * 1000),
      };
    } catch (error: any) {
      if (error.name === "TokenExpiredError") {
        console.log(`❌ Auth code expired`);
      } else {
        console.error("Invalid auth code:", error.message);
      }
      return undefined;
    }
  }

  /**
   * Access tokens are already self-contained JWTs
   * This method is now a no-op since data is embedded in the token
   */
  storeAccessToken(token: string, data: any): void {
    // No-op: access token already contains all data in its claims
    console.log(`📝 Access token is self-contained for user: ${data.email}`);
  }

  /**
   * Extract user data from the access token JWT
   * The token already contains all needed data in custom claims
   */
  getAccessTokenData(token: string): any | undefined {
    try {
      // Decode without verification (we trust our own tokens)
      // VOL has already validated the signature before sending it back
      const decoded = jwt.decode(token) as any;

      if (!decoded) {
        console.log(`❌ Could not decode access token`);
        return undefined;
      }

      // Check if it's one of our mock tokens with embedded data
      if (!decoded.mock_email || !decoded.mock_scenario) {
        console.log(`❌ Access token missing mock data`);
        return undefined;
      }

      // Check expiration manually
      if (decoded.exp && decoded.exp < Math.floor(Date.now() / 1000)) {
        console.log(`❌ Access token expired`);
        return undefined;
      }

      return {
        email: decoded.mock_email,
        scenario: decoded.mock_scenario,
        clientId: decoded.client_id || decoded.aud,
        sub: decoded.sub,
      };
    } catch (error) {
      console.error("Error decoding access token:", error);
      return undefined;
    }
  }

  // These methods remain for compatibility but don't need storage
  getAuthCode(code: string): AuthCode | undefined {
    // Try to decode the JWT
    return this.consumeAuthCode(code);
  }

  generateAccessToken(): string {
    // This is not used anymore - tokenService generates the JWT directly
    return "unused";
  }

  // Cleanup method for testing (no-op in stateless mode)
  clear(): void {
    console.log("📝 Stateless storage - nothing to clear");
  }

  // Stats for monitoring (always empty in stateless mode)
  getStats(): any {
    return {
      authCodes: 0, // No storage
      accessTokens: 0, // No storage
      mode: "stateless",
    };
  }
}

export const storageService = new StorageService();
