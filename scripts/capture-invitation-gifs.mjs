import { spawnSync } from 'node:child_process';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import puppeteer from 'puppeteer-core';

const CHROME = process.env.CHROME_PATH
    || '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const BASE = process.env.MYDAY_BASE_URL || 'https://myday.test';
const OUT_ROOT = path.resolve('public/gifs');
const VIDEO_OUT_ROOT = path.resolve('public/videos');
const SLUG = 'oliver-emily';
const TOKEN = 'bJKZ0ymSiIs5DmcYcqyn3CN9FXIowkfT';
const INVITE_URL = `${BASE}/e/${SLUG}/${TOKEN}?locale=en`;
const CONTACT_URL = `${BASE}/e/${SLUG}/${TOKEN}/contact?locale=en`;

const VIEWPORT = { width: 390, height: 844, deviceScaleFactor: 1.5 };
const FPS = 15;
/** Capture-only: slow entry reveal CSS/JS timing so GIFs read more cinematic. */
const REVEAL_SLOW_FACTOR = 2.2;

/**
 * @typedef {'open-scroll' | 'open-story' | 'rsvp-yes' | 'rsvp-no' | 'contact'} GifFlow
 * @typedef {{
 *   id: string,
 *   filename: string,
 *   theme: string,
 *   template: string,
 *   reveal: string | null,
 *   flow: GifFlow,
 *   durationSec: number,
 * }} GifSpec
 */

/** @type {GifSpec[]} */
const GIFS = [
    {
        id: 'envelope-amber-gold-classic',
        filename: 'envelope-amber-gold-classic.gif',
        theme: 'amber-gold',
        template: 'classic',
        reveal: 'envelope',
        flow: 'open-scroll',
        // idle + slowed reveal + long slow scroll
        durationSec: 33,
    },
    {
        id: 'wax-seal-royal-wedding-editorial',
        filename: 'wax-seal-royal-wedding-editorial.gif',
        theme: 'royal-wedding',
        template: 'editorial',
        reveal: 'wax-seal',
        flow: 'open-scroll',
        durationSec: 33,
    },
    {
        id: 'curtain-dusty-rose-story',
        filename: 'curtain-dusty-rose-story.gif',
        theme: 'dusty-rose',
        template: 'story',
        reveal: 'curtain',
        flow: 'open-story',
        durationSec: 28,
    },
    {
        id: 'storybook-paper-ink-classic',
        filename: 'storybook-paper-ink-classic.gif',
        theme: 'paper-ink',
        template: 'classic',
        reveal: 'storybook',
        flow: 'open-scroll',
        durationSec: 33,
    },
    {
        id: 'garden-gate-lavender-dream-editorial',
        filename: 'garden-gate-lavender-dream-editorial.gif',
        theme: 'lavender-dream',
        template: 'editorial',
        reveal: 'garden-gate',
        flow: 'open-scroll',
        durationSec: 33,
    },
    {
        id: 'sunrise-bloom-pearl-white-story',
        filename: 'sunrise-bloom-pearl-white-story.gif',
        theme: 'pearl-white',
        template: 'story',
        reveal: 'sunrise-bloom',
        flow: 'open-story',
        durationSec: 28,
    },
    {
        id: 'royal-crest-doors-winter-magic-classic',
        filename: 'royal-crest-doors-winter-magic-classic.gif',
        theme: 'winter-magic',
        template: 'classic',
        reveal: 'royal-crest-doors',
        flow: 'open-scroll',
        durationSec: 33,
    },
    {
        id: 'editorial-rsvp-yes',
        filename: 'editorial-rsvp-yes.gif',
        theme: 'royal-wedding',
        template: 'editorial',
        reveal: 'envelope',
        flow: 'rsvp-yes',
        durationSec: 12,
    },
    {
        id: 'classic-rsvp-no',
        filename: 'classic-rsvp-no.gif',
        theme: 'amber-gold',
        template: 'classic',
        reveal: 'envelope',
        flow: 'rsvp-no',
        durationSec: 12,
    },
    {
        id: 'contact-page',
        filename: 'contact-page.gif',
        theme: 'amber-gold',
        template: 'classic',
        reveal: null,
        flow: 'contact',
        durationSec: 20,
    },
];

function parseOnly(argv) {
    const arg = argv.find((a) => a.startsWith('--only='));
    if (! arg) {
        return null;
    }

    return arg.slice('--only='.length).trim();
}

