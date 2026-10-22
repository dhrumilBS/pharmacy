"""Turns pictures you made (any size, PNG/JPG/WebP) into the files the article uses.

Put the five pictures in photos/incoming/ named stock-imbalance, one-view, stock-transfer, records-calm, calm-rollout
(any extension). Run:  python prepare-photos.py
Each is centre-cropped to 2:1, resized to 1600 x 800 and saved as photos/<name>.webp at 50-80 KB (the quality is lowered until
it fits). Then publish with:  D:\\xampp\\php\\php.exe wordpress\\import\\blog-multi-branch.php
Use --focus 0.0..1.0 to move the crop window up (0) or down (1) when the subject is not in the middle, e.g.  --focus 0.3
"""
import os
import sys

from PIL import Image

HERE = os.path.dirname(os.path.abspath(__file__))
SRC = os.path.join(HERE, 'incoming')
NAMES = ('stock-imbalance', 'one-view', 'stock-transfer', 'records-calm', 'calm-rollout')
focus = 0.5
if '--focus' in sys.argv:
    focus = float(sys.argv[sys.argv.index('--focus') + 1])

for name in NAMES:
    found = [f for f in os.listdir(SRC) if os.path.splitext(f)[0] == name] if os.path.isdir(SRC) else []
    if not found:
        print('missing:', name)
        continue
    im = Image.open(os.path.join(SRC, found[0])).convert('RGB')
    w, h = im.size
    if w / h > 2:                     # wider than 2:1: crop the sides
        cw = h * 2
        left = int((w - cw) / 2)
        im = im.crop((left, 0, left + cw, h))
    else:                             # taller than 2:1: crop top and bottom around the focus line
        ch = w // 2
        top = int((h - ch) * focus)
        im = im.crop((0, top, w, top + ch))
    im = im.resize((1600, 800), Image.LANCZOS)
    out = os.path.join(HERE, name + '.webp')
    for q in range(86, 28, -2):
        im.save(out, 'WEBP', quality=q, method=6)
        if os.path.getsize(out) <= 78 * 1024:
            break
    print(name, os.path.getsize(out) // 1024, 'KB, quality', q)
