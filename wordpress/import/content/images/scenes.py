"""One flat 'sticker' scene per article (480x480 box), built from a few shared pieces, so no two blogs look alike even in the same
category. gen-playful-images.py imports SCENES (slug -> svg) and BG (slug -> pastel page colour).

Pieces: box, shelf, clipboard, notebook, bell, gauge, bars, scale, gift, avatar, folder, grid, monitor, cloud, bubble, building, play, cursor.
"""
N = '#0B2A43'
ST = 'stroke="%s" stroke-width="5" stroke-linejoin="round" stroke-linecap="round"' % N
RED, AMBER, BLUE, TEAL, PINK, PURPLE, SUN = '#F43F5E', '#FBBF24', '#60A5FA', '#2DD4BF', '#F472B6', '#A78BFA', '#FDE68A'


def g(x, y, inner, rot=0, s=1):
    t = 'translate(%s %s)' % (x, y)
    if rot:
        t += ' rotate(%s)' % rot
    if s != 1:
        t += ' scale(%s)' % s
    return '<g transform="%s">%s</g>' % (t, inner)


def star(x, y, s=14, fill='#fff'):
    return '<path d="M%d %d Q%d %d %d %d Q%d %d %d %d Q%d %d %d %d Q%d %d %d %d Z" fill="%s"/>' % (
        x, y - s, x, y, x + s, y, x, y, x, y + s, x, y, x - s, y, x, y, x, y - s, fill)


def dots(pts, fill='#fff', r=7):
    return ''.join('<circle cx="%d" cy="%d" r="%d" fill="%s"/>' % (x, y, r, fill) for x, y in pts)


def box(w=120, h=90, c=AMBER):
    return ('<rect x="0" y="0" width="%d" height="%d" rx="12" fill="%s" %s/>' % (w, h, c, ST)
            + '<path d="M0 %d H%d M%d 0 V%d" fill="none" %s/>' % (h * .34, w, w / 2, h * .34, ST))


def clipboard(w=190, h=250, rows=4):
    out = '<rect x="0" y="14" width="%d" height="%d" rx="16" fill="#fff" %s/>' % (w, h, ST)
    out += '<rect x="%d" y="0" width="%d" height="34" rx="10" fill="%s" %s/>' % (w / 2 - 36, 72, N, ST)
    for i in range(rows):
        y = 70 + i * 44
        out += '<circle cx="34" cy="%d" r="13" fill="%s" %s/><path d="M27 %d l5 5 l9 -10" stroke="%s" stroke-width="4" fill="none" stroke-linecap="round" stroke-linejoin="round"/>' % (y, TEAL if i != rows - 1 else '#fff', ST, y, N)
        out += '<rect x="58" y="%d" width="%d" height="12" rx="6" fill="#CBD5E1"/>' % (y - 6, w - 90 - (i % 2) * 30)
    return out


def notebook(w=200, h=250):
    out = '<rect x="0" y="0" width="%d" height="%d" rx="16" fill="#fff" %s/>' % (w, h, ST)
    for i in range(6):
        out += '<rect x="-10" y="%d" width="30" height="14" rx="7" fill="%s"/>' % (24 + i * 38, N)
    for i in range(5):
        out += '<rect x="48" y="%d" width="%d" height="12" rx="6" fill="#CBD5E1"/>' % (38 + i * 40, w - 80 - (i % 3) * 24)
    out += '<rect x="48" y="38" width="70" height="12" rx="6" fill="%s"/>' % RED
    return out


def bell():
    return ('<path d="M0 60 a50 50 0 0 1 100 0 V92 l16 22 H-16 l16 -22 Z" fill="%s" %s/>' % (AMBER, ST)
            + '<path d="M38 126 a14 14 0 0 0 28 0" fill="%s" %s/><circle cx="50" cy="2" r="9" fill="%s" %s/>' % (RED, ST, N, ST))


def gauge(h=250, level=.45):
    fill_h = (h - 20) * level
    return ('<rect x="0" y="0" width="64" height="%d" rx="32" fill="#fff" %s/>' % (h, ST)
            + '<rect x="12" y="%d" width="40" height="%d" rx="20" fill="%s"/>' % (h - 12 - fill_h, fill_h, TEAL)
            + '<path d="M82 %d H140 M82 50 H140" %s fill="none"/>' % (h - 12 - fill_h, ST)
            + '<circle cx="152" cy="%d" r="10" fill="%s" %s/><circle cx="152" cy="50" r="10" fill="%s" %s/>' % (h - 12 - fill_h, AMBER, ST, RED, ST))


