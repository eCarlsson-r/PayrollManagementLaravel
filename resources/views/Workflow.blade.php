@extends('member')

@section('title', 'Payroll Workflow')

@php
    // Agent → accent colour for chat bubbles / avatars.
    $agentColors = [
        'Data Collector'      => '#2563eb',
        'Payroll Calculator'  => '#7c3aed',
        'Compliance Reviewer' => '#d97706',
        'Report Generator'    => '#059669',
    ];
    $statusLabels = [
        'idle' => 'No runs yet',
        'running' => 'Running',
        'awaiting_approval' => 'Awaiting approval',
        'completed' => 'Completed',
    ];
@endphp

@section('content')
<style>
    .wf-head { display:flex; align-items:center; gap:1rem; flex-wrap:wrap; margin-bottom:1.25rem; }
    .wf-head h2 { margin:0; font-weight:700; letter-spacing:-.02em; }
    .wf-badge { padding:.35rem .8rem; border-radius:999px; font-size:.8rem; font-weight:600; }
    .wf-badge.idle { background:#e5e7eb; color:#374151; }
    .wf-badge.running { background:#dbeafe; color:#1d4ed8; }
    .wf-badge.awaiting_approval { background:#fef3c7; color:#b45309; }
    .wf-badge.completed { background:#d1fae5; color:#047857; }
    .wf-grid { display:grid; grid-template-columns: 1.4fr 1fr; gap:1.25rem; }
    @media (max-width: 992px){ .wf-grid { grid-template-columns:1fr; } }

    .wf-panel { background:#fff; border:1px solid #eceef2; border-radius:18px; box-shadow:0 6px 24px rgba(17,24,39,.05); overflow:hidden; }
    .wf-panel-head { padding:1rem 1.25rem; border-bottom:1px solid #f1f2f5; font-weight:600; display:flex; align-items:center; gap:.5rem; }
    .wf-chat { height:62vh; overflow-y:auto; padding:1.25rem; display:flex; flex-direction:column; gap:1rem; background:#fafbfc; }

    .wf-msg { display:flex; gap:.75rem; }
    .wf-avatar { flex:0 0 38px; width:38px; height:38px; border-radius:12px; color:#fff; font-weight:700;
                 display:flex; align-items:center; justify-content:center; font-size:.85rem; }
    .wf-bubble { background:#fff; border:1px solid #eef0f3; border-radius:14px; padding:.7rem .9rem; max-width:100%; }
    .wf-bubble .wf-meta { display:flex; align-items:center; gap:.5rem; margin-bottom:.25rem; }
    .wf-bubble .wf-name { font-weight:600; font-size:.9rem; }
    .wf-bubble .wf-type { font-size:.65rem; text-transform:uppercase; letter-spacing:.05em; padding:.1rem .45rem; border-radius:6px; background:#f3f4f6; color:#6b7280; }
    .wf-bubble .wf-content { white-space:pre-wrap; word-break:break-word; font-size:.92rem; line-height:1.45; color:#1f2937; }
    .wf-bubble .wf-time { font-size:.7rem; color:#9ca3af; margin-top:.3rem; }
    .wf-empty { color:#9ca3af; text-align:center; padding:2rem; }

    .wf-flags { padding:1rem; display:flex; flex-direction:column; gap:.9rem; max-height:62vh; overflow-y:auto; }
    .wf-flag { border:1px solid #eef0f3; border-left:4px solid #d97706; border-radius:14px; padding:.9rem 1rem; }
    .wf-flag.decided-approved { border-left-color:#059669; opacity:.85; }
    .wf-flag.decided-rejected { border-left-color:#dc2626; opacity:.85; }
    .wf-flag-top { display:flex; justify-content:space-between; align-items:center; margin-bottom:.4rem; }
    .wf-flag-emp { font-weight:700; }
    .wf-sev { font-size:.68rem; text-transform:uppercase; padding:.12rem .5rem; border-radius:6px; background:#fee2e2; color:#b91c1c; font-weight:600; }
    .wf-flag-reason { font-size:.88rem; color:#374151; margin-bottom:.5rem; }
    .wf-flag-expl { font-size:.8rem; color:#6b7280; background:#f9fafb; border-radius:10px; padding:.5rem .65rem; margin-bottom:.6rem; white-space:pre-wrap; }
    .wf-actions { display:flex; gap:.5rem; }
    .wf-btn { flex:1; border:none; border-radius:10px; padding:.5rem; font-weight:600; cursor:pointer; font-size:.88rem; }
    .wf-btn-approve { background:#059669; color:#fff; }
    .wf-btn-reject { background:#fff; color:#dc2626; border:1px solid #fecaca; }
    .wf-btn:disabled { opacity:.5; cursor:default; }
    .wf-decision { font-weight:700; font-size:.85rem; }
    .wf-decision.approved { color:#059669; }
    .wf-decision.rejected { color:#dc2626; }
</style>

<div class="wf-head">
    <h2>Payroll Workflow</h2>
    <span id="wf-status" class="wf-badge {{ $status }}">{{ $statusLabels[$status] ?? $status }}</span>
    <div class="ms-auto d-flex align-items-center gap-2 flex-wrap">
        <form method="GET" action="/workflow" class="d-flex align-items-center gap-2">
            <label class="text-muted small mb-0">Period</label>
            <select name="period" class="form-select form-select-sm" onchange="this.form.submit()">
                @forelse ($periods as $p)
                    <option value="{{ $p }}" @selected($p === $period)>{{ $p }}</option>
                @empty
                    <option>{{ $period ?? '—' }}</option>
                @endforelse
            </select>
        </form>

        {{-- Run Payroll trigger button (Admin / Manager) --}}
        <div class="d-flex align-items-center gap-2">
            <input type="month" id="trigger-period" class="form-control form-control-sm"
                   value="{{ now()->format('Y-m') }}" style="width:150px">
            <button id="trigger-btn" class="btn btn-sm btn-primary d-flex align-items-center gap-1"
                    onclick="triggerPipeline()">
                <i class="fa fa-play"></i> Run Payroll
            </button>
        </div>
        <span id="trigger-toast" class="small" style="display:none"></span>
    </div>
</div>

<div class="wf-grid">
    <!-- Live Band chat log -->
    <div class="wf-panel">
        <div class="wf-panel-head"><i class="fa fa-comments"></i> Band agent chat log</div>
        <div id="wf-chat" class="wf-chat">
            @forelse ($messages as $m)
                @php $color = $agentColors[$m->agent_name] ?? '#64748b'; @endphp
                <div class="wf-msg">
                    <div class="wf-avatar" style="background:{{ $color }}">{{ strtoupper(substr($m->agent_name ?? '?', 0, 1)) }}</div>
                    <div class="wf-bubble">
                        <div class="wf-meta">
                            <span class="wf-name" style="color:{{ $color }}">{{ $m->agent_name ?? $m->sender_type }}</span>
                            <span class="wf-type">{{ $m->message_type }}</span>
                        </div>
                        <div class="wf-content">{{ $m->content }}</div>
                        <div class="wf-time">{{ optional($m->created_at)->format('H:i:s') }}</div>
                    </div>
                </div>
            @empty
                <div class="wf-empty">No messages yet. Trigger a run in the Band room.</div>
            @endforelse
        </div>
    </div>

    <!-- Flagged entries needing approval -->
    <div class="wf-panel">
        <div class="wf-panel-head"><i class="fa fa-flag"></i> Flagged entries</div>
        <div id="wf-flags" class="wf-flags">
            @forelse ($flags as $flag)
                <div class="wf-flag decided-{{ $flag->decision }}" id="wf-flag-{{ $flag->id }}" data-id="{{ $flag->id }}">
                    <div class="wf-flag-top">
                        <span class="wf-flag-emp">Employee {{ $flag->employee_id }}</span>
                        <span class="wf-sev">{{ $flag->severity }}</span>
                    </div>
                    <div class="wf-flag-reason">{{ $flag->reason }}</div>
                    @if (!empty($flag->data['explanation']))
                        <div class="wf-flag-expl">{{ $flag->data['explanation'] }}</div>
                    @endif
                    <div class="wf-flag-controls">
                        @if ($flag->decision === 'pending')
                            <div class="mb-2">
                                <label class="small text-muted mb-1">Net amount (Rp)</label>
                                <input type="number" class="form-control form-control-sm wf-net-input"
                                       value="{{ $flag->net_amount }}"
                                       data-original="{{ $flag->net_amount }}"
                                       min="0" step="1000">
                            </div>
                            <div class="wf-actions">
                                <button class="wf-btn wf-btn-approve" onclick="resolveFlag({{ $flag->id }}, 'approved', this)">✓ Approve</button>
                                <button class="wf-btn wf-btn-reject" onclick="resolveFlag({{ $flag->id }}, 'rejected', this)">✕ Reject</button>
                            </div>
                        @else
                            <span class="wf-decision {{ $flag->decision }}">
                                {{ $flag->decision === 'approved' ? '✓ Approved' : '✕ Rejected' }}
                                @if ($flag->decision === 'approved' && !empty($flag->data['corrected_amount']))
                                    <span class="text-muted small">(corrected: Rp {{ number_format($flag->data['corrected_amount']) }})</span>
                                @endif
                            </span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="wf-empty">No flagged entries.</div>
            @endforelse
        </div>
    </div>
</div>

<script>
    const WF_COLORS = @json($agentColors);
    const WF_PERIOD = @json($period);
    const csrf = document.querySelector('meta[name=csrf-token]').getAttribute('content');

    function escapeHtml(s){ const d=document.createElement('div'); d.textContent=s??''; return d.innerHTML; }

    // --- approve / reject (with optional inline net-amount correction) ---
    async function resolveFlag(id, decision, btn){
        const card = document.getElementById('wf-flag-' + id);
        const input = card?.querySelector('.wf-net-input');
        const original = input ? parseFloat(input.dataset.original) : null;
        const entered  = input ? parseFloat(input.value) : null;
        const corrected = (entered !== null && !isNaN(entered) && entered !== original) ? entered : null;

        card?.querySelectorAll('button').forEach(b => b.disabled = true);
        const body = { decision };
        if (corrected !== null) body.corrected_amount = corrected;

        try {
            const res = await fetch('/workflow/flag/' + id, {
                method: 'POST',
                headers: { 'Content-Type':'application/json', 'X-CSRF-Token': csrf, 'Accept':'application/json' },
                body: JSON.stringify(body),
            });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();
            applyDecision(id, decision, data.corrected_amount);
        } catch (e) {
            card?.querySelectorAll('button').forEach(b => b.disabled = false);
            alert('Could not save decision: ' + e.message);
        }
    }

    function applyDecision(id, decision, correctedAmount){
        const card = document.getElementById('wf-flag-' + id);
        if (!card) return;
        card.classList.remove('decided-pending');
        card.classList.add('decided-' + decision);
        const controls = card.querySelector('.wf-flag-controls');
        let label = decision === 'approved' ? '✓ Approved' : '✕ Rejected';
        if (decision === 'approved' && correctedAmount) {
            label += ' <span class="text-muted small">(corrected: Rp ' + Number(correctedAmount).toLocaleString() + ')</span>';
        }
        if (controls) controls.innerHTML = '<span class="wf-decision ' + decision + '">' + label + '</span>';
    }

    // --- new flag raised by Agent 3 -> add a card live ---
    function addFlagCard(e){
        if (WF_PERIOD && e.period && e.period !== WF_PERIOD) return;
        if (document.getElementById('wf-flag-' + e.id)) return; // already shown
        const flags = document.getElementById('wf-flags');
        const empty = flags.querySelector('.wf-empty'); if (empty) empty.remove();
        const name = escapeHtml(e.employee_name || ('Employee ' + e.employee_id));
        const expl = e.explanation
            ? '<div class="wf-flag-expl">' + escapeHtml(e.explanation) + '</div>' : '';
        const net = e.net_amount ?? 0;
        const html =
            '<div class="wf-flag decided-pending" id="wf-flag-' + e.id + '" data-id="' + e.id + '">' +
              '<div class="wf-flag-top"><span class="wf-flag-emp">' + name + '</span>' +
                '<span class="wf-sev">' + escapeHtml(e.severity || 'warning') + '</span></div>' +
              '<div class="wf-flag-reason">' + escapeHtml(e.reason) + '</div>' + expl +
              '<div class="wf-flag-controls">' +
                '<div class="mb-2"><label class="small text-muted mb-1">Net amount (Rp)</label>' +
                  '<input type="number" class="form-control form-control-sm wf-net-input"' +
                  ' value="' + net + '" data-original="' + net + '" min="0" step="1000"></div>' +
                '<div class="wf-actions">' +
                  '<button class="wf-btn wf-btn-approve" onclick="resolveFlag(' + e.id + ",'approved',this)\">✓ Approve</button>" +
                  '<button class="wf-btn wf-btn-reject" onclick="resolveFlag(' + e.id + ",'rejected',this)\">✕ Reject</button>" +
                '</div>' +
              '</div>' +
            '</div>';
        flags.insertAdjacentHTML('beforeend', html);
    }

    // --- live chat log ---
    function appendMessage(e){
        if (WF_PERIOD && e.period && e.period !== WF_PERIOD) return;
        const chat = document.getElementById('wf-chat');
        const empty = chat.querySelector('.wf-empty'); if (empty) empty.remove();
        const color = WF_COLORS[e.agent_name] || '#64748b';
        const time = e.created_at ? new Date(e.created_at).toLocaleTimeString([], {hour12:false}) : '';
        const initial = (e.agent_name || '?').charAt(0).toUpperCase();
        const html =
            '<div class="wf-msg">' +
              '<div class="wf-avatar" style="background:' + color + '">' + escapeHtml(initial) + '</div>' +
              '<div class="wf-bubble">' +
                '<div class="wf-meta"><span class="wf-name" style="color:' + color + '">' +
                   escapeHtml(e.agent_name || e.sender_type) + '</span>' +
                   '<span class="wf-type">' + escapeHtml(e.message_type) + '</span></div>' +
                '<div class="wf-content">' + escapeHtml(e.content) + '</div>' +
                '<div class="wf-time">' + escapeHtml(time) + '</div>' +
              '</div>' +
            '</div>';
        chat.insertAdjacentHTML('beforeend', html);
        chat.scrollTop = chat.scrollHeight;
    }

    // --- subscribe once Echo (loaded after this script) is ready ---
    (function waitForEcho(){
        if (!window.Echo) { return void setTimeout(waitForEcho, 100); }
        window.Echo.private('workflow')
            .listen('.workflow.message', appendMessage)
            .listen('.workflow.flag-created', addFlagCard)
            .listen('.workflow.flag', (e) => applyDecision(e.id, e.decision, e.corrected_amount));
    })();

    // autoscroll on load
    (function(){ const c = document.getElementById('wf-chat'); if (c) c.scrollTop = c.scrollHeight; })();

    // --- Run Payroll trigger ---
    async function triggerPipeline(){
        const btn    = document.getElementById('trigger-btn');
        const toast  = document.getElementById('trigger-toast');
        const period = document.getElementById('trigger-period').value;
        if (!period) return;

        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Triggering…';
        toast.style.display = 'none';

        try {
            const resp = await fetch('/workflow/trigger', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ period }),
            });
            const data = await resp.json();
            if (resp.ok) {
                toast.textContent = '✓ Pipeline triggered for ' + data.period;
                toast.style.color = '#059669';
                // Switch to the new period after a short delay so messages start arriving.
                setTimeout(() => { window.location = '/workflow?period=' + encodeURIComponent(data.period); }, 1200);
            } else {
                toast.textContent = '✗ ' + (data.error ?? 'Trigger failed');
                toast.style.color = '#dc2626';
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-play"></i> Run Payroll';
            }
        } catch(e) {
            toast.textContent = '✗ Network error';
            toast.style.color = '#dc2626';
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-play"></i> Run Payroll';
        }
        toast.style.display = '';
    }
</script>
@endsection
