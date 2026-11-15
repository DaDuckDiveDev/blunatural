# Higgsfield media: handoff for the agent adding it to the site

Written 15 Nov 2026. This folder (`design/higgsfield/`) is where the generated stills and videos land. Your job is to get them into the `blu-naturals` theme. Read `claude/handoff.md` and `claude/landing-media-slots.md` in the Claude project first; the slot letters below come from the second one.

## 1. Get the files (John has to do this step)

The files are not here yet. They live in John's Higgsfield library, and neither the cloud workspace nor the sandboxed shell on his Mac can reach Higgsfield's file host (the proxy returns 403).

John runs this once in a normal Terminal on his Mac:

```
bash ~/blunatural/design/higgsfield/fetch.sh
```

It reads `urls.txt` (19 lines: filename, then URL) and downloads each file into this folder, skipping ones already present. It loops forever, so stop it with Ctrl-C once all 19 are down. If a link has expired, the same files can be downloaded by hand from the Higgsfield library by job id (ids are in `claude/higgsfield-run-2026-11-15.md`).

Do not start section 3 until `ls design/higgsfield` shows the files.

## 2. What each file is and where it goes

Stills are 2K PNG. Videos are 720p MP4 (H.264 from Higgsfield, uncompressed for web).

### Landing page slots

| File | Size | Slot | Notes |
|---|---|---|---|
| `hero-original-birch.png` | 1856 x 2304 | Hero arch and buy panel, The Original (D, G) | Top-down on pale birch, top third empty for the headline |
| `hero-cedarwood-birch.png` | 1856 x 2304 | Hero arch and buy panel, Cedarwood Lavender (E, G) | Same setup, matches the one above |
| `hero-patchouli-birch.png` | 1856 x 2304 | Hero arch and buy panel, Patchouli Orange Lavender (E, G) | Same setup. One orange slice touches the right edge |
| `plate-birch.png` | made by the landing session | Hero plate (D) | Empty birch plate for the real cut-out to sit on. Use this or the three hero stills, not both |
| `video-landing-loop-moss-16x9.mp4` | 1280 x 720, 8 s, silent | Final band "Start with The Original." (A) | Left two thirds dark, so paper-coloured text reads over it. First frame equals last frame |
| `original-moss-banner.png` | 2752 x 1536 | Poster for slot A | The exact start frame of the loop above |
| `video-ingredients-ad-9x16.mp4` | 720 x 1280, 15 s, has audio | Ingredients arch (B) | Product reveal starts at about 7 s. Product stays centred, so the 4:5 arch crop is safe. Strip the audio for the page |
| `ingredients-flatlay.png` | 2048 x 2048 | Ingredients section still, or poster for B | Dark koa board, not birch |
| `video-blakbird-ad-9x16.mp4` | 720 x 1280, 10 s, has audio | Blakbird band (C) | Needs cropping to 1:1 and trimming to the last 2 s, see section 3 |
| `blakbird-lava.png` | 2048 x 2048 | Blakbird band still, or poster for C | |
| `blakbird-wall.png` | made by the landing session | Blakbird band, 4:5 | |
| `trio-shelf.png` | 1856 x 2304 | Set builder, 3-stick pack | All three scent names are correct |
| `jar-sand.png` | 2048 x 2048 | Collection card: jar | |

### Alternates and social (not needed for the landing build)

| File | Size | Use |
|---|---|---|
| `video-landing-loop-shoreline-16x9.mp4` | 1280 x 720, 8 s, silent | Alternate for slot A. Bright, so it only works under dark text |
| `original-shoreline.png` | 2752 x 1536 | Poster for the shoreline loop |
| `original-studio.png` | 2048 x 2048 | Catalog packshot, product page gallery |
| `video-original-beach-9x16.mp4` | 720 x 1280, 8 s, audio | Reels / TikTok |
| `video-trio-pushin-3x4.mp4` | 960 x 1280 approx, 6 s, silent | Feed post |
| `video-soap-stream-9x16.mp4` | 720 x 1280, 8 s, audio | Reels / TikTok, soap product page |

## 3. Preparing the files for the web

`ffmpeg` is installed. None of these commands have been run yet, so check each output.

Put web-ready copies in `wp-content/themes/blu-naturals/assets/media/` (new folder). Keep the originals here in `design/higgsfield/`.

**Loops for the page (silent, small).** Target under 1.5 MB each.

