"""Inner illustrations for long articles (1200x600, 2:1; rendered at 2x by wordpress/tools/render-image.mjs), in the same flat
sticker style as the feature images but with no headline: they sit between sections. Built from the pieces in scenes.py.

Run: python gen-inner-images.py   then render each inner-*.html to .png.
"""
import html
import os

from scenes import (AMBER, BLUE, N, PINK, PURPLE, RED, ST, SUN, TEAL, box, clipboard, dots, g, grid, monitor, notebook,
                    shop, star)

HERE = os.path.dirname(os.path.abspath(__file__))


def shelf(rows, per_row, boxes_color, w=300):
    """A shelf: a frame, plank lines and boxes."""
    out = '<rect x="0" y="0" width="%d" height="%d" rx="14" fill="#fff" %s/>' % (w, rows * 100 + 20, ST)
    for r in range(rows):
        y = 100 + r * 100
        out += '<path d="M14 %d H%d" %s fill="none"/>' % (y, w - 14, ST)
        for i in range(per_row[r]):
            c = boxes_color[(r + i) % len(boxes_color)]
            out += '<rect x="%d" y="%d" width="%d" height="%d" rx="8" fill="%s" %s/>' % (24 + i * 62, y - 62, 52, 62, c, ST)
    return out


def arrow(d, tip):
    return ('<path d="%s" fill="none" stroke="%s" stroke-width="7" stroke-linecap="round"/>'
            '<path d="%s" fill="none" stroke="%s" stroke-width="7" stroke-linecap="round" stroke-linejoin="round"/>') % (d, N, tip, N)


SCENES = {}

# 1. One store overflowing, another empty
SCENES['stock-imbalance'] = (
    '#E3DAFF',
    g(10, 90, shelf(3, (3, 3, 3), (AMBER, PINK, BLUE, TEAL), 210), -2)
    + g(30, 30, '<path d="M0 0 h86 l24 30 l-24 30 h-86 z" fill="%s" %s/><text x="42" y="40" font-family="C, sans-serif" font-weight="700" font-size="22" text-anchor="middle" fill="#fff">EXP</text>' % (RED, ST), -8, .8)
    + g(260, 90, shelf(3, (0, 1, 0), (SUN,), 210), 2)
    + '<text x="365" y="372" font-family="C, sans-serif" font-weight="700" font-size="60" text-anchor="middle" fill="%s">0</text>' % N
    + arrow('M120 420 C170 460 310 460 350 420', 'M326 424 L352 418 L350 444')
    + star(240, 40, 15) + star(450, 70, 13) + dots(((30, 440), (440, 440))))

# 2. Records in many places become one view
SCENES['one-view'] = (
    '#D5E6FF',
    g(20, 150, notebook(110, 150), -8) + g(150, 40, grid(130, 110), 6) + g(130, 250, clipboard(100, 130, 3), -4)
    + '<text x="40" y="100" font-family="C, sans-serif" font-weight="700" font-size="54" fill="%s">?</text>' % N
    + arrow('M250 250 C275 240 295 240 310 250', 'M292 230 L312 250 L292 270')
    + g(320, 150, monitor(150, 110))
    + '<rect x="342" y="200" width="18" height="26" rx="5" fill="%s"/><rect x="368" y="186" width="18" height="40" rx="5" fill="%s"/><rect x="394" y="208" width="18" height="18" rx="5" fill="%s"/><rect x="420" y="192" width="18" height="34" rx="5" fill="%s"/>' % (BLUE, TEAL, AMBER, PINK)
    + star(440, 70, 15) + dots(((30, 400), (440, 410), (250, 440))))

