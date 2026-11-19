#!/usr/bin/env node

import fs from "node:fs/promises";
import path from "node:path";
import { execFileSync } from "node:child_process";
import { chromium } from "playwright";

// ------------------------------------------------------------
// Configuration
// ------------------------------------------------------------

const DEFAULTS = {
  old: "http://localhost:8001",
  new: "http://localhost:8000",
  page: "/Search/Results?lookfor=&type=AllFields&limit=20",
  output: "local/tools/css-report",
  width: 1588,
  height: 960,
  limit: 12,
};

const COMPONENTS = {
  body: ["body"],
  header: [
    "header",
    ".header",
    "#header",
    ".navbar",
    ".navbar-header",
  ],
  logo: [
    ".navbar-brand",
    ".logo",
    "header img",
  ],
  navigation: [
    "nav",
    ".navbar-nav",
    ".nav",
  ],
  container: [
    ".container",
    ".container-fluid",
  ],
  search: [
    ".searchForm",
    ".search-form",
    "#searchForm",
    ".searchbox",
    ".searchFormWrapper",
  ],
  searchInput: [
    "input[name='lookfor']",
    ".searchForm input[type='text']",
    ".search-form input[type='text']",
  ],
  searchButton: [
    ".searchForm button",
    ".search-form button",
  ],
  pageTitle: [
    "h1",
    ".page-header",
    ".search-header",
    ".search-header-title",
  ],
  breadcrumb: [
    ".breadcrumb",
    "nav[aria-label='breadcrumb']",
  ],
  main: [
    "main",
    "#main",
    ".mainbody",
    "#content",
  ],
  sidebar: [
    ".sidebar",
    ".sidebar-container",
    ".sidegroup",
    "#side-panel",
  ],
  facets: [
    ".facet",
    ".facet-group",
    ".facet-list",
    ".facet-title",
  ],
  results: [
    ".result",
    ".result-body",
    ".result-list",
    ".result-item",
  ],
  buttons: [
    ".btn",
  ],
  feedback: [
    "[class*='feedback']",
    "[id*='feedback']",
  ],
};

const CSS_PROPERTIES = [
  "display",
  "visibility",
  "position",
  "box-sizing",
  "top",
  "right",
  "bottom",
  "left",
  "z-index",
  "width",
  "min-width",
  "max-width",
  "height",
  "min-height",
  "max-height",
  "margin-top",
  "margin-right",
  "margin-bottom",
  "margin-left",
  "padding-top",
  "padding-right",
  "padding-bottom",
  "padding-left",
  "border-top-width",
  "border-right-width",
  "border-bottom-width",
  "border-left-width",
  "border-top-style",
  "border-bottom-style",
  "border-top-color",
  "border-bottom-color",
  "border-radius",
  "display",
  "flex",
  "flex-direction",
  "flex-wrap",
  "flex-grow",
  "flex-shrink",
  "align-items",
  "align-self",
  "justify-content",
  "gap",
  "row-gap",
  "column-gap",
  "grid-template-columns",
  "grid-template-rows",
  "float",
  "clear",
  "overflow",
  "overflow-x",
  "overflow-y",
  "font-family",
  "font-size",
  "font-weight",
  "font-style",
  "line-height",
  "letter-spacing",
  "text-align",
  "text-transform",
  "text-decoration-line",
  "white-space",
  "color",
  "background-color",
  "background-image",
  "background-size",
  "background-position",
  "opacity",
  "transform",
].filter((value, index, array) => array.indexOf(value) === index);

// ------------------------------------------------------------
// CLI arguments
// ------------------------------------------------------------