function parseExcept(argv) {
    const arg = argv.find((a) => a.startsWith('--except='));
    if (! arg) {
        return [];
    }

    return arg.slice('--except='.length).split(',').map((s) => s.trim()).filter(Boolean);
}

/** @returns {'gif' | 'mp4' | 'both'} */
function parseFormat(argv) {
    const arg = argv.find((a) => a.startsWith('--format='));
    const value = (arg ? arg.slice('--format='.length) : 'both').toLowerCase();

    if (! ['gif', 'mp4', 'both'].includes(value)) {
        throw new Error(`Unsupported --format=${value}. Use gif|mp4|both.`);
    }

    return /** @type {'gif' | 'mp4' | 'both'} */ (value);
}

function wait(ms) {
    return new Promise((resolve) => setTimeout(resolve, ms));
}

function runPhp(code) {
    const result = spawnSync('php', ['artisan', 'tinker', `--execute=${code}`], {
        cwd: process.cwd(),
        encoding: 'utf8',
        maxBuffer: 10 * 1024 * 1024,
    });

    if (result.status !== 0) {
        throw new Error(`PHP failed:\n${result.stderr || result.stdout}`);
    }

    return (result.stdout || '').trim();
}

function getWeddingSettings() {
    const raw = runPhp(`
        $e = \\App\\Models\\WeddingEvent::where('slug', '${SLUG}')->firstOrFail();
        echo json_encode([
            'theme' => $e->theme?->value ?? $e->theme,
            'template' => $e->template?->value ?? $e->template,
            'reveal_animation' => $e->reveal_animation?->value ?? $e->reveal_animation,
            'is_demo' => (bool) $e->is_demo,
        ]);
    `);
    const line = raw.split('\n').filter(Boolean).at(-1);
    return JSON.parse(line);
}

function setWeddingSettings({ theme, template, reveal }) {
    const revealPhp = reveal === null || reveal === undefined
        ? 'null'
        : `'${reveal}'`;

    runPhp(`
        $e = \\App\\Models\\WeddingEvent::where('slug', '${SLUG}')->firstOrFail();
        $e->theme = \\App\\InvitationTheme::from('${theme}');
        $e->template = \\App\\InvitationTemplate::from('${template}');
        $e->reveal_animation = ${revealPhp === 'null' ? 'null' : `\\App\\InvitationReveal::from(${revealPhp})`};
        $e->save();
        echo 'ok';
    `);
}

function resetGuestRsvp() {
    runPhp(`
        $g = \\App\\Models\\Guest::where('token', '${TOKEN}')->first();
        if ($g) {
            $g->rsvp_status = null;
            $g->rsvp_responded_at = null;
            $g->rsvp_note = null;
            $g->plus_one_name = null;
            $g->menu_option_id = null;
            $g->plus_one_menu_option_id = null;
            $g->save();
        }
        echo 'ok';
    `);
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

    await wait(300);
}

async function dismissOverlays(page) {
    await page.evaluate(() => {
        document.querySelectorAll('button, a').forEach((el) => {
            const t = (el.textContent || '').trim();
            if (/Maybe later|Možda kasnije|Vielleicht später/i.test(t)) {
                el.click();
            }
        });
        document.querySelectorAll('[aria-label="Close"], .fi-no-notification button').forEach((b) => b.click());
    }).catch(() => {});
    await wait(250);
}

async function hideCaptureChrome(page) {
    await page.addStyleTag({
        content: `
            [data-support-bubble],
            .fi-no-notification,
            .fi-notifications,
            .fixed.bottom-6.left-1\\/2 {
                display: none !important;
            }
        `,
    }).catch(() => {});
}

