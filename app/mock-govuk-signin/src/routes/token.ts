import { Router } from "express";
import express from "express";
import { validationService } from "../services/validationService";
import { storageService } from "../services/storageService";
import { tokenService } from "../services/tokenService";

const router = Router();

router.post("/token", express.json(), express.urlencoded({ extended: true }), (req, res) => {
  console.log("📝 Token request body:", JSON.stringify(req.body));
  const validation = validationService.validateTokenRequest(req.body);

  if (!validation.valid && validation.errors) {
    return res.status(400).json({
      error: "invalid_request",
      error_description: validation.errors.join(", "),
    });
  }

  const { params } = validation;

  // Get auth code from storage
  const authCode = storageService.consumeAuthCode(params.code);

  if (!authCode) {
    return res.status(400).json({
      error: "invalid_grant",
      error_description: "Authorization code is invalid or expired",
    });
  }

  // In strict mode, validate client_id matches
  if (validationService["mode"] !== "permissive" && authCode.clientId !== params.client_id) {
    return res.status(400).json({
      error: "invalid_client",
      error_description: "Client ID mismatch",
    });
  }

  // Generate tokens (use the client_id from the original auth request, not the token request)
  const accessToken = tokenService.generateAccessToken(authCode.scenario, authCode.clientId);
  const idToken = tokenService.generateIdToken(authCode.clientId, authCode.nonce, authCode.scenario);

  // Store access token data for userinfo endpoint
  storageService.storeAccessToken(accessToken, {
    email: authCode.email,
    scenario: authCode.scenario,
    clientId: authCode.clientId,
    sub: `mock-user-${authCode.scenario.email}`,
  });

  console.log(`🎫 Tokens issued for ${authCode.email}`);

  // Return tokens
  res.json({
    access_token: accessToken,
    token_type: "Bearer",
    expires_in: 3600,
    id_token: idToken,
    scope: "openid email",
  });
});

export default router;
