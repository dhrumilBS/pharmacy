"""Playful, category-wise feature images for the blog (1200x600, 2:1; rendered at 2x by wordpress/tools/render-image.mjs).

One flat "sticker" illustration per topic (thick navy outline, flat fills, a pastel page), used two ways:
  cat-<topic>.html     the illustration alone, centred on the topic's pastel page (the category image)
  <slug>-feature.html  the same illustration on the right and the article's title on the left (the post's feature image)

Run: python gen-playful-images.py   then render every .html here to .png:  node wordpress/tools/render-image.mjs in.html out.png 1200 600
"""
import html
import os

from scenes import BG, SCENES

HERE = os.path.dirname(os.path.abspath(__file__))
N = '#0B2A43'      # navy outline and text
STROKE = 'stroke="%s" stroke-width="5" stroke-linejoin="round" stroke-linecap="round"' % N


def star(x, y, s=14, fill='#fff'):
    return '<path d="M%d %d l%d %d l%d %d l-%d %d l-%d %d l-%d -%d l%d -%d z" fill="%s"/>' % (
        x, y - s, s * .3, s * .7, s * .7, s * .3, s * .7, s * .3, s * .3, s * .7, s * .3, s * .7, s * .3, s * .7, fill) if False else \
        '<path d="M%d %d Q%d %d %d %d Q%d %d %d %d Q%d %d %d %d Q%d %d %d %d Z" fill="%s"/>' % (
            x, y - s, x, y, x + s, y, x, y, x, y + s, x, y, x - s, y, x, y, x, y - s, fill)


def dots(points, fill='#fff', r=7):
    return ''.join('<circle cx="%d" cy="%d" r="%d" fill="%s"/>' % (x, y, r, fill) for x, y in points)


