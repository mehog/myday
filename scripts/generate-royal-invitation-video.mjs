#!/usr/bin/env node
/**
 * Compose a language-neutral royal invitation marketing clip from
 * text-free still artwork in public/img/video/royal-invitation/.
 *
 * Output: public/videos/royal-invitation-textless.mp4
 * Specs:  720×1280, 60 fps, H.264, yuv420p, no audio, ~12.75 s
 *
 * Usage:
 *   node scripts/generate-royal-invitation-video.mjs
 */

import { spawnSync } from 'node:child_process';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const ROOT = path.resolve(__dirname, '..');
const ASSET_DIR = path.join(ROOT, 'public/img/video/royal-invitation');
const OUT_FILE = path.join(ROOT, 'public/videos/royal-invitation-textless.mp4');

const WIDTH = 720;
const HEIGHT = 1280;
const FPS = 60;
const TOTAL_DURATION = 12.75;

const ASSETS = {
    terraceOpen: path.join(ASSET_DIR, 'terrace-open.png'),
    terraceBirds: path.join(ASSET_DIR, 'terrace-birds.png'),
    envelopeClosed: path.join(ASSET_DIR, 'envelope-closed.png'),
    envelopeOpen: path.join(ASSET_DIR, 'envelope-open.png'),
    sparkles: path.join(ASSET_DIR, 'sparkles-overlay.png'),
};

/**
 * Timeline (progressive xfade; offsets computed from segment lengths):
 *  0.00–3.70  terrace open, slow push-in
 *  3.70–4.50  crossfade → envelope closed
 *  4.50–5.10  envelope closed, subtle drift
 *  5.10–6.10  crossfade → envelope open / glow
 *  6.10–8.30  envelope open hold
 *  8.30–9.50  crossfade → terrace with birds
 *  9.50–12.75 terrace birds, soft pull / drift
 *
 * sum(segments) - sum(xfades) = 15.75 - 3.00 = 12.75
 */
const SEGMENTS = [
    {
        id: 'terrace-open',
        asset: 'terraceOpen',
        duration: 4.5,
        // Slow push-in toward the arch / moon
        motion: "x='(in_w-out_w)/2+8*t':y='(in_h-out_h)/2-6*t'",
        scale: 1.14,
    },
    {
        id: 'envelope-closed',
        asset: 'envelopeClosed',
        duration: 2.4,
        motion: "x='(in_w-out_w)/2':y='(in_h-out_h)/2+3*sin(2*PI*t/3)'",
        scale: 1.08,
    },
    {
        id: 'envelope-open',
        asset: 'envelopeOpen',
        duration: 3.2,
        motion: "x='(in_w-out_w)/2':y='(in_h-out_h)/2-4*t'",
        scale: 1.1,
    },
    {
        id: 'terrace-birds',
        asset: 'terraceBirds',
        duration: 5.65,
        // Gentle pull-back / drift for a loop-friendly close
        motion: "x='(in_w-out_w)/2-6*t':y='(in_h-out_h)/2+4*t'",
        scale: 1.16,
    },
];

const XFADES = [
    { duration: 0.8 },
    { duration: 1.0 },
    { duration: 1.2 },
];

function assertAssets() {
    for (const [key, file] of Object.entries(ASSETS)) {
        if (!fs.existsSync(file)) {
            throw new Error(`Missing artwork asset (${key}): ${file}`);
        }
    }
}

function runFfmpeg(args, label) {
    process.stdout.write(`→ ${label}… `);
    const result = spawnSync('ffmpeg', ['-hide_banner', '-loglevel', 'error', '-y', ...args], {
        encoding: 'utf8',
        maxBuffer: 20 * 1024 * 1024,
    });

    if (result.status !== 0) {
        process.stdout.write('failed\n');
        throw new Error(`${label} failed:\n${result.stderr || result.stdout}`);
    }

    process.stdout.write('ok\n');
}

/**
 * Cover-fit the still to 9:16, upscale for motion headroom, then animate
 * a crop window for subtle camera drift / Ken-Burns style motion.
 */
function buildMotionFilter(segment) {
    const scaledW = Math.round(WIDTH * segment.scale);
    const scaledH = Math.round(HEIGHT * segment.scale);

    return [
        `scale=${scaledW}:${scaledH}:force_original_aspect_ratio=increase:flags=lanczos`,
        `crop=${scaledW}:${scaledH}`,
        `crop=${WIDTH}:${HEIGHT}:${segment.motion}`,
        `fps=${FPS}`,
        'setsar=1',
        'format=yuv420p',
    ].join(',');
}

