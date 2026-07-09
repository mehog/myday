# Twemoji assets for PDF rendering

Dompdf cannot render emoji fonts, so guest names with emoji are converted to inline PNG images using [Twemoji](https://github.com/twitter/twemoji) graphics.

## Attribution

Graphics from Twemoji are copyright Twitter, Inc and other contributors, licensed under [CC-BY 4.0](https://creativecommons.org/licenses/by/4.0/).

## How caching works

Emoji PNGs are **not** stored in source control. On first use, `App\Support\PdfEmoji` fetches the required Twemoji PNG from jsDelivr and caches it on disk at:

```
storage/emoji/twemoji/72x72/{hex}.png
```

Subsequent PDF exports read from that cache. If the CDN is unreachable or the emoji is not in Twemoji 14.0.2, the original character is escaped in the PDF.

Filenames use lowercase Unicode codepoints joined by dashes (e.g. `2764-fe0f-200d-1f525.png` for ZWJ sequences). The helper also tries filenames without variation selectors (`fe0f`) when the full sequence is not found.

## Optional cache warming

No manual setup is required. To pre-warm the cache on servers without outbound internet during PDF generation:

```bash
# Download one emoji by codepoint hex (e.g. 1f338 for cherry blossom)
./scripts/download-twemoji.sh 1f338

# Download the full Twemoji 72x72 PNG set
./scripts/download-twemoji.sh --all
```

The download script writes to `storage/emoji/twemoji/72x72/`.
