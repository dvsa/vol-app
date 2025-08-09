export interface AuthorizationRequest {
  client_id: string;
  redirect_uri: string;
  response_type: string;
  scope: string;
  state: string;
  nonce?: string;
  vtr?: string;
  claims?: string;
}

export interface TokenRequest {
  grant_type: string;
  code: string;
  client_id: string;
  client_secret?: string;
  redirect_uri?: string;
}

export interface AuthCode {
  code: string;
  clientId: string;
  redirectUri: string;
  state: string;
  nonce?: string;
  email: string;
  scenario: UserScenario;
  createdAt: Date;
}

export interface UserScenario {
  email: string;
  vot: "P0" | "P1" | "P2";
  firstName: string;
  familyName: string;
  birthDate: string;
  success: boolean;
  errorType?: string;
}

export interface NamePart {
  type: "GivenName" | "FamilyName";
  value: string;
}

export interface CoreIdentity {
  vot: string;
  vc: {
    credentialSubject: {
      name: Array<{
        validUntil: string | null;
        nameParts: NamePart[];
      }>;
      birthDate: Array<{
        value: string;
      }>;
    };
  };
}

export interface UserInfo {
  "https://vocab.account.gov.uk/v1/coreIdentityJWT": string;
  "https://vocab.account.gov.uk/v1/coreIdentityJWT:decoded": CoreIdentity;
}
