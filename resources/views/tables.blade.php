@extends('layouts.app')
@section('title', 'Fare Tables')
@section('content')
<section class="page"><div class="top"><div><h2>Fare Tables</h2><p>Browse the fare for every route, including discounted and special trip rates.</p></div><button class="btn w" onclick="print()">Download PDF</button></div>
<input type="text" id="tq" placeholder="Search routes" style="max-width:360px;margin-bottom:18px">
<table><thead><tr><th>ROUTE</th><th>DISTANCE</th><th>REGULAR FARE</th><th>STUDENT / SENIOR / PWD</th><th>SPECIAL TRIP</th></tr></thead><tbody id="tb">
@foreach($rows as $r)<tr><td>{{ $r['route'] }}</td><td>{{ number_format($r['km'], 1) }} km</td><td class="f">₱{{ number_format($r['regular'], 2) }}</td><td class="d">₱{{ number_format($r['discounted'], 2) }}</td><td>₱{{ number_format($r['special'], 2) }}</td></tr>@endforeach
</tbody></table></section>
<script>document.getElementById('tq').oninput=e=>document.querySelectorAll('#tb tr').forEach(r=>r.hidden=!r.textContent.toLowerCase().includes(e.target.value.toLowerCase()))</script>
@endsection
