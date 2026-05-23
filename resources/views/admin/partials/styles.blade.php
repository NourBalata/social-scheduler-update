<style>
:root {
    --ink: #0f1117; --mist: #f4f5f7; --steel: #e8eaed;
    --blue: #2563eb; --blue-l: #eff6ff;
    --green: #10b981; --amber: #f59e0b; --red: #ef4444;
    --purple: #7c3aed; --card: #ffffff; --radius: 16px;
}
* { box-sizing: border-box; }
body { font-family: 'DM Sans', system-ui, sans-serif; background: #f0f2f5; }

.dash-wrap { max-width: 1280px; margin: 0 auto; padding: 28px 20px; }


.section-title { font-size: 13px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .06em; margin: 28px 0 14px; }

.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; margin-bottom: 8px; }
.stat-card { background: var(--card); border-radius: 14px; padding: 18px; border: 1px solid var(--steel); }
.stat-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; }
.stat-num  { font-size: 26px; font-weight: 700; color: var(--ink); line-height: 1; }
.stat-label { font-size: 12px; color: #6b7280; margin-top: 4px; }


.charts-row { display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 8px; }
@media(max-width:768px){ .charts-row { grid-template-columns: 1fr; } }
.chart-card { background: var(--card); border-radius: 14px; padding: 20px; border: 1px solid var(--steel); }
.chart-title { font-size: 14px; font-weight: 600; color: var(--ink); margin-bottom: 16px; }


.table-card { background: var(--card); border-radius: 14px; border: 1px solid var(--steel); overflow: hidden; }
.table-header { padding: 16px 20px; border-bottom: 1px solid var(--steel); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; }
.table-title { font-size: 15px; font-weight: 700; color: var(--ink); }
.search-wrap { display: flex; align-items: center; gap: 8px; background: var(--mist); border: 1px solid var(--steel); border-radius: 10px; padding: 7px 12px; }
.search-wrap input { background: none; border: none; outline: none; font-size: 13px; color: var(--ink); width: 170px; }
.add-btn { display: inline-flex; align-items: center; gap: 6px; background: var(--blue); color: #fff; font-weight: 600; font-size: 13px; padding: 8px 16px; border-radius: 9px; border: none; cursor: pointer; }

table { width: 100%; border-collapse: collapse; }
thead { background: #f9fafb; }
th { padding: 10px 16px; font-size: 11px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: .06em; text-align: left; border-bottom: 1px solid var(--steel); }
td { padding: 13px 16px; font-size: 13px; color: #374151; border-bottom: 1px solid #f3f4f6; }
tr:last-child td { border-bottom: none; }
tr.user-row:hover td { background: #fafbff; }


.badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 99px; font-size: 11px; font-weight: 700; white-space: nowrap; }
.badge-purple { background: #f3f0ff; color: #6d28d9; }
.badge-gray   { background: #f3f4f6; color: #6b7280; }
.badge-green  { background: #d1fae5; color: #065f46; }
.badge-blue   { background: #dbeafe; color: #1e40af; }
.badge-amber  { background: #fef3c7; color: #92400e; }
.badge-red    { background: #fee2e2; color: #991b1b; }


.user-avatar { width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg,#60a5fa,#2563eb); color:#fff; font-weight:700; font-size:13px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.user-cell { display: flex; align-items: center; gap: 10px; }


.action-btn { background: none; border: none; cursor: pointer; padding: 5px; border-radius: 7px; display: inline-flex; align-items: center; transition: background .12s; color: #9ca3af; }
.action-btn:hover { background: var(--mist); color: var(--ink); }


.plans-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; }
.plan-card { background: var(--card); border-radius: 12px; border: 1px solid var(--steel); padding: 16px; }
.plan-name { font-weight: 700; font-size: 15px; color: var(--ink); }
.plan-price { font-size: 22px; font-weight: 700; color: var(--blue); margin: 6px 0; }
.plan-meta { font-size: 12px; color: #6b7280; }
.plan-actions { display: flex; gap: 8px; margin-top: 12px; }
.plan-edit-btn { font-size: 12px; padding: 5px 12px; border-radius: 7px; border: 1px solid var(--steel); cursor: pointer; background: var(--mist); color: #374151; font-weight: 500; }

/* ── Modal ── */
.modal-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,.45); display: none; align-items: center; justify-content: center; z-index: 50; padding: 16px; }
.modal-backdrop.open { display: flex; }
.modal-inner { background:#fff; border-radius:18px; max-width:600px; width:100%; max-height:90vh; overflow-y:auto; }
.modal-head { padding:18px 22px; border-bottom:1px solid var(--steel); display:flex; align-items:center; justify-content:space-between; }
.modal-head h3 { font-size:16px; font-weight:700; color:var(--ink); }
.modal-close { background:none; border:none; cursor:pointer; color:#9ca3af; padding:3px; }
.modal-body { padding:22px; display:flex; flex-direction:column; gap:16px; }
.field-label { font-size:12px; font-weight:600; color:#374151; margin-bottom:5px; display:block; }
.field-input { width:100%; border:1.5px solid var(--steel); border-radius:10px; padding:9px 13px; font-size:14px; color:var(--ink); outline:none; transition:border-color .15s; }
.field-input:focus { border-color:var(--blue); }
.grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.btn-save { background:var(--blue); color:#fff; border:none; border-radius:10px; padding:10px 22px; font-size:14px; font-weight:600; cursor:pointer; }
.btn-cancel-sm { background:var(--mist); color:#374151; border:1px solid var(--steel); border-radius:10px; padding:10px 18px; font-size:14px; cursor:pointer; }
.modal-footer { display:flex; gap:10px; padding-top:6px; }


.stripe-chip { display:inline-flex; align-items:center; gap:4px; padding:2px 8px; border-radius:99px; font-size:10px; font-weight:700; }
.chip-active   { background:#d1fae5; color:#065f46; }
.chip-past_due { background:#fef3c7; color:#92400e; }
.chip-canceled { background:#fee2e2; color:#991b1b; }
.chip-none     { background:#f3f4f6; color:#6b7280; }





/* RTL Support */
[dir="rtl"] th,
[dir="rtl"] td { text-align: right; }

[dir="rtl"] .user-cell { flex-direction: row-reverse; }

[dir="rtl"] .table-header { flex-direction: row-reverse; }

[dir="rtl"] .stat-card { text-align: right; }

[dir="rtl"] .plan-actions { flex-direction: row-reverse; }

[dir="rtl"] .modal-head { flex-direction: row-reverse; }

[dir="rtl"] .modal-footer { flex-direction: row-reverse; }

[dir="rtl"] .grid-2 { direction: rtl; }

[dir="rtl"] .search-wrap { flex-direction: row-reverse; }

[dir="rtl"] .plans-grid { direction: rtl; }

[dir="rtl"] .section-title { flex-direction: row-reverse; }

[dir="rtl"] td[style*="text-align:center"] { text-align: center !important; }


.user-cell {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-direction: row;
    justify-content: flex-start;
}

[dir="rtl"] .user-cell {
    flex-direction: row-reverse;
    justify-content: flex-end;
}

</style>