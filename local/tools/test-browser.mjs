
import { chromium } from "playwright";

const browser = await chromium.launch({
  headless: true,
  executablePath: "/usr/bin/chromium",
});

const page = await browser.newPage({
  viewport: { width: 1588, height: 960 },
});

await page.goto("http://localhost:8000/Search/Results", {
  waitUntil: "domcontentloaded",
});

await page.screenshot({
  path: "/tmp/vufind-test.png",
  fullPage: true,
});

console.log("Seitentitel:", await page.title());

await browser.close();