function parseArgs() {
  const args = { ...DEFAULTS };
  const argv = process.argv.slice(2);

  if (argv.includes("--help") || argv.includes("-h")) {
    console.log(`
Usage:
  node local/tools/compare-css.mjs [options]

Options:
  --old URL       Old VuFind URL (default: ${DEFAULTS.old})
  --new URL       New VuFind URL (default: ${DEFAULTS.new})
  --page PATH     Page path including query parameters
  --output DIR    Report directory
  --width N       Viewport width
  --height N      Viewport height
  --limit N       Maximum elements per component/selector
  --browser PATH  Chromium executable path
  --help          Show help

Examples:
  node local/tools/compare-css.mjs

  node local/tools/compare-css.mjs \\
    --page "/Search/Results?lookfor=theater&type=AllFields"

  node local/tools/compare-css.mjs \\
    --width 390 --height 844 \\
    --output local/tools/css-report-mobile
`);
    process.exit(0);
  }

  const numericKeys = new Set(["width", "height", "limit"]);

  for (let i = 0; i < argv.length; i += 1) {
    const token = argv[i];

    if (!token.startsWith("--")) {
      throw new Error(`Unknown argument: ${token}`);
    }

    const key = token.slice(2);
    if (!(key in args) && key !== "browser") {
      throw new Error(`Unknown option: ${token}`);
    }

    const value = argv[++i];
    if (!value || value.startsWith("--")) {
      throw new Error(`Missing value for ${token}`);
    }

    args[key] = numericKeys.has(key) ? Number(value) : value;
  }

  for (const key of numericKeys) {
    if (!Number.isInteger(args[key]) || args[key] < 1) {
      throw new Error(`--${key} must be a positive integer`);
    }
  }

  return args;
}

// ------------------------------------------------------------
// Browser
// ------------------------------------------------------------

function findBrowser(explicitPath) {
  if (explicitPath) {
    return explicitPath;
  }

  const candidates = [
    "chromium",
    "chromium-browser",
    "google-chrome",
    "google-chrome-stable",
  ];

  for (const candidate of candidates) {
    try {
      const result = execFileSync("which", [candidate], {
        encoding: "utf8",
      }).trim();

      if (result) {
        return result;
      }
    } catch {
      // Try the next candidate.
    }
  }

  throw new Error(
    "No local Chromium/Chrome found. Use --browser /path/to/chromium",
  );
}

async function writeJson(filename, data) {
  await fs.writeFile(
    filename,
    JSON.stringify(data, null, 2),
    "utf8",
  );
}

function pageUrl(base, pagePath) {
  return new URL(pagePath, `${base.replace(/\/+$/, "")}/`).href;
}

// ------------------------------------------------------------
// Capture DOM and computed CSS
// ------------------------------------------------------------

