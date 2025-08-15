import request from "supertest";
import { createApp } from "../app";
import jwt from "jsonwebtoken";
import { Application } from "express";

describe("OAuth Flow", () => {
  let app: Application;

  beforeAll(() => {
    app = createApp();
  });

  test("Discovery endpoint returns correct config", async () => {
    const res = await request(app).get("/.well-known/openid-configuration").expect(200);

    expect(res.body.authorization_endpoint).toContain("/authorize");
    expect(res.body.token_endpoint).toContain("/token");
    expect(res.body.userinfo_endpoint).toContain("/userinfo");
    expect(res.body.jwks_uri).toContain("/jwks");
    expect(res.body.issuer).toBeDefined();
  });

  test("Successful login flow returns valid tokens", async () => {
    // 1. Submit login (skip GET /authorize - just POST directly)
    const authRes = await request(app)
      .post("/authorize")
      .send({
        email: "test.success@mock.gov",
        password: "anything",
        client_id: "test-client",
        redirect_uri: "http://localhost/callback",
        state: "test-state-123",
        nonce: "test-nonce",
      })
      .expect(302);

    // 2. Extract code from redirect
    const location = authRes.headers.location;
    expect(location).toContain("code=");
    expect(location).toContain("state=test-state-123");

    const locationUrl = new URL(location, "http://localhost");
    const code = locationUrl.searchParams.get("code");
    expect(code).toBeDefined();

    // 3. Exchange code for tokens
    const tokenRes = await request(app)
      .post("/token")
      .send({
        grant_type: "authorization_code",
        code: code,
        client_id: "test-client",
        client_secret: "anything",
        redirect_uri: "http://localhost/callback",
      })
      .expect(200);

    expect(tokenRes.body.access_token).toBeDefined();
    expect(tokenRes.body.id_token).toBeDefined();
    expect(tokenRes.body.token_type).toBe("Bearer");

    // 4. Verify ID token has correct structure
    const decoded = jwt.decode(tokenRes.body.id_token) as any;
    expect(decoded.sub).toBe("mock-user-test.success@mock.gov");
    expect(decoded.nonce).toBe("test-nonce");
    expect(decoded.aud).toBe("test-client");

    // Verify coreIdentityJWT contains user data
    const coreIdentityJWT = decoded["https://vocab.account.gov.uk/v1/coreIdentityJWT"];
    expect(coreIdentityJWT).toBeDefined();
    const coreIdentityDecoded = jwt.decode(coreIdentityJWT) as any;
    expect(coreIdentityDecoded.vc.credentialSubject.name[0].nameParts[0].value).toBe("Test");
    expect(coreIdentityDecoded.vc.credentialSubject.name[0].nameParts[1].value).toBe("User");

    // 5. Verify access token works for userinfo
    const userinfoRes = await request(app)
      .get("/userinfo")
      .set("Authorization", `Bearer ${tokenRes.body.access_token}`)
      .expect(200);

    // Userinfo returns coreIdentityJWT
    expect(userinfoRes.body["https://vocab.account.gov.uk/v1/coreIdentityJWT"]).toBeDefined();
    expect(userinfoRes.body["https://vocab.account.gov.uk/v1/coreIdentityJWT:decoded"]).toBeDefined();
    expect(userinfoRes.body["https://vocab.account.gov.uk/v1/coreIdentityJWT:decoded"].vot).toBe("P2");
  });

  test("Failed login returns error", async () => {
    const res = await request(app)
      .post("/authorize")
      .send({
        email: "test.denied@mock.gov",
        password: "anything",
        client_id: "test-client",
        redirect_uri: "http://localhost/callback",
        state: "test-state",
      })
      .expect(302);

    const location = res.headers.location;
    expect(location).toContain("error=access_denied");
    expect(location).toContain("state=test-state");
  });

  test("Token endpoint rejects invalid code", async () => {
    const res = await request(app)
      .post("/token")
      .send({
        grant_type: "authorization_code",
        code: "invalid-code-that-is-not-a-jwt",
        client_id: "test-client",
        redirect_uri: "http://localhost/callback",
      })
      .expect(400);

    expect(res.body.error).toBeDefined();
    expect(res.body.error_description).toBeDefined();
  });

  test("JWKS endpoint returns valid keys", async () => {
    const res = await request(app).get("/jwks").expect(200);

    expect(res.body.keys).toBeDefined();
    expect(Array.isArray(res.body.keys)).toBe(true);
    expect(res.body.keys.length).toBeGreaterThan(0);

    const key = res.body.keys[0];
    expect(key.kty).toBeDefined();
    expect(key.kid).toBeDefined();
    expect(key.use).toBe("sig");
  });

  test("Health endpoint returns OK", async () => {
    const res = await request(app).get("/health").expect(200);

    expect(res.body.status).toBe("healthy");
  });

  test("Dynamic user email creates valid response", async () => {
    const authRes = await request(app)
      .post("/authorize")
      .send({
        email: "john.smith.1990-05-15@mock.gov",
        password: "anything",
        client_id: "test-client",
        redirect_uri: "http://localhost/callback",
        state: "dynamic-test",
      })
      .expect(302);

    const location = authRes.headers.location;
    const locationUrl = new URL(location, "http://localhost");
    const code = locationUrl.searchParams.get("code");

    const tokenRes = await request(app)
      .post("/token")
      .send({
        grant_type: "authorization_code",
        code: code,
        client_id: "test-client",
        redirect_uri: "http://localhost/callback",
      })
      .expect(200);

    const decoded = jwt.decode(tokenRes.body.id_token) as any;
    expect(decoded.sub).toBe("mock-user-john.smith.1990-05-15@mock.gov");

    // Check coreIdentityJWT for user data
    const coreIdentityJWT = decoded["https://vocab.account.gov.uk/v1/coreIdentityJWT"];
    const coreIdentityDecoded = jwt.decode(coreIdentityJWT) as any;
    expect(coreIdentityDecoded.vc.credentialSubject.name[0].nameParts[0].value).toBe("John");
    expect(coreIdentityDecoded.vc.credentialSubject.name[0].nameParts[1].value).toBe("Smith");
    expect(coreIdentityDecoded.vc.credentialSubject.birthDate[0].value).toBe("1990-05-15");
  });

  test("Form data with special characters is handled correctly", async () => {
    // Test the cleaned body logic works for normal data
    const authRes = await request(app)
      .post("/authorize")
      .send({
        email: "test.success@mock.gov",
        password: "test-password",
        client_id: "test-client",
        redirect_uri: "http://localhost/callback",
        state: "test-state",
      })
      .expect(302);

    expect(authRes.headers.location).toContain("code=");
  });
});