```
ffmpeg -i video-landing-loop-moss-16x9.mp4 -an -c:v libx264 -crf 27 -preset slow -pix_fmt yuv420p -movflags +faststart ../../wp-content/themes/blu-naturals/assets/media/landing-loop.mp4
ffmpeg -i video-landing-loop-moss-16x9.mp4 -an -c:v libvpx-vp9 -crf 36 -b:v 0 ../../wp-content/themes/blu-naturals/assets/media/landing-loop.webm
```

Repeat for `video-ingredients-ad-9x16.mp4` as `ingredients.mp4` / `.webm`.

**Blakbird square loop (slot C).** Take the last 2 s, crop the centre square, and play it forward then backward so the loop has no jump:

```
ffmpeg -ss 8 -i video-blakbird-ad-9x16.mp4 -t 2 -an -filter_complex "[0:v]crop=720:720:0:280,split[a][b];[b]reverse[r];[a][r]concat=n=2:v=1" -c:v libx264 -crf 27 -preset slow -pix_fmt yuv420p -movflags +faststart ../../wp-content/themes/blu-naturals/assets/media/blakbird-loop.mp4
```

**Stills.** Convert to WebP at the size the slot needs, not 2K. The arch frames need about 1200 px wide; the banner poster about 1920 px wide. The repo's Python scripts already use Pillow (`design/scripts`), so:

```
python3 -c "from PIL import Image; im=Image.open('hero-original-birch.png'); im.thumbnail((1200,1500)); im.save('../../wp-content/themes/blu-naturals/assets/media/hero-original.webp', quality=82)"
```

## 4. Putting them in the theme

The Nalu landing is not built into the theme yet (see handoff section 3). The theme is a block theme: templates in `templates/`, patterns in `patterns/*.php`. Media goes in when the landing patterns are written.

Video markup for every loop:

```html
<video class="blu-loop" autoplay muted loop playsinline preload="none"
       poster="<?php echo esc_url( get_theme_file_uri( 'assets/media/landing-poster.webp' ) ); ?>">
  <source src="<?php echo esc_url( get_theme_file_uri( 'assets/media/landing-loop.webm' ) ); ?>" type="video/webm">
  <source src="<?php echo esc_url( get_theme_file_uri( 'assets/media/landing-loop.mp4' ) ); ?>" type="video/mp4">
</video>
```

Rules:
- `muted` and `playsinline` are both required or phones will not autoplay.
- Every loop on this page is below the fold, so keep `preload="none"` and start playback with an IntersectionObserver when the section scrolls into view. The poster shows until then.
- Under `prefers-reduced-motion: reduce`, do not play; show the poster only. The rest of the landing already follows this rule.
- The per-scent hero stills swap with the scent picker. The picker must also add the real WooCommerce variation to the cart (handoff section 3).
- Give each still real alt text, for example "The Original deodorant stick on pale birch with eucalyptus, peppermint and rosemary". Loops are decorative: `aria-hidden="true"`, no alt.

## 5. Check before anything ships

Labels were checked once at about half size and at one or two frames per video. That is not enough for publishing.

- Open every still at full size and compare the label with the real photo in `design/cutouts/`. The script "Blu", "naturals", the coloured scent line and "deodorant" must match.
- `blakbird-lava.png`: the scent line shows punctuation between the words ("cedarwood. sage. tea tree. vanilla"). Compare with the real label; if the real one has none, do not use this still at a size where it can be read.
- In the videos the small word "deodorant" under the scent line softens or breaks at 720p. Fine in an arch or a background band; do not use them full-screen.
- `video-original-beach-9x16.mp4`: the wet-sand reflection shows smeared mirrored text.
- `video-soap-stream-9x16.mp4`: the bar looks slightly pinker than the real one.

## 6. Not made yet

- Collection stills in the birch set: Baking Soda Free, Essential Oil Free, Unscented, boxed soap. The collection rail still uses the old shoreline composites.
- Holiday still life, 4:5 and 16:9.
- Portrait hero loop for the arch. Plan: animate `hero-original-birch.png` with only light and leaf shadow moving.
- Captions, price and URL on the social videos. Add them in an editor; never ask the model to render text.

The Higgsfield free trial that made these ends 17 Nov, 23:58 Belgrade time. It allows one image and one video at a time, and rejects the next image for five minutes or more after each accepted one.

## 7. Ground rules

- Commit or push only when John asks. `design/higgsfield/` is untracked right now. Ask John whether the originals (19 large binaries, 7 of them video) belong in git or in `.gitignore`.
- The products keep their current labels in every image, because that is what ships. Never composite the new logo onto a product.
- No AI people, no invented reviews, no application shots of the open stick. A real demo needs phone footage from the family.
