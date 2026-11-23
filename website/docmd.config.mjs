import { fileURLToPath } from "node:url";
import { buildNavigation } from "./navigation.mjs";

const docsDir = fileURLToPath(new URL("../docs/", import.meta.url));

// Stamped into the footer so a reader can tell whether a deploy has landed: it changes on every
// build, even one where no page content changed. GITHUB_SHA is set by Actions; a local build has
// no commit to name, so it only gets the time.
const builtAt = new Date().toLocaleString("en-GB", {
  dateStyle: "long",
  timeStyle: "short",
  timeZone: "Europe/London",
});
const commit = process.env.GITHUB_SHA;
const builtFrom = commit
  ? ` from <a href="https://github.com/dvsa/vol-app/commit/${commit}">${commit.slice(0, 7)}</a>`
  : "";

export default {
  title: "VOL Application",
  url: "https://dvsa.github.io/vol-app",
  base: "/vol-app/",
  src: "../docs",
  out: "build",

  // logo.svg draws its wordmark in currentColor, which is black in an <img>; logo-dark.svg is the
  // same file with a white wordmark for dark mode.
  logo: {
    light: "img/logo.svg",
    dark: "img/logo-dark.svg",
    alt: "Driver and Vehicle Standards Agency logo",
  },
  // Just the crest, which turns white in dark mode so it shows on a dark tab bar.
  favicon: "img/favicon.svg",

  navigation: buildNavigation(docsDir),

  markdown: {
    // The docs are hard-wrapped. docmd's default turns every newline into a line break.
    breaks: false,
    // Leave quotes and dashes as written, rather than converting them to typographic ones.
    typographer: false,
    // GitHub-style heading anchors, so #links written against GitHub's rendering resolve.
    headingIds: "github",
  },

  theme: {
    appearance: "system",
    customCss: ["css/vol.css"],
  },

  layout: {
    // No "copy page" buttons, which put the page's Markdown on the clipboard for pasting into an AI
    // chat. (docmd embeds that Markdown in every page regardless.)
    copyWidgets: { enabled: false },
    footer: {
      branding: false,
      copyright: "",
      content:
        "All content is available under the Open Government Licence v3.0, except where otherwise stated." +
        `<br>Built ${builtAt} (UK time)${builtFrom}.`,
    },
  },

  plugins: {
    // Edit links and the "Last updated" date. Commit history is off: it shows committers'
    // Gravatar images, which would have every reader's browser request gravatar.com.
    // The deploy workflow's checkout needs full history (fetch-depth: 0), or every page is dated
    // to the latest commit.
    git: {
      repo: "https://github.com/dvsa/vol-app",
      branch: "main",
      commitHistory: false,
    },
    // Renders Docusaurus-style callouts as they were (`:::tip`, titles); see the file for why.
    "./plugins/docusaurus-callouts.mjs": {},
    // llms.txt and the OKF bundle stay on (static files in the build, as DVSA RFC-005 proposes
    // for docmd sites). The AI chat widget is off: it calls api.docmd.io from readers' browsers,
    // and RFC-005 leaves the policy on it open.
    ai: false,
    analytics: false,
    openapi: false,
    playground: false,
    mdx: false,
  },
};