function renderSegment(tmpDir, segment) {
    const input = ASSETS[segment.asset];
    const outFile = path.join(tmpDir, `${segment.id}.mp4`);

    runFfmpeg(
        [
            '-loop', '1',
            '-i', input,
            '-vf', buildMotionFilter(segment),
            '-t', String(segment.duration),
            '-r', String(FPS),
            '-c:v', 'libx264',
            '-preset', 'medium',
            '-crf', '18',
            '-pix_fmt', 'yuv420p',
            '-an',
            outFile,
        ],
        `segment ${segment.id} (${segment.duration.toFixed(2)}s)`,
    );

    return outFile;
}

function crossfadeSegments(tmpDir, segmentFiles) {
    if (segmentFiles.length === 1) {
        return segmentFiles[0];
    }

    // Build a progressive xfade chain across all segments.
    const inputs = [];
    for (const file of segmentFiles) {
        inputs.push('-i', file);
    }

    const filterParts = [];
    let lastLabel = '[0:v]';
    let cumulative = SEGMENTS[0].duration;

    for (let i = 0; i < XFADES.length; i += 1) {
        const fade = XFADES[i];
        const nextIndex = i + 1;
        const outLabel = i === XFADES.length - 1 ? '[vout]' : `[v${i}]`;
        const offset = cumulative - fade.duration;

        filterParts.push(
            `${lastLabel}[${nextIndex}:v]xfade=transition=fade:duration=${fade.duration}:offset=${offset.toFixed(3)}${outLabel}`,
        );

        cumulative = cumulative + SEGMENTS[nextIndex].duration - fade.duration;
        lastLabel = outLabel;
    }

    const outFile = path.join(tmpDir, 'crossfaded.mp4');

    runFfmpeg(
        [
            ...inputs,
            '-filter_complex', filterParts.join(';'),
            '-map', '[vout]',
            '-t', String(TOTAL_DURATION),
            '-r', String(FPS),
            '-c:v', 'libx264',
            '-preset', 'medium',
            '-crf', '18',
            '-pix_fmt', 'yuv420p',
            '-an',
            outFile,
        ],
        'crossfade timeline',
    );

    return outFile;
}

function overlaySparkles(tmpDir, baseVideo) {
    const outFile = path.join(tmpDir, 'with-sparkles.mp4');

    // Soft drifting sparkle layer from the black-background overlay.
    // Double-height scroll creates continuous downward glitter motion.
    runFfmpeg(
        [
            '-i', baseVideo,
            '-loop', '1',
            '-t', String(TOTAL_DURATION),
            '-i', ASSETS.sparkles,
            '-filter_complex', [
                `[1:v]scale=${WIDTH}:${HEIGHT}:flags=lanczos,format=gbrp,` +
                    `split[s1][s2];` +
                    `[s1][s2]vstack,` +
                    `crop=${WIDTH}:${HEIGHT}:0:'mod(t*36\\,${HEIGHT})',` +
                    `eq=brightness=0.05:saturation=1.1[sp]`,
                '[0:v]format=gbrp[base]',
                '[base][sp]blend=all_mode=screen:all_opacity=0.4,format=yuv420p,setsar=1[vout]',
            ].join(';'),
            '-map', '[vout]',
            '-t', String(TOTAL_DURATION),
            '-r', String(FPS),
            '-c:v', 'libx264',
            '-preset', 'medium',
            '-crf', '18',
            '-pix_fmt', 'yuv420p',
            '-an',
            outFile,
        ],
        'screen-blend sparkles',
    );

    return outFile;
}

function finalize(inputVideo) {
    fs.mkdirSync(path.dirname(OUT_FILE), { recursive: true });

    runFfmpeg(
        [
            '-i', inputVideo,
            '-vf', `fps=${FPS},scale=${WIDTH}:${HEIGHT}:flags=lanczos,format=yuv420p`,
            '-t', String(TOTAL_DURATION),
            '-r', String(FPS),
            '-c:v', 'libx264',
            '-preset', 'medium',
            '-crf', '20',
            '-pix_fmt', 'yuv420p',
            '-movflags', '+faststart',
            '-an',
            OUT_FILE,
        ],
        'final encode',
    );
}

function main() {
    assertAssets();

    const tmpDir = fs.mkdtempSync(path.join(os.tmpdir(), 'royal-invitation-video-'));
    console.log(`Working directory: ${tmpDir}`);
    console.log(`Assets: ${ASSET_DIR}`);
    console.log(`Output: ${OUT_FILE}`);
    console.log('');

    try {
        const segmentFiles = SEGMENTS.map((segment) => renderSegment(tmpDir, segment));
        const crossfaded = crossfadeSegments(tmpDir, segmentFiles);
        const withSparkles = overlaySparkles(tmpDir, crossfaded);
        finalize(withSparkles);

        const sizeKb = Math.round(fs.statSync(OUT_FILE).size / 1024);
        console.log('');
        console.log(`saved ${OUT_FILE} (${sizeKb} KB)`);
    } finally {
        fs.rmSync(tmpDir, { recursive: true, force: true });
    }
}

main();
