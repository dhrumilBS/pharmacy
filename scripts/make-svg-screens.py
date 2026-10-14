"""Build crisp vector SVG screens from the extracted v2 HTML (see make-svg-screens-extract.mjs).
Each SVG holds the screen as live HTML/CSS in a foreignObject, with a subsetted Campton font embedded
(an SVG loaded through <img> cannot fetch fonts). Usage (from the site root):
  node scripts/make-svg-screens-extract.mjs screens.json     # needs: npm i playwright-core, Edge installed
  python scripts/make-svg-screens.py screens.json assets/img/screens   # needs: fontTools + brotli
Output files are named v2-<screen>.svg and shown with svg_img() in partials/config.php."""
import base64, io, json, pathlib, re, sys
ROOT = pathlib.Path(__file__).resolve().parent.parent
from fontTools import subset
from fontTools.ttLib import TTFont

FONT_DIR = str(ROOT / 'assets/fonts/campton') + '/'
WEIGHTS = ['400', '500', '600', '700', '800']
data = json.load(open(sys.argv[1], encoding='utf-8'))
out = sys.argv[2]

# Symbols Campton lacks are drawn as tiny inline vectors instead of relying on a system fallback font.
GLYPHS = {
    '\u2713': '<svg xmlns="http://www.w3.org/2000/svg" class="gsym" viewBox="0 0 12 12"><path d="M2 6.4l2.6 2.6L10 3.2" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    '\u25cf': '<svg xmlns="http://www.w3.org/2000/svg" class="gsym" viewBox="0 0 12 12"><circle cx="6" cy="6" r="3.6" fill="currentColor"/></svg>',
    '\u25be': '<svg xmlns="http://www.w3.org/2000/svg" class="gsym" viewBox="0 0 12 12"><path d="M2.4 4.2h7.2L6 9z" fill="currentColor"/></svg>',
    '\u25b8': '<svg xmlns="http://www.w3.org/2000/svg" class="gsym" viewBox="0 0 12 12"><path d="M4.2 2.4v7.2L9 6z" fill="currentColor"/></svg>',
}

def font_face(weight, text):
    opts = subset.Options(); opts.flavor = 'woff2'; opts.layout_features = ['kern', 'liga', 'tnum', 'lnum']
    f = TTFont(FONT_DIR + 'campton-%s.woff2' % weight)
    s = subset.Subsetter(opts); s.populate(text=text); s.subset(f)
    buf = io.BytesIO(); f.flavor = 'woff2'; f.save(buf)
    b64 = base64.b64encode(buf.getvalue()).decode()
    return '@font-face{font-family:"CamptonS";font-weight:%s;font-style:normal;src:url(data:font/woff2;base64,%s) format("woff2")}' % (weight, b64)

for name, d in data.items():
    ui = re.sub(r'@import\s+url\([^)]*\)\s*;', '', open(ROOT / 'screenshots/v2/src/ui.css', encoding='utf-8').read())
    css, html, text = ui + '\n' + d['css'], d['html'], d['text']
    css = re.sub(r'(?<![\w-])html\s*,\s*body(?![\w-])', '.sv', css)
    css = re.sub(r'(?<![\w.#-])body(?![\w-])', '.sv', css)
    css = css.replace('Inter', 'CamptonS').replace('"Plus Jakarta Sans"', 'CamptonS').replace('Plus Jakarta Sans', 'CamptonS')
    for ch, svg in GLYPHS.items():
        html = html.replace(ch, svg)
    css += '\n.sv .gsym{display:inline-block;width:.9em;height:.9em;vertical-align:-.12em;flex:none}\n.sv{font-family:CamptonS,system-ui,sans-serif;position:relative}'
    chars = ''.join(sorted(set(re.sub(r'<[^>]+>', ' ', html) + text + ' 0123456789.,:;/()%+-\u2013\u2014\u2019\u2026\u20b9\u00b7\u00d7\u2212\u2191')))
    fonts = '\n'.join(font_face(w, chars) for w in WEIGHTS)
    svg = ('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 900" width="1440" height="900" role="img">'
           '<style>%s\n%s</style>'
           '<foreignObject x="0" y="0" width="1440" height="900"><div xmlns="http://www.w3.org/1999/xhtml" class="sv">%s</div></foreignObject></svg>') % (fonts, css, html)
    path = '%s/v2-%s.svg' % (out, name)
    open(path, 'w', encoding='utf-8').write(svg)
    print(path, round(len(svg) / 1024), 'KB')
