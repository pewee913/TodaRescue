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
let hasGpsFix = false;

// Terminal and navigation state
let dest = null; // Optional trip destination [lat, lng]
let sel = null; // Selected TODA id
let markers = [];
let line = null;
let data = [];
let activeFilter = "all";
let isNavigating = false;
let selectedToda = null;
let activeDestinationQuery = "";
let allTodas = [];

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
    userHeading = getBearing(lat, lng, +selectedToda.lat, +selectedToda.lng);
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
    const title = source === "gps" ? `Live GPS (±${Math.round(accuracy || 10)}m)` : "Custom Location (drag pin to move)";
    meMarker.setPopupContent(`<b>You are here</b><br><small>${title}</small>`);
  } else {
    meMarker = L.marker(me, {
      icon: createUserIcon(userHeading),
      zIndexOffset: 1000,
      draggable: true
    }).addTo(map);

    meMarker.bindPopup(`<b>You are here</b><br><small>${source === "gps" ? "Live GPS Location" : "Drag pin or tap map to change location"}</small>`);
    meMarker.on("dragend", (e) => {
      const ll = e.target.getLatLng();
      setUserLocation(ll.lat, ll.lng, null, false, "manual");
      showToast("📍 Location pin moved.", "info");
      if (selectedToda) {
        displayTodaRoute(selectedToda);
      }
    });
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
    if (source === "gps") {
      const isCoarse = accuracy && accuracy > 2000;
      txt.innerHTML = isCoarse
        ? `<b>Network Location (±${(accuracy / 1000).toFixed(1)}km)</b> · Tap map to fine-tune`
        : `<b>Live GPS Connected</b> · accuracy ±${Math.round(accuracy || 15)}m`;
    } else {
      txt.innerHTML = `<b>Location Set</b> · Click pin or map to change`;
    }
  }

  // Only refetch TODA list if moved > 25 meters or if list not yet loaded
  const movedMeters = prevCoord ? hav(prevCoord, me) * 1000 : 999;
  if (!data.length || movedMeters > 25 || source !== "gps") {
    load();
  }

  // If a TODA is selected, immediately recalculate route and direction guidance
  if (selectedToda) {
    displayTodaRoute(selectedToda);
  }
}

// Click on map to place or move user location
map.on("click", (e) => {
  setUserLocation(e.latlng.lat, e.latlng.lng, null, false, "manual");
  showToast("📍 Location set. Calculating walking route to TODA…", "info");
  if (selectedToda) {
    displayTodaRoute(selectedToda);
  }
});

// Real-time location detection with true GPS hardware preference
function detectLocation(userInitiated = false) {
  const locateBtn = $("#btn-detect-gps");
  const mapBtn = $("#btn-map-locate");
  if (locateBtn) locateBtn.textContent = "Locating GPS…";
  if (mapBtn) mapBtn.classList.add("locating");

  if (!navigator.geolocation) {
    fallbackLocation("Geolocation is not supported by your browser.", userInitiated);
    return;
  }

  // Check secure origin (browsers strictly block GPS over plain HTTP except localhost)
  if (window.isSecureContext === false && location.hostname !== "localhost" && location.hostname !== "127.0.0.1") {
    fallbackLocation("Mobile browsers block GPS on insecure HTTP. Please use HTTPS or tap the map to set location.", userInitiated);
    return;
  }

  const isMobile = /Android|iPhone|iPad|iPod|Mobile/i.test(navigator.userAgent);
  // Phones have dedicated GPS hardware; PCs on Wi-Fi rely on Wi-Fi network triangulation
  const primaryHighAccuracy = isMobile;

  navigator.geolocation.getCurrentPosition(
    (pos) => {
      onLocationSuccess(pos, userInitiated);
      startWatchingPosition();
    },
    (err) => {
      console.warn("Primary location attempt failed:", err.code, err.message);

      // If user denied permission, do not retry — instruct how to unblock
      if (err.code === 1) { // PERMISSION_DENIED
        fallbackLocation("Location permission is blocked. Click the tune/padlock icon in the address bar to allow location, or tap the map.", userInitiated);
        return;
      }

      // Try alternate accuracy mode
      navigator.geolocation.getCurrentPosition(
        (pos2) => {
          onLocationSuccess(pos2, userInitiated);
          startWatchingPosition();
        },
        (err2) => {
          console.warn("Alternate location attempt failed:", err2.code, err2.message);
          let reason = "Location timed out. Check 'Let desktop apps access your location' in Windows Settings, or tap the map.";
          if (err2.code === 1) {
            reason = "Location permission blocked. Enable Location in browser settings or tap map.";
          } else if (err2.code === 2) {
            reason = "Location service unavailable. Turn on Wi-Fi / Location in Windows or tap the map.";
          }
          fallbackLocation(reason, userInitiated);
        },
        { enableHighAccuracy: !primaryHighAccuracy, timeout: 8000, maximumAge: 300000 }
      );
    },
    { enableHighAccuracy: primaryHighAccuracy, timeout: 8000, maximumAge: 60000 }
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

  const lat = pos.coords.latitude;
  const lng = pos.coords.longitude;
  const acc = pos.coords.accuracy;

  const shouldPan = userInitiated || !hasGpsFix;
  hasGpsFix = true;

  setUserLocation(lat, lng, acc, shouldPan, "gps");

  if (userInitiated) {
    showToast(`📍 Live GPS detected (accuracy ±${Math.round(acc)}m)!`, "success");
  }
}

// Live GPS movement tracker
function startWatchingPosition() {
  if (watchId !== null || !navigator.geolocation) return;
  try {
    watchId = navigator.geolocation.watchPosition(
      (pos) => {
        onLocationSuccess(pos, false);
      },
      (err) => {
        console.warn("watchPosition notice:", err.message);
      },
      { enableHighAccuracy: true, maximumAge: 5000, timeout: 25000 }
    );
  } catch (e) {
    console.warn("Unable to start watchPosition", e);
  }
}

// Guaranteed fallback so user location pin & status are always clear
function fallbackLocation(reason, userInitiated = false) {
  const locateBtn = $("#btn-detect-gps");
  const mapBtn = $("#btn-map-locate");
  if (locateBtn) locateBtn.textContent = "Locate Me";
  if (mapBtn) {
    mapBtn.classList.remove("locating");
    mapBtn.classList.remove("active");
  }

  const banner = $("#location-status-banner");
  const txt = $("#location-text");
  if (banner && txt) {
    banner.className = "location-banner warning";
    txt.innerHTML = `<span>⚠️ ${escapeHtml(reason)}</span>`;
  }

  showToast(reason, userInitiated ? "info" : "info");
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
    if (!$("#q")?.value && !dest && activeFilter === "all" && data.length) {
      allTodas = data;
    } else if (allTodas.length === 0 && data.length) {
      allTodas = data;
    }
  } catch (err) {
    console.error("Failed to load TODAs:", err);
    data = [];
  }

  draw();
  if (selectedToda) {
    displayTodaRoute(selectedToda);
  }
}

