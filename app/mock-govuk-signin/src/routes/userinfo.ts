import { Router } from "express";
import { storageService } from "../services/storageService";
import { tokenService } from "../services/tokenService";

const router = Router();

router.get("/userinfo", (req, res) => {
  // Extract bearer token
  const authHeader = req.headers.authorization;

  if (!authHeader || !authHeader.startsWith("Bearer ")) {
    return res.status(401).json({
      error: "invalid_token",
      error_description: "Bearer token required",
    });
  }

  const accessToken = authHeader.substring(7);

  // Get token data from storage
  const tokenData = storageService.getAccessTokenData(accessToken);

  if (!tokenData) {
    return res.status(401).json({
      error: "invalid_token",
      error_description: "Access token is invalid or expired",
    });
  }

  // Generate userinfo response with core identity
  const userInfo = tokenService.createUserInfo(tokenData.scenario, tokenData.clientId, tokenData.sub);

  console.log(`👤 UserInfo requested for ${tokenData.email}`);

  res.json(userInfo);
});

export default router;
