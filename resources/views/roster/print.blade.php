<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Roster {{ $weekStart->format('d M Y') }}</title>
    <style>
        *{box-sizing:border-box;-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important}body{font-family:Arial,Helvetica,sans-serif;color:#111;margin:14px;background:#fff}.toolbar{display:flex;justify-content:flex-end;margin-bottom:8px}.print-button{border:0;border-radius:5px;background:#111827;color:#fff;padding:8px 18px;font-weight:700;cursor:pointer}.sheet{width:100%;border-collapse:collapse;table-layout:fixed;font-size:10px}.sheet th,.sheet td{border:1px solid #555;text-align:left;vertical-align:top}.title th{text-align:center;background:#fff;font-size:14px;font-weight:500;padding:3px;text-transform:uppercase}.days th{background:#e7c6e7!important;text-align:center;font-size:10px;padding:4px 2px}.day-cell{padding:0;background:#d9d9d9!important;height:440px}.day-content{min-height:440px;background:#d9d9d9!important}.group{break-inside:avoid}.group-title{background:#8fd14f!important;border-bottom:1px solid #555;padding:3px;font-weight:700;line-height:1.15}.weekend .group-title{background:#40cf66!important}.person{background:#fff!important;border-bottom:1px solid #aaa;min-height:18px;padding:3px}.person-note{font-weight:400;color:#333}.empty{padding:5px;color:#666;font-style:italic}.leave{margin-top:8px;break-inside:avoid}.leave-title{background:#78c4e1!important;border-top:1px solid #555;border-bottom:1px solid #555;padding:3px}.leave-person{background:#fff!important;border-bottom:1px solid #aaa;min-height:18px;padding:3px}.summary{display:flex;justify-content:space-between;gap:20px;margin-top:6px;font-size:9px;color:#555}@page{size:A4 landscape;margin:7mm}@media print{body{margin:0}.no-print{display:none}.sheet{font-size:9px}.day-cell,.day-content{height:178mm;min-height:178mm}}
    </style>
</head>
<body>
    <div class="toolbar no-print"><button class="print-button" onclick="window.print()">Print roster</button></div>
    <table class="sheet">
        <thead>
            <tr class="title"><th colspan="7">Rosters for week commencing {{ $weekStart->format('jS F Y') }}</th></tr>
            <tr class="days">
                @foreach($days as $day)<th>{{ $day->format('l, jS F Y') }}</th>@endforeach
            </tr>
        </thead>
        <tbody>
            <tr>
                @foreach($days as $day)
                    @php
                        $dayShifts = $shifts->filter(fn($shift) => $shift->shift_date->isSameDay($day));
                        $groups = $dayShifts->groupBy(fn($shift) => implode('|', [
                            $shift->role ?: 'Scheduled shift',
                            $shift->location ?: '',
                            $shift->start_time,
                            $shift->end_time,
                        ]));
                        $dayLeave = $leave->filter(fn($item) => $day->between($item->start_date, $item->end_date));
                    @endphp
                    <td class="day-cell {{ $day->isWeekend() ? 'weekend' : '' }}">
                        <div class="day-content">
                            @forelse($groups as $group)
                                @php $first = $group->first(); @endphp
                                <div class="group">
                                    <div class="group-title">
                                        {{ $first->role ?: 'Scheduled shift' }}
                                        @if($first->location) · {{ $first->location }} @endif
                                        · {{ \Carbon\Carbon::parse($first->start_time)->format('g:ia') }}–{{ \Carbon\Carbon::parse($first->end_time)->format('g:ia') }}
                                    </div>
                                    @foreach($group as $shift)
                                        <div class="person">{{ $shift->employee->name }}@if($shift->notes) <span class="person-note">({{ $shift->notes }})</span>@endif</div>
                                    @endforeach
                                </div>
                            @empty
                                <div class="empty">No rostered shifts</div>
                            @endforelse

                            @if($dayLeave->isNotEmpty())
                                @foreach($dayLeave->groupBy('leave_type') as $leaveType => $requests)
                                    <div class="leave">
                                        <div class="leave-title">{{ $leaveType }}</div>
                                        @foreach($requests as $request)<div class="leave-person">{{ $request->employee->name }}</div>@endforeach
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </td>
                @endforeach
            </tr>
        </tbody>
    </table>
    <div class="summary"><span>NOAHFACE SYNC · MANAGER ROSTER</span><span>Printed {{ now()->format('d M Y, g:i A') }}</span></div>
    <script>if(new URLSearchParams(location.search).has('autoprint'))window.print()</script>
</body>
</html>