function parseRouteList(text) {
  if (!text || typeof text !== "string") return [];
  return text
    .split(/[,;\n]/)
    .map(s => s.trim())
    .filter(s => s.length > 0);
}

function draw() {
  const cnt = $("#cnt");
  const list = $("#list");
  if (cnt) cnt.textContent = `${data.length} TODA terminal${data.length === 1 ? "" : "s"} found`;

  if (list) {
    list.innerHTML = data.map((t, idx) => {
      const brgys = parseRouteList(t.barangays_passed || t.serves);
      const landmarks = parseRouteList(t.landmarks);

      return `
        <div class="item ${sel === t.id ? "on" : ""}" data-id="${t.id}">
          <h4>
            <span>${escapeHtml(t.name)}</span>
            <span class="tag ${t.is_active ? "" : "c"}">${t.is_active ? "Active" : "Closed"}</span>
          </h4>

          ${brgys.length ? `
            <div class="list-brgy-cards">
              ${brgys.map((b, i) => `
                <span class="list-brgy-pill">
                  <span class="list-brgy-num">${i + 1}</span> ${escapeHtml(b)}
                </span>
              `).join("")}
            </div>
          ` : `<div class="sm"><b>Serves:</b> ${escapeHtml(t.serves)}</div>`}

          ${landmarks.length ? `
            <div class="list-landmark-cards">
              ${landmarks.map(lm => `
                <span class="list-landmark-pill">📍 ${escapeHtml(lm)}</span>
              `).join("")}
            </div>
          ` : ""}

          ${t.km_from_you !== undefined ? `
            <div class="sm" style="margin-top:6px">
              📍 ${formatDist(t.km_from_you)} from your location
            </div>
          ` : ""}

          ${activeDestinationQuery ? `
            <div style="margin-top:6px;display:inline-flex;align-items:center;gap:5px;background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;padding:3px 8px;border-radius:6px;font-size:11.5px;font-weight:700;">
              <span>✓ Passes through ${escapeHtml(activeDestinationQuery)}</span>
            </div>
          ` : ""}

          ${t.fare_to_dest ? `<div style="margin-top:8px;color:var(--t);font-weight:700">Fare to dest: ${formatDist(t.km_to_dest)} · ${peso(t.fare_to_dest)}</div>` : ""}

          <div style="margin-top:10px;display:flex;justify-content:flex-end">
            <span class="btn-text-action ${sel === t.id ? 'active' : ''}">
              ${sel === t.id ? 'Viewing Details ✓' : 'View Terminal Details →'}
            </span>
          </div>
        </div>
      `;
    }).join("") || '<div class="sm">No TODA found matching that search.</div>';

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

    const brgys = parseRouteList(t.barangays_passed || t.serves);
    const landmarks = parseRouteList(t.landmarks);

    m.bindPopup(`
      <div style="min-width:190px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
          <b style="font-size:14px">${escapeHtml(t.name)}</b>
          <span class="tag ${t.is_active ? "" : "c"}">${t.is_active ? "Open" : "Closed"}</span>
        </div>

        ${brgys.length ? `
          <div style="font-size:11px;font-weight:700;color:var(--primary);text-transform:uppercase;margin-top:4px">🏘️ Barangays (${brgys.length}):</div>
          <div style="display:flex;flex-wrap:wrap;gap:3px;margin-top:2px">
            ${brgys.map(b => `<span style="display:inline-block;background:#f1f5f9;color:var(--ink);font-size:11.5px;padding:2px 6px;border-radius:4px;font-weight:600">${escapeHtml(b)}</span>`).join("")}
          </div>
        ` : `<div class="sm">${escapeHtml(t.serves)}</div>`}

        ${landmarks.length ? `
          <div style="font-size:11px;font-weight:700;color:#d95a10;text-transform:uppercase;margin-top:6px">📍 Via Landmarks:</div>
          <div style="display:flex;flex-wrap:wrap;gap:3px;margin-top:2px">
            ${landmarks.map(lm => `<span style="display:inline-block;background:#fff8f3;color:#8a3b00;font-size:11.5px;padding:2px 6px;border-radius:4px;font-weight:600">📍 ${escapeHtml(lm)}</span>`).join("")}
          </div>
        ` : ""}

        <div style="margin-top:10px">
          <button class="btn s btn-full ${isSelected ? '' : 'outline'}" onclick="pick(${t.id}, true)">
            ${isSelected ? 'Selected Terminal ✓' : `Select Option #${i + 1}`}
          </button>
        </div>
      </div>
    `);

    m._todaId = t.id;
    m.on("click", () => pick(t.id));
    return m;
  });

  renderOptionsUI();
}

// Renders the In-Card Switcher Pills and the Matching TODAs Options Section
function renderOptionsUI() {
  const switcherWrap = $("#toda-switcher-wrap");
  const switcherEl = $("#toda-options-switcher");
  const switcherCount = $("#toda-switcher-count");
  const matchingSection = $("#matching-todas-section");
  const matchingTitle = $("#matching-todas-title");
  const matchingCount = $("#matching-count-badge");
  const matchingSub = $("#matching-todas-sub");
  const matchingList = $("#matching-options-list");

  // In-Card Switcher Pills (inside #nav-card)
  if (switcherWrap && switcherEl) {
    if (data.length > 1) {
      switcherWrap.style.display = "block";
      if (switcherCount) switcherCount.textContent = `${data.length} options`;

      switcherEl.innerHTML = data.map((t, idx) => {
        const isSel = sel === t.id;
        const distStr = t.km_from_you !== undefined ? `🚶 ${formatDist(t.km_from_you)}` : 'Terminal';
        return `
          <button type="button" class="toda-option-pill ${isSel ? 'active' : ''}" data-id="${t.id}" title="Switch to ${escapeHtml(t.name)}">
            <span class="pill-num">${idx + 1}</span>
            <div>
              <div class="pill-name">${escapeHtml(t.name)}${isSel ? ' ✓' : ''}</div>
              <div class="pill-dist">${distStr}</div>
            </div>
          </button>
        `;
      }).join("");

      switcherEl.querySelectorAll(".toda-option-pill").forEach(btn => {
        btn.onclick = () => {
          const id = +btn.dataset.id;
          if (id !== sel) pick(id, true);
        };
      });
    } else {
      switcherWrap.style.display = "none";
    }
  }

  // Matching Options Section Card (in sidebar)
  if (matchingSection && matchingList) {
    const shouldShow = data.length > 1 && (activeDestinationQuery || sel !== null);
    if (shouldShow) {
      matchingSection.style.display = "block";
      if (matchingTitle) {
        matchingTitle.textContent = activeDestinationQuery
          ? `TODAs Passing Through “${activeDestinationQuery}”`
          : `Available TODA Options`;
      }
      if (matchingCount) {
        matchingCount.textContent = `${data.length} Options`;
      }
      if (matchingSub) {
        matchingSub.textContent = activeDestinationQuery
          ? `Found ${data.length} TODAs passing through this area. Tap an option below to view its route:`
          : `Choose a terminal option below to see live walking directions and route:`;
      }

      matchingList.innerHTML = data.map((t, idx) => {
        const isSel = sel === t.id;
        const brgys = parseRouteList(t.barangays_passed || t.serves);
        const distStr = t.km_from_you !== undefined ? formatDist(t.km_from_you) : null;
        const walkMins = t.km_from_you !== undefined ? Math.max(1, Math.round(t.km_from_you * 12)) : null;

        return `
          <div class="matching-option-card ${isSel ? 'selected' : ''}" data-id="${t.id}">
            <div class="matching-option-top">
              <div class="matching-option-title">
                <span class="matching-option-num">${idx + 1}</span>
                <span>${escapeHtml(t.name)}</span>
              </div>
              <span class="tag ${t.is_active ? '' : 'c'}">${t.is_active ? 'Active' : 'Closed'}</span>
            </div>

            <div class="matching-option-meta">
              ${distStr ? `<span>🚶 <b>${distStr}</b> walk</span>` : ''}
              ${walkMins ? `<span>⏱️ ~${walkMins} mins</span>` : ''}
              ${t.fare_to_dest ? `<span>💵 Fare: <b>${peso(t.fare_to_dest)}</b></span>` : ''}
            </div>

            ${brgys.length ? `
              <div class="matching-option-route">
                <b>🏘️ Route passes:</b> ${escapeHtml(brgys.slice(0, 5).join(', '))}${brgys.length > 5 ? ` <span style="color:var(--mu)">(+${brgys.length - 5} more)</span>` : ''}
              </div>
            ` : ''}

            <div class="matching-option-btn-row">
              <button type="button" class="matching-option-btn ${isSel ? 'active' : 'select'}">
                ${isSel ? 'Selected Option ✓' : 'Select This Option →'}
              </button>
            </div>
          </div>
        `;
      }).join("");

      matchingList.querySelectorAll(".matching-option-card").forEach(card => {
        card.onclick = () => {
          const id = +card.dataset.id;
          pick(id, true);
        };
      });
    } else {
      matchingSection.style.display = "none";
    }
  }
}

// Route Visualization State
let currentRoutePolyline = null;
let currentRouteDecorator = null;
let currentRouteMarkers = [];
let userToTodaLine = null;
let routeRequestId = 0;

function clearCurrentRoute() {
  if (userToTodaLine) {
    userToTodaLine.remove();
    userToTodaLine = null;
  }
  if (currentRoutePolyline) {
    currentRoutePolyline.remove();
    currentRoutePolyline = null;
  }
  if (currentRouteDecorator) {
    currentRouteDecorator.remove();
    currentRouteDecorator = null;
  }
  if (currentRouteMarkers && currentRouteMarkers.length) {
    currentRouteMarkers.forEach(m => m.remove());
    currentRouteMarkers = [];
  }
}

// Updates the walking direction guidance HUD banner & stats towards selected TODA
function updateGuidanceUI(t, distKm, walkMins) {
  if (!t || !me) return;

  const bearing = getBearing(me[0], me[1], +t.lat, +t.lng);
  const compName = getCompassName(bearing);
  const arrowSymbol = getCompassArrow(bearing);

  userHeading = bearing;
  if (meMarker) {
    meMarker.setIcon(createUserIcon(userHeading));
  }

  const distMeters = Math.round(distKm * 1000);

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

  if (distMeters <= 30) {
    if (mainStep) mainStep.innerHTML = `🎉 You have reached ${escapeHtml(t.name)}!`;
    if (subStep) subStep.textContent = "The terminal is right here. Ride your tricycle!";
  } else {
    if (mainStep) mainStep.textContent = `Walk ${compName} towards ${escapeHtml(t.name)}`;
    if (subStep) subStep.textContent = `${formatDist(distKm)} walk (~${walkMins} min) · Follow blue dashed route on map`;
  }
}

window.focusWalkRoute = function () {
  if (userToTodaLine) {
    map.fitBounds(userToTodaLine.getBounds(), { padding: [50, 50], maxZoom: 17 });
  } else if (me && selectedToda) {
    map.fitBounds(L.latLngBounds([me, [+selectedToda.lat, +selectedToda.lng]]), { padding: [50, 50] });
  }
};

window.focusTodaTerminal = function () {
  if (selectedToda) {
    map.flyTo([+selectedToda.lat, +selectedToda.lng], 16, { duration: 0.8 });
  }
};

function getTodaRoutePins(t) {
  let pins = [];
  if (t.route_waypoints && Array.isArray(t.route_waypoints)) {
    pins = JSON.parse(JSON.stringify(t.route_waypoints));
  } else if (typeof t.route_waypoints === "string") {
    try {
      const parsed = JSON.parse(t.route_waypoints);
      if (Array.isArray(parsed)) pins = parsed;
    } catch (e) {}
  }

  // Filter out any redundant starting/origin point (the TODA marker itself is the start)
  pins = pins.filter(p => p.type !== 'origin' && !/\b(origin|terminal pickup)\b/i.test(p.name || ''));

  if (pins.length > 0) return pins;

  // Fallback: build pins from entered barangays and landmarks
  const brgys = parseRouteList(t.barangays_passed);
  const landmarks = parseRouteList(t.landmarks);
  const destName = t.serves ? t.serves.split(',')[0].trim() : `${t.name} Destination`;
  const combined = [...brgys, ...landmarks];
  if (!combined.includes(destName)) combined.push(destName);

  if (combined.length > 0) {
    const startLat = +t.lat, startLng = +t.lng;
    const endLat = +(startLat - 0.04).toFixed(6);
    const endLng = +(startLng - 0.04).toFixed(6);
    return combined.map((name, i) => {
      const ratio = (i + 1) / combined.length;
      return {
        name: name,
        lat: +(startLat + (endLat - startLat) * ratio).toFixed(6),
        lng: +(startLng + (endLng - startLng) * ratio).toFixed(6)
      };
    });
  }

  return [];
}

async function fetchRoadRoute(waypoints) {
  const coordsStr = waypoints.map(w => `${w.lng},${w.lat}`).join(";");
  const cacheKey = "osrm_route|" + coordsStr;
  if (cache[cacheKey]) return cache[cacheKey];

  try {
    const url = `https://router.project-osrm.org/route/v1/driving/${coordsStr}?overview=full&geometries=geojson`;
    const res = await fetch(url);
    const data = await res.json();
    if (data.code === "Ok" && data.routes && data.routes.length > 0) {
      const r = data.routes[0];
      const latlngs = r.geometry.coordinates.map(c => [c[1], c[0]]);
      return cache[cacheKey] = {
        latlngs,
        distance: r.distance,
        duration: r.duration
      };
    }
  } catch (err) {
    console.warn("OSRM routing request failed:", err);
  }

  return {
    latlngs: waypoints.map(w => [w.lat, w.lng]),
    distance: 0,
    duration: 0
  };
}

async function displayTodaRoute(t) {
  const reqId = ++routeRequestId;

  // 1. Clear previous route and additional pins
  clearCurrentRoute();

  // 2. Calculate and draw walking route from user location to the TODA terminal
  if (me) {
    try {
      const r = await road(me, [+t.lat, +t.lng]);
      if (reqId === routeRequestId && (!selectedToda || selectedToda.id === t.id)) {
        userToTodaLine = L.polyline(r.c, {
          color: "#0284c7",
          weight: 5,
          opacity: 0.95,
          dashArray: "7, 7",
          lineJoin: "round",
          lineCap: "round"
        }).addTo(map);

        const distKm = r.d;
        const walkMins = Math.max(1, Math.round(r.m || distKm * 13));

        userToTodaLine.bindTooltip(`🚶 <b>Walk to ${escapeHtml(t.name)}:</b> ${formatDist(distKm)} (~${walkMins} min)`, {
          sticky: true
        });

        updateGuidanceUI(t, distKm, walkMins);
      }
    } catch (e) {
      console.warn("Could not calculate road to TODA:", e);
      const distKm = hav(me, [+t.lat, +t.lng]);
      const walkMins = Math.max(1, Math.round(distKm * 13));
      updateGuidanceUI(t, distKm, walkMins);
    }
  }

  const pins = getTodaRoutePins(t);
  if (pins && pins.length > 0) {
    // 3. Route starts at the TODA terminal and passes through all additional pins to the farthest pin
    const startPoint = { name: t.name, lat: +t.lat, lng: +t.lng };
    const allRoutePoints = [startPoint, ...pins];

    // 4. Fetch OSRM road-following route geometry
    const routeData = await fetchRoadRoute(allRoutePoints);
    if (reqId !== routeRequestId) return;

    const latlngs = routeData.latlngs;
    if (latlngs && latlngs.length >= 2) {
      currentRoutePolyline = L.polyline(latlngs, {
        color: "#ff5f15",
        weight: 5,
        opacity: 0.9,
        lineJoin: "round",
        lineCap: "round"
      }).addTo(map);

      // Add directional arrows with L.PolylineDecorator
      const Decorator = L.polylineDecorator || (typeof L.PolylineDecorator === "function" ? (paths, opts) => new L.PolylineDecorator(paths, opts) : null);
      if (typeof Decorator === "function" && L.Symbol && L.Symbol.arrowHead) {
        try {
          currentRouteDecorator = Decorator(currentRoutePolyline, {
            patterns: [
              {
                offset: 25,
                repeat: 55,
                symbol: L.Symbol.arrowHead({
                  pixelSize: 11,
                  headAngle: 50,
                  polygon: false,
                  pathOptions: {
                    stroke: true,
                    weight: 3,
                    color: "#ffffff",
                    opacity: 0.95
                  }
                })
              },
              {
                offset: 25,
                repeat: 55,
                symbol: L.Symbol.arrowHead({
                  pixelSize: 11,
                  headAngle: 50,
                  polygon: false,
                  pathOptions: {
                    stroke: true,
                    weight: 1.5,
                    color: "#d9480f",
                    opacity: 1
                  }
                })
              }
            ]
          }).addTo(map);
        } catch (e) {
          console.warn("Could not create polyline decorator:", e);
        }
      }

      // Draw additional pins ONLY for the places where the tricycle passes (TODA marker is already the start)
      pins.forEach((wp, idx) => {
        const isFarthest = idx === pins.length - 1;
        const isBrgy = /brgy|barangay/i.test(wp.name);
        let iconHtml = "";
        let className = "route-point-marker";

        if (isFarthest) {
          className += " destination";
          iconHtml = `<div class="route-marker-pin destination" title="Farthest Pin: ${escapeHtml(wp.name)}">🏁</div>`;
        } else if (isBrgy) {
          className += " passed-brgy";
          iconHtml = `<div class="route-marker-pin passed-brgy" title="Dadaan sa: ${escapeHtml(wp.name)}">🏘️</div>`;
        } else {
          className += " passed-landmark";
          iconHtml = `<div class="route-marker-pin passed-landmark" title="Dadaan sa: ${escapeHtml(wp.name)}">📍</div>`;
        }

        const markerIcon = L.divIcon({
          className: className,
          html: iconHtml,
          iconSize: [28, 28],
          iconAnchor: [14, 14]
        });

        const m = L.marker([wp.lat, wp.lng], { icon: markerIcon, zIndexOffset: 850 }).addTo(map);
        m._wpName = wp.name;

        m.bindTooltip(`<b>${isFarthest ? '🏁 Farthest End: ' : '📍 Dadaan sa: '}${escapeHtml(wp.name)}</b>`, { direction: 'top', offset: [0, -14] });

        m.bindPopup(`
          <div style="min-width:175px;font-family:inherit;">
            <div style="display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:700;text-transform:uppercase;color:${isFarthest ? '#dc2626' : '#ea580c'};background:${isFarthest ? '#fef2f2' : '#fff7ed'};padding:2px 7px;border-radius:4px;letter-spacing:0.3px;">
              ${isFarthest ? '🏁 Farthest Pin / Destination' : '📍 Dadaan Dito'}
            </div>
            <div style="font-size:14px;font-weight:800;color:var(--ink);margin-top:5px;">
              ${escapeHtml(wp.name)}
            </div>
            <div style="font-size:12px;color:#16a34a;font-weight:600;margin-top:4px;">
              ✓ Dadaan dito ang tricycle
            </div>
            <div style="font-size:11.5px;color:var(--mu);margin-top:2px;line-height:1.4;">
              Sakyan ang <b>${escapeHtml(t.name)}</b> kung pupunta rito.
            </div>
          </div>
        `);

        currentRouteMarkers.push(m);
      });
    }
  }

  // 5. Fit map bounds to show BOTH the user walk route and tricycle route
  const bounds = L.latLngBounds([]);
  if (userToTodaLine) bounds.extend(userToTodaLine.getBounds());
  if (currentRoutePolyline) bounds.extend(currentRoutePolyline.getBounds());
  if (me) bounds.extend(me);
  bounds.extend([+t.lat, +t.lng]);

  if (bounds.isValid()) {
    map.fitBounds(bounds, {
      padding: [50, 50],
      maxZoom: 16
    });
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
  clearCurrentRoute();
  draw();
}

// Focuses and opens popup of a passed landmark or barangay pin on the map
window.focusRoutePin = function (name) {
  if (!name || !currentRouteMarkers.length) return;
  const cleanTarget = name.toLowerCase().replace(/^(brgy\.?|barangay)\s*/i, '').trim();
  const m = currentRouteMarkers.find(marker => {
    if (!marker._wpName) return false;
    const wpClean = marker._wpName.toLowerCase().replace(/^(brgy\.?|barangay)\s*/i, '').trim();
    return wpClean.includes(cleanTarget) || cleanTarget.includes(wpClean);
  });

  if (m) {
    const latLng = m.getLatLng();
    map.flyTo(latLng, 16, { duration: 0.8 });
    setTimeout(() => {
      m.openPopup();
      const el = m.getElement();
      if (el) {
        const pinEl = el.querySelector('.route-marker-pin');
        if (pinEl) {
          pinEl.classList.add('highlighted-pin');
          setTimeout(() => pinEl.classList.remove('highlighted-pin'), 2000);
        }
      }
    }, 450);
  }
};

// Select a TODA terminal to show details & route cards
function pick(id, pan = false) {
  sel = id;
  const t = data.find(x => x.id === id);
  if (!t) return;
  selectedToda = t;

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

  const brgys = parseRouteList(t.barangays_passed || t.serves);
  const landmarks = parseRouteList(t.landmarks);
  const pins = getTodaRoutePins(t);
  const endPointName = pins.length ? pins[pins.length - 1].name : (t.serves || t.name);

  if (title) title.textContent = `Directions to ${t.name}`;
  if (sub) sub.textContent = `Live walking guidance & route`;

  if (info) {
    info.innerHTML = `
      <div class="terminal-meta-header">
        <div class="terminal-name-badge">
          <span class="terminal-tag-dot ${t.is_active ? 'open' : 'closed'}"></span>
          <b style="font-size:15px;color:var(--ink)">${escapeHtml(t.name)}</b>
        </div>
        <span class="tag ${t.is_active ? '' : 'c'}">${t.is_active ? 'Open now' : 'Closed'}</span>
      </div>

      ${activeDestinationQuery ? `
        <div class="search-match-banner">
          <div style="font-size:24px;line-height:1;">🎯</div>
          <div style="flex:1;min-width:0;">
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;color:#15803d;letter-spacing:0.3px;">Destination Match</div>
            <div style="font-size:13.5px;font-weight:800;color:var(--ink);">${escapeHtml(t.name)} passes through <u>${escapeHtml(activeDestinationQuery)}</u>!</div>
            <div style="font-size:11.5px;color:var(--mu);">
              ${data.length > 1 ? `Found <b>${data.length} candidate TODAs</b> for this area. Use the switcher above to switch between options.` : 'Sakyan ang TODA na ito para makapunta roon.'}
            </div>
          </div>
        </div>
      ` : ''}

      ${pins.length ? `
        <div style="margin-top:8px;padding:9px 12px;background:#fff8f3;border-radius:var(--radius-sm);border:1px solid #fed7aa;display:flex;align-items:center;gap:8px;">
          <span style="font-size:16px">🛣️</span>
          <div style="min-width:0;flex:1;">
            <div style="font-size:10.5px;font-weight:700;text-transform:uppercase;color:#c2410c;letter-spacing:0.3px;">Ruta / Dadaan Dito</div>
            <div style="font-size:13px;font-weight:700;color:var(--ink);">${escapeHtml(t.name)} → ${escapeHtml(endPointName)}</div>
          </div>
        </div>
      ` : ''}

      ${brgys.length ? `
        <div class="brgy-section">
          <div class="route-block-header">
            <span class="route-block-title">🏘️ Barangays Along Route</span>
            <span class="route-count-badge">${brgys.length} barangay${brgys.length === 1 ? '' : 's'}</span>
          </div>
          <div class="brgy-cards-grid">
            ${brgys.map((b, i) => `
              <div class="brgy-info-card" onclick="focusRoutePin('${escapeHtml(b)}')" title="Click to locate on map">
                <div class="brgy-step-indicator">🏘️</div>
                <div class="brgy-card-details">
                  <div class="brgy-card-name">${escapeHtml(b)}</div>
                  <div class="brgy-card-sub">Passes through</div>
                </div>
              </div>
            `).join('')}
          </div>
        </div>
      ` : ''}

      ${landmarks.length ? `
        <div class="landmarks-section">
          <div class="route-block-header">
            <span class="route-block-title">📍 Places & Landmarks Along Way</span>
            <span class="route-count-badge landmark-badge">${landmarks.length} place${landmarks.length === 1 ? '' : 's'}</span>
          </div>
          <div class="landmark-cards-grid">
            ${landmarks.map(lm => `
              <div class="landmark-info-card" onclick="focusRoutePin('${escapeHtml(lm)}')" title="Click to locate on map">
                <div class="landmark-pin-circle">📍</div>
                <div class="landmark-card-details">
                  <div class="landmark-card-name">${escapeHtml(lm)}</div>
                  <div class="landmark-card-sub">Passes by</div>
                </div>
              </div>
            `).join('')}
          </div>
        </div>
      ` : ''}

      ${t.fare_to_dest ? `
        <div class="dest-fare-pill">
          <div>
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--t);letter-spacing:0.3px">Fare to Destination</div>
            <div class="sm">${formatDist(t.km_to_dest)} from terminal</div>
          </div>
          <div style="font-size:16px;font-weight:800;color:var(--t-dark)">${peso(t.fare_to_dest)}</div>
        </div>
      ` : ''}
    `;
  }

  draw();
  if (selectedToda) {
    displayTodaRoute(selectedToda);
  }

  // Pop up terminal pin on map
  setTimeout(() => {
    const activeMarker = markers.find(m => m._todaId === t.id);
    if (activeMarker) {
      activeMarker.openPopup();
    }
  }, 450);
}

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
      const placeName = o.text.trim();
      if (searchInput) searchInput.value = placeName;
      await setDest([+o.dataset.lat, +o.dataset.lng], placeName);
      executeSearch(placeName);
    } else {
      dest = null;
      activeDestinationQuery = "";
      hideNavCard();
      load();
    }
  };
}