def illo(topic):
    """An illustration in a 480x480 box."""
    if topic == 'expiry':
        return (
            # blister pack
            '<g transform="rotate(-8 250 360)"><rect x="90" y="300" width="300" height="110" rx="22" fill="#fff" %s/>' % STROKE
            + ''.join('<circle cx="%d" cy="355" r="26" fill="%s" %s/>' % (x, c, STROKE) for x, c in ((150, '#FBBF24'), (230, '#60A5FA'), (310, '#F472B6')))
            + '</g>'
            # calendar
            + '<rect x="60" y="70" width="250" height="230" rx="28" fill="#fff" %s/>' % STROKE
            + '<path d="M60 130 V98 a28 28 0 0 1 28 -28 H282 a28 28 0 0 1 28 28 V130 Z" fill="#F43F5E" %s/>' % STROKE
            + '<rect x="112" y="48" width="24" height="54" rx="12" fill="%s"/><rect x="234" y="48" width="24" height="54" rx="12" fill="%s"/>' % (N, N)
            + ''.join('<rect x="%d" y="%d" width="40" height="34" rx="9" fill="%s"/>' % (x, y, c) for x, y, c in (
                (92, 156, '#CCFBF1'), (146, 156, '#CCFBF1'), (200, 156, '#CCFBF1'), (254, 156, '#FDE68A'),
                (92, 202, '#CCFBF1'), (146, 202, '#FDE68A'), (200, 202, '#FDA4AF'), (254, 202, '#CCFBF1'),
                (92, 248, '#CCFBF1'), (146, 248, '#CCFBF1')))
            + '<path d="M206 214 l28 28 M234 214 l-28 28" stroke="%s" stroke-width="6" stroke-linecap="round"/>' % N
            # clock
            + '<circle cx="372" cy="150" r="62" fill="#FDE68A" %s/>' % STROKE
            + '<path d="M372 150 V112 M372 150 L398 166" stroke="%s" stroke-width="6" stroke-linecap="round" fill="none"/><circle cx="372" cy="150" r="7" fill="%s"/>' % (N, N)
            + star(430, 60, 16) + star(28, 330, 12) + dots(((440, 250), (36, 120)))
        )
    if topic == 'purchase':
        return (
            # boxes
            '<rect x="60" y="250" width="190" height="150" rx="14" fill="#FBBF24" %s/>' % STROKE
            + '<path d="M60 296 H250 M155 250 V296" %s fill="none"/>' % STROKE
            + '<rect x="210" y="300" width="190" height="110" rx="14" fill="#60A5FA" %s/>' % STROKE
            + '<path d="M210 336 H400 M305 300 V336" %s fill="none"/>' % STROKE
            # bill
            + '<g transform="rotate(-7 250 160)"><rect x="140" y="30" width="230" height="270" rx="18" fill="#fff" %s/>' % STROKE
            + '<rect x="168" y="62" width="110" height="14" rx="7" fill="%s"/>' % N
            + ''.join('<rect x="168" y="%d" width="%d" height="10" rx="5" fill="#CBD5E1"/><rect x="318" y="%d" width="28" height="10" rx="5" fill="#CBD5E1"/>' % (y, w, y) for y, w in ((110, 120), (142, 100), (174, 130)))
            + '<circle cx="320" cy="248" r="32" fill="#2DD4BF" %s/>' % STROKE
            + '<path d="M304 248 l12 12 l22 -24" stroke="%s" stroke-width="6" fill="none" stroke-linecap="round" stroke-linejoin="round"/></g>' % N
            # tag
            + '<path d="M370 70 h70 l30 36 l-30 36 h-70 z" fill="#F43F5E" %s/><circle cx="386" cy="106" r="8" fill="#fff"/>' % STROKE
            + star(36, 120, 14) + dots(((440, 230), (60, 220)))
        )
    if topic == 'switching':
        return (
            '<rect x="30" y="110" width="220" height="170" rx="18" fill="#fff" %s/>' % STROKE
            + '<path d="M30 148 H250" %s/>' % STROKE + dots(((56, 130), (80, 130)), '#F43F5E', 7)
            + '<rect x="56" y="170" width="90" height="14" rx="7" fill="#CBD5E1"/><rect x="56" y="198" width="140" height="14" rx="7" fill="#CBD5E1"/><rect x="56" y="226" width="70" height="14" rx="7" fill="#CBD5E1"/>'
            + '<rect x="230" y="210" width="220" height="170" rx="18" fill="#fff" %s/>' % STROKE
            + '<path d="M230 248 H450" %s/>' % STROKE + dots(((256, 230), (280, 230)), '#2DD4BF', 7)
            + '<rect x="256" y="270" width="110" height="14" rx="7" fill="#60A5FA"/><rect x="256" y="298" width="150" height="14" rx="7" fill="#60A5FA"/><rect x="256" y="326" width="80" height="14" rx="7" fill="#60A5FA"/>'
            # arrows
            + '<path d="M150 90 C220 30 330 40 380 120" fill="none" stroke="%s" stroke-width="7" stroke-linecap="round"/><path d="M356 112 L384 128 L388 96" fill="none" stroke="%s" stroke-width="7" stroke-linecap="round" stroke-linejoin="round"/>' % (N, N)
            + '<path d="M330 420 C260 460 150 450 100 370" fill="none" stroke="%s" stroke-width="7" stroke-linecap="round"/><path d="M122 376 L96 366 L90 398" fill="none" stroke="%s" stroke-width="7" stroke-linecap="round" stroke-linejoin="round"/>' % (N, N)
            + star(430, 60, 15) + star(40, 410, 12) + dots(((440, 440), (20, 40)))
        )
    if topic == 'staff':
        def person(cx, body, skin):
            return ('<path d="M%d 330 a62 62 0 0 1 124 0 V400 H%d Z" fill="%s" %s/>' % (cx - 62, cx - 62, body, STROKE)
                    + '<circle cx="%d" cy="250" r="44" fill="%s" %s/>' % (cx, skin, STROKE)
                    + '<path d="M%d 244 q14 12 28 0" fill="none" stroke="%s" stroke-width="5" stroke-linecap="round" transform="translate(-14 14)"/>' % (cx, N))
        return (
            person(110, '#60A5FA', '#FDE68A') + person(240, '#2DD4BF', '#FBCFE8') + person(370, '#F43F5E', '#FCD9B6')
            # key
            + '<circle cx="150" cy="120" r="44" fill="#FBBF24" %s/><circle cx="150" cy="120" r="14" fill="#fff" %s/>' % (STROKE, STROKE)
            + '<path d="M194 120 H320 M280 120 V152 M310 120 V146" fill="none" %s/>' % STROKE
            # lock badge
            + '<rect x="360" y="70" width="86" height="72" rx="14" fill="#fff" %s/><path d="M382 70 v-18 a21 21 0 0 1 42 0 v18" fill="none" %s/><circle cx="403" cy="104" r="9" fill="%s"/>' % (STROKE, STROKE, N)
            + star(40, 60, 15) + dots(((450, 300), (30, 200)))
        )
    # buying
    return (
        '<g><circle cx="210" cy="200" r="130" fill="#fff" %s/>' % STROKE
        + '<rect x="130" y="130" width="160" height="16" rx="8" fill="#CBD5E1"/><rect x="130" y="176" width="130" height="16" rx="8" fill="#CBD5E1"/><rect x="130" y="222" width="150" height="16" rx="8" fill="#CBD5E1"/>'
        + ''.join('<circle cx="108" cy="%d" r="14" fill="#2DD4BF" %s/><path d="M101 %d l5 5 l9 -10" stroke="%s" stroke-width="4" fill="none" stroke-linecap="round" stroke-linejoin="round"/>' % (y, STROKE, y, N) for y in (138, 184, 230))
        + '<path d="M306 296 L410 400" stroke="%s" stroke-width="30" stroke-linecap="round"/><path d="M306 296 L410 400" stroke="#A78BFA" stroke-width="16" stroke-linecap="round"/></g>' % N
        + star(410, 90, 18) + star(60, 400, 13) + dots(((440, 220), (30, 70)))
    )


