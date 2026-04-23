import { Router } from "express";
import { tokenService } from "../services/tokenService";

const router = Router();

router.get("/jwks", (req, res) => {
  res.json(tokenService.getPublicKey());
});

export default router;