def bars(w=240, h=190):
    out = '<rect x="0" y="0" width="%d" height="%d" rx="18" fill="#fff" %s/>' % (w, h, ST)
    for i, (bh, c) in enumerate(((60, BLUE), (110, TEAL), (80, AMBER), (130, PINK), (48, BLUE))):
        out += '<rect x="%d" y="%d" width="30" height="%d" rx="8" fill="%s" %s/>' % (24 + i * 42, h - 22 - bh, bh, c, ST)
    return out


def scale():
    return ('<path d="M100 20 V190" %s fill="none"/><path d="M40 190 H160" %s fill="none"/>' % (ST, ST)
            + '<path d="M10 54 H190" %s fill="none"/>' % ST
            + '<path d="M10 54 l-30 70 h60 Z" fill="%s" %s/><path d="M190 54 l-30 70 h60 Z" fill="%s" %s/>' % (SUN, ST, '#CCFBF1', ST)
            + '<circle cx="100" cy="20" r="12" fill="%s" %s/>' % (AMBER, ST))


def gift(w=150, h=130, c=PINK):
    return ('<rect x="0" y="34" width="%d" height="%d" rx="12" fill="%s" %s/>' % (w, h - 34, c, ST)
            + '<rect x="-8" y="34" width="%d" height="34" rx="10" fill="%s" %s/>' % (w + 16, c, ST)
            + '<path d="M%d 34 V%d" %s fill="none"/>' % (w / 2, h, ST)
            + '<path d="M%d 34 c-36 -44 -62 -10 -22 0 Z M%d 34 c36 -44 62 -10 22 0 Z" fill="#fff" %s/>' % (w / 2, w / 2, ST))


def avatar(body, skin, hat=None):
    out = ('<path d="M-62 150 a62 62 0 0 1 124 0 V220 H-62 Z" fill="%s" %s/>' % (body, ST)
           + '<circle cx="0" cy="64" r="46" fill="%s" %s/>' % (skin, ST)
           + '<path d="M-14 74 q14 12 28 0" fill="none" stroke="%s" stroke-width="5" stroke-linecap="round"/>' % N
           + '<circle cx="-16" cy="58" r="4" fill="%s"/><circle cx="16" cy="58" r="4" fill="%s"/>' % (N, N))
    if hat == 'cap':
        out += '<path d="M-64 -6 L0 -34 L64 -6 L0 22 Z" fill="%s" %s/><path d="M-36 8 V30 q36 22 72 0 V8" fill="%s" %s/><path d="M64 -6 V40" %s/>' % (N, ST, N, ST, ST)
    return out


def folder(c=AMBER, w=180, h=130):
    return ('<path d="M0 22 a14 14 0 0 1 14 -14 H64 l20 22 H%d a14 14 0 0 1 14 14 V%d a14 14 0 0 1 -14 14 H14 a14 14 0 0 1 -14 -14 Z" fill="%s" %s/>' % (w - 14, h - 14, c, ST)
            + '<path d="M0 48 H%d" %s fill="none"/>' % (w, ST))


def grid(w=220, h=180):
    out = '<rect x="0" y="0" width="%d" height="%d" rx="14" fill="#fff" %s/><rect x="0" y="0" width="%d" height="36" rx="14" fill="%s" %s/>' % (w, h, ST, w, TEAL, ST)
    out += '<path d="M0 72 H%d M0 108 H%d M0 144 H%d M%d 0 V%d M%d 0 V%d" %s fill="none"/>' % (w, w, w, w / 3, h, 2 * w / 3, h, ST)
    out += '<rect x="%d" y="78" width="%d" height="24" fill="%s"/>' % (w / 3 + 6, w / 3 - 12, SUN)
    return out


def monitor(w=260, h=170, c='#fff'):
    return ('<rect x="0" y="0" width="%d" height="%d" rx="16" fill="%s" %s/>' % (w, h, c, ST)
            + '<path d="M0 36 H%d" %s fill="none"/><circle cx="22" cy="18" r="5" fill="%s"/><circle cx="40" cy="18" r="5" fill="%s"/>' % (w, ST, RED, TEAL)
            + '<path d="M%d %d v34 M%d %d h80" %s fill="none"/>' % (w / 2, h, w / 2 - 40, h + 34, ST))


def cloud(c='#fff'):
    return '<path d="M30 110 a36 36 0 0 1 8 -70 a50 50 0 0 1 96 -8 a42 42 0 0 1 14 78 Z" fill="%s" %s/>' % (c, ST)


