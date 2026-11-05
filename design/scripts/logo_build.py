"""Build the Blu Naturals logo system as outline SVGs (+ PNG renders).
All text is converted to paths with fontTools so the files are portable.
"""
import os, subprocess, json
from fontTools.ttLib import TTFont
from fontTools.pens.svgPathPen import SVGPathPen
from fontTools.pens.transformPen import TransformPen

FONTS = '/home/claude/blu/work/fonts/ttf'
OUT = '/home/claude/blu/out/logo'
os.makedirs(OUT, exist_ok=True)

# ---------- palette (Tidepool = recommended) ----------
PAL = {
    'tidepool': dict(ink='#10364A', blu='#2A7A96', glass='#8FC7D6', mist='#EAF3F6', paper='#F7FAFB', sand='#F3EEE6', coral='#E8866A', night='#0B2A3A'),
    'kai':      dict(ink='#0B2A3A', blu='#1B5E8A', glass='#A9CFE0', mist='#E6EFF4', paper='#F4F8FA', sand='#EFE9DF', coral='#D9B36B', night='#071E2B'),
    'lagoon':   dict(ink='#173A3F', blu='#1FA3B5', glass='#9ADFE6', mist='#E3F4F3', paper='#F6FBFB', sand='#F4F0E8', coral='#E05C7B', night='#0F2D33'),
}

# ---------- bird mark (100 x 100 box) ----------
def bird_paths(fill, line, lines=True, eye=True):
    beak, tail = 0.75, 1.6
    bx = 74 + 12*beak
    tx, ty = 4 - 2*tail, 90 + 2*tail
    d = (f"M {bx} 31 L 73 27.5 C 71 18, 63 13.5, 55.5 16.5 C 49.5 19, 46.5 25, 47.5 31 "
         f"C 39.5 34, 31.5 40.5, 25.5 48.5 C 19.5 56.5, 16 66, 14 74 L {tx} {ty} L {tx+6.5} {ty} L 20 78.5 "
         f"C 26.5 82.5, 34.5 84.5, 42.5 82.5 C 56.5 78.5, 66.5 66.5, 70.5 54.5 C 72.5 48.5, 73 42.5, 72 36.5 L {bx} 31 Z")
    s = f'<path d="{d}" fill="{fill}"/>'
    if lines:
        s += f'<path d="M 53 39.5 C 44 44, 38 52, 33 60 C 29 66, 26.5 71, 24.5 76.5" stroke="{line}" stroke-width="2.6" fill="none" stroke-linecap="round"/>'
        s += f'<path d="M 61 47 C 52 52, 46 60, 41.5 68 C 39 72, 37.5 75, 36.5 78" stroke="{line}" stroke-width="1.6" fill="none" stroke-linecap="round"/>'
    if eye:
        s += f'<circle cx="61.5" cy="24.5" r="2.1" fill="{line}"/>'
    return s

BIRD_BOX = (0.5, 13.5, 83, 93.2)   # x0,y0,x1,y1 of ink in the 100 box

# ---------- text to path ----------
_font_cache = {}
def font(name):
    if name not in _font_cache:
        _font_cache[name] = TTFont(f'{FONTS}/{name}.ttf')
    return _font_cache[name]

def text_path(text, fontname, size, tracking=0.0, x=0, y=0):
    """Return (svg_path_d, width). y is the baseline. tracking in em."""
    f = font(fontname)
    gs = f.getGlyphSet(); cmap = f.getBestCmap(); upm = f['head'].unitsPerEm
    sc = size / upm
    pen = SVGPathPen(gs)
    cur = x
    kern = {}
    if 'kern' in f:
        for st in f['kern'].kernTables:
            kern.update(st.kernTable)
    prev = None
    for ch in text:
        gname = cmap.get(ord(ch))
        if gname is None: continue
        if prev and (prev, gname) in kern:
            cur += kern[(prev, gname)] * sc
        tp = TransformPen(pen, (sc, 0, 0, -sc, cur, y))
        gs[gname].draw(tp)
        cur += gs[gname].width * sc + tracking * size
        prev = gname
    return pen.getCommands(), cur - x

def svg(w, h, body, bg=None):
    r = f'<rect width="100%" height="100%" fill="{bg}"/>' if bg else ''
    return f'<svg xmlns="http://www.w3.org/2000/svg" width="{w}" height="{h}" viewBox="0 0 {w} {h}">{r}{body}</svg>'

def write(name, content, png_width=1600):
    p = f'{OUT}/{name}.svg'
    open(p, 'w').write(content)
    subprocess.run(['rsvg-convert', '-w', str(png_width), p, '-o', f'{OUT}/{name}.png'], check=True)

