"""Writes the 2:1 feature-image sources (1200x600, rendered at 2x by wordpress/tools/render-image.mjs) for the guides
that came with the first import. Same family as distributor-bill-feature.html: a pale page, the topic and the headline
on the left, and one small illustrative card on the right. Every card is labelled as a sample or an illustration.

Run:  python gen-feature-images.py   then render each .html to .png with render-image.mjs (see import/blog-feature-images.php).
"""
import html
import os

HERE = os.path.dirname(os.path.abspath(__file__))

CSS = """
@font-face{font-family:C;font-weight:700;src:url("../../../themes/pharmacy/assets/fonts/campton/campton-700.woff2")}
@font-face{font-family:C;font-weight:600;src:url("../../../themes/pharmacy/assets/fonts/campton/campton-600.woff2")}
@font-face{font-family:C;font-weight:400;src:url("../../../themes/pharmacy/assets/fonts/campton/campton-400.woff2")}
*{box-sizing:border-box;margin:0}
body{width:1200px;height:600px;overflow:hidden;font-family:C,sans-serif;color:#081C2C;background:#F6F8FB;position:relative;display:flex;align-items:center;gap:52px;padding:0 72px}
.l{flex:none;width:480px}
.k{font:600 15px C;margin-bottom:16px;letter-spacing:.14em;text-transform:uppercase;color:%(accent)s}
h1{font:700 54px/1.08 C;letter-spacing:-.03em}
.card{flex:1;min-width:0;background:#fff;border-radius:16px;box-shadow:inset 0 0 0 1px #E2E8F0;padding:24px 30px 10px}
.card header{display:flex;justify-content:space-between;align-items:baseline;padding-bottom:14px;border-bottom:1.5px solid #081C2C}
.card header b{font:700 19px C}
.card header span{font:400 14px C;color:#64748B}
.row{display:grid;grid-template-columns:1fr auto;align-items:center;gap:14px;padding:17px 0;border-bottom:1px solid #E2E8F0;font:400 17px C}
.row:last-child{border-bottom:0}
.row em{font:600 16px C;font-style:normal;color:#0F766E}
.row.w em{color:#B45309}
.row.b em{color:#1D4ED8}
.row.t{border-top:1.5px solid #081C2C;border-bottom:0;margin-top:2px}
.row.t span{font-weight:600}
.row.t em{font:700 26px C;color:#1D4ED8}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:12px 16px;padding:18px 0 14px}
.chip{display:flex;align-items:center;gap:12px;padding:14px 16px;border-radius:12px;background:#F6F8FB;font:600 17px C}
.chip i{width:22px;height:22px;border-radius:50%%;background:#CCFBF1;display:grid;place-items:center;flex:none}
.chip svg{width:13px;height:13px}
.two{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr) minmax(0,1fr);gap:0 14px;font:400 15.5px C}
.two>*{padding:15px 0;border-bottom:1px solid #E2E8F0}
.two .h{font:600 13px C;letter-spacing:.08em;text-transform:uppercase;color:#64748B;border-bottom:1.5px solid #081C2C;padding-top:4px}
.two .l{color:#475569}
.tag{position:absolute;right:72px;bottom:26px;font:400 13px C;color:#94A3B8}
"""

CHECK = '<svg viewBox="0 0 12 12" fill="none" stroke="#0F766E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m2 6.5 2.6 2.6L10 3"/></svg>'


def rows(items):
    out = ''
    for kind, label, value in items:
        out += '<div class="row %s"><span>%s</span><em>%s</em></div>' % (kind, html.escape(label), html.escape(value))
    return out


def grid(items):
    return '<div class="grid">' + ''.join('<div class="chip"><i>%s</i>%s</div>' % (CHECK, html.escape(t)) for t in items) + '</div>'


def two(head, items):
    out = '<div class="two"><div class="h"></div><div class="h">%s</div><div class="h">%s</div>' % (html.escape(head[0]), html.escape(head[1]))
    for l, a, b in items:
        out += '<div class="l">%s</div><div>%s</div><div>%s</div>' % (html.escape(l), html.escape(a), html.escape(b))
    return out + '</div>'


GUIDES = [
    ('cut-expiry-loss', 'Expiry & stock', '#0F766E', 'A 15-minute weekly expiry check',
     ('Weekly expiry check', 'Sample, not real stock'),
     rows([('w', 'Within 30 days', 'Return first'), ('w', 'Next 31 to 60 days', 'Plan the returns'), ('', 'Beyond 60 days', 'Leave on the shelf')]), 'Illustration'),
    ('min-max-stock', 'Expiry & stock', '#0F766E', 'Set a minimum and a maximum for each item',
     ('Minimum stock', 'Illustrative numbers'),
     rows([('', 'Daily sales', '4 strips'), ('', 'Days to next delivery', '× 3 days'), ('', 'Cushion, one extra day', '+ 4 strips'), ('t', 'Minimum', '14 strips')]), 'Illustration'),
    ('check-distributor-bill', 'Purchase', '#1D4ED8', 'Six checks before you save a distributor bill',
     ('Before you save', 'Every line of the bill'),
     grid(['Batch', 'Expiry', 'Quantity', 'Free quantity', 'Rate', 'MRP']), 'Illustration'),
    ('desktop-vs-browser', 'Buying guides', '#475569', 'Desktop or browser-based: what actually changes',
     ('A general comparison', 'Not about one product'),
     two(('Desktop', 'Browser'), [('Where the data lives', 'On one PC', 'Online'), ('If a PC fails', 'Restore a backup', 'Open another device'), ('Updates', 'Installed by hand', 'Applied for you')]), 'Illustration'),
    ('staff-logins-roles', 'Counter & staff', '#B45309', 'Who can see and change what',
     ('Staff and roles', 'Example roles'),
     rows([('b', 'Owner', 'Everything'), ('b', 'Pharmacist', 'Stock and billing'), ('b', 'Counter staff', 'Billing only')]), 'Illustration'),
    ('switch-pharmacy-software', 'Switching', '#0B1F33', 'Switch without stopping billing',
     ('Four-week plan', 'Illustrative'),
     rows([('b', 'Week 1', 'Load items and stock'), ('b', 'Week 2', 'Run both together'), ('b', 'Week 3', 'Move one counter'), ('', 'Week 4', 'Go live')]), 'Illustration'),
]

for slug, topic, accent, title, head, body, tag in GUIDES:
    page = '<!doctype html><html lang="en"><head><meta charset="utf-8"><title>%s: feature image (1200x600, 2:1, rendered at 2x)</title><style>%s</style></head><body>' % (html.escape(title), CSS % {'accent': accent})
    page += '<div class="l"><p class="k">%s</p><h1>%s</h1></div><div class="card"><header><b>%s</b><span>%s</span></header>%s</div><div class="tag">%s</div></body></html>\n' % (
        html.escape(topic), html.escape(title), html.escape(head[0]), html.escape(head[1]), body, tag)
    with open(os.path.join(HERE, slug + '-feature.html'), 'w', encoding='utf-8') as f:
        f.write(page)
    print(slug)