async function clickReveal(page) {
    return page.evaluate(() => {
        const trigger = document.querySelector([
            '.env-photo-trigger',
            '.seal-photo-trigger',
            '.curtain-photo-trigger',
            '.story-photo-trigger',
            '.gate-photo-trigger',
            '.bloom-photo-trigger',
            '.crest-photo-trigger',
            '#env-photo-envelope',
            '#seal-photo-trigger',
            '#curtain-photo-trigger',
            '#story-photo-trigger',
            '#gate-photo-trigger',
            '#bloom-photo-trigger',
            '#crest-photo-trigger',
            'button.env-photo-trigger',
            '[data-reveal-trigger]',
            '.reveal-trigger',
        ].join(', '));
        if (trigger) {
            trigger.click();
            return trigger.id || trigger.className?.toString?.().slice(0, 40) || 'reveal-trigger';
        }

        const labels = [
            'TOUCH TO OPEN',
            'TAP TO OPEN',
            'OPEN INVITATION',
            'TOUCH',
            'DODIRNITE',
            'OTVORI POZIVNICU',
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

        const hint = document.querySelector('[class*="tap-hint"]');
        if (hint) {
            const button = hint.closest('button, [role="button"]') || hint;
            button.click();
            return 'tap-hint';
        }

        return null;
    });
}

async function smoothScroll(page, distance, durationMs) {
    await page.evaluate(async ({ distance, durationMs }) => {
        const start = window.scrollY;
        const startTime = performance.now();

        await new Promise((resolve) => {
            const step = (now) => {
                const t = Math.min(1, (now - startTime) / durationMs);
                const eased = t < 0.5 ? 2 * t * t : 1 - Math.pow(-2 * t + 2, 2) / 2;
                window.scrollTo(0, start + distance * eased);
                if (t < 1) {
                    requestAnimationFrame(step);
                } else {
                    resolve();
                }
            };
            requestAnimationFrame(step);
        });
    }, { distance, durationMs });
}

async function advanceStory(page, times = 2) {
    for (let i = 0; i < times; i++) {
        const clicked = await page.evaluate(() => {
            const next = document.querySelector('.story-nav-next, [aria-label="Next slide"]');
            if (next) {
                next.click();
                return 'story-nav-next';
            }

            const byText = Array.from(document.querySelectorAll('button, [role="button"], a'))
                .find((el) => /next|dalje|weiter|→|›/i.test((el.textContent || '').trim()));
            if (byText) {
                byText.click();
                return 'text-next';
            }

            const slide = document.querySelector('.story-slide, #invitation-content');
            slide?.dispatchEvent(new MouseEvent('click', { bubbles: true, clientX: window.innerWidth * 0.8, clientY: window.innerHeight * 0.5 }));
            return slide ? 'slide-tap' : null;
        });
        console.log('  story advance:', clicked);
        await wait(2800);
    }
}

async function scrollToRsvp(page) {
    await page.evaluate(() => {
        const el = document.querySelector('#rsvp, [id*="rsvp"]');
        if (el) {
            el.scrollIntoView({ behavior: 'instant', block: 'center' });
        } else {
            window.scrollTo(0, document.body.scrollHeight * 0.75);
        }
    });
    await wait(500);
}

async function clickRsvp(page, answer) {
    const clicked = await page.evaluate((ans) => {
        const preferred = ans === 'yes'
            ? document.querySelector('button.rsvp-btn-yes')
            : document.querySelector('button.rsvp-btn-no');
        if (preferred) {
            preferred.click();
            return (preferred.textContent || '').replace(/\s+/g, ' ').trim().slice(0, 60);
        }

        const re = ans === 'yes'
            ? /Yes,\s*I will attend/i
            : /No,\s*I cannot attend/i;
        const buttons = Array.from(document.querySelectorAll('button'));
        const match = buttons.find((b) => re.test((b.textContent || '').replace(/\s+/g, ' ').trim())
            && ! /saving|spreman/i.test(b.textContent || ''));
        if (! match) {
            return null;
        }
        match.click();
        return (match.textContent || '').replace(/\s+/g, ' ').trim().slice(0, 60);
    }, answer);

    console.log(`  RSVP click (${answer}):`, clicked);
    await wait(1000);

    // Fill optional modal fields, then confirm.
    await page.evaluate(() => {
        document.querySelectorAll('select').forEach((select) => {
            if (select.options.length > 1 && ! select.value) {
                select.value = select.options[1].value;
                select.dispatchEvent(new Event('input', { bubbles: true }));
                select.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
    });
    await wait(400);

    const confirmed = await page.evaluate(() => {
        const confirm = Array.from(document.querySelectorAll('button'))
            .find((b) => /^(Confirm|Potvrdi|Bestätigen)$/i.test((b.textContent || '').trim())
                || /Confirm|Potvrdi|Bestätigen/i.test((b.textContent || '').trim()));
        if (! confirm) {
            return null;
        }
        confirm.click();
        return (confirm.textContent || '').trim().slice(0, 40);
    });
    console.log('  RSVP confirm:', confirmed);
    await wait(1200);

    await page.evaluate(() => {
        document.querySelectorAll('button, a').forEach((el) => {
            const t = (el.textContent || '').trim();
            if (/Maybe later|Možda kasnije|Vielleicht später/i.test(t)) {
                el.click();
            }
        });
    });
    await wait(800);

    // Keep the resulting RSVP status card in view.
    await page.evaluate(() => {
        const el = document.querySelector('#rsvp, [id*="rsvp"]');
        el?.scrollIntoView({ behavior: 'instant', block: 'center' });
    });
    await wait(600);
}

/**
 * Capture frames on a sequential timeline (best for short interaction clips).
 * Long-running cues should be avoided; prefer per-frame updates via onFrame.
 * @param {import('puppeteer-core').Page} page
 * @param {string} framesDir
 * @param {number} durationSec
 * @param {{
 *   cues?: Array<{ atMs: number, run: () => Promise<void> }>,
 *   onFrame?: (elapsedMs: number, frame: number) => Promise<void>,
 * }} [options]
 */
async function captureFrames(page, framesDir, durationSec, options = {}) {
    const cues = [...(options.cues || [])].sort((a, b) => a.atMs - b.atMs);
    const onFrame = options.onFrame || (async () => {});

    fs.mkdirSync(framesDir, { recursive: true });

    const totalFrames = Math.round(durationSec * FPS);
    const frameInterval = 1000 / FPS;
    const started = Date.now();
    let cueIndex = 0;

    for (let frame = 0; frame < totalFrames; frame += 1) {
        const elapsed = Date.now() - started;

        while (cueIndex < cues.length && cues[cueIndex].atMs <= elapsed + 30) {
            await cues[cueIndex].run();
            cueIndex += 1;
        }

        await onFrame(elapsed, frame);

        const file = path.join(framesDir, `frame-${String(frame).padStart(4, '0')}.jpg`);
        await page.screenshot({
            path: file,
            type: 'jpeg',
            quality: 82,
            captureBeyondViewport: false,
        });

        const target = started + (frame + 1) * frameInterval;
        const delay = target - Date.now();
        if (delay > 0) {
            await wait(delay);
        }
    }

    while (cueIndex < cues.length) {
        await cues[cueIndex].run();
        cueIndex += 1;
    }
}

/**
 * Real-time CDP screencast so CSS reveal animations are not compressed by slow screenshots.
 * @param {import('puppeteer-core').Page} page
 * @param {string} framesDir
 * @param {number} durationSec
 * @param {(api: { waitUntil: (atMs: number) => Promise<void>, elapsed: () => number }) => Promise<void>} timelineFn
 */
async function recordRealtime(page, framesDir, durationSec, timelineFn) {
    fs.mkdirSync(framesDir, { recursive: true });

    const client = await page.createCDPSession();
    /** @type {{ file: string, ts: number }[]} */
    const raw = [];
    let writeChain = Promise.resolve();
    let index = 0;

    client.on('Page.screencastFrame', (frame) => {
        const idx = index;
        index += 1;
        const file = path.join(framesDir, `raw-${String(idx).padStart(5, '0')}.jpg`);

        writeChain = writeChain.then(async () => {
            fs.writeFileSync(file, Buffer.from(frame.data, 'base64'));
            raw.push({ file, ts: frame.metadata?.timestamp ?? (idx / 30) });
            await client.send('Page.screencastFrameAck', { sessionId: frame.sessionId });
        }).catch(() => {});
    });

    await client.send('Page.startScreencast', {
        format: 'jpeg',
        quality: 78,
        maxWidth: VIEWPORT.width,
        maxHeight: VIEWPORT.height,
        everyNthFrame: 1,
    });

    const started = Date.now();
    await timelineFn({
        waitUntil: async (atMs) => {
            const delay = started + atMs - Date.now();
            if (delay > 0) {
                await wait(delay);
            }
        },
        elapsed: () => Date.now() - started,
    });

    const remaining = durationSec * 1000 - (Date.now() - started);
    if (remaining > 0) {
        await wait(remaining);
    }

    await client.send('Page.stopScreencast').catch(() => {});
    await writeChain;

    if (raw.length === 0) {
        throw new Error('Screencast produced no frames');
    }

    const startTs = raw[0].ts;
    const totalFrames = Math.round(durationSec * FPS);

    for (let frame = 0; frame < totalFrames; frame += 1) {
        const targetTs = startTs + (frame / FPS);
        let best = raw[0];
        let bestDist = Math.abs(raw[0].ts - targetTs);

        for (let i = 1; i < raw.length; i += 1) {
            const dist = Math.abs(raw[i].ts - targetTs);
            if (dist < bestDist) {
                best = raw[i];
                bestDist = dist;
            }
        }

        fs.copyFileSync(best.file, path.join(framesDir, `frame-${String(frame).padStart(4, '0')}.jpg`));
    }

    console.log(`  screencast raw frames: ${raw.length}, resampled: ${totalFrames}`);
}

function encodeGif(framesDir, outFile) {
    const palette = path.join(framesDir, 'palette.png');
    const inputPattern = path.join(framesDir, 'frame-%04d.jpg');

    const paletteResult = spawnSync('ffmpeg', [
        '-y',
        '-framerate', String(FPS),
        '-i', inputPattern,
        '-vf', `fps=${FPS},scale=390:-1:flags=lanczos,palettegen=max_colors=192:stats_mode=diff`,
        palette,
    ], { encoding: 'utf8' });

    if (paletteResult.status !== 0) {
        throw new Error(`ffmpeg palettegen failed:\n${paletteResult.stderr}`);
    }

    const gifResult = spawnSync('ffmpeg', [
        '-y',
        '-framerate', String(FPS),
        '-i', inputPattern,
        '-i', palette,
        '-lavfi', `fps=${FPS},scale=390:-1:flags=lanczos[x];[x][1:v]paletteuse=dither=bayer:bayer_scale=3`,
        outFile,
    ], { encoding: 'utf8' });

    if (gifResult.status !== 0) {
        throw new Error(`ffmpeg paletteuse failed:\n${gifResult.stderr}`);
    }
}

function encodeMp4(framesDir, outFile) {
    const inputPattern = path.join(framesDir, 'frame-%04d.jpg');

    const result = spawnSync('ffmpeg', [
        '-y',
        '-framerate', String(FPS),
        '-i', inputPattern,
        '-vf', `fps=${FPS},scale=390:-2:flags=lanczos`,
        '-c:v', 'libx264',
        '-pix_fmt', 'yuv420p',
        '-crf', '20',
        '-preset', 'medium',
        '-movflags', '+faststart',
        outFile,
    ], { encoding: 'utf8' });

    if (result.status !== 0) {
        throw new Error(`ffmpeg mp4 encode failed:\n${result.stderr}`);
    }
}

function encodeMp4FromGif(gifFile, outFile) {
    const result = spawnSync('ffmpeg', [
        '-y',
        '-i', gifFile,
        '-vf', 'scale=trunc(iw/2)*2:trunc(ih/2)*2',
        '-c:v', 'libx264',
        '-pix_fmt', 'yuv420p',
        '-crf', '20',
        '-preset', 'medium',
        '-movflags', '+faststart',
        outFile,
    ], { encoding: 'utf8' });

    if (result.status !== 0) {
        throw new Error(`ffmpeg gif→mp4 failed for ${gifFile}:\n${result.stderr}`);
    }
}

async function installSlowReveal(page) {
    const factor = REVEAL_SLOW_FACTOR;
    await page.evaluateOnNewDocument((slowFactor) => {
        const inject = () => {
            if (document.getElementById('myday-gif-slow-reveal')) {
                return;
            }

            const prefixes = ['env', 'seal', 'curtain', 'story', 'gate', 'bloom', 'crest'];
            const timingVars = prefixes.map((p) => `
                --${p}-crossfade: ${Math.round(1350 * slowFactor)} !important;
                --${p}-hold: ${Math.round(900 * slowFactor)} !important;
                --${p}-zoom: ${Math.round(900 * slowFactor)} !important;
            `).join('');

            const mediaSelectors = [
                '.env-photo', '.seal-photo', '.curtain-photo', '.story-photo',
                '.gate-photo', '.bloom-photo', '.crest-photo',
            ];

            const style = document.createElement('style');
            style.id = 'myday-gif-slow-reveal';
            style.textContent = `
                :root { ${timingVars} }

                ${mediaSelectors.map((base) => `
                    ${base}-stage {
                        transition: opacity ${0.78 * slowFactor}s ease, filter ${0.78 * slowFactor}s ease !important;
                    }
                    ${base}-media {
                        transition:
                            transform ${0.95 * slowFactor}s cubic-bezier(0.65, 0, 0.35, 1),
                            filter ${0.7 * slowFactor}s ease !important;
                    }
                    ${base}-closed {
                        transition:
                            opacity ${1.05 * slowFactor}s cubic-bezier(0.4, 0, 0.2, 1),
                            transform ${1.45 * slowFactor}s cubic-bezier(0.2, 0.75, 0.22, 1),
                            filter ${1 * slowFactor}s ease !important;
                    }
                    ${base}-open {
                        transition:
                            opacity ${1.2 * slowFactor}s cubic-bezier(0.4, 0, 0.2, 1) ${0.12 * slowFactor}s,
                            transform ${1.65 * slowFactor}s cubic-bezier(0.16, 1, 0.3, 1) ${0.08 * slowFactor}s,
                            filter ${1.1 * slowFactor}s ease ${0.08 * slowFactor}s !important;
                    }
                `).join('\n')}
            `;

            (document.head || document.documentElement).appendChild(style);
        };

        if (document.documentElement) {
            inject();
        }
        document.addEventListener('DOMContentLoaded', inject, { once: true });
    }, factor);
}

async function armRevealWatcher(page) {
    await page.evaluate(() => {
        window.__mydayInviteRevealedAt = null;
        document.addEventListener('invitation:revealed', () => {
            window.__mydayInviteRevealedAt = Date.now();
        }, { once: true });
    });
}

async function prepareInvitePage(page, url, { slowReveal = false } = {}) {
    if (slowReveal) {
        await installSlowReveal(page);
    }

    await page.goto(url, { waitUntil: 'networkidle2', timeout: 60000 });
    await wait(900);
    await dismissOverlays(page);
    await hideCaptureChrome(page);
    await waitForVisualReady(page);

    if (slowReveal) {
        const timing = await page.evaluate((slowFactor) => {
            const root = document.documentElement;
            const prefixes = ['env', 'seal', 'curtain', 'story', 'gate', 'bloom', 'crest'];
            for (const p of prefixes) {
                root.style.setProperty(`--${p}-crossfade`, String(Math.round(1350 * slowFactor)));
                root.style.setProperty(`--${p}-hold`, String(Math.round(900 * slowFactor)));
                root.style.setProperty(`--${p}-zoom`, String(Math.round(900 * slowFactor)));
            }
            const css = getComputedStyle(root);
            return {
                env: css.getPropertyValue('--env-crossfade').trim(),
                seal: css.getPropertyValue('--seal-crossfade').trim(),
            };
        }, REVEAL_SLOW_FACTOR);
        console.log('  slowed reveal CSS vars:', timing);
        await armRevealWatcher(page);
    }
}

/**
 * Slow scroll with two short random pauses (same feel as the approved sample).
 * @param {import('puppeteer-core').Page} page
 * @param {{ elapsed: () => number }} api
 * @param {{ scrollStartMs: number, scrollEndMs: number }} opts
 */
async function runSlowScrollWithPauses(page, { elapsed }, { scrollStartMs, scrollEndMs }) {
    const maxScroll = await page.evaluate(() => {
        const max = Math.max(0, document.body.scrollHeight - window.innerHeight);
        return max > 0 ? Math.floor(max * 0.95) : 2000;
    });

    const scrollDuration = Math.max(1000, scrollEndMs - scrollStartMs - 1000);
    const pauseMs = 500;
    const pauseAt = [0.28, 0.62]
        .map((base) => base + (Math.random() * 0.1 - 0.05))
        .map((t) => Math.min(0.85, Math.max(0.18, t)))
        .sort((a, b) => a - b);
    const pauseFired = [false, false];
    console.log(`  scroll pauses at ~${pauseAt.map((t) => Math.round(t * 100)).join('% and ~')}%`);

    let motionElapsed = 0;
    let lastTick = elapsed();

    while (motionElapsed < scrollDuration) {
        const now = elapsed();
        const delta = Math.max(0, now - lastTick);
        lastTick = now;

        const progress = Math.min(1, motionElapsed / scrollDuration);

        let paused = false;
        for (let i = 0; i < pauseAt.length; i += 1) {
            if (! pauseFired[i] && progress >= pauseAt[i]) {
                pauseFired[i] = true;
                console.log(`  scroll pause ${i + 1} at ${(progress * 100).toFixed(0)}%`);
                await wait(pauseMs);
                lastTick = elapsed();
                paused = true;
                break;
            }
        }
        if (paused) {
            continue;
        }

        motionElapsed += delta;
        const t = Math.min(1, motionElapsed / scrollDuration);
        const eased = t < 0.5
            ? 2 * t * t
            : 1 - ((-2 * t + 2) ** 2) / 2;
        await page.evaluate((y) => window.scrollTo(0, y), maxScroll * eased);
        await wait(1000 / FPS);
    }
}

async function captureGif(browser, spec, workRoot, formats) {
    console.log(`\n=== Capturing ${spec.id} ===`);

    if (spec.flow === 'rsvp-yes' || spec.flow === 'rsvp-no') {
        resetGuestRsvp();
    }

    if (spec.reveal !== null) {
        setWeddingSettings({
            theme: spec.theme,
            template: spec.template,
            reveal: spec.reveal,
        });
    } else {
        setWeddingSettings({
            theme: spec.theme,
            template: spec.template,
            reveal: 'envelope',
        });
    }

    const framesDir = path.join(workRoot, spec.id);
    fs.rmSync(framesDir, { recursive: true, force: true });
    fs.mkdirSync(framesDir, { recursive: true });

    const page = await browser.newPage();
    await page.setViewport(VIEWPORT);

    try {
        if (spec.flow === 'contact') {
            await prepareInvitePage(page, CONTACT_URL);
            await page.evaluate(() => window.scrollTo(0, 0));

            const scrollStartMs = 1200;
            const scrollEndMs = spec.durationSec * 1000 - 1000;

            await recordRealtime(page, framesDir, spec.durationSec, async ({ waitUntil, elapsed }) => {
                await waitUntil(scrollStartMs);
                await runSlowScrollWithPauses(page, { elapsed }, { scrollStartMs, scrollEndMs });
            });
        } else {
            await prepareInvitePage(page, INVITE_URL, { slowReveal: true });

            if (spec.flow === 'open-scroll') {
                const idleMs = 1800;
                const revealTimelineMs = Math.round((1350 + 900 + 900) * REVEAL_SLOW_FACTOR);
                const fadeOutMs = Math.round(800 * REVEAL_SLOW_FACTOR);
                const heroHoldMs = 1600;
                const scrollStartMs = idleMs + revealTimelineMs + fadeOutMs + heroHoldMs;
                const scrollEndMs = spec.durationSec * 1000 - 1200;

                console.log(`  timing: idle=${idleMs}ms reveal≈${revealTimelineMs + fadeOutMs}ms scrollFrom=${scrollStartMs}ms scroll≈${scrollEndMs - scrollStartMs}ms total=${spec.durationSec}s`);

                await recordRealtime(page, framesDir, spec.durationSec, async ({ waitUntil, elapsed }) => {
                    await waitUntil(idleMs);
                    const clicked = await clickReveal(page);
                    console.log('  reveal click:', clicked);

                    await waitUntil(scrollStartMs);
                    await runSlowScrollWithPauses(page, { elapsed }, { scrollStartMs, scrollEndMs });
                });
            } else if (spec.flow === 'open-story') {
                const idleMs = 1800;
                const revealHoldMs = Math.round((1350 + 900 + 900) * REVEAL_SLOW_FACTOR) + Math.round(800 * REVEAL_SLOW_FACTOR) + 1200;
                const slideGapMs = 3500;

                console.log(`  story timing: idle=${idleMs}ms reveal≈${revealHoldMs}ms`);

                await recordRealtime(page, framesDir, spec.durationSec, async ({ waitUntil }) => {
                    await waitUntil(idleMs);
                    const clicked = await clickReveal(page);
                    console.log('  reveal click:', clicked);

                    await waitUntil(idleMs + revealHoldMs);
                    await advanceStory(page, 1);

                    await waitUntil(idleMs + revealHoldMs + slideGapMs);
                    await advanceStory(page, 1);

                    await waitUntil(idleMs + revealHoldMs + slideGapMs * 2);
                    await advanceStory(page, 1);
                });
            } else if (spec.flow === 'rsvp-yes' || spec.flow === 'rsvp-no') {
                // Open invite first (off the critical RSVP window), then record the RSVP interaction.
                const clicked = await clickReveal(page);
                console.log('  reveal click:', clicked);
                const revealWait = Math.round((1350 + 900 + 900 + 800) * REVEAL_SLOW_FACTOR) + 800;
                await wait(revealWait);
                await waitForVisualReady(page);
                await dismissOverlays(page);
                await hideCaptureChrome(page);

                await recordRealtime(page, framesDir, spec.durationSec, async ({ waitUntil }) => {
                    await waitUntil(500);
                    await scrollToRsvp(page);
                    await waitUntil(1400);
                    await clickRsvp(page, spec.flow === 'rsvp-yes' ? 'yes' : 'no');
                });
            }
        }

        const saved = [];

        if (formats === 'gif' || formats === 'both') {
            const gifFile = path.join(OUT_ROOT, spec.filename);
            encodeGif(framesDir, gifFile);
            const sizeKb = Math.round(fs.statSync(gifFile).size / 1024);
            console.log(`saved ${gifFile} (${sizeKb} KB)`);
            saved.push(gifFile);
        }

        if (formats === 'mp4' || formats === 'both') {
            const mp4Name = `${spec.id}.mp4`;
            const mp4File = path.join(VIDEO_OUT_ROOT, mp4Name);
            encodeMp4(framesDir, mp4File);
            const sizeKb = Math.round(fs.statSync(mp4File).size / 1024);
            console.log(`saved ${mp4File} (${sizeKb} KB)`);
            saved.push(mp4File);
        }

        return saved;
    } finally {
        await page.close();
        fs.rmSync(framesDir, { recursive: true, force: true });
    }
}

function convertExistingGifsToMp4(selected) {
    fs.mkdirSync(VIDEO_OUT_ROOT, { recursive: true });
    const saved = [];

    for (const spec of selected) {
        const gifFile = path.join(OUT_ROOT, spec.filename);
        if (! fs.existsSync(gifFile)) {
            console.warn(`skip missing gif: ${gifFile}`);
            continue;
        }

        const mp4File = path.join(VIDEO_OUT_ROOT, `${spec.id}.mp4`);
        console.log(`\n=== Converting ${spec.filename} → ${path.basename(mp4File)} ===`);
        encodeMp4FromGif(gifFile, mp4File);
        const sizeKb = Math.round(fs.statSync(mp4File).size / 1024);
        console.log(`saved ${mp4File} (${sizeKb} KB)`);
        saved.push(mp4File);
    }

    return saved;
}

async function main() {
    const argv = process.argv.slice(2);
    const only = parseOnly(argv);
    const except = parseExcept(argv);
    const formats = parseFormat(argv);
    const convertOnly = argv.includes('--from-gifs');

    let selected = only
        ? GIFS.filter((g) => g.id === only || g.filename === only || g.id === only.replace(/\.mp4$/, ''))
        : GIFS;

    if (except.length > 0) {
        selected = selected.filter((g) => ! except.includes(g.id) && ! except.includes(g.filename));
    }

    if (selected.length === 0) {
        throw new Error(`No GIF matched filters. Known ids:\n${GIFS.map((g) => g.id).join('\n')}`);
    }

    if (convertOnly) {
        const saved = convertExistingGifsToMp4(selected);
        console.log('\nDone:', saved.map((f) => path.basename(f)).join(', '));
        return;
    }

    if (! fs.existsSync(CHROME)) {
        throw new Error(`Chrome not found at ${CHROME}`);
    }

    fs.mkdirSync(OUT_ROOT, { recursive: true });
    fs.mkdirSync(VIDEO_OUT_ROOT, { recursive: true });
    const workRoot = fs.mkdtempSync(path.join(os.tmpdir(), 'myday-gifs-'));

    const original = getWeddingSettings();
    console.log('Original wedding settings:', original);
    console.log('Output formats:', formats);

    const browser = await puppeteer.launch({
        executablePath: CHROME,
        headless: true,
        defaultViewport: null,
        args: [
            '--ignore-certificate-errors',
            '--no-sandbox',
            `--window-size=${VIEWPORT.width},${VIEWPORT.height}`,
        ],
    });

    try {
        for (const spec of selected) {
            await captureGif(browser, spec, workRoot, formats);
        }
    } finally {
        await browser.close();
        setWeddingSettings({
            theme: original.theme,
            template: original.template,
            reveal: original.reveal_animation,
        });
        console.log('Restored wedding settings:', original);
        fs.rmSync(workRoot, { recursive: true, force: true });
    }

    console.log('\nDone:', selected.map((g) => g.id).join(', '));
}

const isDirectRun = process.argv[1]
    && path.resolve(process.argv[1]) === path.resolve(new URL(import.meta.url).pathname);

if (isDirectRun) {
    main().catch((err) => {
        console.error(err);
        process.exitCode = 1;
    });
}
