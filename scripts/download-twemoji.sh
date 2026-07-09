#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
TARGET_DIR="${ROOT_DIR}/storage/emoji/twemoji/72x72"
CDN_BASE="https://cdn.jsdelivr.net/gh/twitter/twemoji@14.0.2/assets/72x72"

mkdir -p "${TARGET_DIR}"

download_hex() {
    local hex="$1"
    local target="${TARGET_DIR}/${hex}.png"

    if [[ -f "${target}" ]]; then
        echo "skip ${hex}.png"
        return 0
    fi

    if curl -fsSL -o "${target}" "${CDN_BASE}/${hex}.png"; then
        echo "saved ${hex}.png"
        return 0
    fi

    rm -f "${target}"
    echo "missing ${hex}.png" >&2
    return 1
}

if [[ "${1:-}" == "--all" ]]; then
    tmp_dir="$(mktemp -d)"
    trap 'rm -rf "${tmp_dir}"' EXIT

    curl -fsSL -o "${tmp_dir}/twemoji.tar.gz" \
        "https://github.com/twitter/twemoji/archive/refs/tags/v14.0.2.tar.gz"

    tar -xzf "${tmp_dir}/twemoji.tar.gz" \
        -C "${tmp_dir}" \
        --strip-components=3 \
        twemoji-14.0.2/assets/72x72

    cp -n "${tmp_dir}"/*.png "${TARGET_DIR}/" 2>/dev/null || true
    echo "Twemoji 72x72 assets synced to ${TARGET_DIR}"
    exit 0
fi

if [[ $# -eq 0 ]]; then
    echo "Usage: $0 <hex> [hex...] | --all" >&2
    exit 1
fi

for hex in "$@"; do
    download_hex "${hex}"
done
