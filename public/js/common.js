const $ = s => document.querySelector(s);
const peso = x => "₱" + Number(x).toFixed(2);
const escapeHtml = t => String(t || "").replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);
const tile = m => L.tileLayer("https://tile.openstreetmap.org/{z}/{x}/{y}.png", { maxZoom: 19, attribution: "© OpenStreetMap" }).addTo(m);
const pin = (n, c = "") => L.divIcon({ className: "", html: `<div class="pin ${c}">${n}</div>`, iconSize: [32, 32], iconAnchor: [16, 16] });
const hav = (a, b) => { const r = Math.PI / 180, x = Math.sin((b[0] - a[0]) * r / 2) ** 2 + Math.cos(a[0] * r) * Math.cos(b[0] * r) * Math.sin((b[1] - a[1]) * r / 2) ** 2; return 12742 * Math.asin(Math.sqrt(x)) * 1.3; };
const cache = {};
// Real road route (OSRM). Falls back to a straight-line estimate if unreachable.
async function road(a, b) {
  const k = a + "|" + b; if (cache[k]) return cache[k];
  try {
    const j = await (await fetch(`https://router.project-osrm.org/route/v1/driving/${a[1]},${a[0]};${b[1]},${b[0]}?overview=full&geometries=geojson`)).json(), x = j.routes[0];
    return cache[k] = { d: x.distance / 1000, m: x.duration / 60 + 2, c: x.geometry.coordinates.map(p => [p[1], p[0]]) };
  } catch (e) { const d = hav(a, b); return { d, m: d * 3 + 2, c: [a, b], est: true }; }
}
async function geocode(q) {
  try {
    const j = await (await fetch("https://nominatim.openstreetmap.org/search?format=json&limit=1&countrycodes=ph&viewbox=123.7,13.1,124.0,12.85&q=" + encodeURIComponent(q))).json();
    return j[0];
  } catch (e) {}
}
const api = (path, opt) => fetch(TODA.api + path, { headers: { Accept: "application/json", "Content-Type": "application/json", "X-CSRF-TOKEN": TODA.csrf }, ...opt }).then(r => r.json());

// Toggleable sections. Remembers open/closed state in localStorage per key.
function toggleInit(key, btnSel, sectionSel) {
  const btn = $(btnSel), sec = $(sectionSel);
  if (!btn || !sec) return;
  const open = localStorage.getItem(key) !== 'closed';
  if (!open) { sec.classList.add('collapsed'); btn.classList.add('collapsed'); }
  btn.onclick = () => {
    const now = sec.classList.toggle('collapsed');
    btn.classList.toggle('collapsed');
    localStorage.setItem(key, now ? 'closed' : 'open');
  };
}