async function capture(page, url, directory, options) {
  await fs.mkdir(directory, { recursive: true });

  console.log(`  Opening ${url}`);

  const response = await page.goto(url, {
    waitUntil: "domcontentloaded",
    timeout: 30000,
  });

  if (response && !response.ok()) {
    console.warn(`  Warning: HTTP ${response.status()}`);
  }

  // Wait for stylesheets and fonts.
  await page.evaluate(async () => {
    await Promise.all(
      [...document.querySelectorAll('link[rel="stylesheet"]')]
        .map((link) => {
          if (link.sheet) {
            return Promise.resolve();
          }

          return new Promise((resolve) => {
            link.addEventListener("load", resolve, { once: true });
            link.addEventListener("error", resolve, { once: true });
            setTimeout(resolve, 5000);
          });
        }),
    );

    await document.fonts.ready;
  });

  // Allow application scripts to finish when possible.
  await page.waitForLoadState("networkidle", {
    timeout: 10000,
  }).catch(() => {});

  await page.screenshot({
    path: path.join(directory, "screenshot.png"),
    fullPage: true,
    animations: "disabled",
  });

  const html = await page.content();

  await fs.writeFile(
    path.join(directory, "page.html"),
    html,
    "utf8",
  );

  const snapshot = await page.evaluate(
    ({ components, properties, limit }) => {
      function elementSignature(el) {
        const attributes = {};

        for (const name of [
          "id",
          "name",
          "role",
          "type",
          "aria-label",
          "data-testid",
        ]) {
          const value = el.getAttribute(name);

          if (value !== null) {
            attributes[name] = value;
          }
        }

        return {
          tag: el.tagName.toLowerCase(),
          id: el.id,
          classes: [...el.classList],
          attributes,
          text: (el.innerText || el.textContent || "")
            .trim()
            .replace(/\s+/g, " ")
            .slice(0, 160),
        };
      }

      function cssPath(el) {
        const segments = [];
        let current = el;

        while (current && current.nodeType === Node.ELEMENT_NODE) {
          if (current.id) {
            segments.unshift(`#${CSS.escape(current.id)}`);
            break;
          }

          let segment = current.tagName.toLowerCase();

          if (current.classList.length) {
            segment += [...current.classList]
              .slice(0, 3)
              .map((name) => `.${CSS.escape(name)}`)
              .join("");
          }

          if (current.parentElement) {
            const siblings = [...current.parentElement.children]
              .filter((sibling) => sibling.tagName === current.tagName);

            if (siblings.length > 1) {
              segment += `:nth-of-type(${siblings.indexOf(current) + 1})`;
            }
          }

          segments.unshift(segment);
          current = current.parentElement;
        }

        return segments.join(" > ");
      }

      function inspectElement(el, selector) {
        const computed = getComputedStyle(el);
        const rect = el.getBoundingClientRect();

        const styles = Object.fromEntries(
          properties.map((name) => [
            name,
            computed.getPropertyValue(name).trim(),
          ]),
        );

        return {
          selector,
          cssPath: cssPath(el),
          ...elementSignature(el),
          rect: {
            x: Math.round(rect.x * 100) / 100,
            y: Math.round(rect.y * 100) / 100,
            width: Math.round(rect.width * 100) / 100,
            height: Math.round(rect.height * 100) / 100,
          },
          styles,
        };
      }

      const elements = {};
      const domIds = new Set();

      for (const [component, selectors] of Object.entries(components)) {
        const matches = [];
        const seen = new Set();

        for (const selector of selectors) {
          let found;

          try {
            found = document.querySelectorAll(selector);
          } catch {
            continue;
          }

          let count = 0;

          for (const el of found) {
            if (seen.has(el)) {
              continue;
            }

            seen.add(el);
            matches.push(inspectElement(el, selector));
            count += 1;

            if (count >= limit) {
              break;
            }
          }
        }

        elements[component] = matches;
      }

      for (const el of document.querySelectorAll("[id]")) {
        domIds.add(el.id);
      }

      const stylesheets = [...document.styleSheets].map((sheet) => ({
        href: sheet.href,
        disabled: sheet.disabled,
      }));

      return {
        url: location.href,
        title: document.title,
        viewport: {
          width: window.innerWidth,
          height: window.innerHeight,
          documentWidth: document.documentElement.scrollWidth,
          documentHeight: document.documentElement.scrollHeight,
          devicePixelRatio: window.devicePixelRatio,
        },
        stylesheets,
        domIds: [...domIds].sort(),
        elements,
      };
    },
    {
      components: COMPONENTS,
      properties: CSS_PROPERTIES,
      limit: options.limit,
    },
  );

  await writeJson(path.join(directory, "styles.json"), snapshot);

  return snapshot;
}

// ------------------------------------------------------------
// Element matching
// ------------------------------------------------------------

// Prefer stable IDs and semantic attributes.
// Fall back to shared classes and DOM order within components.
//
// The fallback is heuristic: Bootstrap 3 and Bootstrap 5 can have
// different markup. Each match includes a confidence indicator.

function matchScore(oldEl, newEl) {
  if (oldEl.id && newEl.id && oldEl.id === newEl.id) {
    return 100;
  }

  const oldTestId = oldEl.attributes["data-testid"];
  const newTestId = newEl.attributes["data-testid"];

  if (oldTestId && oldTestId === newTestId) {
    return 95;
  }

  const oldName = oldEl.attributes.name;
  const newName = newEl.attributes.name;

  if (
    oldName &&
    oldName === newName &&
    oldEl.tag === newEl.tag
  ) {
    return 90;
  }

  let score = 0;

  if (oldEl.tag === newEl.tag) {
    score += 15;
  }

  const commonClasses = oldEl.classes.filter(
    (name) => newEl.classes.includes(name),
  );

  score += Math.min(commonClasses.length * 10, 40);

  if (oldEl.text && newEl.text && oldEl.text === newEl.text) {
    score += 35;
  }

  if (
    oldEl.attributes.role &&
    oldEl.attributes.role === newEl.attributes.role
  ) {
    score += 20;
  }

  if (
    oldEl.attributes["aria-label"] &&
    oldEl.attributes["aria-label"] ===
      newEl.attributes["aria-label"]
  ) {
    score += 25;
  }

  return score;
}

