// ==========================================================================
// TODA Rescue - Live User Direction & Navigation Tracker
// ==========================================================================

const map = L.map("map").setView(TODA.center, 15);
tile(map);

// User location state
let me = null; // [lat, lng]
let meAccuracy = null;
let meMarker = null;
let meCircle = null;
let watchId = null;
let userHeading = 0; // degrees

// Terminal and navigation state
let dest = null; // Optional trip destination [lat, lng]
let sel = null; // Selected TODA id
let markers = [];
let line = null;
let data = [];
let activeFilter = "all";
let isNavigating = false;
let selectedToda = null;

// ==========================================================================
// COMPASS & DIRECTION CALCULATIONS
// ==========================================================================

function toRad(deg) {
  return deg * Math.PI / 180;
}

// Computes bearing angle from point A to point B in degrees (0 - 360)
function getBearing(lat1, lon1, lat2, lon2) {
  const r1 = toRad(lat1), r2 = toRad(lat2);
  const dLon = toRad(lon2 - lon1);
  const y = Math.sin(dLon) * Math.cos(r2);
  const x = Math.cos(r1) * Math.sin(r2) - Math.sin(r1) * Math.cos(r2) * Math.cos(dLon);
  return (Math.atan2(y, x) * 180 / Math.PI + 360) % 360;
}

// Compass direction names
function getCompassName(deg) {
  const points = ["North", "Northeast", "East", "Southeast", "South", "Southwest", "West", "Northwest"];
  return points[Math.round(deg / 45) % 8];
}

// Compass direction arrows
function getCompassArrow(deg) {
  const arrows = ["⬆️", "↗️", "➡️", "↘️", "⬇️", "↙️", "⬅️", "↖️"];
  return arrows[Math.round(deg / 45) % 8];
}

// Format distance nicely: "250 m" or "1.4 km"
function formatDist(km) {
  if (km < 1) {
    return `${Math.round(km * 1000)} m`;
  }
  return `${km.toFixed(1)} km`;
}

// ==========================================================================
// USER LOCATION PIN & REAL-TIME GPS WATCHER
// ==========================================================================

// Builds the custom user GPS icon with a directional heading cone
function createUserIcon(heading = 0) {
  return L.divIcon({
    className: "user-gps-container",
    html: `<div class="user-gps-marker" title="Your Location">
            <div class="user-gps-pulse"></div>
            <div class="user-gps-heading" style="transform: rotate(${Math.round(heading)}deg)">
              <div class="user-gps-arrow"></div>
            </div>
            <div class="user-gps-dot"></div>
          </div>`,
    iconSize: [44, 44],
    iconAnchor: [22, 22],
    popupAnchor: [0, -22]
  });
}

// Places or updates the user location marker on the map
function setUserLocation(lat, lng, accuracy = null, pan = false, source = "gps") {
  const prevCoord = me;
  me = [lat, lng];
  meAccuracy = accuracy;

  // Calculate heading towards target TODA or movement direction
  if (selectedToda) {
    userHeading = getBearing(lat, lng, selectedToda.lat, selectedToda.lng);
  } else if (prevCoord) {
    const moved = hav(prevCoord, me);
    if (moved > 0.005) { // moved > 5 meters
      userHeading = getBearing(prevCoord[0], prevCoord[1], lat, lng);
    }
  }

  // Update or create Leaflet marker
  if (meMarker) {
    meMarker.setLatLng(me);
    meMarker.setIcon(createUserIcon(userHeading));
  } else {
    meMarker = L.marker(me, {
      icon: createUserIcon(userHeading),
      zIndexOffset: 1000,
      draggable: false
    }).addTo(map);

    meMarker.bindPopup(`<b>You are here</b><br><small>${source === "gps" ? "Live GPS Location" : "Default Location"}</small>`);
  }

  // Accuracy circle
  if (accuracy && accuracy < 2000) {
    if (meCircle) {
      meCircle.setLatLng(me).setRadius(accuracy);
    } else {
      meCircle = L.circle(me, {
        radius: accuracy,
        color: "#2563eb",
        fillColor: "#3b82f6",
        fillOpacity: 0.12,
        weight: 1.5
      }).addTo(map);
    }
  } else if (meCircle) {
    meCircle.remove();
    meCircle = null;
  }

  if (pan) {
    map.flyTo(me, 16, { duration: 1.2 });
  }

  // Update top banner
  const banner = $("#location-status-banner");
  const txt = $("#location-text");
  if (banner && txt) {
    banner.className = "location-banner " + (source === "gps" ? "success" : "");
    txt.innerHTML = source === "gps"
      ? `<b>Live GPS Connected</b> · accuracy ±${Math.round(accuracy || 15)}m`
      : `<b>Location Set</b> · Click pin or map to change`;
  }

  // Re-sort TODAs by distance from user
  load();

  // If live navigation is active, update turn guidance in real-time
  if (selectedToda) {
    updateNavigationGuidance();
  }
}

