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
                <h3 id="form-title">Add New TODA</h3>
                <form id="toda-form" onsubmit="return saveToda(event)">
                    <input type="hidden" id="toda-id" name="id">
                    <input type="hidden" id="toda-method" name="_method" value="POST">
                    <div class="field">
                        <label>Name <span class="req">*</span></label>
                        <input type="text" id="toda-name" name="name" required placeholder="e.g. Poblacion TODA">
                    </div>
                    <div class="field">
                        <label>Serves <span class="req">*</span></label>
                        <input type="text" id="toda-serves" name="serves" required placeholder="e.g. Poblacion, Zone 1">
                    </div>
                    <div class="field">
                        <label>Phone</label>
                        <input type="text" id="toda-phone" name="phone" placeholder="0900 000 0000">
                    </div>
                    <div class="field">
                        <label>Units</label>
                        <input type="number" id="toda-units" name="units" min="0" value="0">
                    </div>
                    <div class="field" style="display:flex;align-items:center;gap:10px;">
                        <input type="checkbox" id="toda-active" name="is_active" value="1" checked>
                        <label for="toda-active" style="margin:0;font-weight:500">Active (shows on user map)</label>
                    </div>
                    <div class="field sm coords" style="font-size:12px;color:var(--muted);">
                        <b>Position:</b> <span id="toda-coords">—</span>
                    </div>
                    <div class="form-actions">
                        <button type="submit" id="form-submit" class="btn btn-full">Save TODA</button>
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
        <div class="admin-map-wrap">
            <div id="admin-map"></div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
const adminMap = L.map('admin-map').setView(TODA.center, 15);
tile(adminMap);

let markers = [], editMarker = null, editingId = null, todas = [];
const form = $('#toda-form');

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
  try { todas = await adminApi('/admin/todas'); } catch (e) { todos = []; }
  drawList();
  drawMarkers();
  $('#toda-count').textContent = `(${todas.length})`;
}

function drawList() {
  $('#toda-list').innerHTML = todas.map(t => `
    <div class="item${editingId === t.id ? ' on' : ''}">
      <div class="item-main" onclick="editToda(${t.id})">
        <h4>${escapeHtml(t.name)} <span class="tag ${t.is_active ? '' : 'c'}">${t.is_active ? 'Active' : 'Closed'}</span></h4>
        <div class="sm">${escapeHtml(t.serves)}</div>
        <div class="sm">📍 ${t.lat.toFixed(4)}, ${t.lng.toFixed(4)} · 📞 ${t.phone || '—'}</div>
      </div>
      <div class="item-actions">
        <button type="button" class="btn s" onclick="event.stopPropagation(); editToda(${t.id})" title="Edit">Edit</button>
        <button type="button" class="btn s danger" onclick="event.stopPropagation(); startDelete(${t.id})" title="Delete">Delete</button>
      </div>
    </div>
  `).join('') || '<div class="sm">No TODA terminals yet. Click the map to add one.</div>';
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
    .bindPopup(`<b>${escapeHtml(t.name)}</b><br>${escapeHtml(t.serves)}`)
    .on('click', () => editToda(t.id));

    if (isEditing) {
      editMarker = m;
      m.on('dragend', () => {
        const p = m.getLatLng();
        $('#toda-coords').textContent = `${p.lat.toFixed(6)}, ${p.lng.toFixed(6)}`;
      });
    }

    markers.push(m);
  });
}

adminMap.on('click', e => {
  const { lat, lng } = e.latlng;
  $('#toda-id').value = '';
  $('#toda-method').value = 'POST';
  $('#form-title').textContent = 'Add New TODA';
  $('#form-submit').textContent = 'Save TODA';
  $('#form-delete').style.display = 'none';
  $('#toda-name').value = '';
  $('#toda-serves').value = '';
  $('#toda-phone').value = '';
  $('#toda-units').value = 0;
  $('#toda-active').checked = true;
  $('#toda-coords').textContent = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
  editingId = null;

  editMarker && editMarker.remove();
  editMarker = L.marker([lat, lng], { draggable: true, icon: L.divIcon({ className: '', html: '<div class="pin new"></div>', iconSize: [32, 32], iconAnchor: [16, 16] }) }).addTo(adminMap);
  editMarker.on('dragend', () => {
    const p = editMarker.getLatLng();
    $('#toda-coords').textContent = `${p.lat.toFixed(6)}, ${p.lng.toFixed(6)}`;
  });
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
  $('#toda-phone').value = t.phone || '';
  $('#toda-units').value = t.units ?? 0;
  $('#toda-active').checked = t.is_active;
  $('#toda-coords').textContent = `${t.lat.toFixed(6)}, ${t.lng.toFixed(6)}`;

  adminMap.setView([t.lat, t.lng], 16);
  drawList();
  drawMarkers();
};

window.clearForm = function () {
  editingId = null;
  $('#toda-id').value = '';
  $('#toda-method').value = 'POST';
  $('#form-title').textContent = 'Add New TODA';
  $('#form-submit').textContent = 'Save TODA';
  $('#form-delete').style.display = 'none';
  $('#toda-name').value = '';
  $('#toda-serves').value = '';
  $('#toda-phone').value = '';
  $('#toda-units').value = 0;
  $('#toda-active').checked = true;
  $('#toda-coords').textContent = '—';
  editMarker && (editMarker.remove(), editMarker = null);
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
    phone: $('#toda-phone').value.trim(),
    units: parseInt($('#toda-units').value) || 0,
    is_active: $('#toda-active').checked,
  };
  if (!payload.name || !payload.serves) {
    alert('Please fill in the name and area served.');
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

function escapeHtml(t) {
  return String(t).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);
}

load();
</script>
@endpush