import fs from 'node:fs';
import path from 'node:path';
import puppeteer from 'puppeteer-core';

const CHROME = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const BASE = 'https://myday.test';
const OUT = path.resolve('public/img/landing');
const EMAIL = 'jasmin-djordje@nasdan.ba';
const PASSWORD = '5E3L1Y84uFdd';
const GUEST_TOKEN = '1JqzTAncJ5Rtg01J8HIDHWGeD50KSm2f';
const INVITE = `${BASE}/e/jasmina-djordje/${GUEST_TOKEN}`;

fs.mkdirSync(OUT, { recursive: true });

async function wait(ms) {
    await new Promise((r) => setTimeout(r, ms));
}

async function dismissOverlays(page) {
    await page.evaluate(() => {
        // Click "Možda kasnije" if present
        document.querySelectorAll('button, a').forEach((el) => {
            const t = (el.textContent || '').trim();
            if (/Možda kasnije|Maybe later|Vielleicht später/i.test(t)) {
                el.click();
            }
        });
        document.querySelectorAll('[aria-label="Close"], .fi-no-notification button').forEach((b) => b.click());
    }).catch(() => {});
    await wait(400);
}

async function hideUiChrome(page) {
    await page.addStyleTag({
        content: `
            .fi-topbar-close-overlay,
            [data-support-bubble],
            .fixed.bottom-6.left-1\\/2 { display: none !important; }
            .fi-no-notification, .fi-notifications { display: none !important; }
        `,
    }).catch(() => {});
}

async function shot(page, name) {
    await hideUiChrome(page);
    await wait(500);
    const file = path.join(OUT, `${name}.png`);
    await page.screenshot({ path: file, type: 'png', captureBeyondViewport: false });
    console.log('saved', file);
}

async function openInvite(page) {
    await page.goto(INVITE, { waitUntil: 'networkidle2', timeout: 60000 });
    await wait(1200);

    // Click reveal open control
    const clicked = await page.evaluate(() => {
        const candidates = Array.from(document.querySelectorAll('button, [role="button"], a, div'));
        for (const el of candidates) {
            const t = (el.textContent || '').trim().toUpperCase();
            if (t.includes('DODIRNITE') || t.includes('OTVORI POZIVNICU') || t.includes('TOUCH')) {
                el.click();
                return t.slice(0, 40);
            }
        }
        // Try wax seal / reveal root
        const seal = document.querySelector('[data-reveal], .reveal-open, .wax-seal, canvas, img[alt*="pečat"], img[alt*="seal"]');
        if (seal) {
            seal.click();
            return 'seal';
        }
        return null;
    });
    console.log('reveal click:', clicked);
    await wait(3200);
}

async function login(page) {
    await page.setViewport({ width: 1600, height: 1000, deviceScaleFactor: 2 });
    await page.goto(`${BASE}/app/login`, { waitUntil: 'networkidle2', timeout: 60000 });
    await wait(600);
    await page.waitForSelector('input[type="email"]', { timeout: 15000 });
    await page.click('input[type="email"]', { clickCount: 3 });
    await page.type('input[type="email"]', EMAIL, { delay: 8 });
    await page.click('input[type="password"]', { clickCount: 3 });
    await page.type('input[type="password"]', PASSWORD, { delay: 8 });
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 60000 }).catch(() => {}),
        page.keyboard.press('Enter'),
    ]);
    await wait(2000);
    await dismissOverlays(page);
}

const browser = await puppeteer.launch({
    executablePath: CHROME,
    headless: true,
    defaultViewport: null,
    args: ['--ignore-certificate-errors', '--no-sandbox', '--window-size=1600,1000'],
});