// Multi-stage location detection
function detectLocation(userInitiated = false) {
  const locateBtn = $("#btn-detect-gps");
  const mapBtn = $("#btn-map-locate");
  if (locateBtn) locateBtn.textContent = "Locating…";
  if (mapBtn) mapBtn.classList.add("locating");

  if (!navigator.geolocation) {
    fallbackLocation("Geolocation is not supported by your browser");
    return;
  }

  // Fast/Standard accuracy first (works reliably on WiFi/desktop)
  navigator.geolocation.getCurrentPosition(
    (pos) => {
      onLocationSuccess(pos, userInitiated);
      startWatchingPosition();
    },
    (err) => {
      // Retry with high accuracy
      console.warn("Standard location failed, retrying with high accuracy...", err.message);
      navigator.geolocation.getCurrentPosition(
        (pos2) => {
          onLocationSuccess(pos2, userInitiated);
          startWatchingPosition();
        },
        (err2) => {
          console.warn("Geolocation failed or denied:", err2.message);
          fallbackLocation(err2.code === 1 ? "Location permission denied" : "Location timed out");
        },
        { enableHighAccuracy: true, timeout: 8000, maximumAge: 0 }
      );
    },
    { enableHighAccuracy: false, timeout: 6000, maximumAge: 60000 }
  );
}

function onLocationSuccess(pos, userInitiated) {
  const locateBtn = $("#btn-detect-gps");
  const mapBtn = $("#btn-map-locate");
  if (locateBtn) locateBtn.textContent = "Refresh GPS";
  if (mapBtn) {
    mapBtn.classList.remove("locating");
    mapBtn.classList.add("active");
  }

  setUserLocation(pos.coords.latitude, pos.coords.longitude, pos.coords.accuracy, userInitiated, "gps");
  if (userInitiated) {
    showToast("📍 Location detected successfully!", "success");
  }
}

// Live GPS movement tracker
function startWatchingPosition() {
  if (watchId !== null || !navigator.geolocation) return;
  try {
    watchId = navigator.geolocation.watchPosition(
      (pos) => {
        setUserLocation(pos.coords.latitude, pos.coords.longitude, pos.coords.accuracy, isNavigating, "gps");
      },
      (err) => {
        console.warn("watchPosition warning:", err.message);
      },
      { enableHighAccuracy: true, maximumAge: 3000, timeout: 10000 }
    );
  } catch (e) {
    console.warn("Unable to start watchPosition", e);
  }
}

// Guaranteed fallback so the user location pin NEVER fails to show
function fallbackLocation(reason) {
  const locateBtn = $("#btn-detect-gps");
  const mapBtn = $("#btn-map-locate");
  if (locateBtn) locateBtn.textContent = "Detect GPS";
  if (mapBtn) {
    mapBtn.classList.remove("locating");
    mapBtn.classList.remove("active");
  }

  if (!me) {
    setUserLocation(TODA.center[0], TODA.center[1], null, false, "default");
    const banner = $("#location-status-banner");
    const txt = $("#location-text");
    if (banner && txt) {
      banner.className = "location-banner warning";
      txt.innerHTML = `<span>📍 Default location set (San Pablo Center). Click map or pin to move.</span>`;
    }
  }

  showToast(`${reason}. Tap the map to set your location.`, "info");
}

