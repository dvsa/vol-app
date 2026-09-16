import { config } from "../config";
import { AuthorizationRequest, TokenRequest } from "../types";

export class ValidationService {
  private mode = config.validationMode;

  validateAuthorizationRequest(params: Partial<AuthorizationRequest>): {
    valid: boolean;
    params: AuthorizationRequest;
    errors?: string[];
  } {
    const errors: string[] = [];

    if (this.mode === "permissive") {
      // Auto-fix everything in permissive mode
      const fixed: AuthorizationRequest = {
        client_id: params.client_id || "mock-client-id",
        redirect_uri: params.redirect_uri || "http://localhost/callback",
        response_type: params.response_type || "code",
        scope: params.scope || "openid",
        state: params.state || "auto-generated-state-" + Date.now(),
        nonce: params.nonce,
        vtr: params.vtr,
        claims: params.claims,
      };

      console.log("✅ Permissive mode: Auto-fixed authorization request");
      if (!params.client_id) console.log("   - Generated client_id:", fixed.client_id);
      if (!params.redirect_uri) console.log("   - Generated redirect_uri:", fixed.redirect_uri);
      if (!params.state) console.log("   - Generated state:", fixed.state);

      return { valid: true, params: fixed };
    }

    if (this.mode === "standard") {
      // Check required fields exist but be lenient
      if (!params.client_id) errors.push("Missing client_id");
      if (!params.redirect_uri) errors.push("Missing redirect_uri");
      if (!params.state) errors.push("Missing state");

      if (errors.length > 0) {
        console.warn("⚠️  Standard mode: Missing parameters but continuing:", errors);
      }

      // Use defaults for missing values
      const params_with_defaults: AuthorizationRequest = {
        client_id: params.client_id || "default-client",
        redirect_uri: params.redirect_uri || "http://localhost/callback",
        response_type: params.response_type || "code",
        scope: params.scope || "openid",
        state: params.state || "default-state",
        nonce: params.nonce,
        vtr: params.vtr,
        claims: params.claims,
      };

      return { valid: true, params: params_with_defaults, errors };
    }

    // Strict mode
    if (!params.client_id) errors.push("client_id is required");
    if (!params.redirect_uri) errors.push("redirect_uri is required");
    if (!params.response_type) errors.push("response_type is required");
    if (!params.scope) errors.push("scope is required");
    if (!params.state) errors.push("state is required");

    if (params.response_type && params.response_type !== "code") {
      errors.push("Only response_type=code is supported");
    }

    if (params.redirect_uri) {
      try {
        new URL(params.redirect_uri);
      } catch {
        errors.push("redirect_uri must be a valid URL");
      }
    }

    return {
      valid: errors.length === 0,
      params: params as AuthorizationRequest,
      errors: errors.length > 0 ? errors : undefined,
    };
  }

  validateTokenRequest(params: Partial<TokenRequest>): {
    valid: boolean;
    params: TokenRequest;
    errors?: string[];
  } {
    const errors: string[] = [];

    if (this.mode === "permissive") {
      // Accept anything in permissive mode
      const fixed: TokenRequest = {
        grant_type: params.grant_type || "authorization_code",
        code: params.code || "mock-code",
        client_id: params.client_id || "mock-client-id",
        client_secret: params.client_secret || "mock-secret",
        redirect_uri: params.redirect_uri,
      };

      console.log("✅ Permissive mode: Auto-fixed token request");
      return { valid: true, params: fixed };
    }

    if (this.mode === "standard") {
      // Check but be lenient
      if (!params.grant_type) errors.push("Missing grant_type");
      if (!params.code) errors.push("Missing code");
      if (!params.client_id) errors.push("Missing client_id");

      if (errors.length > 0) {
        console.warn("⚠️  Standard mode: Missing parameters but continuing:", errors);
      }

      const params_with_defaults: TokenRequest = {
        grant_type: params.grant_type || "authorization_code",
        code: params.code || "",
        client_id: params.client_id || "",
        client_secret: params.client_secret,
        redirect_uri: params.redirect_uri,
      };

      return { valid: true, params: params_with_defaults, errors };
    }

    // Strict mode
    if (!params.grant_type) errors.push("grant_type is required");
    if (!params.code) errors.push("code is required");
    if (!params.client_id) errors.push("client_id is required");
    if (!params.client_secret) errors.push("client_secret is required");

    if (params.grant_type && params.grant_type !== "authorization_code") {
      errors.push("Only grant_type=authorization_code is supported");
    }

    return {
      valid: errors.length === 0,
      params: params as TokenRequest,
      errors: errors.length > 0 ? errors : undefined,
    };
  }

  validateStateToken(token: string): { valid: boolean; decoded?: any } {
    if (this.mode === "permissive") {
      // Don't validate JWT in permissive mode
      try {
        // Try to decode without verification
        const parts = token.split(".");
        if (parts.length === 3) {
          const decoded = JSON.parse(Buffer.from(parts[1], "base64").toString());
          return { valid: true, decoded };
        }
      } catch {
        // Even if decode fails, accept it in permissive mode
      }
      return { valid: true, decoded: { journey: "unknown", id: "unknown" } };
    }

    // In standard/strict mode, at least try to decode
    try {
      const parts = token.split(".");
      if (parts.length !== 3) {
        return { valid: false };
      }
      const decoded = JSON.parse(Buffer.from(parts[1], "base64").toString());
      return { valid: true, decoded };
    } catch {
      return { valid: false };
    }
  }
}

export const validationService = new ValidationService();