try {
    // Mobile invitation (opened)
    {
        const page = await browser.newPage();
        await page.setViewport({ width: 390, height: 844, deviceScaleFactor: 2.77 });
        await openInvite(page);
        await page.evaluate(() => window.scrollTo(0, 0));
        await wait(600);
        await shot(page, 'hero-invitation-mobile');
        await page.close();
    }

    // Demo templates via Livewire preview selects
    {
        const page = await browser.newPage();
        await page.setViewport({ width: 390, height: 844, deviceScaleFactor: 2.77 });

        for (const template of ['classic', 'editorial', 'story']) {
            await page.goto(`${BASE}/e/demo-islamsko`, { waitUntil: 'networkidle2', timeout: 60000 });
            await wait(1000);

            // Select template via wire:model.live="previewTemplate"
            await page.evaluate((tpl) => {
                const selects = Array.from(document.querySelectorAll('select'));
                const templateSelect = selects.find((s) => (s.getAttribute('wire:model.live') || s.getAttribute('wire:model') || '').includes('previewTemplate'))
                    || selects[1];
                if (templateSelect) {
                    templateSelect.value = tpl;
                    templateSelect.dispatchEvent(new Event('change', { bubbles: true }));
                    templateSelect.dispatchEvent(new Event('input', { bubbles: true }));
                }
            }, template);
            await wait(1800);

            // Hide floating demo controls + locale picker
            await page.evaluate(() => {
                document.querySelectorAll('select').forEach((el) => {
                    let node = el.parentElement;
                    for (let i = 0; i < 8 && node; i++) {
                        const pos = getComputedStyle(node).position;
                        if (pos === 'fixed' || pos === 'sticky') {
                            node.style.visibility = 'hidden';
                            break;
                        }
                        node = node.parentElement;
                    }
                });
            });

            await page.evaluate(() => window.scrollTo(0, 0));
            await wait(400);
            await shot(page, `demo-${template}-mobile`);
        }
        await page.close();
    }

    // App panel shots
    {
        const page = await browser.newPage();
        await login(page);

        await page.goto(`${BASE}/app`, { waitUntil: 'networkidle2', timeout: 60000 });
        await wait(1800);
        await dismissOverlays(page);
        await wait(400);
        await page.evaluate(() => window.scrollTo(0, 0));
        await shot(page, 'hero-dashboard-desktop');

        await page.evaluate(() => window.scrollTo(0, 180));
        await wait(400);
        await shot(page, 'feature-updates-insights-desktop');

        await page.goto(`${BASE}/app/poruke-gostiju`, { waitUntil: 'networkidle2', timeout: 60000 });
        await wait(1500);
        await dismissOverlays(page);
        await page.evaluate(() => window.scrollTo(0, 0));
        await shot(page, 'feature-messages-desktop');

        await page.goto(`${BASE}/app/raspored-sjedenja`, { waitUntil: 'networkidle2', timeout: 60000 });
        await wait(3000);
        await dismissOverlays(page);
        await page.evaluate(() => window.scrollTo(0, 0));
        await shot(page, 'feature-seating-plan-desktop');

        // Guests relation tab
        const weddingId = await page.evaluate(async () => {
            // navigate via moje-vjencanje
            return null;
        });
        await page.goto(`${BASE}/app/moje-vjencanje`, { waitUntil: 'networkidle2', timeout: 60000 });
        await wait(1200);
        const href = await page.evaluate(() => {
            const a = document.querySelector('a[href*="moje-vjencanje/"]');
            return a?.href || null;
        });
        if (href) {
            await page.goto(href, { waitUntil: 'networkidle2', timeout: 60000 });
            await wait(1500);
        }
        await dismissOverlays(page);

        await page.evaluate(() => {
            const tabs = Array.from(document.querySelectorAll('button, a, [role="tab"]'));
            const guests = tabs.find((t) => /gost/i.test(t.textContent || ''));
            guests?.click();
        });
        await wait(2200);
        await dismissOverlays(page);
        await page.evaluate(() => window.scrollTo(0, 280));
        await wait(400);
        await shot(page, 'feature-rsvp-guests-desktop');

        await page.close();
    }

    console.log('Recapture complete');
} catch (err) {
    console.error(err);
    process.exitCode = 1;
} finally {
    await browser.close();
}