// Build suggestions list from loaded TODAs in database
function getSuggestions(query) {
  if (!query || query.length < 1) return [];
  const q = query.toLowerCase().replace(/^(brgy\.?|barangay)\s*/i, '').trim();
  const results = [];
  const sourceList = allTodas.length ? allTodas : data;

  sourceList.forEach(t => {
    const brgys = parseRouteList(t.barangays_passed);
    const landmarks = parseRouteList(t.landmarks);
    const pins = getTodaRoutePins(t);

    brgys.forEach(b => {
      const clean = b.toLowerCase().replace(/^(brgy\.?|barangay)\s*/i, '').trim();
      if (clean.includes(q)) {
        const key = "b:" + clean;
        const existing = results.find(r => r._key === key);
        if (existing) {
          existing.todaCount = (existing.todaCount || 1) + 1;
        } else {
          results.push({ _key: key, name: b, type: "Barangay", icon: "🏘️", todaName: t.name, todaId: t.id, todaCount: 1 });
        }
      }
    });

    landmarks.forEach(lm => {
      const clean = lm.toLowerCase().trim();
      if (clean.includes(q)) {
        const key = "l:" + clean;
        const existing = results.find(r => r._key === key);
        if (existing) {
          existing.todaCount = (existing.todaCount || 1) + 1;
        } else {
          results.push({ _key: key, name: lm, type: "Landmark", icon: "📍", todaName: t.name, todaId: t.id, todaCount: 1 });
        }
      }
    });

    pins.forEach(p => {
      if (p.name) {
        const clean = p.name.toLowerCase().replace(/^(brgy\.?|barangay)\s*/i, '').trim();
        if (clean.includes(q)) {
          const isB = /brgy|barangay/i.test(p.name);
          const key = (isB ? "b:" : "p:") + clean;
          const existing = results.find(r => r._key === key);
          if (existing) {
            existing.todaCount = (existing.todaCount || 1) + 1;
          } else {
            results.push({ _key: key, name: p.name, type: isB ? "Barangay" : "Landmark", icon: isB ? "🏘️" : "📍", todaName: t.name, todaId: t.id, todaCount: 1 });
          }
        }
      }
    });

    if (t.serves) {
      parseRouteList(t.serves).forEach(s => {
        const clean = s.toLowerCase().replace(/^(brgy\.?|barangay)\s*/i, '').trim();
        if (clean.includes(q)) {
          const key = "s:" + clean;
          const existing = results.find(r => r._key === key);
          if (existing) {
            existing.todaCount = (existing.todaCount || 1) + 1;
          } else {
            results.push({ _key: key, name: s, type: "Serves", icon: "🏁", todaName: t.name, todaId: t.id, todaCount: 1 });
          }
        }
      });
    }

    if (t.name.toLowerCase().includes(q)) {
      const key = "t:" + t.id;
      if (!results.find(r => r._key === key)) {
        results.push({ _key: key, name: t.name, type: "Terminal", icon: "🛺", todaName: t.name, todaId: t.id, todaCount: 1 });
      }
    }
  });

  return results.slice(0, 6);
}

