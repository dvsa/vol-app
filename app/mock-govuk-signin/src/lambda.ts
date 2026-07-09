import serverlessExpress from "@codegenie/serverless-express";
import { createApp } from "./app";

// Create the Express app
const app = createApp();

// Lambda handler that wraps the Express app
export const handler = serverlessExpress({ app });