# ---------- lockups ----------
def wordmark(ink, size=100, sub=True, word='blu', sub_word='naturals', sub_gap=0.08):
    """Returns (body, width, height). 'blu' in Fraunces Soft Medium, 'naturals' tracked Manrope."""
    d1, w1 = text_path(word, 'Fraunces-Soft-Medium', size, tracking=-0.01)
    body = f'<path d="{d1}" fill="{ink}"/>'
    if sub:
        ssize = size * 0.30
        d2, w2 = text_path(sub_word.upper(), 'Manrope-SemiBold', ssize, tracking=0.22)
        yoff = size * (0.3 + sub_gap)
        body += f'<g transform="translate(0 {yoff})"><path d="{d2}" fill="{ink}"/></g>'
        return body, max(w1, w2), size * 1.1
    return body, w1, size * 0.8

def lockup_horizontal(pal, name, bg=None, mono=None, word='blu', sub_word='naturals'):
    ink = mono or pal['ink']; birdfill = mono or pal['blu']; line = bg or '#FFFFFF'
    S = 100  # wordmark size
    wm, ww, wh = wordmark(ink, S, word=word, sub_word=sub_word)
    bird_h = S * 1.3
    sc = bird_h / 100
    bw = 84 * sc                      # ink width of the bird
    gap = S * 0.2
    pad = S * 0.3
    W = pad + bw + gap + ww + pad
    H = bird_h + pad * 2
    # wordmark block: ascender top ~ base-0.75S, sub-word bottom ~ base+0.68S -> block centre at base-0.035S
    base = pad + bird_h/2 + S*0.035 + S*0.02
    body = f'<g transform="translate({pad - 0.5*sc} {pad - 13.5*sc}) scale({sc})">{bird_paths(birdfill, line)}</g>'
    body += f'<g transform="translate({pad + bw + gap} {base})">{wm}</g>'
    write(name, svg(round(W), round(H), body, bg))
    return W, H

def lockup_stacked(pal, name, bg=None, mono=None, word='blu', sub_word='naturals'):
    ink = mono or pal['ink']; birdfill = mono or pal['blu']; line = bg or '#FFFFFF'
    S = 100
    wm, ww, wh = wordmark(ink, S, word=word, sub_word=sub_word)
    bird_h = S * 1.25; sc = bird_h / 100; bw = 100 * sc
    pad = S * 0.3; gap = S * 0.12
    W = max(bw, ww) + pad * 2
    bird_h = S * 1.25; sc = bird_h / 100; bw = 84 * sc
    H = pad + bird_h*0.8 + gap + S * 0.72 + S * 0.42 + pad
    body = f'<g transform="translate({W/2 - 41.75*sc} {pad - 13.5*sc}) scale({sc})">{bird_paths(birdfill, line)}</g>'
    body += f'<g transform="translate({(W - ww)/2} {pad + bird_h*0.8 + gap + S*0.72})">{wm}</g>'
    write(name, svg(round(W), round(H), body, bg), png_width=1200)

def mark_only(pal, name, bg=None, mono=None, circle=False):
    birdfill = mono or pal['blu']; line = bg or '#FFFFFF'
    if circle:
        body = f'<circle cx="50" cy="50" r="50" fill="{birdfill}"/><g transform="translate(14 10) scale(0.76)">{bird_paths(bg or pal["paper"], birdfill)}</g>'
        write(name, svg(100, 100, body), png_width=1024)
    else:
        body = bird_paths(birdfill, line)
        write(name, svg(100, 100, body, bg), png_width=1024)

if __name__ == '__main__':
    P = PAL['tidepool']
    lockup_horizontal(P, 'blu-naturals-horizontal')
    lockup_horizontal(P, 'blu-naturals-horizontal-mono', mono=P['ink'])
    lockup_horizontal(P, 'blu-naturals-horizontal-reversed', bg=P['night'], mono='#FFFFFF')
    lockup_stacked(P, 'blu-naturals-stacked')
    lockup_stacked(P, 'blu-naturals-stacked-reversed', bg=P['blu'], mono='#FFFFFF')
    mark_only(P, 'blu-mark')
    mark_only(P, 'blu-mark-mono', mono=P['ink'])
    mark_only(P, 'blu-mark-circle', circle=True)
    # Blakbird sub-brand: same bird, near-black, wordmark 'blakbird' with 'by blu naturals'
    BK = dict(P, blu='#1C1C1E', ink='#1C1C1E')
    lockup_horizontal(BK, 'blakbird-horizontal', word='blakbird', sub_word='by blu naturals')
    lockup_stacked(BK, 'blakbird-stacked', word='blakbird', sub_word='by blu naturals')
    lockup_horizontal(BK, 'blakbird-horizontal-reversed', bg='#1C1C1E', mono='#FFFFFF', word='blakbird', sub_word='by blu naturals')
    # colourway previews of the primary lockup
    for k, pal in PAL.items():
        lockup_horizontal(pal, f'preview-{k}', bg=pal['paper'])
    print('built', sorted(os.listdir(OUT)))
