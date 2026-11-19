@extends('layouts.admin')
@section('title', 'Admin - TODA Map')
@section('content')
<section class="page admin-map">
    <div class="admin-map-toolbar">
        <h2 class="admin-title">Manage TODA Pinpoints</h2>
        <p class="admin-subtitle">Click the map to place a new TODA terminal, or click a marker to edit/move it. Changes save to the database and show up on the user side immediately.</p>
        <div class="admin-toolbar-actions">
            <a href="{{ route('admin.logout') }}" class="admin-logout-btn" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2h-4"/><polyline points="9 12 11 14 15 10"/></svg>
                Logout
            </a>
            <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display:none">@csrf @method('POST')</form>
        </div>
    </div>

    <div class="admin-map-layout">
        <div class="admin-sidebar">
            <div class="admin-form-card">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                    <h3 id="form-title" style="margin:0;">Record New TODA</h3>
                    <button type="button" class="btn s outline" onclick="prepareNewToda()">+ New Terminal</button>
                </div>
                <form id="toda-form" onsubmit="return saveToda(event)">
                    <input type="hidden" id="toda-id" name="id">
                    <input type="hidden" id="toda-method" name="_method" value="POST">
                    <div class="field">
                        <label>TODA Terminal Name <span class="req">*</span></label>
                        <input type="text" id="toda-name" name="name" required placeholder="e.g. Santiago 1 TODA">
                    </div>
                    <div class="field">
                        <label>Base / Serves Area <span class="req">*</span></label>
                        <input type="text" id="toda-serves" name="serves" required placeholder="e.g. Santiago 1, San Pablo City">
                    </div>
                    <div class="field">
                        <label style="display:flex;justify-content:space-between;align-items:center;">
                            <span>Barangays Passed Along Route</span>
                            <span style="font-size:11px;background:#e0f2fe;color:#0369a1;padding:1px 6px;border-radius:4px;font-weight:600">Route Cards</span>
                        </label>
                        <input type="text" id="toda-barangays-passed" name="barangays_passed" placeholder="e.g. Brgy. San Gabriel, San Miguel, San Bartolome, Santiago 1">
                        <small style="display:block;margin-top:3px;font-size:11.5px;color:var(--mu)">Enter barangays separated by commas. Each will display as its own route card on the user map.</small>
                    </div>
                    <div class="field">
                        <label style="display:flex;justify-content:space-between;align-items:center;">
                            <span>Places & Landmarks Along Route</span>
                            <span style="font-size:11px;background:#ffedd5;color:#c2410c;padding:1px 6px;border-radius:4px;font-weight:600">Via Areas</span>
                        </label>
                        <input type="text" id="toda-landmarks" name="landmarks" placeholder="e.g. Lumang Libingan, Prosperity Memorial">
                        <small style="display:block;margin-top:3px;font-size:11.5px;color:var(--mu)">Enter landmarks or places separated by commas.</small>
                    </div>
                    <div class="field" style="display:flex;align-items:center;gap:10px;margin-top:10px">
                        <input type="checkbox" id="toda-active" name="is_active" value="1" checked>
                        <label for="toda-active" style="margin:0;font-weight:500">Active (shows on user map)</label>
                    </div>
                    <div class="field sm coords" style="font-size:12px;color:var(--muted);margin-top:10px">
                        <b>Terminal Pinpoint:</b> <span id="toda-coords">—</span>
                        <div style="font-size:11px;color:var(--mu);margin-top:2px">Click anywhere on the map to place or reposition this terminal's pin.</div>
                    </div>

                    <!-- Additional Route Pins (Kung saan dadaan) Editor -->
                    <div class="admin-waypoints-section">
                        <div class="admin-waypoints-header">
                            <div>
                                <span class="admin-waypoints-title">Route Pins (Kung Saan Dadaan)</span>
                                <div style="font-size:11px;color:var(--mu);margin-top:2px;">Lagyan ng pins ang mapa kung saan dadaan ang tricycle, hanggang sa pinakadulong bababaan.</div>
                            </div>
                            <span id="admin-wp-count" class="route-count-badge" style="background:#e0f2fe;color:#0369a1;">0 pins</span>
                        </div>
                        <div id="admin-wp-list" class="admin-wp-list"></div>
                        <div class="admin-wp-actions">
                            <button type="button" class="btn s outline" id="btn-toggle-add-pin" onclick="toggleAddPinMode()" style="flex:1;">
                                <span id="add-pin-btn-text">📍 Click Map to Add Route Pin</span>
                            </button>
                            <button type="button" class="btn s outline" onclick="generateWaypointsFromInputs()" title="Auto-place pins from entered barangays & landmarks">
                                ⚡ Auto-add Pins
                            </button>
                        </div>
                    </div>

                    <div class="form-actions" style="margin-top:14px">
                        <button type="submit" id="form-submit" class="btn btn-full">Save TODA Terminal</button>
                        <button type="button" class="btn outline" onclick="clearForm()">Cancel</button>
                        <button type="button" id="form-delete" class="btn danger" onclick="deleteToda()" style="display:none;">Delete</button>
                    </div>
                </form>
            </div>
            <div class="admin-list">
                <h3>All Terminals <span id="toda-count" class="sm">(0)</span></h3>
                <div id="toda-list" class="toda-list"></div>
            </div>
        </div>
        <div class="admin-map-wrap" style="min-height:650px;height:100%;">
            <div id="admin-map" style="width:100%;height:100%;min-height:650px;"></div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
