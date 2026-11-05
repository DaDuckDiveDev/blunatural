"""Compose product cutouts onto brand backgrounds so they dissolve into the page.
Usage: python3 -I compose.py <cutouts_dir> <out_dir> [colorway]
Produces card (1:1), hero (3:2) and a 4:5 portrait per image.
"""
import sys, os, json, math, random
import numpy as np
from PIL import Image, ImageFilter, ImageDraw, ImageChops

SRC, OUT = sys.argv[1], sys.argv[2]
CW = sys.argv[3] if len(sys.argv) > 3 else 'tidepool'

PAL = {
    'tidepool': dict(ink='#10364A', blu='#2A7A96', glass='#8FC7D6', mist='#EAF3F6', paper='#F7FAFB', sand='#F3EEE6', coral='#E8866A', night='#0B2A3A', deep='#DCEBF0'),
    'kai':      dict(ink='#0B2A3A', blu='#1B5E8A', glass='#A9CFE0', mist='#E6EFF4', paper='#F4F8FA', sand='#EFE9DF', coral='#D9B36B', night='#071E2B', deep='#D6E5ED'),
    'lagoon':   dict(ink='#173A3F', blu='#1FA3B5', glass='#9ADFE6', mist='#E3F4F3', paper='#F6FBFB', sand='#F4F0E8', coral='#E05C7B', night='#0F2D33', deep='#D2EEEE'),
}[CW]

def hexrgb(h): h = h.lstrip('#'); return tuple(int(h[i:i+2], 16) for i in (0, 2, 4))
def lerp(a, b, t): return tuple(int(round(a[i] + (b[i]-a[i])*t)) for i in range(3))

def gradient(w, h, c0, c1, angle_deg=35):
    """Linear gradient as RGB image."""
    yy, xx = np.mgrid[0:h, 0:w].astype(np.float32)
    a = math.radians(angle_deg)
    t = (xx*math.cos(a) + yy*math.sin(a))
    t = (t - t.min()) / (t.max() - t.min() + 1e-6)
    c0 = np.array(c0, np.float32); c1 = np.array(c1, np.float32)
    img = c0[None, None, :]*(1-t[..., None]) + c1[None, None, :]*t[..., None]
    return Image.fromarray(img.astype(np.uint8), 'RGB')

def radial_glow(w, h, cx, cy, r, color, strength=0.55):
    """RGBA layer: soft radial glow."""
    yy, xx = np.mgrid[0:h, 0:w].astype(np.float32)
    d = np.sqrt((xx-cx)**2 + (yy-cy)**2) / r
    a = np.clip(1 - d, 0, 1) ** 2 * strength
    layer = np.zeros((h, w, 4), np.uint8)
    layer[..., :3] = color
    layer[..., 3] = (a*255).astype(np.uint8)
    return Image.fromarray(layer, 'RGBA')

def wave_lines(w, h, color, alpha=0.10, n=3, y0=0.62, amp=0.035, thick=None, seed=1):
    """The two-line wave motif from the wing, drawn large and faint."""
    layer = Image.new('RGBA', (w, h), (0, 0, 0, 0)); d = ImageDraw.Draw(layer)
    thick = thick or max(2, int(h*0.006))
    rnd = random.Random(seed)
    for k in range(n):
        pts = []
        off = y0 + k*0.075
        ph = rnd.random()*2*math.pi
        for x in range(0, w+1, 6):
            t = x / w
            y = h*(off + amp*math.sin(t*2*math.pi*1.1 + ph) + amp*0.4*math.sin(t*2*math.pi*2.3 + ph*1.7))
            pts.append((x, y))
        d.line(pts, fill=color + (int(255*alpha*(1 - k*0.25)),), width=thick if k == 0 else max(1, int(thick*0.6)), joint='curve')
    return layer.filter(ImageFilter.GaussianBlur(thick*0.35))

def blobs(w, h, color, alpha=0.18, seed=3, n=3):
    """Soft organic light-through-water shapes."""
    layer = Image.new('RGBA', (w, h), (0, 0, 0, 0)); d = ImageDraw.Draw(layer)
    rnd = random.Random(seed)
    for i in range(n):
        rx = rnd.uniform(0.25, 0.5)*w; ry = rnd.uniform(0.18, 0.4)*h
        cx = rnd.uniform(0.1, 0.9)*w; cy = rnd.uniform(0.1, 0.9)*h
        d.ellipse([cx-rx, cy-ry, cx+rx, cy+ry], fill=color + (int(255*alpha),))
    return layer.filter(ImageFilter.GaussianBlur(min(w, h)*0.12))

def vignette_to_paper(bg, paper, strength=1.0, start=0.60):
    """Fade the outer rim to the page paper colour (superellipse, no boxy corners)."""
    w, h = bg.size
    yy, xx = np.mgrid[0:h, 0:w].astype(np.float32)
    nx = (xx - w/2) / (w/2); ny = (yy - h/2) / (h/2)
    r = (np.abs(nx)**4 + np.abs(ny)**4) ** 0.25           # superellipse radius, 1 at mid-edges
    e = np.clip((r - start) / (1.08 - start), 0, 1)        # 0 inside -> 1 past the edge
    e = e*e*(3-2*e)
    a = np.asarray(bg).astype(np.float32); p = np.array(paper, np.float32)
    out = a*(1 - e[..., None]*strength) + p[None, None, :]*e[..., None]*strength
    return Image.fromarray(out.astype(np.uint8), 'RGB')

