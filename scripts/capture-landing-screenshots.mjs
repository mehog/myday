import fs from 'node:fs';
import path from 'node:path';
import puppeteer from 'puppeteer-core';

const CHROME = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const BASE = process.env.MYDAY_BASE_URL || 'https://myday.test';
const OUT_ROOT = path.resolve('public/img/landing');
const PASSWORD = '5E3L1Y84uFdd';

const PROFILES = {
    bs: {
        email: 'jasmin-djordje@nasdan.ba',
        slug: 'jasmina-djordje',
        guestToken: 'mktbsfeaturedguesttoken000000001',
        islamicDemo: 'demo-islamsko',
        christianDemo: 'demo-krscansko',
    },
    hr: {
        email: 'marketing-hr@nasdan.ba',
        slug: 'ivan-lucija',
        guestToken: 'mkthrfeaturedguesttoken000000001',
        islamicDemo: 'demo-islamsko-hr',
        christianDemo: 'demo-krscansko-hr',
    },
    de: {
        email: 'marketing-de@nasdan.ba',
        slug: 'lukas-sophie',
        guestToken: 'mktdefaturedguesttoken000000001',
        islamicDemo: 'demo-islamsko-de',
        christianDemo: 'demo-krscansko-de',
    },
    en: {
        email: 'marketing-en@nasdan.ba',
        slug: 'oliver-emily',
        guestToken: 'mktenfeaturedguesttoken000000001',
        islamicDemo: 'demo-islamsko-en',
        christianDemo: 'demo-krscansko-en',
    },
};

const ALL_LOCALES = Object.keys(PROFILES);

function parseLocales(argv) {
    const arg = argv.find((a) => a.startsWith('--locale='));
    const value = (arg ? arg.slice('--locale='.length) : 'all').toLowerCase();

    if (value === 'all') {
        return ALL_LOCALES;
    }

    if (! PROFILES[value]) {
        throw new Error(`Unsupported locale [${value}]. Use bs|hr|de|en|all.`);
    }

    return [value];
}

async function wait(ms) {
    await new Promise((r) => setTimeout(r, ms));
}

async function waitForVisualReady(page) {
    await page.evaluate(async () => {
        if (document.fonts?.ready) {
            await document.fonts.ready;
        }

        const images = Array.from(document.images || []);
        await Promise.all(
            images.map((img) => {
                if (img.complete) {
                    return Promise.resolve();
                }

                return new Promise((resolve) => {
                    img.addEventListener('load', resolve, { once: true });
                    img.addEventListener('error', resolve, { once: true });
                });
            }),
        );
    }).catch(() => {});

    await wait(400);
}