// Floating Locate Me button on the Leaflet map
const LocateControl = L.Control.extend({
  options: { position: "topright" },
  onAdd: function () {
    const container = L.DomUtil.create("div", "leaflet-bar leaflet-control-locate");
    container.innerHTML = `
      <button id="btn-map-locate" type="button" title="Detect my live location" aria-label="Detect my live location">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="7"/>
          <line x1="12" y1="1" x2="12" y2="4"/>
          <line x1="12" y1="20" x2="12" y2="23"/>
          <line x1="1" y1="12" x2="4" y2="12"/>
          <line x1="20" y1="12" x2="23" y2="12"/>
        </svg>
      </button>`;
    L.DomEvent.disableClickPropagation(container);
    container.onclick = (e) => {
      e.preventDefault();
      detectLocation(true);
    };
    return container;
  }
});
map.addControl(new LocateControl());

const detectGpsBtn = $("#btn-detect-gps");
if (detectGpsBtn) {
  detectGpsBtn.onclick = () => detectLocation(true);
}

// ==========================================================================
// TERMINAL LIST & MAP MARKERS
// ==========================================================================

async function load() {
  const p = new URLSearchParams({ q: $("#q") ? $("#q").value : "" });
  if (me) {
    p.set("lat", me[0]);
    p.set("lng", me[1]);
  }
  if (dest) {
    p.set("dest_lat", dest[0]);
    p.set("dest_lng", dest[1]);
  }
  if (activeFilter === "open") p.set("open", 1);

  try {
    data = await api("/todas?" + p);
  } catch (err) {
    console.error("Failed to load TODAs:", err);
    data = [];
  }

  draw();
}

function draw() {
  const cnt = $("#cnt");
  const list = $("#list");
  if (cnt) cnt.textContent = `${data.length} TODA terminal${data.length === 1 ? "" : "s"} found`;

  if (list) {
    list.innerHTML = data.map((t, idx) => `
      <div class="item ${sel === t.id ? "on" : ""}" data-id="${t.id}">
        <h4>
          <span>${t.name}</span>
          <span class="tag ${t.is_active ? "" : "c"}">${t.is_active ? "Active" : "Closed"}</span>
        </h4>
        <div class="sm">Serves ${t.serves}</div>
        <div class="sm" style="margin-top:6px">
          📍 ${formatDist(t.km_from_you)} from you · 📞 ${t.phone} · ${t.units} tricycles
        </div>
        ${t.fare_to_dest ? `<div style="margin-top:8px;color:var(--t);font-weight:700">Fare to dest: ${formatDist(t.km_to_dest)} · ${peso(t.fare_to_dest)}</div>` : ""}
        <div style="margin-top:10px;display:flex;gap:8px">
          <button class="btn s ${sel === t.id ? "" : "w"}" onclick="event.stopPropagation(); pick(${t.id}, true)">
            Get Directions
          </button>
        </div>
      </div>
    `).join("") || '<div class="sm">No TODA found matching that search.</div>';

    document.querySelectorAll(".item").forEach(el => {
      el.onclick = () => pick(+el.dataset.id, true);
    });
  }

  // Draw Terminal Pins
  markers.forEach(m => m.remove());
  markers = data.map((t, i) => {
    const isSelected = sel === t.id;
    const m = L.marker([t.lat, t.lng], {
      icon: pin(i + 1, isSelected ? "d" : "")
    }).addTo(map);

    m.bindPopup(`
      <b>${t.name}</b><br>
      ${t.serves}<br>
      📞 ${t.phone}<br>
      <div style="margin-top:8px">
        <button class="btn s btn-full" onclick="pick(${t.id}, true)">Navigate Here</button>
      </div>
    `);

    m.on("click", () => pick(t.id));
    return m;
  });

  if (selectedToda) {
    updateNavigationGuidance();
  }
}

// Hide the TODA info card and show empty state (close button)
function hideNavCard() {
  const navCard = $("#nav-card");
  const empty = $("#empty-state");
  if (navCard) navCard.style.display = "none";
  if (empty) empty.style.display = "block";
  sel = null;
  selectedToda = null;
  draw();
}

