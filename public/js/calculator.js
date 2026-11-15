const map = L.map("map2").setView(TODA.center, 14); tile(map);
let layers = [];
async function run(e) {
  e && e.preventDefault();
  const f = cf.f.selectedOptions[0], t = cf.t.selectedOptions[0];
  if (!f.value || !t.value || f.value === t.value) { $("#out").textContent = "Choose two different places."; return; }
  const a = [+f.dataset.lat, +f.dataset.lng], b = [+t.dataset.lat, +t.dataset.lng], r = await road(a, b);
  const x = await api("/fare", { method: "POST", body: JSON.stringify({ distance: r.d, passenger_type: cf.type.value, special: cf.special.value === "1", passengers: +cf.n.value }) });
  $("#out").innerHTML = `<div>Estimated fare</div><div class="big">${peso(x.per_passenger)} <span class="sm" style="font-size:15px;font-weight:400">per passenger</span></div>
  <div class="row"><span>Distance</span><b>${x.distance} km</b></div><div class="row"><span>Base fare (first 1 km)</span><b>${peso(x.base)}</b></div>
  <div class="row"><span>Additional distance (${x.extra_km} km × ₱5.00)</span><b>${peso(x.extra)}</b></div>
  ${x.special ? `<div class="row"><span>Special trip</span><b>${peso(x.special)}</b></div>` : ""}
  <div class="row"><span>Discount</span><b>${peso(x.discount)}</b></div><div class="row t"><span>Total (${x.passengers} passenger${x.passengers > 1 ? "s" : ""})</span><span>${peso(x.total)}</span></div>`;
  $("#rp").textContent = `${x.distance} km · about ${x.minutes} min${r.est ? " (estimate)" : ""}`;
  layers.forEach(l => l.remove());
  layers = [L.marker(a, { icon: pin("A") }).addTo(map), L.marker(b, { icon: pin("B", "d") }).addTo(map), L.polyline(r.c, { color: "#0f7a76", weight: 5 }).addTo(map)];
  map.fitBounds([a, b], { padding: [40, 40] });
}
cf.onsubmit = run;
if (cf.f.value && cf.t.value) run();