async function dismissOverlays(page) {
    await page.evaluate(() => {
        document.querySelectorAll('button, a').forEach((el) => {
            const t = (el.textContent || '').trim();
            if (/Možda kasnije|Maybe later|Vielleicht später|Možda kasnije/i.test(t)) {
                el.click();
            }
        });
        document.querySelectorAll('[aria-label="Close"], .fi-no-notification button, .fi-modal-close-btn').forEach((b) => b.click());
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

async function shot(page, outDir, name) {
    await hideUiChrome(page);
    await waitForVisualReady(page);
    await wait(300);
    const file = path.join(outDir, `${name}.webp`);
    await page.screenshot({
        path: file,
        type: 'webp',
        quality: 82,
        captureBeyondViewport: false,
    });
    console.log('saved', file);
}

async function openInvite(page, url) {
    await page.goto(url, { waitUntil: 'networkidle2', timeout: 60000 });
    await wait(1200);

    const clicked = await page.evaluate(() => {
        const trigger = document.querySelector(
            '.env-photo-trigger, .seal-photo-trigger, #env-photo-envelope, #seal-photo-trigger, button.env-photo-trigger',
        );
        if (trigger) {
            trigger.click();
            return 'reveal-trigger';
        }

        const hint = document.querySelector('.env-tap-hint, .seal-tap-hint');
        if (hint) {
            const button = hint.closest('button, [role="button"]') || hint;
            button.click();
            return 'tap-hint';
        }

        const labels = [
            'DODIRNITE',
            'OTVORI POZIVNICU',
            'TOUCH TO OPEN',
            'TOUCH',
            'OPEN INVITATION',
            'BERÜHREN',
            'EINLADUNG ÖFFNEN',
            'ZUM ÖFFNEN',
        ];

        const candidates = Array.from(document.querySelectorAll('button, [role="button"], a'))
            .filter((el) => {
                const style = window.getComputedStyle(el);
                const rect = el.getBoundingClientRect();
                return style.display !== 'none'
                    && style.visibility !== 'hidden'
                    && rect.width > 0
                    && rect.height > 0;
            });

        for (const el of candidates) {
            const t = (el.textContent || '').replace(/\s+/g, ' ').trim().toUpperCase();
            if (! t) {
                continue;
            }
            if (labels.some((label) => t.includes(label))) {
                el.click();
                return t.slice(0, 40);
            }
        }

        return null;
    });

    console.log('reveal click:', clicked);
    await wait(4500);
    await waitForVisualReady(page);
}

async function hideDemoControls(page) {
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
}

async function fillLogin(page, email) {
    await page.waitForSelector('input[type="email"]', { timeout: 15000 });
    await page.waitForSelector('input[type="password"]', { timeout: 15000 });

    await page.evaluate((creds) => {
        const emailInput = document.querySelector('input[type="email"]');
        const passwordInput = document.querySelector('input[type="password"]');
        if (! emailInput || ! passwordInput) {
            throw new Error('Login inputs not found');
        }

        const setValue = (input, value) => {
            input.focus();
            input.value = value;
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        };

        setValue(emailInput, creds.email);
        setValue(passwordInput, creds.password);
    }, { email, password: PASSWORD });

    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 60000 }).catch(() => {}),
        page.evaluate(() => {
            const form = document.querySelector('form');
            if (form) {
                form.requestSubmit();
                return;
            }
            const button = Array.from(document.querySelectorAll('button'))
                .find((b) => /prijavi|log in|anmelden|sign in/i.test(b.textContent || ''));
            button?.click();
        }),
    ]);
    await wait(2000);
    await dismissOverlays(page);
}

async function login(page, email) {
    await page.setViewport({ width: 1600, height: 1000, deviceScaleFactor: 2 });
    await page.goto(`${BASE}/app/login`, { waitUntil: 'networkidle2', timeout: 60000 });
    await wait(800);
    await fillLogin(page, email);

    const blocked = await page.evaluate(() => {
        const text = (document.body?.innerText || '').toLowerCase();
        return /verify your email|potvrdite email|e-mail-adresse bestätigen|potvrdite e-mail/.test(text);
    });
    if (blocked) {
        throw new Error(`Login blocked by email verification for ${email}. Re-seed with verified marketing users.`);
    }
}

async function clickTabByText(page, pattern) {
    const clicked = await page.evaluate((source) => {
        const re = new RegExp(source, 'i');
        const selectors = [
            'button[role="tab"]',
            '[role="tab"]',
            'button.fi-tabs-item',
            'a.fi-tabs-item',
            '.fi-sc-tabs button',
            '.fi-tabs button',
        ];
        const tabs = selectors.flatMap((sel) => Array.from(document.querySelectorAll(sel)));
        const unique = [...new Set(tabs)];
        const match = unique.find((t) => re.test((t.textContent || '').replace(/\s+/g, ' ').trim()));
        if (! match) {
            return null;
        }
        match.click();
        return (match.textContent || '').trim().slice(0, 60);
    }, pattern.source);

    console.log('tab click:', clicked);
    await wait(1800);
    await dismissOverlays(page);
}