def bubble(c='#fff', q='?'):
    return ('<path d="M20 0 H150 a20 20 0 0 1 20 20 V90 a20 20 0 0 1 -20 20 H70 l-32 30 v-30 H20 a20 20 0 0 1 -20 -20 V20 A20 20 0 0 1 20 0 Z" fill="%s" %s/>' % (c, ST)
            + '<text x="85" y="78" font-family="C, sans-serif" font-weight="700" font-size="64" text-anchor="middle" fill="%s">%s</text>' % (N, q))


def building():
    return ('<rect x="0" y="40" width="220" height="190" rx="14" fill="#fff" %s/>' % ST
            + '<rect x="75" y="0" width="70" height="70" rx="14" fill="%s" %s/>' % (RED, ST)
            + '<path d="M110 14 V56 M89 35 H131" stroke="#fff" stroke-width="10" stroke-linecap="round"/>'
            + ''.join('<rect x="%d" y="%d" width="34" height="30" rx="8" fill="%s" %s/>' % (24 + i * 64, 96 + j * 52, SUN if (i + j) % 2 else '#CCFBF1', ST) for i in range(3) for j in range(2))
            + '<rect x="88" y="180" width="44" height="50" rx="8" fill="%s" %s/>' % (BLUE, ST))


def play(c=RED):
    return '<circle cx="0" cy="0" r="46" fill="%s" %s/><path d="M-12 -22 L26 0 L-12 22 Z" fill="#fff" %s/>' % (c, ST, ST)


def cursor():
    return '<path d="M0 0 L0 74 L22 54 L40 92 L58 84 L40 46 L72 44 Z" fill="#fff" %s/>' % ST


def arrow_curve(d, tip):
    return '<path d="%s" fill="none" stroke="%s" stroke-width="7" stroke-linecap="round"/><path d="%s" fill="none" stroke="%s" stroke-width="7" stroke-linecap="round" stroke-linejoin="round"/>' % (d, N, tip, N)


def tag(c=RED):
    return '<path d="M0 0 h70 l30 36 l-30 36 h-70 z" fill="%s" %s/><circle cx="18" cy="36" r="8" fill="#fff"/>' % (c, ST)


def zz():
    return ''.join('<text x="%d" y="%d" font-family="C, sans-serif" font-weight="700" font-size="%d" fill="%s">z</text>' % (x, y, s, N) for x, y, s in ((0, 40, 44), (40, 0, 34), (70, -34, 26)))


SCENES = {}
BG = {}

# ---- expiry ----
SCENES['min-max-stock'] = (
    g(70, 90, gauge(280, .42)) + g(300, 190, box(130, 100, AMBER)) + g(300, 290, box(130, 100, BLUE)) + g(330, 120, box(100, 70, PINK))
    + star(420, 70, 15) + dots(((40, 40), (450, 420), (260, 70))))
BG['min-max-stock'] = '#C6F0DC'

SCENES['monthly-stock-count'] = (
    g(40, 70, clipboard(210, 290, 5), -4) + g(290, 200, box(130, 100, AMBER)) + g(290, 310, box(130, 100, PINK)) + g(340, 150, box(100, 70, BLUE))
    + '<circle cx="420" cy="80" r="34" fill="%s" %s/><path d="M404 80 l11 11 l20 -22" stroke="%s" stroke-width="6" fill="none" stroke-linecap="round" stroke-linejoin="round"/>' % (TEAL, ST, N)
    + star(30, 400, 13) + dots(((450, 300), (250, 60))))
BG['monthly-stock-count'] = '#CDEFF0'

SCENES['non-moving-stock'] = (
    g(80, 190, box(220, 190, '#D8C7A8')) + '<path d="M80 224 H300" stroke="%s" stroke-width="5"/>' % N
    + '<circle cx="150" cy="300" r="9" fill="%s"/><circle cx="230" cy="300" r="9" fill="%s"/><path d="M160 340 q30 -22 60 0" fill="none" stroke="%s" stroke-width="5" stroke-linecap="round"/>' % (N, N, N)
    + g(310, 160, zz()) + g(330, 330, '<circle cx="50" cy="50" r="50" fill="%s" %s/><path d="M50 50 V20 M50 50 L72 62" stroke="%s" stroke-width="6" stroke-linecap="round" fill="none"/>' % (SUN, ST, N))
    + star(420, 70, 14) + dots(((40, 60), (440, 420))))