function matchElements(oldElements, newElements) {
  const matches = [];
  const usedNew = new Set();
  const usedOld = new Set();

  // First pass: confident semantic matches.
  const candidates = [];

  for (let i = 0; i < oldElements.length; i += 1) {
    for (let j = 0; j < newElements.length; j += 1) {
      const score = matchScore(oldElements[i], newElements[j]);

      if (score >= 50) {
        candidates.push({ i, j, score });
      }
    }
  }

  candidates.sort((a, b) => b.score - a.score);

  for (const candidate of candidates) {
    if (usedOld.has(candidate.i) || usedNew.has(candidate.j)) {
      continue;
    }

    matches.push({
      oldIndex: candidate.i,
      newIndex: candidate.j,
      confidence: "high",
      score: candidate.score,
    });

    usedOld.add(candidate.i);
    usedNew.add(candidate.j);
  }

  // Second pass: index-based fallback for remaining elements.
  const remainingOld = oldElements
    .map((_, i) => i)
    .filter((i) => !usedOld.has(i));

  const remainingNew = newElements
    .map((_, i) => i)
    .filter((i) => !usedNew.has(i));

  for (
    let i = 0;
    i < Math.min(remainingOld.length, remainingNew.length);
    i += 1
  ) {
    matches.push({
      oldIndex: remainingOld[i],
      newIndex: remainingNew[i],
      confidence: "low",
      score: 0,
    });

    usedOld.add(remainingOld[i]);
    usedNew.add(remainingNew[i]);
  }

  return {
    matches,
    unmatchedOld: oldElements
      .map((_, i) => i)
      .filter((i) => !usedOld.has(i)),
    unmatchedNew: newElements
      .map((_, i) => i)
      .filter((i) => !usedNew.has(i)),
  };
}

// ------------------------------------------------------------
// Comparison
// ------------------------------------------------------------

function compareElement(oldEl, newEl, match) {
  const geometry = {};

  for (const key of ["x", "y", "width", "height"]) {
    const oldValue = oldEl.rect[key];
    const newValue = newEl.rect[key];
    const delta = Math.round((newValue - oldValue) * 100) / 100;

    if (Math.abs(delta) > 1) {
      geometry[key] = {
        old: oldValue,
        new: newValue,
        delta,
      };
    }
  }

  const styles = {};

  for (const [property, oldValue] of Object.entries(oldEl.styles)) {
    const newValue = newEl.styles[property];

    if (oldValue !== newValue) {
      styles[property] = {
        old: oldValue,
        new: newValue,
      };
    }
  }

  if (
    Object.keys(geometry).length === 0 &&
    Object.keys(styles).length === 0
  ) {
    return null;
  }

  return {
    old: {
      cssPath: oldEl.cssPath,
      selector: oldEl.selector,
      text: oldEl.text,
      classes: oldEl.classes,
      rect: oldEl.rect,
    },
    new: {
      cssPath: newEl.cssPath,
      selector: newEl.selector,
      text: newEl.text,
      classes: newEl.classes,
      rect: newEl.rect,
    },
    match,
    geometry,
    styles,
  };
}

function calculateSeverity(change) {
  const deltas = Object.values(change.geometry)
    .map((entry) => Math.abs(entry.delta));

  const maxDelta = Math.max(0, ...deltas);

  if (maxDelta >= 50) {
    return "CRITICAL";
  }

  if (maxDelta >= 15) {
    return "HIGH";
  }

  if (maxDelta >= 5) {
    return "MEDIUM";
  }

  if (Object.keys(change.styles).length > 0) {
    return "LOW";
  }

  return "INFO";
}

