import express, { Router } from "express";
import { validationService } from "../services/validationService";
import { userService } from "../services/userService";
import { storageService } from "../services/storageService";
import { renderLoginPage } from "../views/login";
import { renderErrorPage } from "../views/error";
import { AuthCode } from "../types";

const router = Router();

// GET /authorize - Display login page
router.get("/authorize", (req, res) => {
  const validation = validationService.validateAuthorizationRequest(req.query as any);

  if (!validation.valid && validation.errors) {
    // In permissive mode, this won't happen
    return res.status(400).send(renderErrorPage("Invalid Request", validation.errors));
  }

  const { params } = validation;

  // Render login page with OAuth parameters
  const html = renderLoginPage({
    client_id: params.client_id,
    redirect_uri: params.redirect_uri,
    state: params.state,
    nonce: params.nonce || "",
    vtr: params.vtr || "",
    claims: params.claims || "",
  });

  res.send(html);
});

// POST /authorize - Process login
router.post("/authorize", express.urlencoded({ extended: true }), (req, res) => {
  const { email, password, ...oauthParams } = req.body;

  // Get user scenario based on email
  const scenario = userService.getUserScenario(email);

  console.log(`🔐 Login attempt: ${email} -> ${scenario.success ? "SUCCESS" : scenario.errorType}`);

  // Handle failure scenarios
  if (!scenario.success) {
    const errorParams = new URLSearchParams({
      error: scenario.errorType || "access_denied",
      error_description: `Mock service: ${scenario.errorType}`,
      state: oauthParams.state,
    });

    return res.redirect(`${oauthParams.redirect_uri}?${errorParams}`);
  }

  // Generate and store auth code
  const authCode: AuthCode = {
    code: storageService.generateAuthCode(),
    clientId: oauthParams.client_id,
    redirectUri: oauthParams.redirect_uri,
    state: oauthParams.state,
    nonce: oauthParams.nonce,
    email: email,
    scenario: scenario,
    createdAt: new Date(),
  };

  storageService.storeAuthCode(authCode);

  // Redirect back to client with auth code
  const successParams = new URLSearchParams({
    code: authCode.code,
    state: oauthParams.state,
  });

  const redirectUrl = `${oauthParams.redirect_uri}?${successParams}`;
  console.log(`✅ Redirecting to: ${redirectUrl}`);

  res.redirect(redirectUrl);
});

export default router;