// Select a TODA terminal to navigate to
function pick(id, pan = false) {
  sel = id;
  const t = data.find(x => x.id === id);
  if (!t) return;
  selectedToda = t;

  if (pan) {
    map.flyTo([t.lat, t.lng], 16, { duration: 1.0 });
  }

  // Hide empty state, show TODA info card
  const empty = $("#empty-state");
  const navCard = $("#nav-card");
  const title = $("#nav-title");
  const sub = $("#nav-subtitle");
  const info = $("#nav-toda-info");

  if (empty) empty.style.display = "none";
  if (navCard) {
    navCard.style.display = "block";
    if (window.innerWidth <= 768) {
      setTimeout(() => {
        navCard.scrollIntoView({ behavior: "smooth", block: "nearest" });
      }, 120);
    }
  }
  if (title) title.textContent = `Directions to ${t.name}`;
  if (sub) sub.textContent = `Serving ${t.serves}`;

  if (info) {
    info.innerHTML = `
      <div style="display:flex;justify-content:space-between;align-items:center">
        <span><b>Terminal:</b> ${t.name}</span>
        <span class="tag ${t.is_active ? "" : "c"}">${t.is_active ? "Open now" : "Closed"}</span>
      </div>
      <div style="margin-top:4px">📞 Contact: <a href="tel:${t.phone}" style="color:var(--primary);font-weight:700">${t.phone}</a></div>
      <div class="sm" style="margin-top:2px">${t.units} registered tricycle units at terminal</div>
    `;
  }

  draw();
  updateNavigationGuidance();
}

// ==========================================================================
// REAL-TIME DIRECTION & WALKING NAVIGATION
// ==========================================================================

async function updateNavigationGuidance() {
  if (!selectedToda || !me) return;

  const t = selectedToda;

  // Calculate road/walking route from user location to terminal
  const r = await road(me, [+t.lat, +t.lng]);

  line && line.remove();
  line = L.polyline(r.c, {
    color: "#0f7a76",
    weight: 6,
    opacity: 0.9
  }).addTo(map);

  if (!isNavigating) {
    map.fitBounds(line.getBounds(), { padding: [50, 50] });
  } else {
    // When actively navigating, follow user
    map.panTo(me);
  }

  // Distance & Estimated walking time
  const distKm = r.d;
  const distMeters = Math.round(distKm * 1000);
  const walkMins = Math.max(1, Math.round(distKm * 13)); // ~4.6 km/h walking pace

  // Calculate bearing from user to the TODA terminal
  const bearing = getBearing(me[0], me[1], +t.lat, +t.lng);
  const compName = getCompassName(bearing);
  const arrowSymbol = getCompassArrow(bearing);

  // Rotate arrow on user pin towards destination
  userHeading = bearing;
  if (meMarker) {
    meMarker.setIcon(createUserIcon(userHeading));
  }

  // Update Direction Stats
  const distEl = $("#nav-dist");
  const etaEl = $("#nav-eta");
  const bearingEl = $("#nav-bearing");
  const arrowIcon = $("#nav-arrow-icon");
  const mainStep = $("#nav-step-main");
  const subStep = $("#nav-step-sub");

  if (distEl) distEl.textContent = formatDist(distKm);
  if (etaEl) etaEl.textContent = `~${walkMins} min`;
  if (bearingEl) bearingEl.textContent = `${compName} (${Math.round(bearing)}°)`;
  if (arrowIcon) arrowIcon.textContent = arrowSymbol;

  // Arrival check (< 30 meters)
  if (distMeters <= 30) {
    if (mainStep) mainStep.innerHTML = "🎉 You have arrived at the terminal!";
    if (subStep) subStep.textContent = `${t.name} is right here.`;
    if (isNavigating) {
      showToast(`🎉 You have reached ${t.name}!`, "success");
      if (navigator.vibrate) navigator.vibrate([150, 80, 150]);
    }
  } else {
    if (mainStep) mainStep.textContent = `Walk ${compName} towards ${t.name}`;
    if (subStep) subStep.textContent = `${formatDist(distKm)} remaining · follow the highlighted route`;
  }
}

