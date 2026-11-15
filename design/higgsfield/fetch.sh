#!/bin/bash
# Pulls Higgsfield results listed in urls.txt into this folder so the landing
# canvas can use them. Each line of urls.txt is: <filename> <url>
# Only Higgsfield's two CDN hosts are fetched. Leave it running; Ctrl-C stops it.
cd "$(dirname "$0")" || exit 1
touch urls.txt
echo "Watching $(pwd)/urls.txt (Ctrl-C to stop)"
while true; do
  while read -r name url; do
    [ -z "$name" ] && continue
    case "$name" in *[!A-Za-z0-9._-]*) continue ;; esac
    case "$url" in
      https://d8j0ntlcm91z4.cloudfront.net/*|https://d2ol7oe51mr4n9.cloudfront.net/*) ;;
      *) continue ;;
    esac
    [ -s "$name" ] && continue
    echo "fetching $name"
    curl -fsSL --max-time 300 -o "$name.part" "$url" && mv "$name.part" "$name"
  done < urls.txt
  sleep 6
done