function renderSuggestions(list) {
  const container = $("#search-suggestions");
  if (!container) return;
  if (!list.length) {
    container.style.display = "none";
    return;
  }

  container.innerHTML = list.map(item => `
    <div class="search-suggestion-item" data-name="${escapeHtml(item.name)}" data-toda="${item.todaId}">
      <div style="display:flex;align-items:center;gap:10px;min-width:0;">
        <span style="font-size:16px;">${item.icon}</span>
        <div>
          <div class="search-sug-name">${escapeHtml(item.name)}</div>
          <div class="search-sug-toda">
            Ride: <b>${escapeHtml(item.todaName)}</b>
            ${item.todaCount > 1 ? `<span style="font-weight:600;color:var(--mu);font-size:11px;"> (+${item.todaCount - 1} more option${item.todaCount > 2 ? 's' : ''})</span>` : ''}
          </div>
        </div>
      </div>
      <span class="search-sug-badge">${item.type}</span>
    </div>
  `).join("");

  container.style.display = "block";

  container.querySelectorAll(".search-suggestion-item").forEach(el => {
    el.onclick = () => {
      const destName = el.dataset.name;
      const todaId = +el.dataset.toda;
      $("#q").value = destName;
      container.style.display = "none";
      executeSearch(destName, todaId);
    };
  });
}

