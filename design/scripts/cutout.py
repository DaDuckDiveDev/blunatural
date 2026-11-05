"""Batch background removal for Blu Naturals product photos.
Usage: python3 -I cutout.py <attachments_dir> <manifest.csv> <out_dir>
Writes out/<handle>/NN.png (RGBA, long side 2000px) + a JSON index.
"""
import sys, os, csv, json, time
import numpy as np
from PIL import Image, ImageOps
from rembg import new_session, remove

A, manifest, OUT = sys.argv[1], sys.argv[2], sys.argv[3]

# attachment id -> project image path (order of delivery == order in project file list)
ID_MAP = {
 '5c447a81': 'all-natural-dry-shampoo/01', 'a9a163c5': 'all-natural-dry-shampoo/03',
 '007570d6': 'all-natural-dry-shampoo/02', 'ee574d7b': 'all-natural-dry-shampoo/04',
 '009a96eb': 'blakbird-natural-deodorant-for-men/01', '195eeb70': 'blakbird-natural-deodorant-for-men/03',
 '08598a53': 'blakbird-natural-deodorant-for-men/02', '2a619b00': 'blakbird-natural-deodorant-for-men/04',
 '7bfdfc88': 'unscented-all-natural-deodorant/01',
 '2636a0ed': 'essential-oil-free-deodorant/01', '3a92a700': 'essential-oil-free-deodorant/03',
 '8fd748d9': 'essential-oil-free-deodorant/02',
 '3a7fa8dc': 'blackbird-organic-black-tea-tree-bar-soap/01', 'c8916a2e': 'blackbird-organic-black-tea-tree-bar-soap/02',
 'f2edd8ce': 'organic-citrus-lavender-bar-soap/01', 'cd930577': 'organic-citrus-lavender-bar-soap/03',
 '25dec6e1': 'organic-citrus-lavender-bar-soap/02', 'f883271d': 'organic-citrus-lavender-bar-soap/06',
 'dceabd44': 'organic-citrus-lavender-bar-soap/07', '6393e1ff': 'organic-citrus-lavender-bar-soap/10',
 '4143dcce': 'organic-citrus-lavender-bar-soap/05', '0f436d37': 'organic-citrus-lavender-bar-soap/04',
 'ccf49f99': 'organic-citrus-lavender-bar-soap/09', '21534ba9': 'organic-citrus-lavender-bar-soap/08',
 '7db799e4': 'naturally-digital-download/01',
 'a905fa28': 'all-natural-deodorant-baking-soda-free/01', 'a794cc20': 'all-natural-deodorant-baking-soda-free/02',
 'ad55359f': 'dry-shampoo-refill/01', '77321a3a': 'dry-shampoo-refill/03',
 '876e00b0': 'dry-shampoo-refill/02', 'f08b4a75': 'dry-shampoo-refill/04',
 '99092654': 'all-natural-deodorant/01', '91848450': 'all-natural-deodorant/03',
 'ac9f0db9': 'all-natural-deodorant/02', 'bdcc61a2': 'all-natural-deodorant/04',
 '418229b8': 'all-natural-herbal-infused-bug-spray/01', '710e703e': 'all-natural-herbal-infused-bug-spray/02',
 'a47b5a4b': 'all-natural-deodorant-jar/01', '6adbc27f': 'all-natural-deodorant-jar/03',
 '55ebe81d': 'all-natural-deodorant-jar/05', '840ee451': 'all-natural-deodorant-jar/02',
 '25a0225f': 'all-natural-deodorant-jar/04',
}
SKIP = {'naturally-digital-download/01'}  # illustration, no cutout

def white_balance(im):
    """Mild grey-world WB restricted to bright pixels: the wood table warms the whites."""
    a = np.asarray(im).astype(np.float32)
    lum = a.mean(axis=2)
    mask = lum > np.percentile(lum, 90)
    if mask.sum() < 500:
        return im
    ref = a[mask].mean(axis=0)          # average colour of the brightest 10%
    target = ref.mean()
    gain = target / np.maximum(ref, 1)
    gain = 1 + (gain - 1) * 0.85        # don't fully neutralise
    a = np.clip(a * gain, 0, 255)
    return Image.fromarray(a.astype(np.uint8))

session = new_session('isnet-general-use')
index = []
t0 = time.time()
for fid, rel in ID_MAP.items():
    if rel in SKIP:
        continue
    src = f'{A}/{fid}-{rel.split("/")[1]}.jpg'
    im = ImageOps.exif_transpose(Image.open(src)).convert('RGB')
    im.thumbnail((2000, 2000), Image.LANCZOS)
    im = white_balance(im)
    out = remove(im, session=session, post_process_mask=True)
    # trim to content bbox with padding
    bbox = out.getbbox()
    if bbox:
        pad = int(0.03 * max(out.size))
        bbox = (max(0, bbox[0]-pad), max(0, bbox[1]-pad), min(out.width, bbox[2]+pad), min(out.height, bbox[3]+pad))
        out = out.crop(bbox)
    dst = f'{OUT}/{rel}.png'
    os.makedirs(os.path.dirname(dst), exist_ok=True)
    out.save(dst, optimize=True)
    index.append({'file': rel + '.png', 'w': out.width, 'h': out.height})
    print(f'{rel}  {out.size}  {time.time()-t0:.0f}s', flush=True)

json.dump(index, open(f'{OUT}/index.json', 'w'), indent=1)
print('DONE', len(index))