function compareSnapshots(oldSnapshot, newSnapshot) {
  const components = {};
  const summary = {
    critical: 0,
    high: 0,
    medium: 0,
    low: 0,
    changedElements: 0,
    unmatchedElements: 0,
  };

  for (const component of Object.keys(COMPONENTS)) {
    const oldElements = oldSnapshot.elements[component] || [];
    const newElements = newSnapshot.elements[component] || [];

    const mapping = matchElements(oldElements, newElements);

    const changes = [];

    for (const match of mapping.matches) {
      const change = compareElement(
        oldElements[match.oldIndex],
        newElements[match.newIndex],
        match,
      );

      if (!change) {
        continue;
      }

      change.severity = calculateSeverity(change);

      summary.changedElements += 1;

      const key = change.severity.toLowerCase();

      if (key in summary) {
        summary[key] += 1;
      }

      changes.push(change);
    }

    summary.unmatchedElements +=
      mapping.unmatchedOld.length + mapping.unmatchedNew.length;

    components[component] = {
      oldCount: oldElements.length,
      newCount: newElements.length,
      changes,
      unmatchedOld: mapping.unmatchedOld.map((i) => oldElements[i]),
      unmatchedNew: mapping.unmatchedNew.map((i) => newElements[i]),
    };
  }

  return {
    generatedAt: new Date().toISOString(),
    oldUrl: oldSnapshot.url,
    newUrl: newSnapshot.url,
    summary,
    viewport: {
      old: oldSnapshot.viewport,
      new: newSnapshot.viewport,
    },
    components,
  };
}

// ------------------------------------------------------------
// Markdown report
// ------------------------------------------------------------

function formatNumber(value) {
  return value > 0 ? `+${value}` : String(value);
}

function generateMarkdown(diff) {
  const lines = [
    "# VuFind CSS Comparison",
    "",
    `Generated: ${diff.generatedAt}`,
    "",
    `- Old: ${diff.oldUrl}`,
    `- New: ${diff.newUrl}`,
    "",
    "## Summary",
    "",
    `- Critical: ${diff.summary.critical}`,
    `- High: ${diff.summary.high}`,
    `- Medium: ${diff.summary.medium}`,
    `- Low: ${diff.summary.low}`,
    `- Changed elements: ${diff.summary.changedElements}`,
    `- Unmatched elements: ${diff.summary.unmatchedElements}`,
    "",
    "## Viewport",
    "",
    `Old: ${diff.viewport.old.width} x ${diff.viewport.old.height}`,
    "",
    `New: ${diff.viewport.new.width} x ${diff.viewport.new.height}`,
    "",
    "## Component differences",
    "",
  ];

  for (const [name, component] of Object.entries(diff.components)) {
    lines.push(`### ${name}`);
    lines.push("");
    lines.push(
      `Elements: old=${component.oldCount}, new=${component.newCount}`,
    );
    lines.push("");

    if (
      component.changes.length === 0 &&
      component.unmatchedOld.length === 0 &&
      component.unmatchedNew.length === 0
    ) {
      lines.push("No differences found.");
      lines.push("");
      continue;
    }

    const severityOrder = {
      CRITICAL: 0,
      HIGH: 1,
      MEDIUM: 2,
      LOW: 3,
      INFO: 4,
    };

    const sortedChanges = [...component.changes].sort(
      (a, b) =>
        severityOrder[a.severity] - severityOrder[b.severity],
    );

    for (const change of sortedChanges) {
      lines.push(`#### [${change.severity}] ${change.old.cssPath}`);
      lines.push("");

      lines.push(`- Old element: \`${change.old.cssPath}\``);
      lines.push(`- New element: \`${change.new.cssPath}\``);
      lines.push(`- Match confidence: ${change.match.confidence}`);
      lines.push("");

      const geometryEntries = Object.entries(change.geometry);

      if (geometryEntries.length > 0) {
        lines.push("**Geometry**");
        lines.push("");
        lines.push("| Property | Old | New | Delta |");
        lines.push("|---|---:|---:|---:|");

        for (const [property, values] of geometryEntries) {
          lines.push(
            `| ${property} | ${values.old}px | ${values.new}px | ${formatNumber(values.delta)}px |`,
          );
        }

        lines.push("");
      }

      const styleEntries = Object.entries(change.styles);

      if (styleEntries.length > 0) {
        lines.push("**Computed CSS**");
        lines.push("");
        lines.push("| Property | Old | New |");
        lines.push("|---|---|---|");

        for (const [property, values] of styleEntries) {
          const oldValue = String(values.old).replace(/\|/g, "\\|");
          const newValue = String(values.new).replace(/\|/g, "\\|");

          lines.push(
            `| ${property} | \`${oldValue}\` | \`${newValue}\` |`,
          );
        }

        lines.push("");
      }
    }

    if (component.unmatchedOld.length > 0) {
      lines.push("**Elements only in old version**");
      lines.push("");

      for (const el of component.unmatchedOld) {
        lines.push(`- \`${el.cssPath}\``);
      }

      lines.push("");
    }

    if (component.unmatchedNew.length > 0) {
      lines.push("**Elements only in new version**");
      lines.push("");

      for (const el of component.unmatchedNew) {
        lines.push(`- \`${el.cssPath}\``);
      }

      lines.push("");
    }
  }

  lines.push("## Notes");
  lines.push("");
  lines.push(
    "- Geometry is measured using getBoundingClientRect().",
  );
  lines.push(
    "- CSS values come from window.getComputedStyle().",
  );
  lines.push(
    "- Element matching is heuristic; low-confidence matches require review.",
  );
  lines.push(
    "- A CSS difference does not necessarily indicate a visual regression.",
  );
  lines.push(
    "- The report does not identify the originating SCSS rule.",
  );
  lines.push(
    "- Large positional deltas may be caused by changes in parent elements.",
  );
  lines.push("");

  return lines.join("\n");
}