BG['non-moving-stock'] = '#D6EFC9'

SCENES['fefo-explained'] = (
    g(40, 220, box(130, 110, PINK)) + g(190, 220, box(130, 110, BLUE)) + g(340, 220, box(110, 110, AMBER))
    + ''.join('<rect x="%d" y="262" width="%d" height="26" rx="8" fill="#fff" %s/>' % (x + 14, w - 28, ST) for x, w in ((40, 130), (190, 130), (340, 110)))
    + '<text x="105" y="282" font-family="C, sans-serif" font-weight="700" font-size="16" text-anchor="middle" fill="%s">NOV</text>' % N
    + '<text x="255" y="282" font-family="C, sans-serif" font-weight="700" font-size="16" text-anchor="middle" fill="%s">MAR</text>' % N
    + '<text x="395" y="282" font-family="C, sans-serif" font-weight="700" font-size="16" text-anchor="middle" fill="%s">JUN</text>' % N
    + arrow_curve('M105 190 C105 120 200 90 300 100', 'M276 78 L304 100 L280 124')
    + '<circle cx="105" cy="400" r="26" fill="%s" %s/><text x="105" y="410" font-family="C, sans-serif" font-weight="700" font-size="28" text-anchor="middle" fill="%s">1</text>' % (RED, ST, '#fff')
    + star(430, 80, 15) + dots(((40, 70), (450, 430))))
BG['fefo-explained'] = '#CFEFE8'

SCENES['short-book'] = (
    g(70, 80, notebook(210, 280), -5) + g(290, 120, bell(), 8, 1.25)
    + '<circle cx="430" cy="120" r="26" fill="%s" %s/><text x="430" y="130" font-family="C, sans-serif" font-weight="700" font-size="30" text-anchor="middle" fill="#fff">3</text>' % (RED, ST)
    + star(40, 420, 13) + dots(((440, 60), (250, 420))))
BG['short-book'] = '#BFEFE3'

SCENES['read-the-expiry-report'] = (
    g(40, 150, bars(250, 200)) + g(300, 100, '<circle cx="90" cy="90" r="80" fill="#fff" %s/><path d="M70 90 h40 M90 70 v40" stroke="%s" stroke-width="7" stroke-linecap="round"/><path d="M150 150 L220 220" stroke="%s" stroke-width="26" stroke-linecap="round"/><path d="M150 150 L220 220" stroke="%s" stroke-width="13" stroke-linecap="round"/>' % (ST, TEAL, N, PURPLE))
    + g(60, 70, '<rect x="0" y="0" width="150" height="40" rx="20" fill="#fff" %s/>' % ST + dots(((30, 20), (75, 20), (120, 20)), RED, 9)) + star(430, 70, 14) + dots(((30, 420), (450, 400))))
BG['read-the-expiry-report'] = '#D5F3E0'

# ---- purchase ----
SCENES['compare-distributor-rates'] = (
    g(150, 200, scale(), 0, 1.15) + g(40, 60, tag(BLUE), -10, 1.2) + g(300, 50, tag(AMBER), 8, 1.2)
    + '<text x="72" y="140" font-family="C, sans-serif" font-weight="700" font-size="28" fill="#fff" transform="rotate(-10 72 140)">₹92</text>'
    + '<text x="338" y="112" font-family="C, sans-serif" font-weight="700" font-size="28" fill="%s" transform="rotate(8 338 112)">₹99</text>' % N
    + star(430, 250, 14) + dots(((40, 300), (450, 420))))
BG['compare-distributor-rates'] = '#C9DBFF'

SCENES['scheme-free-quantity'] = (
    g(150, 80, gift(180, 150, PINK)) + g(40, 270, box(130, 100, BLUE)) + g(180, 270, box(130, 100, AMBER)) + g(320, 270, box(120, 100, TEAL))
    + '<circle cx="410" cy="110" r="42" fill="%s" %s/><text x="410" y="124" font-family="C, sans-serif" font-weight="700" font-size="38" text-anchor="middle" fill="%s">+1</text>' % (RED, ST, '#fff')
    + star(60, 90, 16) + dots(((440, 400), (40, 420))))
BG['scheme-free-quantity'] = '#D4E3FF'

