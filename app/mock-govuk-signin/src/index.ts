import { createApp } from "./app";
import { config, logConfig } from "./config";

const app = createApp();

app.listen(config.port, () => {
  logConfig();
  console.log(`
🌐 Mock service ready at: http://localhost:${config.port}
📋 View test users at: http://localhost:${config.port}/mock-info
  `);
});