// ------------------------------------------------------------
// Main
// ------------------------------------------------------------

async function main() {
  const options = parseArgs();
  const output = path.resolve(options.output);

  const oldDirectory = path.join(output, "old");
  const newDirectory = path.join(output, "new");

  const browserPath = findBrowser(options.browser);

  console.log("VuFind CSS comparison");
  console.log(`Browser: ${browserPath}`);
  console.log(`Output: ${output}`);
  console.log("");

  await fs.mkdir(output, { recursive: true });

  const browser = await chromium.launch({
    executablePath: browserPath,
    headless: true,
    args: ["--disable-dev-shm-usage"],
  });

  try {
    const context = await browser.newContext({
      viewport: {
        width: options.width,
        height: options.height,
      },
      deviceScaleFactor: 1,
      locale: "de-DE",
      reducedMotion: "reduce",
    });

    const oldPage = await context.newPage();
    const newPage = await context.newPage();

    console.log("[1/3] Capturing old VuFind version");

    const oldSnapshot = await capture(
      oldPage,
      pageUrl(options.old, options.page),
      oldDirectory,
      options,
    );

    console.log("[2/3] Capturing new VuFind version");

    const newSnapshot = await capture(
      newPage,
      pageUrl(options.new, options.page),
      newDirectory,
      options,
    );

    console.log("[3/3] Comparing CSS and geometry");

    const diff = compareSnapshots(oldSnapshot, newSnapshot);

    await writeJson(path.join(output, "diff.json"), diff);

    await fs.writeFile(
      path.join(output, "diff.md"),
      generateMarkdown(diff),
      "utf8",
    );

    console.log("");
    console.log("Comparison complete.");
    console.log("");
    console.log(`Changed elements: ${diff.summary.changedElements}`);
    console.log(`Critical: ${diff.summary.critical}`);
    console.log(`High: ${diff.summary.high}`);
    console.log(`Medium: ${diff.summary.medium}`);
    console.log(`Low: ${diff.summary.low}`);
    console.log(`Unmatched: ${diff.summary.unmatchedElements}`);
    console.log("");
    console.log(`Markdown report: ${path.join(output, "diff.md")}`);
    console.log(`JSON report: ${path.join(output, "diff.json")}`);

    await context.close();
  } finally {
    await browser.close();
  }
}

main().catch((error) => {
  console.error(error);
  process.exitCode = 1;
});