SCENES['purchase-return-credit-note'] = (
    g(50, 220, box(160, 130, AMBER)) + arrow_curve('M240 230 C320 130 400 150 380 80', 'M358 100 L382 76 L410 98')
    + g(280, 250, '<rect x="0" y="0" width="150" height="190" rx="14" fill="#fff" %s/><rect x="22" y="26" width="70" height="12" rx="6" fill="%s"/><rect x="22" y="58" width="104" height="10" rx="5" fill="#CBD5E1"/><rect x="22" y="84" width="84" height="10" rx="5" fill="#CBD5E1"/><circle cx="75" cy="140" r="30" fill="%s" %s/><text x="75" y="152" font-family="C, sans-serif" font-weight="700" font-size="34" text-anchor="middle" fill="%s">₹</text>' % (ST, N, TEAL, ST, N), 5)
    + star(60, 90, 15) + dots(((440, 440), (40, 420))))
BG['purchase-return-credit-note'] = '#D9E4FF'

# ---- staff ----
SCENES['shift-handover'] = (
    '<circle cx="110" cy="100" r="62" fill="%s" %s/>' % (SUN, ST)
    + '<path d="M410 56 a62 62 0 1 0 0 120 a50 50 0 1 1 0 -120 Z" fill="#fff" %s/>' % ST
    + g(110, 180, avatar(BLUE, '#FDE68A'), 0, .85) + g(370, 180, avatar(PINK, '#FCD9B6'), 0, .85)
    + g(190, 240, clipboard(110, 140, 3), -8, 1.0) + star(250, 70, 14) + dots(((40, 250), (450, 250), (250, 440))))
BG['shift-handover'] = '#FFE8B8'

SCENES['train-counter-assistant'] = (
    g(130, 120, avatar(TEAL, '#FBCFE8', 'cap'), 0, 1.0)
    + '<path d="M290 400 h60 v-40 h60 v-40 h60" fill="none" %s/>' % ST
    + '<path d="M290 400 h60 v-40 h60 v-40 h60 V400 Z" fill="%s" %s/>' % (AMBER, ST)
    + star(440, 270, 18, AMBER) + g(310, 180, '<rect x="0" y="0" width="110" height="80" rx="10" fill="#fff" %s/><path d="M55 0 V80" %s/>' % (ST, ST), 6)
    + star(50, 80, 14) + dots(((450, 90), (40, 400))))
BG['train-counter-assistant'] = '#FFEDC2'

# ---- switching ----
SCENES['after-the-switch-old-data'] = (
    g(40, 140, folder(AMBER, 190, 140), -4) + g(100, 250, folder(PINK, 190, 140), 3) + g(250, 70, monitor(210, 140), 6)
    + '<rect x="290" y="268" width="90" height="74" rx="14" fill="#fff" %s/><path d="M308 268 v-20 a27 27 0 0 1 54 0 v20" fill="none" %s/><circle cx="335" cy="305" r="9" fill="%s"/>' % (ST, ST, N)
    + star(430, 400, 14) + dots(((40, 70), (440, 220))))
BG['after-the-switch-old-data'] = '#FFD5CC'

SCENES['from-excel-to-software'] = (
    g(30, 120, grid(210, 190), -4) + arrow_curve('M240 200 C280 130 320 130 350 170', 'M330 190 L354 172 L338 146')
    + g(290, 200, monitor(180, 120), 5) + g(80, 340, '<rect x="0" y="0" width="150" height="70" rx="10" fill="#fff" %s/><path d="M0 24 H150" %s fill="none"/><path d="M50 0 V70 M100 0 V70" %s fill="none"/>' % (ST, ST, ST), -3)
    + star(430, 70, 15) + dots(((40, 60), (440, 440))))
BG['after-the-switch-old-data'] = '#FFD5CC'
BG['from-excel-to-software'] = '#FFDDD4'

# ---- buying ----
SCENES['desktop-vs-browser'] = (
    g(30, 200, monitor(210, 140), -3) + g(260, 70, cloud(), 0, 1.2) + g(300, 270, '<rect x="0" y="0" width="150" height="110" rx="16" fill="#fff" %s/><path d="M0 30 H150" %s fill="none"/><circle cx="20" cy="15" r="5" fill="%s"/><circle cx="38" cy="15" r="5" fill="%s"/><rect x="20" y="50" width="80" height="12" rx="6" fill="%s"/><rect x="20" y="74" width="106" height="12" rx="6" fill="#CBD5E1"/>' % (ST, ST, RED, TEAL, BLUE), 4)
    + '<text x="262" y="320" font-family="C, sans-serif" font-weight="700" font-size="38" text-anchor="middle" fill="%s">vs</text>' % N + star(420, 90, 15) + dots(((40, 90), (440, 430))))