TOPICS = {
    'expiry': ('Expiry & stock', '#BFEFE3'),
    'purchase': ('Purchase', '#C9DBFF'),
    'switching': ('Switching', '#FFD9D2'),
    'staff': ('Counter & staff', '#FFE8B8'),
    'buying': ('Buying', '#E4DAFF'),
}

POSTS = [
    ('cut-expiry-loss', 'expiry', 'A 15-minute weekly expiry check'),
    ('min-max-stock', 'expiry', 'Set a minimum and a maximum for each item'),
    ('check-distributor-bill-before-entry', 'purchase', 'Check the bill before you save'),
    ('switch-pharmacy-software', 'switching', 'Switch without stopping billing'),
    ('staff-logins-roles', 'staff', 'One login for each person'),
    ('desktop-vs-browser', 'buying', 'Desktop or browser-based?'),
    ('hospital-pharmacy-software-ipd-opd', 'buying', 'One stock, two counters'),
    ('read-the-expiry-report', 'expiry', 'Read the expiry report in five minutes'),
    ('monthly-stock-count', 'expiry', 'A monthly stock count without closing'),
    ('non-moving-stock', 'expiry', 'Find the stock that stopped moving'),
    ('fefo-explained', 'expiry', 'Earliest expiry goes out first'),
    ('short-book', 'expiry', 'A short book that builds itself'),
    ('compare-distributor-rates', 'purchase', 'Compare rates without a spreadsheet'),
    ('scheme-free-quantity', 'purchase', 'What a scheme really saves'),
    ('purchase-return-credit-note', 'purchase', 'Returns and credit notes, step by step'),
    ('shift-handover', 'staff', 'A handover that takes five minutes'),
    ('train-counter-assistant', 'staff', 'Train a new assistant in a week'),
    ('after-the-switch-old-data', 'switching', 'What to do with your old data'),
    ('from-excel-to-software', 'switching', 'From registers to software'),
    ('questions-for-vendors', 'buying', 'Eight questions for any vendor'),
    ('run-a-good-demo', 'buying', 'Get more out of a demo'),
    ('multi-branch-pharmacy-chains', 'buying', 'Many stores, one set of rules'),
    ('pharmacy-inventory-management-tips', 'expiry', '12 ways to manage pharmacy stock'),
]

CSS = """
@font-face{font-family:C;font-weight:700;src:url("../../../themes/pharmacy/assets/fonts/campton/campton-700.woff2")}
@font-face{font-family:C;font-weight:600;src:url("../../../themes/pharmacy/assets/fonts/campton/campton-600.woff2")}
*{box-sizing:border-box;margin:0}
body{width:1200px;height:600px;overflow:hidden;font-family:C,sans-serif;color:#0B2A43;background:%(bg)s;position:relative;display:flex;align-items:center}
.t{flex:none;width:560px;padding-left:80px;position:relative;z-index:2}
.k{display:inline-block;padding:7px 14px;border-radius:999px;background:#fff;border:3px solid #0B2A43;font:600 15px C;letter-spacing:.08em;text-transform:uppercase}
h1{margin-top:22px;font:700 62px/1.06 C;letter-spacing:-.03em}
.i{position:absolute;top:60px;height:480px;width:480px;z-index:1}
.l{right:70px}.c{left:360px}
.blob{position:absolute;border-radius:50%%;background:rgba(255,255,255,.55)}
"""


def page(title, bg, body):
    return '<!doctype html><html lang="en"><head><meta charset="utf-8"><title>%s (1200x600, 2:1)</title><style>%s</style></head><body>%s</body></html>\n' % (
        html.escape(title), CSS % {'bg': bg}, body)


for topic, (label, bg) in TOPICS.items():
    body = '<div class="blob" style="left:-120px;top:-140px;width:420px;height:420px"></div><div class="blob" style="right:-90px;bottom:-160px;width:380px;height:380px"></div>'
    body += '<svg class="i c" viewBox="0 0 480 480">%s</svg>' % illo(topic)
    with open(os.path.join(HERE, 'cat-%s.html' % topic), 'w', encoding='utf-8') as f:
        f.write(page('Category: ' + label, bg, body))

for slug, topic, title in POSTS:
    label, bg = TOPICS[topic]
    bg = BG.get(slug, bg)
    body = '<div class="blob" style="left:-120px;top:-140px;width:420px;height:420px"></div><div class="blob" style="right:-90px;bottom:-160px;width:380px;height:380px"></div>'
    body += '<div class="t"><span class="k">%s</span><h1>%s</h1></div><svg class="i l" viewBox="0 0 480 480">%s</svg>' % (html.escape(label), html.escape(title), SCENES.get(slug) or illo(topic))
    with open(os.path.join(HERE, slug + '-feature.html'), 'w', encoding='utf-8') as f:
        f.write(page(title, bg, body))
print('ok')
