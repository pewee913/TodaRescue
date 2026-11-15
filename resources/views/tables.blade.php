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

            </div>
        </div>

        <div class="table-controls">
            <div class="table-search-wrap">
                <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8" />
                    <path d="m21 21-4.3-4.3" />
                </svg>
                <input type="text" id="tq" placeholder="Filter routes by barangay or landmark...">
            </div>
            <span class="sm route-count">{{ count($rows) }} routes listed</span>
        </div>

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
                            <td class="route-name">
                                <b>{{ $r->exit_point }} → {{ $r->destination }}</b>
                                @if ($r->route)
                                    <div class="sm">{{ $r->route }}</div>
                                @endif
                            </td>
                            <td class="distance">{{ $r->km }} km</td>
                            <td class="f">₱{{ number_format($r->fare, 2) }}</td>
                            <td class="d">₱{{ number_format($r->discounted_fare, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <p class="sm" style="margin-top:14px">
            Minimum fare ₱20.00 (₱16.00 with 20% discount) plus ₱2.00 per additional km. Kilometers are measured from the
            exit point to the end point of the barangay.<br>
            Concerns? Landline (049) 503-2200 · spctmo@gmail.com · Facebook: Ctmo San Pablo
        </p>
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
    </script>
@endsection
