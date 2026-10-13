"""WordPress SQL dump (phpMyAdmin) -> Store-API-like JSON + extract used uploads from zip.
usage: python -I wpdump.py <dump.sql> <site.zip> <outdir>
"""
import json, os, re, sys, zipfile

sys.stdout.reconfigure(encoding='utf-8')
dump, zpath, out = sys.argv[1], sys.argv[2], sys.argv[3]
P = 'wpdz_'
WANT = {P + t for t in ['posts', 'postmeta', 'terms', 'term_taxonomy', 'term_relationships', 'termmeta', 'options']}

def parse_values(s, i):
    """parse '(a, 'b', NULL), (...);' starting at i; yields tuples"""
    rows = []
    n = len(s)
    while i < n:
        while i < n and s[i] in ' \n\r\t,':
            i += 1
        if i >= n or s[i] == ';':
            break
        assert s[i] == '(', s[i:i+50]
        i += 1
        row = []
        while True:
            while s[i] in ' \n\r\t':
                i += 1
            if s[i] == "'":
                i += 1
                buf = []
                while True:
                    c = s[i]
                    if c == '\\':
                        nx = s[i+1]
                        buf.append({'n': '\n', 'r': '\r', 't': '\t', '0': '\0', 'Z': '\x1a'}.get(nx, nx))
                        i += 2
                    elif c == "'":
                        if s[i+1] == "'":
                            buf.append("'"); i += 2
                        else:
                            i += 1; break
                    else:
                        buf.append(c); i += 1
                row.append(''.join(buf))
            else:
                j = i
                while s[j] not in ',)':
                    j += 1
                tok = s[i:j].strip()
                row.append(None if tok == 'NULL' else tok)
                i = j
            while s[i] in ' \n\r\t':
                i += 1
            if s[i] == ',':
                i += 1; continue
            if s[i] == ')':
                i += 1; break
        rows.append(row)
    return rows, i

data = {t: [] for t in WANT}
cols = {}
with open(dump, encoding='utf-8', errors='replace') as f:
    s = f.read()
for m in re.finditer(r'INSERT INTO `(\w+)` \(([^)]*)\) VALUES\s*', s):
    t = m.group(1)
    if t not in WANT:
        continue
    cols[t] = [c.strip(' `') for c in m.group(2).split(',')]
    rows, _ = parse_values(s, m.end())
    data[t].extend(dict(zip(cols[t], r)) for r in rows)
print({t: len(v) for t, v in data.items()})

posts = {int(p['ID']): p for p in data[P + 'posts']}
meta = {}
for r in data[P + 'postmeta']:
    meta.setdefault(int(r['post_id']), {})[r['meta_key']] = r['meta_value']
terms = {int(t['term_id']): t for t in data[P + 'terms']}
tax = {int(t['term_taxonomy_id']): t for t in data[P + 'term_taxonomy']}
tmeta = {}
for r in data[P + 'termmeta']:
    tmeta.setdefault(int(r['term_id']), {})[r['meta_key']] = r['meta_value']
rel = {}
for r in data[P + 'term_relationships']:
    rel.setdefault(int(r['object_id']), []).append(int(r['term_taxonomy_id']))
siteurl = next((o['option_value'] for o in data[P + 'options'] if o['option_name'] == 'siteurl'), 'https://globalprocurement.az')
print('siteurl', siteurl)

def att_url(aid):
    p = posts.get(int(aid or 0))
    if not p:
        return ''
    f = meta.get(int(p['ID']), {}).get('_wp_attached_file')
    return (siteurl.rstrip('/') + '/wp-content/uploads/' + f) if f else (p.get('guid') or '')

# categories
cats = []
for tt in tax.values():
    if tt['taxonomy'] != 'product_cat':
        continue
    tid = int(tt['term_id'])
    t = terms[tid]
    img = att_url(tmeta.get(tid, {}).get('thumbnail_id'))
    cats.append({'id': tid, 'name': t['name'], 'slug': t['slug'], 'description': tt['description'] or '',
                 'parent': int(tt['parent'] or 0), 'count': int(tt['count'] or 0), 'image': {'src': img} if img else None})

