import { UserScenario } from "../types";

export class UserService {
  getUserScenario(email: string): UserScenario {
    // Predefined test scenarios
    const scenarios: Record<string, UserScenario> = {
      "test.success@mock.gov": {
        email,
        vot: "P2",
        firstName: "Test",
        familyName: "User",
        birthDate: "1990-01-15",
        success: true,
      },
      "test.denied@mock.gov": {
        email,
        vot: "P0",
        firstName: "Denied",
        familyName: "User",
        birthDate: "1990-01-01",
        success: false,
        errorType: "access_denied",
      },
      "test.p1only@mock.gov": {
        email,
        vot: "P1",
        firstName: "Limited",
        familyName: "User",
        birthDate: "1985-06-15",
        success: true,
      },
      "test.cancel@mock.gov": {
        email,
        vot: "P0",
        firstName: "Cancelled",
        familyName: "User",
        birthDate: "1990-01-01",
        success: false,
        errorType: "user_cancelled",
      },
      "test.timeout@mock.gov": {
        email,
        vot: "P0",
        firstName: "Timeout",
        familyName: "User",
        birthDate: "1990-01-01",
        success: false,
        errorType: "session_timeout",
      },
    };

    // Check for predefined scenario
    if (scenarios[email]) {
      return scenarios[email];
    }

    // Dynamic user parsing: john.smith.1990-01-15@mock.gov
    const dynamicMatch = email.match(/^(.+?)\.(.+?)\.(\d{4}-\d{2}-\d{2})@mock\.gov$/);
    if (dynamicMatch) {
      return {
        email,
        vot: "P2",
        firstName: this.capitalize(dynamicMatch[1]),
        familyName: this.capitalize(dynamicMatch[2]),
        birthDate: dynamicMatch[3],
        success: true,
      };
    }

    // Simpler dynamic format: john.smith@mock.gov
    const simpleMatch = email.match(/^(.+?)\.(.+?)@mock\.gov$/);
    if (simpleMatch) {
      return {
        email,
        vot: "P2",
        firstName: this.capitalize(simpleMatch[1]),
        familyName: this.capitalize(simpleMatch[2]),
        birthDate: "1990-01-01", // Default date
        success: true,
      };
    }

    // Default: treat any email as success with generated data
    const parts = email.split("@")[0].split(".");
    return {
      email,
      vot: "P2",
      firstName: parts[0] ? this.capitalize(parts[0]) : "Test",
      familyName: parts[1] ? this.capitalize(parts[1]) : "User",
      birthDate: "1990-01-01",
      success: true,
    };
  }

  private capitalize(str: string): string {
    return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
  }

  getTestUsersInfo(): any {
    return {
      predefined: [
        { email: "test.success@mock.gov", description: "Successful P2 verification" },
        { email: "test.denied@mock.gov", description: "Access denied" },
        { email: "test.p1only@mock.gov", description: "P1 level only (insufficient)" },
        { email: "test.cancel@mock.gov", description: "User cancellation" },
        { email: "test.timeout@mock.gov", description: "Session timeout" },
      ],
      dynamic: [
        { format: "firstname.lastname.YYYY-MM-DD@mock.gov", description: "Dynamic user with specific DOB" },
        { format: "firstname.lastname@mock.gov", description: "Dynamic user with default DOB (1990-01-01)" },
      ],
      note: "Any other email will be accepted as a successful P2 verification with generated data",
    };
  }
}

export const userService = new UserService();