// Start Live Guidance
function startNavigation() {
  if (!selectedToda) {
    showToast("Please select a TODA terminal first", "info");
    return;
  }

  if (!me) {
    detectLocation(true);
  }

  isNavigating = true;
  startWatchingPosition();

  $("#btn-start-nav").style.display = "none";
  $("#btn-stop-nav").style.display = "inline-flex";
  $("#nav-badge").style.display = "inline-flex";

  showToast(`🧭 Live guidance started towards ${selectedToda.name}!`, "success");

  if (me) {
    map.flyTo(me, 17, { duration: 1.0 });
  }

  updateNavigationGuidance();
}

// Stop Live Guidance
function stopNavigation() {
  isNavigating = false;

  $("#btn-start-nav").style.display = "inline-flex";
  $("#btn-stop-nav").style.display = "none";
  $("#nav-badge").style.display = "none";

  showToast("Guidance paused.", "info");

  if (line) {
    map.fitBounds(line.getBounds(), { padding: [50, 50] });
  }
}

const startNavBtn = $("#btn-start-nav");
if (startNavBtn) startNavBtn.onclick = startNavigation;

const stopNavBtn = $("#btn-stop-nav");
if (stopNavBtn) stopNavBtn.onclick = stopNavigation;

// ==========================================================================
// DESTINATION & SEARCH
// ==========================================================================

async function setDest(ll, name) {
  dest = ll;
  // Destination star pin removed — pinning is admin-only

  // Hide TODA info card when setting a new destination
  hideNavCard();

  await load();

  // Auto-track the closest TODA to this destination
  if (data.length > 0 && !sel) {
    pick(data[0].id, true);
  }
}

const dstSelect = $("#dst");
if (dstSelect) {
  dstSelect.onchange = async () => {
    const o = dstSelect.selectedOptions[0];
    if (o.value) {
      await setDest([+o.dataset.lat, +o.dataset.lng], o.text);
    } else {
      dest = null;
      hideNavCard();
      load();
    }
  };
}

// Search
const searchInput = $("#q");
const searchBtn = $("#go");
if (searchBtn && searchInput) {
  searchBtn.onclick = async () => {
    const q = searchInput.value.trim();
    await load();
    if (!q) return;

    const hit = data.some(t => (t.name + t.serves).toLowerCase().includes(q.toLowerCase()));
    if (hit) return;

    showToast(`Searching map for “${q}”…`, "info");
    const r = await geocode(q);
    if (r) {
      if (dstSelect) dstSelect.value = "";
      await setDest([+r.lat, +r.lon], r.display_name.split(",")[0]);
      map.flyTo([+r.lat, +r.lon], 16);
    } else {
      showToast(`No place found for “${q}”.`, "info");
    }
  };

  searchInput.onkeydown = (e) => e.key === "Enter" && searchBtn.click();
}

// Chips
document.querySelectorAll("[data-f]").forEach(c => {
  c.onclick = () => {
    document.querySelectorAll("[data-f]").forEach(x => x.classList.remove("on"));
    c.classList.add("on");
    activeFilter = c.dataset.f;

    if (activeFilter === "near") {
      if (!me) {
        detectLocation(true);
      } else {
        map.flyTo(me, 15);
      }
    }
    load();
  };
});

// Helper: Toast Notifications
function showToast(msg, type = "info") {
  const container = $("#toda-toast-container");
  if (!container) return;

  const toast = document.createElement("div");
  toast.className = `toda-toast ${type}`;
  toast.innerHTML = `<span>${msg}</span>`;

  container.appendChild(toast);
  setTimeout(() => {
    toast.style.opacity = "0";
    toast.style.transform = "translateY(10px)";
    toast.style.transition = "all 0.3s ease";
    setTimeout(() => toast.remove(), 320);
  }, 4000);
}

// ==========================================================================
// INITIALIZATION
// ==========================================================================

// 1. Detect user location immediately (with fallback so pin ALWAYS shows)
detectLocation(false);

// 2. Load terminal markers and initial routes
load();