BG['desktop-vs-browser'] = '#E1D6FF'

SCENES['hospital-pharmacy-software-ipd-opd'] = (
    g(30, 120, building(), 0, 1.0)
    + g(280, 230, '<rect x="0" y="40" width="160" height="50" rx="12" fill="#fff" %s/><rect x="-10" y="20" width="60" height="40" rx="12" fill="%s" %s/><circle cx="30" cy="40" r="10" fill="#fff" %s/><path d="M20 90 v28 M140 90 v28" %s/>' % (ST, BLUE, ST, ST, ST), 0, 1)
    + g(290, 340, '<rect x="0" y="0" width="150" height="70" rx="12" fill="%s" %s/><text x="75" y="50" font-family="C, sans-serif" font-weight="700" font-size="42" text-anchor="middle" fill="%s">042</text>' % ('#fff', ST, N), 4)
    + star(430, 90, 15) + dots(((40, 90), (440, 440))))
BG['hospital-pharmacy-software-ipd-opd'] = '#E8DEFF'

SCENES['questions-for-vendors'] = (
    g(40, 90, bubble('#fff', '?'), -5) + g(240, 170, bubble(SUN, '?'), 4) + g(60, 300, clipboard(150, 160, 3), -4)
    + g(330, 330, '<circle cx="50" cy="50" r="46" fill="%s" %s/><path d="M30 52 l14 14 l28 -30" stroke="%s" stroke-width="7" fill="none" stroke-linecap="round" stroke-linejoin="round"/>' % (TEAL, ST, N))
    + star(430, 80, 15) + dots(((250, 70), (450, 280))))
BG['questions-for-vendors'] = '#EADFFF'

SCENES['run-a-good-demo'] = (
    g(50, 90, monitor(300, 200), -2) + g(190, 190, play(RED)) + g(330, 250, cursor(), 8)
    + '<path d="M60 380 l12 24 l26 4 l-19 18 l5 26 l-24 -13 l-24 13 l5 -26 l-19 -18 l26 -4 Z" fill="%s" %s transform="translate(20 -20) scale(.8)"/>' % (AMBER, ST)
    + g(380, 340, '<rect x="0" y="0" width="80" height="80" rx="16" fill="#fff" %s/>' % ST + dots(((24, 26), (24, 54)), TEAL, 8) + '<rect x="40" y="20" width="26" height="10" rx="5" fill="#CBD5E1"/><rect x="40" y="48" width="26" height="10" rx="5" fill="#CBD5E1"/>')
    + star(440, 80, 14) + dots(((40, 70), (250, 440))))
BG['run-a-good-demo'] = '#DDD3FF'


# ---- a chain of stores around one dashboard ----
def shop(c=BLUE):
    return ('<rect x="0" y="46" width="120" height="96" rx="10" fill="#fff" %s/>' % ST
            + '<path d="M-8 46 L8 6 H112 L128 46 Z" fill="%s" %s/>' % (c, ST)
            + ''.join('<path d="M%d 46 q10 22 20 0" fill="#fff" %s/>' % (x, ST) for x in (0, 40, 80))
            + '<rect x="46" y="86" width="28" height="56" rx="6" fill="%s" %s/>' % (SUN, ST)
            + '<rect x="12" y="84" width="24" height="24" rx="6" fill="#CCFBF1" %s/><rect x="84" y="84" width="24" height="24" rx="6" fill="#CCFBF1" %s/>' % (ST, ST))


SCENES['multi-branch-pharmacy-chains'] = (
    '<path d="M240 190 L90 300 M240 190 L240 300 M240 190 L390 300" fill="none" stroke="%s" stroke-width="5" stroke-dasharray="3 11" stroke-linecap="round"/>' % N
    + g(150, 40, monitor(180, 110))
    + '<rect x="176" y="92" width="22" height="40" rx="5" fill="%s"/><rect x="206" y="72" width="22" height="60" rx="5" fill="%s"/><rect x="236" y="104" width="22" height="28" rx="5" fill="%s"/><rect x="266" y="82" width="22" height="50" rx="5" fill="%s"/>' % (BLUE, TEAL, AMBER, PINK)
    + g(30, 290, shop(BLUE), 0, .9) + g(180, 300, shop(RED), 0, .9) + g(330, 290, shop(TEAL), 0, .9)
    + star(60, 90, 15) + star(430, 80, 14) + dots(((450, 240), (30, 440), (250, 450))))
BG['multi-branch-pharmacy-chains'] = '#E3DAFF'