# 3. A stock transfer between two stores
SCENES['stock-transfer'] = (
    '#FFD9D2',
    g(10, 230, shop(BLUE), 0, 1.0) + g(340, 230, shop(TEAL), 0, 1.0)
    + arrow('M110 210 C170 60 310 60 370 210', 'M348 190 L372 214 L394 190')
    + g(190, 70, box(100, 80, AMBER), -8)
    + '<circle cx="70" cy="400" r="26" fill="#fff" %s/><text x="70" y="410" font-family="C, sans-serif" font-weight="700" font-size="28" text-anchor="middle" fill="%s">9</text>' % (ST, N)
    + '<circle cx="400" cy="400" r="26" fill="#fff" %s/><text x="400" y="410" font-family="C, sans-serif" font-weight="700" font-size="28" text-anchor="middle" fill="%s">+3</text>' % (ST, N)
    + star(240, 30, 15) + dots(((30, 60), (450, 80), (240, 440))))

# 4. Records and rules, kept calm
SCENES['records-calm'] = (
    '#D4F1E6',
    g(40, 60, clipboard(220, 300, 5), -4)
    + '<path d="M370 120 L450 150 V230 C450 290 410 330 370 350 C330 330 290 290 290 230 V150 Z" fill="%s" %s/>' % (BLUE, ST)
    + '<path d="M335 235 l26 26 l50 -56" stroke="#fff" stroke-width="12" fill="none" stroke-linecap="round" stroke-linejoin="round"/>'
    + g(290, 370, '<rect x="0" y="0" width="150" height="44" rx="22" fill="#fff" %s/><circle cx="26" cy="22" r="8" fill="%s"/><rect x="46" y="16" width="80" height="12" rx="6" fill="#CBD5E1"/>' % (ST, TEAL))
    + star(440, 60, 15) + star(30, 410, 13) + dots(((250, 40), (440, 430))))

# 5. A calm rollout: one store, then the next, then the rest
SCENES['calm-rollout'] = (
    '#FFE8B8',
    g(10, 130, shop(TEAL), 0, .95) + g(180, 140, shop(BLUE), 0, .95) + '<g opacity=".55">' + g(350, 150, shop(PINK), 0, .95) + '</g>'
    + '<circle cx="64" cy="100" r="30" fill="%s" %s/><path d="M50 100 l10 10 l20 -22" stroke="%s" stroke-width="6" fill="none" stroke-linecap="round" stroke-linejoin="round"/>' % (TEAL, ST, N)
    + '<path d="M140 250 C150 270 170 270 180 250" fill="none" stroke="%s" stroke-width="5" stroke-dasharray="3 10" stroke-linecap="round"/>' % N
    + '<path d="M310 260 C320 280 340 280 350 260" fill="none" stroke="%s" stroke-width="5" stroke-dasharray="3 10" stroke-linecap="round"/>' % N
    + '<path d="M20 380 H460" stroke="%s" stroke-width="6" stroke-linecap="round" stroke-dasharray="1 14"/>' % N
    + '<circle cx="70" cy="380" r="14" fill="%s" %s/><circle cx="240" cy="380" r="14" fill="%s" %s/><circle cx="410" cy="380" r="14" fill="#fff" %s/>' % (TEAL, ST, AMBER, ST, ST)
    + star(440, 60, 15) + star(40, 60, 13) + dots(((250, 50), (450, 440))))

CSS = """
*{box-sizing:border-box;margin:0}
body{width:1200px;height:600px;overflow:hidden;background:%(bg)s;position:relative;display:grid;place-items:center}
.blob{position:absolute;border-radius:50%%;background:rgba(255,255,255,.55)}
svg{position:relative;width:520px;height:520px}
"""

for name, (bg, art) in SCENES.items():
    page = ('<!doctype html><html lang="en"><head><meta charset="utf-8"><title>%s (inner image, 1200x600)</title><style>%s'
            '@font-face{font-family:C;font-weight:700;src:url("../../../themes/pharmacy/assets/fonts/campton/campton-700.woff2")}</style></head><body>'
            '<div class="blob" style="left:-120px;top:-140px;width:420px;height:420px"></div>'
            '<div class="blob" style="right:-90px;bottom:-160px;width:380px;height:380px"></div>'
            '<svg viewBox="0 0 480 480">%s</svg></body></html>\n') % (html.escape(name), CSS % {'bg': bg}, art)
    with open(os.path.join(HERE, 'inner-%s.html' % name), 'w', encoding='utf-8') as f:
        f.write(page)
    print(name)
