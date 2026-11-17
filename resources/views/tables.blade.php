@extends('layouts.app')
@section('title', 'Fare Tables')
@section('content')
    <section class="page">
        <div class="top">
            <div>
                <h2>Fare Tables</h2>
                <p>Browse official San Pablo City tricycle fares (City Ordinance 72, series of 2026), including discounted
                    rates.</p>
            </div>
            <div class="top-actions">
                <button class="toggle-btn" id="toggle-table" aria-label="Toggle route table"><svg class="t-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><polyline points="6 9 12 15 18 9"/></svg></button>
            </div>
        </div>

        <div class="table-controls">
            <div class="table-search-wrap">
                <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8" />
                    <path d="m21 21-4.3-4.3" />
                </svg>
                <input type="search" id="tq" placeholder="Filter routes by barangay or landmark..." autocomplete="off">
            </div>
            <span class="sm route-count">{{ count($rows) }} routes listed</span>
        </div>

        <div class="collapse-section" id="route-table">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Route</th>
                            <th>Distance</th>
                            <th>Regular Fare</th>
                            <th>Student / Senior / PWD</th>
                        </tr>
                    </thead>
                    <tbody id="tb">
                        @foreach ($rows as $r)
                            <tr>
                                <td class="route-name" data-label="Route">
                                    <b class="route-title">{{ $r->exit_point }} → {{ $r->destination }}</b>
                                    @if ($r->route)
                                        <div class="sm route-via">{{ $r->route }}</div>
                                    @endif
                                </td>
                                <td class="distance" data-label="Distance">
                                    <span class="dist-badge">{{ $r->km }} km</span>
                                </td>
                                <td class="f" data-label="Regular Fare">₱{{ number_format($r->fare, 2) }}</td>
                                <td class="d" data-label="Student / Senior / PWD">₱{{ number_format($r->discounted_fare, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card fare-policy-card" style="margin-top:16px;">
            <p class="sm" style="margin:0">
                <b>Official Fare Policy:</b> Minimum fare is ₱20.00 (₱16.00 with 20% discount) plus ₱2.00 per additional kilometer. Distances are officially measured from the exit point to the end point of the barangay.<br>
                <b>Inquiries & Concerns:</b> Landline <a href="tel:0495032200" style="color:var(--primary);font-weight:700">(049) 503-2200</a> · spctmo@gmail.com · Facebook: Ctmo San Pablo
            </p>
        </div>
    </section>

    <script>
        document.getElementById('tq').oninput = function(e) {
            const q = e.target.value.toLowerCase();
            let visible = 0;
            document.querySelectorAll('#tb tr').forEach(r => {
                const match = r.textContent.toLowerCase().includes(q);
                r.hidden = !match;
                if (match) visible++;
            });
            const counter = document.querySelector('.route-count');
            if (counter) {
                counter.textContent = `${visible} route${visible === 1 ? '' : 's'} found`;
            }
        };
        (function(){
            const btn = document.getElementById('toggle-table'), sec = document.getElementById('route-table');
            const open = localStorage.getItem('route-table') !== 'closed';
            if (!open){ sec.classList.add('collapsed'); btn.classList.add('collapsed'); }
            btn.onclick = function(){
                const now = sec.classList.toggle('collapsed');
                btn.classList.toggle('collapsed');
                localStorage.setItem('route-table', now ? 'closed' : 'open');
            };
        })();
    </script>
@endsection