async function captureLocale(browser, locale) {
    const profile = PROFILES[locale];
    const outDir = path.join(OUT_ROOT, locale);
    fs.mkdirSync(outDir, { recursive: true });

    const inviteUrl = `${BASE}/e/${profile.slug}/${profile.guestToken}?locale=${locale}`;
    const contactUrl = `${BASE}/e/${profile.slug}/${profile.guestToken}/contact?locale=${locale}`;

    console.log(`\n=== Capturing locale [${locale}] ===`);

    const context = await browser.createBrowserContext();

    {
        const page = await context.newPage();
        await page.setViewport({ width: 390, height: 844, deviceScaleFactor: 2.77 });
        await openInvite(page, inviteUrl);
        await page.evaluate(() => window.scrollTo(0, 0));
        await wait(600);
        await shot(page, outDir, 'hero-invitation-mobile');
        await page.close();
    }

    {
        const page = await context.newPage();
        await page.setViewport({ width: 390, height: 844, deviceScaleFactor: 2.77 });
        await openInvite(page, contactUrl);
        await page.evaluate(() => window.scrollTo(0, 0));
        await wait(800);
        await shot(page, outDir, 'feature-guest-upload-mobile');
        await page.close();
    }

    {
        const page = await context.newPage();
        await page.setViewport({ width: 390, height: 844, deviceScaleFactor: 2.77 });

        for (const [demoSlug, shotName] of [
            [profile.islamicDemo, 'demo-classic-mobile'],
            [profile.christianDemo, 'demo-editorial-mobile'],
        ]) {
            await openInvite(page, `${BASE}/e/${demoSlug}?locale=${locale}`);
            await hideDemoControls(page);
            await page.evaluate(() => window.scrollTo(0, 0));
            await wait(500);
            await shot(page, outDir, shotName);
        }

        await page.close();
    }

    {
        const page = await context.newPage();
        await login(page, profile.email);

        await page.goto(`${BASE}/app?locale=${locale}`, { waitUntil: 'networkidle2', timeout: 60000 });
        await wait(1800);
        await dismissOverlays(page);
        await page.evaluate(() => window.scrollTo(0, 0));
        await shot(page, outDir, 'hero-dashboard-desktop');

        await clickTabByText(page, /meni|menu|menü/i);
        await page.evaluate(() => window.scrollTo(0, 0));
        await wait(600);
        await shot(page, outDir, 'feature-menu-accommodation-desktop');

        await clickTabByText(page, /statist/i);
        await page.evaluate(() => window.scrollTo(0, 0));
        await wait(600);
        await shot(page, outDir, 'feature-updates-insights-desktop');

        await page.goto(`${BASE}/app/poruke-gostiju?locale=${locale}`, { waitUntil: 'networkidle2', timeout: 60000 });
        await wait(1500);
        await dismissOverlays(page);
        await page.evaluate(() => window.scrollTo(0, 0));
        await shot(page, outDir, 'feature-messages-desktop');

        await page.goto(`${BASE}/app/raspored-sjedenja?locale=${locale}`, { waitUntil: 'networkidle2', timeout: 60000 });
        await wait(3000);
        await dismissOverlays(page);
        await page.evaluate(() => window.scrollTo(0, 0));
        await shot(page, outDir, 'feature-seating-plan-desktop');

        await page.goto(`${BASE}/app/moje-vjencanje?locale=${locale}`, { waitUntil: 'networkidle2', timeout: 60000 });
        await wait(1200);
        const href = await page.evaluate(() => {
            const a = document.querySelector('a[href*="moje-vjencanje/"]');
            return a?.href || null;
        });
        if (href) {
            const editUrl = href.includes('?')
                ? `${href}&locale=${locale}`
                : `${href}?locale=${locale}`;
            await page.goto(editUrl, { waitUntil: 'networkidle2', timeout: 60000 });
            await wait(1500);
        }
        await dismissOverlays(page);
        await clickTabByText(page, /^(Gosti|Guests|Gäste)(\b|\s|\()/);
        await wait(2200);
        await dismissOverlays(page);
        await page.evaluate(() => window.scrollTo(0, 280));
        await wait(400);
        await shot(page, outDir, 'feature-rsvp-guests-desktop');

        await page.close();
    }

    await context.close();
    console.log(`=== Done locale [${locale}] ===`);
}

const locales = parseLocales(process.argv.slice(2));
fs.mkdirSync(OUT_ROOT, { recursive: true });

const browser = await puppeteer.launch({
    executablePath: CHROME,
    headless: true,
    defaultViewport: null,
    args: ['--ignore-certificate-errors', '--no-sandbox', '--window-size=1600,1000'],
});

try {
    for (const locale of locales) {
        await captureLocale(browser, locale);
    }
    console.log('\nRecapture complete for:', locales.join(', '));
} catch (err) {
    console.error(err);
    process.exitCode = 1;
} finally {
    await browser.close();
}