def shoreline(w, h, paper, mist, deep, horizon=0.60):
    """Vertical sky->water gradient with a soft horizon; slight diagonal."""
    yy, xx = np.mgrid[0:h, 0:w].astype(np.float32)
    t = yy/h + (xx/w - 0.5)*0.06
    sky = np.clip(t/horizon, 0, 1)[..., None]
    water = np.clip((t-horizon)/(1-horizon), 0, 1)[..., None]
    P, M, D = (np.array(c, np.float32) for c in (paper, mist, deep))
    top = P*(1-sky) + M*sky
    bot = M*(1-water) + D*water
    img = np.where(t[..., None] < horizon, top, bot)
    return Image.fromarray(np.clip(img, 0, 255).astype(np.uint8), 'RGB')

def contact_shadow(w, h, px, py, pw, ph, ink, strength=0.32):
    """Elliptical blurred shadow under the product footprint."""
    layer = Image.new('RGBA', (w, h), (0, 0, 0, 0)); d = ImageDraw.Draw(layer)
    sy = py + ph - ph*0.015
    rx, ry = pw*0.55, max(8, pw*0.08)
    d.ellipse([px + pw/2 - rx, sy - ry, px + pw/2 + rx, sy + ry], fill=ink + (int(255*strength),))
    layer = layer.filter(ImageFilter.GaussianBlur(pw*0.06))
    # tighter dark core
    core = Image.new('RGBA', (w, h), (0, 0, 0, 0)); dc = ImageDraw.Draw(core)
    dc.ellipse([px + pw/2 - rx*0.6, sy - ry*0.5, px + pw/2 + rx*0.6, sy + ry*0.5], fill=ink + (int(255*strength*0.8),))
    core = core.filter(ImageFilter.GaussianBlur(pw*0.025))
    layer.alpha_composite(core)
    return layer

def harmonise(prod, tint, amount=0.06):
    """Pull the product's colour slightly toward the scene tint; soften alpha edge by 1px."""
    r, g, b, a = prod.split()
    rgb = Image.merge('RGB', (r, g, b))
    tinted = Image.blend(rgb, Image.new('RGB', rgb.size, tint), amount)
    # cool the shadows a touch more than the highlights
    lum = np.asarray(rgb.convert('L')).astype(np.float32)/255
    m = Image.fromarray(((1-lum)*0.5*255).astype(np.uint8))
    tinted2 = Image.composite(Image.blend(rgb, Image.new('RGB', rgb.size, tint), amount*2.5), tinted, m)
    a = a.filter(ImageFilter.GaussianBlur(0.8))
    return Image.merge('RGBA', (*tinted2.split(), a))

def compose(prod, W, H, layout='card', seed=1):
    pal = {k: hexrgb(v) for k, v in PAL.items()}
    # background: paper -> mist diagonal, glow behind product, blobs, faint wave lines
    if layout == 'card':
        px_frac, py_frac, max_w, max_h, horizon = 0.5, 0.50, 0.80, 0.80, 0.62
    elif layout == 'hero':
        px_frac, py_frac, max_w, max_h, horizon = 0.70, 0.50, 0.36, 0.84, 0.64
    else:  # portrait 4:5
        px_frac, py_frac, max_w, max_h, horizon = 0.5, 0.50, 0.82, 0.76, 0.62
    bg = shoreline(W, H, pal['paper'], pal['mist'], pal['deep'], horizon).convert('RGBA')
    bg.alpha_composite(blobs(W, H, pal['glass'], alpha=0.28, seed=seed))
    bg.alpha_composite(wave_lines(W, H, pal['blu'], alpha=0.09, y0=horizon+0.10, seed=seed))
    bg.alpha_composite(radial_glow(W, H, W*px_frac, H*py_frac*0.8, max(W, H)*0.30, (255, 255, 255), strength=0.30))
    # product
    p = prod.copy()
    scale = min(W*max_w / p.width, H*max_h / p.height)
    p = p.resize((max(1, int(p.width*scale)), max(1, int(p.height*scale))), Image.LANCZOS)
    p = harmonise(p, pal['mist'])
    px = int(W*px_frac - p.width/2); py = int(H*py_frac - p.height/2)
    bg.alpha_composite(contact_shadow(W, H, px, py, p.width, p.height, pal['ink'], strength=0.40))
    bg.alpha_composite(p, (px, py))
    out = bg.convert('RGB')
    out = vignette_to_paper(out, pal['paper'])
    return out

if __name__ == '__main__':
    idx = json.load(open(f'{SRC}/index.json'))
    os.makedirs(OUT, exist_ok=True)
    for i, e in enumerate(idx):
        prod = Image.open(f'{SRC}/{e["file"]}').convert('RGBA')
        stem = e['file'][:-4]
        os.makedirs(f'{OUT}/{os.path.dirname(stem)}', exist_ok=True)
        compose(prod, 1200, 1200, 'card', seed=i).save(f'{OUT}/{stem}-card.jpg', quality=90, subsampling=0)
        compose(prod, 1200, 1500, 'portrait', seed=i).save(f'{OUT}/{stem}-portrait.jpg', quality=90, subsampling=0)
        if stem.endswith('/01'):
            compose(prod, 2000, 1333, 'hero', seed=i).save(f'{OUT}/{stem}-hero.jpg', quality=90, subsampling=0)
        print(stem, flush=True)
    print('DONE')