// Executes destination search and automatically pops up the matching TODA
async function executeSearch(query, preferredTodaId = null) {
  const q = (query || "").trim();
  const sug = $("#search-suggestions");
  if (sug) sug.style.display = "none";

  if (!q) {
    activeDestinationQuery = "";
    await load();
    return;
  }

  activeDestinationQuery = q;
  await load();

  if (data.length > 0) {
    let target = preferredTodaId ? data.find(t => t.id === preferredTodaId) : null;
    if (!target) target = data[0];

    // Automatically pop up the matching TODA and its routes!
    pick(target.id, true);

    if (data.length > 1) {
      showToast(`🎯 Found ${data.length} TODA options passing through “${q}”! You can switch between them anytime.`, "success");
    } else {
      showToast(`🎯 Found TODA passing through “${q}”: ${target.name}!`, "success");
    }

    // Automatically highlight the specific pin where the tricycle passes that area
    setTimeout(() => {
      window.focusRoutePin(q);
    }, 700);
  } else {
    showToast(`No TODA found passing through “${q}”. Try searching another landmark or barangay.`, "info");
  }
}

// Search Inputs & Autocomplete Binding
const searchInput = $("#q");
const searchBtn = $("#go");

if (searchInput) {
  searchInput.addEventListener("input", () => {
    const text = searchInput.value.trim();
    if (text.length >= 2) {
      const sugs = getSuggestions(text);
      renderSuggestions(sugs);
    } else {
      const sug = $("#search-suggestions");
      if (sug) sug.style.display = "none";
    }
  });

  searchInput.addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
      e.preventDefault();
      executeSearch(searchInput.value);
    } else if (e.key === "Escape") {
      const sug = $("#search-suggestions");
      if (sug) sug.style.display = "none";
    }
  });

  searchInput.addEventListener("search", () => {
    if (!searchInput.value.trim()) {
      executeSearch("");
    }
  });

  // Close suggestions on click outside
  document.addEventListener("click", (e) => {
    if (!e.target.closest(".search-input-wrap")) {
      const sug = $("#search-suggestions");
      if (sug) sug.style.display = "none";
    }
  });
}

if (searchBtn && searchInput) {
  searchBtn.onclick = () => executeSearch(searchInput.value);
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

// 1. Set default user location immediately so pin & navigation are ALWAYS available right away
setUserLocation(TODA.center[0], TODA.center[1], null, false, "default");

// 2. Query live GPS in background (smoothly moves pin to live GPS when granted)
detectLocation(false);

// 3. Load terminal markers and initial routes
load();

// 3. Ensure Leaflet recalculates on mobile viewport changes / orientation changes
window.addEventListener("resize", () => {
  if (map) map.invalidateSize();
});
setTimeout(() => {
  if (map) map.invalidateSize();
}, 300);
