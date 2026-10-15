"""Convert the Campton OTFs in font/campton/ into subset WOFF2 webfonts in assets/fonts/campton/.
font/ is git-ignored (source OTFs are local only); only the WOFF2 output is committed.

Only the weights the site uses are converted. Glyphs are subset to Latin + Latin-1 + general punctuation,
plus the symbols the site prints (₹, arrows, ✓, ×, ·) to keep files small. Characters missing from Campton
fall back to the next font in the stack.

Usage:  python scripts/make-webfonts.py      (needs fontTools + brotli: pip install fonttools brotli)
"""
from pathlib import Path
from fontTools import subset
from fontTools.ttLib import TTFont

ROOT = Path(__file__).resolve().parent.parent
SRC = ROOT / "font" / "campton"
OUT = ROOT / "assets" / "fonts" / "campton"

# source file -> (output name, css weight, css style)
WEIGHTS = {
    "CamptonLight.otf":      ("campton-300", 300, "normal"),
    "CamptonBook.otf":       ("campton-400", 400, "normal"),
    "CamptonBookItalic.otf": ("campton-400i", 400, "italic"),
    "CamptonMedium.otf":     ("campton-500", 500, "normal"),
    "CamptonSemiBold.otf":   ("campton-600", 600, "normal"),
    "CamptonBold.otf":       ("campton-700", 700, "normal"),
    "CamptonExtraBold.otf":  ("campton-800", 800, "normal"),
}
UNICODES = "U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+2000-206F,U+20B9,U+2122,U+2190-2193,U+2212,U+2713,U+00D7"


def main() -> None:
    OUT.mkdir(parents=True, exist_ok=True)
    for src_name, (out_name, weight, style) in WEIGHTS.items():
        src = SRC / src_name
        font = TTFont(src)
        opts = subset.Options()
        opts.flavor = "woff2"
        opts.layout_features = ["*"]          # keep kerning, ligatures, tabular figures
        opts.name_IDs = ["*"]
        opts.notdef_outline = True
        sub = subset.Subsetter(options=opts)
        sub.populate(unicodes=subset.parse_unicodes(UNICODES))
        sub.subset(font)
        dst = OUT / f"{out_name}.woff2"
        font.flavor = "woff2"
        font.save(dst)
        has_rupee = 0x20B9 in TTFont(src).getBestCmap()
        print(f"{dst.name:20} {weight} {style:6} {dst.stat().st_size / 1024:6.1f} KB  (from {src.stat().st_size / 1024:.0f} KB, ₹ glyph: {'yes' if has_rupee else 'no'})")


if __name__ == "__main__":
    main()
