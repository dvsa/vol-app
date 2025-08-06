import { AuthCode } from "../types";
import { v4 as uuidv4 } from "uuid";

export class StorageService {
  private authCodes: Map<string, AuthCode> = new Map();
  private accessTokens: Map<string, any> = new Map();

  // Auth codes expire after 10 minutes
  private AUTH_CODE_TTL = 10 * 60 * 1000;
  // Access tokens expire after 1 hour
  private ACCESS_TOKEN_TTL = 60 * 60 * 1000;

  storeAuthCode(authCode: AuthCode): void {
    this.authCodes.set(authCode.code, authCode);

    // Auto-cleanup after TTL
    setTimeout(() => {
      this.authCodes.delete(authCode.code);
    }, this.AUTH_CODE_TTL);

    console.log(`📝 Stored auth code: ${authCode.code} for ${authCode.email}`);
  }

  getAuthCode(code: string): AuthCode | undefined {
    const authCode = this.authCodes.get(code);

    if (authCode) {
      // Check if expired
      const age = Date.now() - authCode.createdAt.getTime();
      if (age > this.AUTH_CODE_TTL) {
        this.authCodes.delete(code);
        console.log(`❌ Auth code expired: ${code}`);
        return undefined;
      }
    }

    return authCode;
  }

  consumeAuthCode(code: string): AuthCode | undefined {
    const authCode = this.getAuthCode(code);
    if (authCode) {
      // Auth codes are single-use
      this.authCodes.delete(code);
      console.log(`✅ Consumed auth code: ${code}`);
    }
    return authCode;
  }

  storeAccessToken(token: string, data: any): void {
    this.accessTokens.set(token, {
      data,
      createdAt: new Date(),
    });

    // Auto-cleanup after TTL
    setTimeout(() => {
      this.accessTokens.delete(token);
    }, this.ACCESS_TOKEN_TTL);

    console.log(`📝 Stored access token for user: ${data.email}`);
  }

  getAccessTokenData(token: string): any | undefined {
    const stored = this.accessTokens.get(token);

    if (stored) {
      // Check if expired
      const age = Date.now() - stored.createdAt.getTime();
      if (age > this.ACCESS_TOKEN_TTL) {
        this.accessTokens.delete(token);
        console.log(`❌ Access token expired`);
        return undefined;
      }
      return stored.data;
    }

    return undefined;
  }

  generateAuthCode(): string {
    return "code_" + uuidv4();
  }

  generateAccessToken(): string {
    return "at_" + uuidv4();
  }

  // Cleanup method for testing
  clear(): void {
    this.authCodes.clear();
    this.accessTokens.clear();
  }

  // Stats for monitoring
  getStats(): any {
    return {
      authCodes: this.authCodes.size,
      accessTokens: this.accessTokens.size,
    };
  }
}

export const storageService = new StorageService();