const adminMap = L.map('admin-map').setView(TODA.center, 15);
tile(adminMap);
setTimeout(() => { if (adminMap) adminMap.invalidateSize(); }, 250);

let markers = [], editMarker = null, editingId = null, todas = [];
let editingWaypoints = [];
let adminRoutePolyline = null;
let adminRouteDecorator = null;
let adminWaypointMarkers = [];
let isAddPinMode = false;
const form = $('#toda-form');

function parseRouteList(text) {
  if (!text || typeof text !== "string") return [];
  return text.split(/[,;\n]/).map(s => s.trim()).filter(Boolean);
}

function getTodaWaypoints(t) {
  let pins = [];
  if (t.route_waypoints && Array.isArray(t.route_waypoints)) {
    pins = JSON.parse(JSON.stringify(t.route_waypoints));
  } else if (typeof t.route_waypoints === "string") {
    try {
      const parsed = JSON.parse(t.route_waypoints);
      if (Array.isArray(parsed)) pins = parsed;
    } catch (e) {}
  }

  // Filter out any redundant starting/origin pin (the TODA marker itself is the start)
  pins = pins.filter(p => p.type !== 'origin' && !/\b(origin|terminal pickup)\b/i.test(p.name || ''));

  if (pins.length > 0) return pins;

  // Fallback: build pins from entered barangays and landmarks ending at serves
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

function toggleAddPinMode() {
  isAddPinMode = !isAddPinMode;
  const btn = $('#btn-toggle-add-pin');
  const txt = $('#add-pin-btn-text');
  if (isAddPinMode) {
    btn.classList.add('on');
    btn.style.background = '#ff5f15';
    btn.style.color = '#ffffff';
    txt.textContent = '🎯 Click map to place route pin (Tap to stop)';
    adminMap.getContainer().style.cursor = 'crosshair';
  } else {
    btn.classList.remove('on');
    btn.style.background = '';
    btn.style.color = '';
    txt.textContent = '📍 Click Map to Add Route Pin';
    adminMap.getContainer().style.cursor = '';
  }
}

function clearAdminRoute() {
  if (adminRoutePolyline) {
    adminRoutePolyline.remove();
    adminRoutePolyline = null;
  }
  if (adminRouteDecorator) {
    adminRouteDecorator.remove();
    adminRouteDecorator = null;
  }
  adminWaypointMarkers.forEach(m => m.remove());
  adminWaypointMarkers = [];
}

async function fetchRoadRoute(waypoints) {
  if (!waypoints || waypoints.length < 2) return null;
  const coordsStr = waypoints.map(w => `${w.lng},${w.lat}`).join(";");
  const cacheKey = "admin_osrm|" + coordsStr;
  if (cache[cacheKey]) return cache[cacheKey];

  try {
    const url = `https://router.project-osrm.org/route/v1/driving/${coordsStr}?overview=full&geometries=geojson`;
    const res = await fetch(url);
    const data = await res.json();
    if (data.code === "Ok" && data.routes && data.routes.length > 0) {
      const r = data.routes[0];
      const latlngs = r.geometry.coordinates.map(c => [c[1], c[0]]);
      return cache[cacheKey] = { latlngs, distance: r.distance, duration: r.duration };
    }
  } catch (err) {
    console.warn("OSRM routing request failed:", err);
  }

  return { latlngs: waypoints.map(w => [w.lat, w.lng]), distance: 0, duration: 0 };
}

async function redrawAdminRoute() {
  clearAdminRoute();
  renderAdminWaypoints();

  if (!editingWaypoints || editingWaypoints.length === 0) {
    return;
  }

  // Draw draggable markers for each route pin (no starting point marker needed; editMarker is the TODA)
  editingWaypoints.forEach((wp, idx) => {
    const isFarthest = idx === editingWaypoints.length - 1;
    const isBrgy = /brgy|barangay/i.test(wp.name);
    let iconHtml = '';
    let className = 'route-point-marker';

    if (isFarthest) {
      className += ' destination';
      iconHtml = `<div class="route-marker-pin destination" title="Farthest Pin: ${escapeHtml(wp.name)}">🏁</div>`;
    } else if (isBrgy) {
      className += ' passed-brgy';
      iconHtml = `<div class="route-marker-pin passed-brgy" title="Dadaan sa: ${escapeHtml(wp.name)}">🏘️</div>`;
    } else {
      className += ' passed-landmark';
      iconHtml = `<div class="route-marker-pin passed-landmark" title="Dadaan sa: ${escapeHtml(wp.name)}">📍</div>`;
    }

    const markerIcon = L.divIcon({
      className: className,
      html: iconHtml,
      iconSize: [28, 28],
      iconAnchor: [14, 14]
    });

    const m = L.marker([wp.lat, wp.lng], {
      draggable: true,
      icon: markerIcon,
      zIndexOffset: 950
    }).addTo(adminMap);

    const label = isFarthest ? `🏁 End Pin: ${escapeHtml(wp.name)}` : `📍 Pin #${idx + 1}: ${escapeHtml(wp.name)}`;
    m.bindTooltip(`<b>${label}</b><br><span style="font-size:11px">Drag to adjust pin location</span>`, { direction: 'top' });

    m.on('dragend', () => {
      const pos = m.getLatLng();
      wp.lat = +pos.lat.toFixed(6);
      wp.lng = +pos.lng.toFixed(6);
      renderAdminWaypoints();
      redrawAdminRoute();
    });

    adminWaypointMarkers.push(m);
  });

  // Calculate road route starting from the TODA terminal pin through all additional pins
  const startLatLng = editMarker ? editMarker.getLatLng() : adminMap.getCenter();
  const allPoints = [
    { name: "TODA Terminal", lat: +startLatLng.lat.toFixed(6), lng: +startLatLng.lng.toFixed(6) },
    ...editingWaypoints
  ];

  const routeData = await fetchRoadRoute(allPoints);
  if (!routeData || !routeData.latlngs || routeData.latlngs.length < 2) {
    return;
  }

  adminRoutePolyline = L.polyline(routeData.latlngs, {
    color: "#ff5f15",
    weight: 5,
    opacity: 0.85,
    lineJoin: "round",
    lineCap: "round"
  }).addTo(adminMap);

  const Decorator = L.polylineDecorator || (typeof L.PolylineDecorator === 'function' ? (p, o) => new L.PolylineDecorator(p, o) : null);
  if (typeof Decorator === 'function' && L.Symbol && L.Symbol.arrowHead) {
    try {
      adminRouteDecorator = Decorator(adminRoutePolyline, {
        patterns: [
          {
            offset: 25,
            repeat: 55,
            symbol: L.Symbol.arrowHead({
              pixelSize: 10,
              headAngle: 50,
              polygon: false,
              pathOptions: { stroke: true, weight: 2.5, color: '#ffffff', opacity: 0.95 }
            })
          },
          {
            offset: 25,
            repeat: 55,
            symbol: L.Symbol.arrowHead({
              pixelSize: 10,
              headAngle: 50,
              polygon: false,
              pathOptions: { stroke: true, weight: 1.5, color: '#d9480f', opacity: 1 }
            })
          }
        ]
      }).addTo(adminMap);
    } catch (e) {}
  }
}

function renderAdminWaypoints() {
  const container = $('#admin-wp-list');
  const countBadge = $('#admin-wp-count');
  if (countBadge) countBadge.textContent = `${editingWaypoints.length} pin${editingWaypoints.length === 1 ? '' : 's'}`;
  if (!container) return;

  if (!editingWaypoints.length) {
    container.innerHTML = '<div class="sm" style="padding:6px;color:var(--mu);font-size:12px">No route pins yet. Click "📍 Click Map to Add Route Pin" or "⚡ Auto-add Pins" to place pins showing where the tricycle passes.</div>';
    return;
  }

  container.innerHTML = editingWaypoints.map((wp, idx) => {
    const isFarthest = idx === editingWaypoints.length - 1;
    const isBrgy = /brgy|barangay/i.test(wp.name);
    const badgeType = isFarthest ? 'destination' : (isBrgy ? 'barangay' : 'landmark');
    const badgeText = isFarthest ? '🏁' : (idx + 1);

    return `
      <div class="admin-wp-item">
        <span class="admin-wp-badge ${badgeType}">${badgeText}</span>
        <input type="text" class="admin-wp-input" value="${escapeHtml(wp.name)}" oninput="editingWaypoints[${idx}].name = this.value" placeholder="Landmark o Barangay (kung saan dadaan)">
        <span class="admin-wp-coords">${wp.lat.toFixed(4)}, ${wp.lng.toFixed(4)}</span>
        <button type="button" class="admin-wp-del" onclick="deleteWaypoint(${idx})" title="Remove pin">✕</button>
      </div>
    `;
  }).join('');
}

window.deleteWaypoint = function(idx) {
  editingWaypoints.splice(idx, 1);
  redrawAdminRoute();
};

window.generateWaypointsFromInputs = function() {
  const serves = $('#toda-serves').value.trim();
  const brgysText = $('#toda-barangays-passed').value.trim();
  const landmarksText = $('#toda-landmarks').value.trim();

  const terminalCoords = editMarker ? editMarker.getLatLng() : adminMap.getCenter();
  const brgys = parseRouteList(brgysText);
  const landmarks = parseRouteList(landmarksText);
  const destName = serves ? serves.split(',')[0].trim() : 'Farthest Destination';

  const combined = [...brgys, ...landmarks];
  if (!combined.includes(destName)) combined.push(destName);

  if (combined.length === 0) {
    alert('Please enter some barangays, landmarks, or base area first.');
    return;
  }

  // Spread pins outward from the TODA terminal towards destination
  const startLat = +terminalCoords.lat, startLng = +terminalCoords.lng;
  const endLat = +(startLat - 0.04).toFixed(6);
  const endLng = +(startLng - 0.04).toFixed(6);

  editingWaypoints = combined.map((name, i) => {
    const ratio = (i + 1) / combined.length;
    return {
      name: name,
      lat: +(startLat + (endLat - startLat) * ratio).toFixed(6),
      lng: +(startLng + (endLng - startLng) * ratio).toFixed(6)
    };
  });

  redrawAdminRoute();
  if (adminRoutePolyline) {
    adminMap.fitBounds(adminRoutePolyline.getBounds(), { padding: [40, 40] });
  }
};

// Admin API calls live under /admin/... (NOT /api/...), so use a direct fetch helper.
const adminApi = (path, opt) => fetch(path, {
  headers: { Accept: "application/json", "Content-Type": "application/json", "X-CSRF-TOKEN": TODA.csrf },
  ...opt,
}).then(async r => {
  const body = await r.json().catch(() => ({}));
  if (!r.ok) throw new Error(`HTTP ${r.status}: ${body.message || JSON.stringify(body).slice(0, 200)}`);
  return body;
});

async function load() {
  try { todas = await adminApi('/admin/todas'); } catch (e) { todas = []; }
  drawList();
  drawMarkers();
  $('#toda-count').textContent = `(${todas.length})`;
}

function drawList() {
  $('#toda-list').innerHTML = todas.map(t => `
    <div class="item${editingId === t.id ? ' on' : ''}">
      <div class="item-main" onclick="editToda(${t.id})">
        <h4>${escapeHtml(t.name)} <span class="tag ${t.is_active ? '' : 'c'}">${t.is_active ? 'Active' : 'Closed'}</span></h4>
        <div class="sm"><b>Base:</b> ${escapeHtml(t.serves)}</div>
        ${t.barangays_passed ? `<div class="sm">🏘️ <b>Passes:</b> ${escapeHtml(t.barangays_passed)}</div>` : ''}
        ${t.landmarks ? `<div class="sm" style="color:var(--primary)">📍 <b>Via:</b> ${escapeHtml(t.landmarks)}</div>` : ''}
        <div class="sm" style="margin-top:2px;font-size:11.5px;color:var(--mu)">📍 Pin: ${t.lat.toFixed(4)}, ${t.lng.toFixed(4)}</div>
      </div>
      <div class="item-actions">
        <button type="button" class="btn s" onclick="event.stopPropagation(); editToda(${t.id})" title="Edit">Edit</button>
        <button type="button" class="btn s danger" onclick="event.stopPropagation(); startDelete(${t.id})" title="Delete">Delete</button>
      </div>
    </div>
  `).join('') || '<div class="sm">No TODA terminals yet. Click the map or "+ New Terminal" to record one.</div>';
}

function drawMarkers() {
  markers.forEach(m => m.remove());
  markers = [];
  editMarker && editMarker.remove();
  editMarker = null;

  todas.forEach(t => {
    const isEditing = editingId === t.id;
    const m = L.marker([t.lat, t.lng], { 
      draggable: isEditing,
      icon: L.divIcon({ 
        className: '', 
        html: `<div class="pin ${isEditing ? 'edit' : ''}">${t.name[0] || 'T'}</div>`, 
        iconSize: [32, 32], 
        iconAnchor: [16, 16] 
      }) 
    })
    .addTo(adminMap)
    .bindPopup(`
      <b style="font-size:14px">${escapeHtml(t.name)}</b><br>
      <div class="sm" style="margin-top:2px"><b>Base:</b> ${escapeHtml(t.serves)}</div>
      ${t.barangays_passed ? `<div class="sm">🏘️ <b>Passes:</b> ${escapeHtml(t.barangays_passed)}</div>` : ''}
      ${t.landmarks ? `<div class="sm" style="color:var(--primary)">📍 <b>Via:</b> ${escapeHtml(t.landmarks)}</div>` : ''}
      <div style="margin-top:6px">
        <button class="btn s" style="width:100%" onclick="editToda(${t.id})">Edit Terminal</button>
      </div>
    `)
    .on('click', () => editToda(t.id));

    if (isEditing) {
      editMarker = m;
      m.on('dragend', () => {
        const p = m.getLatLng();
        $('#toda-coords').textContent = `${p.lat.toFixed(6)}, ${p.lng.toFixed(6)}`;
        redrawAdminRoute();
      });
    }

    markers.push(m);
  });
}

window.prepareNewToda = function () {
  clearForm();
  const center = adminMap.getCenter();
  $('#toda-coords').textContent = `${center.lat.toFixed(6)}, ${center.lng.toFixed(6)}`;
  editMarker && editMarker.remove();
  editMarker = L.marker(center, { 
    draggable: true, 
    icon: L.divIcon({ className: '', html: '<div class="pin new">+</div>', iconSize: [32, 32], iconAnchor: [16, 16] }) 
  }).addTo(adminMap);
  editMarker.on('dragend', () => {
    const p = editMarker.getLatLng();
    $('#toda-coords').textContent = `${p.lat.toFixed(6)}, ${p.lng.toFixed(6)}`;
    redrawAdminRoute();
  });
  $('#toda-name').focus();
};

adminMap.on('click', e => {
  const { lat, lng } = e.latlng;

  // If in Add Route Pin Mode:
  if (isAddPinMode) {
    const defaultName = prompt('Pangalan ng landmark o barangay (kung saan dadaan):', `Pin #${editingWaypoints.length + 1}`);
    if (defaultName && defaultName.trim()) {
      const name = defaultName.trim();
      const newWp = {
        name: name,
        lat: +lat.toFixed(6),
        lng: +lng.toFixed(6)
      };
      editingWaypoints.push(newWp);
      redrawAdminRoute();
    }
    return;
  }

  if (!editingId) {
    $('#toda-id').value = '';
    $('#toda-method').value = 'POST';
    $('#form-title').textContent = 'Record New TODA';
    $('#form-submit').textContent = 'Save TODA Terminal';
    $('#form-delete').style.display = 'none';
  }
  $('#toda-coords').textContent = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;

  editMarker && editMarker.remove();
  editMarker = L.marker([lat, lng], { draggable: true, icon: L.divIcon({ className: '', html: '<div class="pin new"></div>', iconSize: [32, 32], iconAnchor: [16, 16] }) }).addTo(adminMap);
  editMarker.on('dragend', () => {
    const p = editMarker.getLatLng();
    $('#toda-coords').textContent = `${p.lat.toFixed(6)}, ${p.lng.toFixed(6)}`;
    redrawAdminRoute();
  });
  redrawAdminRoute();
});

window.editToda = function (id) {
  const t = todas.find(x => x.id === id);
  if (!t) return;
  editingId = t.id;
  $('#toda-id').value = t.id;
  $('#toda-method').value = 'PUT';
  $('#form-title').textContent = `Editing: ${escapeHtml(t.name)}`;
  $('#form-submit').textContent = 'Save Changes';
  $('#form-delete').style.display = 'inline-block';
  $('#toda-name').value = t.name;
  $('#toda-serves').value = t.serves;
  $('#toda-barangays-passed').value = t.barangays_passed || '';
  $('#toda-landmarks').value = t.landmarks || '';
  $('#toda-active').checked = t.is_active;
  $('#toda-coords').textContent = `${t.lat.toFixed(6)}, ${t.lng.toFixed(6)}`;

  drawList();
  drawMarkers();

  // Load and visualize additional route pins
  editingWaypoints = getTodaWaypoints(t);
  redrawAdminRoute();

  if (adminRoutePolyline) {
    adminMap.fitBounds(adminRoutePolyline.getBounds(), { padding: [40, 40] });
  } else {
    adminMap.setView([t.lat, t.lng], 15);
  }
};

window.clearForm = function () {
  editingId = null;
  $('#toda-id').value = '';
  $('#toda-method').value = 'POST';
  $('#form-title').textContent = 'Record New TODA';
  $('#form-submit').textContent = 'Save TODA Terminal';
  $('#form-delete').style.display = 'none';
  $('#toda-name').value = '';
  $('#toda-serves').value = '';
  $('#toda-barangays-passed').value = '';
  $('#toda-landmarks').value = '';
  $('#toda-active').checked = true;
  $('#toda-coords').textContent = '—';
  editMarker && (editMarker.remove(), editMarker = null);

  editingWaypoints = [];
  clearAdminRoute();
  renderAdminWaypoints();

  if (isAddPinMode) toggleAddPinMode();
  drawList();
};

window.saveToda = function (ev) {
  ev.preventDefault();
  const latLng = editMarker ? editMarker.getLatLng() : adminMap.getCenter();
  const payload = {
    name: $('#toda-name').value.trim(),
    lat: latLng.lat,
    lng: latLng.lng,
    serves: $('#toda-serves').value.trim(),
    barangays_passed: $('#toda-barangays-passed').value.trim(),
    landmarks: $('#toda-landmarks').value.trim(),
    route_waypoints: editingWaypoints,
    is_active: $('#toda-active').checked,
  };
  if (!payload.name || !payload.serves) {
    alert('Please fill in the terminal name and base area.');
    return false;
  }

  const isNew = $('#toda-method').value === 'POST';
  const path = isNew ? '/admin/todas' : `/admin/todas/${editingId}`;
  const method = isNew ? 'POST' : 'PUT';

  adminApi(path, { method, body: JSON.stringify(payload) })
    .then(() => { load(); clearForm(); adminMap.setView([latLng.lat, latLng.lng], 16); })
    .catch(err => alert('Failed to save TODA: ' + err.message));
  return false;
};

window.startDelete = function (id) {
  const t = todas.find(x => x.id === id);
  if (!t || !confirm(`Delete “${escapeHtml(t.name)}” and its pinpoint?`)) return;
  editingId = id;
  deleteToda();
};

window.deleteToda = function () {
  if (!editingId) return;
  adminApi(`/admin/todas/${editingId}`, { method: 'DELETE' })
    .then(() => { load(); clearForm(); })
    .catch(err => alert('Failed to delete: ' + err.message));
};

load();
</script>
@endpush