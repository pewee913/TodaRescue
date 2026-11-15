const map = L.map("map").setView(TODA.center, 15); tile(map);
let filter = "all", me = null, dest = null, sel = null, markers = [], destPin = null, line = null, data = [];
async function load() {
  const p = new URLSearchParams({ q: $("#q").value });
  if (me) { p.set("lat", me[0]); p.set("lng", me[1]); }
  if (dest) { p.set("dest_lat", dest[0]); p.set("dest_lng", dest[1]); }
  if (filter === "open") p.set("open", 1);
  data = await api("/todas?" + p);
  if (dest && sel === null && data[0]) sel = data[0].id;
  draw();
}
function draw() {
  $("#cnt").textContent = `${data.length} TODA terminal${data.length === 1 ? "" : "s"} found`;
  $("#list").innerHTML = data.map(t => `<div class="item ${sel === t.id ? "on" : ""}" data-id="${t.id}"><h4>${t.name}<span class="tag ${t.is_active ? "" : "c"}">${t.is_active ? "Active" : "Closed"}</span></h4>
    <div class="sm">Serves ${t.serves}</div><div class="sm" style="margin-top:6px">📍 ${t.km_from_you} km from ${me ? "you" : "center"} · 📞 ${t.phone} · ${t.units} units</div>
    ${t.fare_to_dest ? `<div style="margin-top:8px;color:var(--t);font-weight:700">To destination: ${t.km_to_dest} km · ${peso(t.fare_to_dest)}</div>` : ""}
    <button class="btn nav-btn" data-lat="${t.lat}" data-lng="${t.lng}" data-name="${t.name}" title="Open in GPS/maps">Navigate</button>
  </div>`).join("") || '<div class="sm">No TODA matches that search.</div>';
  markers.forEach(m => m.remove());
  markers = data.map((t, i) => L.marker([t.lat, t.lng], { icon: pin(i + 1) }).addTo(map).bindPopup(`<b>${t.name}</b><br>${t.serves}<br>${t.phone}<br><a href="https://www.google.com/maps/dir/?api=1&destination=${t.lat},${t.lng}&destination_alias=${encodeURIComponent(t.name)}" target="_blank" style="color:var(--t);font-weight:700">Open in GPS →</a>`).on("click", () => pick(t.id)));
  document.querySelectorAll(".item").forEach(el => el.onclick = () => pick(+el.dataset.id, true));
  document.querySelectorAll(".nav-btn").forEach(btn => btn.onclick = e => { e.stopPropagation(); navigateTo(btn.dataset.lat, btn.dataset.lng, btn.dataset.name); });
  route();
}
async function route() {
  line && line.remove(); line = null;
  const t = data.find(x => x.id === sel);
  if (!dest || !t) { if (dest) $("#info").textContent = "Tap a TODA to see its fare to your destination."; return; }
  const r = await road([+t.lat, +t.lng], dest);
  const x = await api("/fare", { method: "POST", body: JSON.stringify({ distance: r.d }) });
  const d = await api("/fare", { method: "POST", body: JSON.stringify({ distance: r.d, passenger_type: "student" }) });
  line && line.remove(); line = L.polyline(r.c, { color: "#0f7a76", weight: 5 }).addTo(map); map.fitBounds(line.getBounds(), { padding: [50, 50] });
  $("#info").innerHTML = `<b>${t.name}</b> to destination: ${x.distance} km by road, about ${Math.round(r.m)} min${r.est ? " (estimate)" : ""}.<br><span style="color:var(--t);font-weight:700;font-size:18px">${peso(x.per_passenger)}</span> regular · ${peso(d.per_passenger)} student/senior/PWD`;
}
function pick(id, pan) { sel = id; const t = data.find(x => x.id === id); if (pan && t) map.setView([t.lat, t.lng], 16); draw(); }
function setDest(ll, name) {
  dest = ll; sel = null; destPin && destPin.remove();
  destPin = L.marker(ll, { icon: pin("★", "d") }).addTo(map).bindPopup(name || "Your destination"); load();
}
dst.onchange = () => { const o = dst.selectedOptions[0]; o.value ? setDest([+o.dataset.lat, +o.dataset.lng], o.text) : (dest = null, sel = null, destPin && destPin.remove(), load()); };
map.on("click", e => { dst.value = ""; setDest([e.latlng.lat, e.latlng.lng]); });
$("#go").onclick = async () => {
  const q = $("#q").value.trim(); await load();
  if (!q) return;
  const hit = data.some(t => (t.name + t.serves).toLowerCase().includes(q.toLowerCase()));
  if (hit) return;
  $("#info").textContent = `Searching for “${q}”…`;
  const r = await geocode(q);
  r ? (dst.value = "", setDest([+r.lat, +r.lon], r.display_name.split(",")[0])) : $("#info").textContent = `No place found for “${q}”.`;
};
$("#q").onkeydown = e => e.key === "Enter" && $("#go").click();
document.querySelectorAll("[data-f]").forEach(c => c.onclick = () => {
  document.querySelectorAll("[data-f]").forEach(x => x.classList.remove("on")); c.classList.add("on"); filter = c.dataset.f;
  if (filter === "near" && navigator.geolocation) navigator.geolocation.getCurrentPosition(p => { me = [p.coords.latitude, p.coords.longitude]; map.setView(me, 15); L.marker(me, { icon: L.divIcon({ className: "", html: '<div class="me"></div>', iconSize: [16, 16] }) }).addTo(map); load(); }, () => load());
  else load();
});
load();
function navigateTo(lat, lng, name) {
  const url = `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}&destination_alias=${encodeURIComponent(name)}`;
  window.open(url, "_blank");
}

load();
