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
  console.log("📋 Raw POST body:", req.body);

  // Clean up form data - remove ^ characters that seem to be incorrectly added
  const cleanedBody: any = {};
  for (const [key, value] of Object.entries(req.body)) {
    const cleanKey = key.replace(/\^/g, "");
    const cleanValue = typeof value === "string" ? value.replace(/\^/g, "") : value;
    cleanedBody[cleanKey] = cleanValue;
  }

  console.log("🧹 Cleaned body:", cleanedBody);

  const { email, password, ...oauthParams } = cleanedBody;

  // Validate email is present
  if (!email) {
    console.error("❌ Email is missing from form data");
    return res.status(400).json({ error: "Bad Request", message: "Email is required" });
  }

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

  // Generate stateless auth code JWT with embedded data
  const authCode = storageService.createAuthCode({
    clientId: oauthParams.client_id,
    redirectUri: oauthParams.redirect_uri,
    state: oauthParams.state,
    nonce: oauthParams.nonce,
    email: email,
    scenario: scenario,
  });

  console.log(`📝 Generated stateless auth code for ${email}`);

  // Redirect back to client with auth code
  const successParams = new URLSearchParams({
    code: authCode,
    state: oauthParams.state,
  });

  const redirectUrl = `${oauthParams.redirect_uri}?${successParams}`;
  console.log(`✅ Redirecting to: ${redirectUrl}`);

  res.redirect(redirectUrl);
});

export default router;
