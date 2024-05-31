import { themes } from "prism-react-renderer";
import type { Config } from "@docusaurus/types";
import type * as Preset from "@docusaurus/preset-classic";

const lightTheme = themes.github;
const darkTheme = themes.dracula;

const config: Config = {
  title: "VOL Application",
  tagline: "VOL documentation",
  favicon: "img/favicon.ico",
  url: "https://dvsa.github.io",
  baseUrl: "/",
  organizationName: "dvsa",
  projectName: "vol-app",
  trailingSlash: false,

  onBrokenLinks: "throw",
  onBrokenMarkdownLinks: "warn",

  i18n: {
    defaultLocale: "en",
    locales: ["en"],
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
      copyright: "All content is available under the Open Government Licence v3.0, except where otherwise stated.",
    },
    prism: {
      theme: lightTheme,
      darkTheme: darkTheme,
    },
  } satisfies Preset.ThemeConfig,

  markdown: {
    mermaid: true,
  },

  themes: ["@docusaurus/theme-mermaid"],
};

module.exports = config;
