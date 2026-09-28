import { themes } from "prism-react-renderer";
import type { Config } from "@docusaurus/types";
import type * as Preset from "@docusaurus/preset-classic";

const lightTheme = themes.github;
const darkTheme = themes.dracula;

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

const config: Config = {
  title: "VOL Application",
  tagline: "VOL documentation",
  favicon: "img/favicon.ico",
  url: "https://dvsa.github.io",
  baseUrl: "/vol-app/",
  organizationName: "dvsa",
  projectName: "vol-app",
  trailingSlash: false,

  onBrokenLinks: "throw",
  onBrokenMarkdownLinks: "warn",

  // en-GB rather than en: Docusaurus formats the per-page "Last updated" date in this locale.
  i18n: {
    defaultLocale: "en-GB",
    locales: ["en-GB"],
  },

  presets: [
    [
      "classic",
      {
        docs: {
          sidebarPath: require.resolve("./sidebars.js"),
          routeBasePath: "/",
          path: "../docs",
          editUrl: "https://github.com/dvsa/vol-app/tree/main/docs/",
          // Taken from the last commit to touch each page, so the deploy workflow's checkout needs
          // full history (fetch-depth: 0) - a shallow clone would date every page to the latest commit.
          showLastUpdateTime: true,
        },
        blog: false,
        theme: {
          customCss: require.resolve("./src/css/custom.css"),
        },
      },
    ],
  ],

  themeConfig: {
    image: "img/docusaurus-social-card.jpg",
    navbar: {
      title: "VOL Application",
      logo: {
        alt: "Drive and Vehicle Standards Agency logo",
        src: "img/logo.svg",
      },
      items: [
        {
          to: "/",
          label: "Docs",
          position: "left",
        },
        {
          href: "https://github.com/dvsa/vol-app",
          label: "GitHub",
          position: "right",
        },
      ],
    },
    footer: {
      style: "dark",
      copyright:
        "All content is available under the Open Government Licence v3.0, except where otherwise stated." +
        `<br>Built ${builtAt} (UK time)${builtFrom}.`,
    },
    prism: {
      theme: lightTheme,
      darkTheme: darkTheme,
      additionalLanguages: ["hcl"],
    },
  } satisfies Preset.ThemeConfig,

  markdown: {
    mermaid: true,
  },

  themes: ["@docusaurus/theme-mermaid"],
};

module.exports = config;