# attributes (global taxonomy names)
attr_tax = {}
for tt in tax.values():
    if tt['taxonomy'].startswith('pa_'):
        attr_tax.setdefault(int(tt['term_taxonomy_id']), (tt['taxonomy'], terms[int(tt['term_id'])]['name']))

prods = []
seo = []
for pid, p in posts.items():
    if p['post_type'] != 'product' or p['post_status'] != 'publish':
        continue
    m = meta.get(pid, {})
    imgs = []
    if m.get('_thumbnail_id'):
        imgs.append({'src': att_url(m['_thumbnail_id'])})
    for g in (m.get('_product_image_gallery') or '').split(','):
        if g.strip():
            u = att_url(g.strip())
            if u and u not in [x['src'] for x in imgs]:
                imgs.append({'src': u})
    pc = []
    for ttid in rel.get(pid, []):
        tt = tax.get(ttid)
        if tt and tt['taxonomy'] == 'product_cat':
            pc.append({'id': int(tt['term_id'])})
    price = m.get('_price') or ''
    try:
        minor = int(round(float(price) * 100)) if price else 0
    except ValueError:
        minor = 0
    # attributes: from _product_attributes (serialized) — keep simple: taxonomy terms + custom via regex
    attrs = {}
    for ttid in rel.get(pid, []):
        if ttid in attr_tax:
            k, v = attr_tax[ttid]
            attrs.setdefault(k.replace('pa_', '').replace('-', ' ').capitalize(), []).append(v)
    raw = m.get('_product_attributes') or ''
    for name, val in re.findall(r's:4:"name";s:\d+:"([^"]*)";s:5:"value";s:\d+:"([^"]*)"', raw):
        if not name.startswith('pa_') and val:
            attrs[name] = [v.strip() for v in val.split('|') if v.strip()]
    prods.append({'id': pid, 'name': p['post_title'], 'slug': p['post_name'], 'description': p['post_content'] or '',
                  'short_description': p['post_excerpt'] or '', 'images': imgs, 'categories': pc,
                  'prices': {'price': str(minor), 'currency_minor_unit': 2, 'currency_symbol': '₼'},
                  'attributes': [{'name': k, 'terms': [{'name': x} for x in v]} for k, v in attrs.items()],
                  'menu_order': int(p.get('menu_order') or 0), 'date': p['post_date']})
    seo.append({'id': pid, 'slug': p['post_name'], 'yoast_head_json': {'title': m.get('_yoast_wpseo_title') or '', 'description': m.get('_yoast_wpseo_metadesc') or ''}})

os.makedirs(out, exist_ok=True)
json.dump(cats, open(os.path.join(out, 'categories.json'), 'w', encoding='utf-8'), ensure_ascii=False, indent=1)
json.dump(prods, open(os.path.join(out, 'products.json'), 'w', encoding='utf-8'), ensure_ascii=False, indent=1)
json.dump(seo, open(os.path.join(out, 'seo.json'), 'w', encoding='utf-8'), ensure_ascii=False, indent=1)
print('categories', len(cats), 'products', len(prods))

# extract only referenced uploads (+ images inside descriptions)
need = set()
for p in prods:
    for im in p['images']:
        need.add(im['src'])
    need.update(re.findall(r'(?:src|href)="([^"]+/wp-content/uploads/[^"]+\.(?:jpe?g|png|webp|gif))"', p['description'], re.I))
for c in cats:
    if c['image']:
        need.add(c['image']['src'])
paths = {re.sub(r'^.*?/wp-content/uploads/', 'wp-content/uploads/', u) for u in need if '/wp-content/uploads/' in u}
with zipfile.ZipFile(zpath) as z:
    names = set(z.namelist())
    got = 0
    for rel_ in sorted(paths):
        cand = [n for n in (rel_, 'public_html/' + rel_) if n in names]
        if not cand:
            print('missing in zip:', rel_); continue
        dest = os.path.normpath(os.path.join(out, rel_))
        if not dest.startswith(os.path.normpath(out)):
            continue
        os.makedirs(os.path.dirname(dest), exist_ok=True)
        with z.open(cand[0]) as src, open(dest, 'wb') as dst:
            dst.write(src.read())
        got += 1
print('extracted images', got, 'of', len(paths))
