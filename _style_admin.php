<?php /* Shared admin CSS */ ?>
<style>
:root{
  --navy:#000E72;--navy-dark:#000855;--navy-mid:#001aaa;--navy-light:#eef0fb;--navy-xlight:#f5f6fd;
  --yellow:#FFDF00;--yellow-light:#fffce0;
  --bg:#f0f2f8;--white:#ffffff;
  --text:#1a1a3e;--muted:#6b7280;--border:#e2e5f0;
  --radius:10px;--radius-lg:14px;--radius-xl:18px;
  --shadow-sm:0 1px 3px rgba(0,14,114,.07),0 1px 2px rgba(0,14,114,.05);
  --shadow:0 4px 16px rgba(0,14,114,.10);
  --shadow-lg:0 8px 32px rgba(0,14,114,.14);
}
*{box-sizing:border-box}
body{font-family:'Poppins',sans-serif;background:var(--bg);color:var(--text);font-size:.9rem;min-height:100vh}
a{color:var(--navy)}

.main-header{height:64px;background:var(--navy);display:flex;align-items:center;position:sticky;top:0;z-index:100;border-bottom:3px solid var(--yellow);box-shadow:0 2px 16px rgba(0,14,114,.25)}
.main-header .navbar-brand img{height:40px;width:auto;object-fit:contain}
.main-header .navbar-brand span{color:#fff;font-weight:700;font-size:1rem;letter-spacing:.2px}
.main-header .user-pill{display:flex;align-items:center;gap:8px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);border-radius:24px;padding:5px 14px 5px 8px}
.main-header .user-avatar{width:28px;height:28px;border-radius:50%;background:var(--yellow);color:var(--navy);display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:700;flex-shrink:0}
.main-header .user-name{color:rgba(255,255,255,.9);font-size:.8rem;font-weight:500}
.main-header .btn-outline-light{border-color:rgba(255,255,255,.3);color:rgba(255,255,255,.9);font-size:.78rem;padding:5px 13px;border-radius:8px}
.main-header .btn-outline-light:hover{background:rgba(255,255,255,.15);color:#fff;border-color:rgba(255,255,255,.5)}

.sidebar{background:var(--white);border-right:1px solid var(--border);min-height:calc(100vh - 62px)}
.sidebar-brand{padding:18px 20px 12px;border-bottom:1px solid var(--border);margin-bottom:6px}
.sidebar-brand .brand-text{font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:1.2px;color:var(--muted)}
.sidebar .nav-link{color:var(--muted);border-radius:0 10px 10px 0;padding:10px 16px;margin:2px 10px 2px 0;transition:all .15s;font-size:.85rem;display:flex;align-items:center;gap:10px;font-weight:400}
.sidebar .nav-link i{width:16px;text-align:center;font-size:.88rem;flex-shrink:0}
.sidebar .nav-link:hover{background:var(--navy-xlight);color:var(--navy)}
.sidebar .nav-link.active{background:var(--navy-light);color:var(--navy);font-weight:600;border-left:3px solid var(--navy);border-radius:0 10px 10px 0;margin-left:0;padding-left:19px}

.card{border:1px solid var(--border);border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);margin-bottom:20px;background:var(--white)}
.card-header{background:transparent;border-bottom:1px solid var(--border);font-weight:600;font-size:.88rem;color:var(--text);padding:14px 20px;display:flex;align-items:center;gap:8px}
.card-body{padding:20px}

.stat-card{transition:transform .15s,box-shadow .15s}
.stat-card:hover{transform:translateY(-2px);box-shadow:var(--shadow)}
.stat-card .card-body{padding:20px 20px 18px;display:flex;flex-direction:column;gap:2px}
.stat-icon{width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:17px;margin-bottom:12px}
.stat-icon.navy{background:var(--navy-light);color:var(--navy)}
.stat-icon.yellow{background:#fffbeb;color:#b45309}
.stat-icon.red{background:#fef2f2;color:#b91c1c}
.stat-icon.blue{background:#eff6ff;color:#1d4ed8}
.stat-icon.green{background:#ecfdf5;color:#047857}
.stat-icon.gray{background:#f3f4f6;color:#6b7280}
.stat-value{font-size:2rem;font-weight:700;color:var(--navy);line-height:1;letter-spacing:-.5px}
.stat-label{font-size:.68rem;color:var(--muted);text-transform:uppercase;letter-spacing:.8px;font-weight:600;margin-top:4px}

.status-badge,.type-badge{display:inline-flex;align-items:center;gap:4px;padding:4px 11px;border-radius:20px;font-size:.72rem;font-weight:600;letter-spacing:.2px;white-space:nowrap}
.status-pendente{background:#FEF3C7;color:#92400E}
.status-em_analise{background:#DBEAFE;color:#1E40AF}
.status-respondida{background:#D1FAE5;color:#065F46}
.status-arquivada{background:#F3F4F6;color:#6B7280}
.type-sugestao{background:#EFF6FF;color:#1D4ED8}
.type-critica{background:#FFFBEB;color:#B45309}
.type-elogio{background:#ECFDF5;color:#047857}
.type-reclamacao{background:#FEF2F2;color:#B91C1C}
.level-admin{background:#f5f0ff;color:#5b21b6}
.level-ouvidor{background:#fff7ed;color:#c2410c}
.level-analista{background:#ecfdf5;color:#047857}
.status-active{background:#D1FAE5;color:#065F46}
.status-inactive{background:#FEF2F2;color:#B91C1C}

.table-wrapper{overflow-x:auto}
.table{margin-bottom:0;width:100%;border-collapse:collapse}
.table thead th{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--muted);border-bottom:2px solid var(--border);padding:12px 18px;background:var(--navy-xlight);white-space:nowrap}
.table td{padding:13px 18px;font-size:.85rem;border-bottom:1px solid var(--border);vertical-align:middle}
.table tbody tr:last-child td{border-bottom:none}
.table tbody tr{transition:background .1s}
.table tbody tr:hover{background:var(--navy-xlight)}

.form-label{font-size:.8rem;font-weight:600;color:var(--text);margin-bottom:5px}
.form-control,.form-select{border:1px solid var(--border);border-radius:9px;padding:9px 13px;font-size:.875rem;color:var(--text);font-family:'Poppins',sans-serif;transition:border .15s,box-shadow .15s;background:#fff}
.form-control:focus,.form-select:focus{border-color:var(--navy);box-shadow:0 0 0 3px rgba(0,14,114,.1);outline:none}
textarea.form-control{resize:vertical;min-height:100px}

.btn{border-radius:9px;font-size:.85rem;padding:8px 16px;font-weight:500;transition:all .15s;font-family:'Poppins',sans-serif}
.btn-primary{background:var(--navy);border-color:var(--navy);color:#fff}
.btn-primary:hover{background:var(--navy-dark);border-color:var(--navy-dark);color:#fff;transform:translateY(-1px)}
.btn-outline-primary{border-color:var(--navy);color:var(--navy)}
.btn-outline-primary:hover{background:var(--navy);color:#fff}
.btn-outline-secondary{border-color:var(--border);color:var(--muted)}
.btn-outline-secondary:hover{background:var(--navy-light);color:var(--navy);border-color:var(--navy-light)}
.btn-sm{padding:5px 11px;font-size:.78rem}

.page-header{margin-bottom:24px;padding-bottom:18px;padding-left:8px;border-bottom:1px solid var(--border)}
.page-header h2{font-size:1.35rem;font-weight:700;color:var(--navy);margin-bottom:3px;display:flex;align-items:center;gap:8px}
.page-header h2 .ph-icon{width:36px;height:36px;border-radius:10px;background:var(--navy);color:var(--yellow);display:flex;align-items:center;justify-content:center;font-size:.95rem;flex-shrink:0;margin-right:0}
.page-header p{font-size:.83rem;color:var(--muted);margin:0}

.alert{border:none;border-radius:var(--radius);font-size:.85rem;padding:12px 16px;display:flex;align-items:center;gap:8px}
.alert-success{background:#D1FAE5;color:#065F46}
.alert-danger{background:#FEF2F2;color:#B91C1C}
.alert-warning{background:#FEF3C7;color:#92400E}
.alert i{font-size:1rem;flex-shrink:0}

.pagination .page-link{color:var(--navy);border:1px solid var(--border);border-radius:8px !important;margin:0 2px;padding:6px 12px;font-size:.82rem;font-weight:500}
.pagination .page-item.active .page-link{background:var(--navy);border-color:var(--navy);color:#fff}
.pagination .page-link:hover{background:var(--navy-light);color:var(--navy);border-color:var(--navy-light)}

.filter-card{background:var(--white);border:1px solid var(--border);border-radius:var(--radius-lg);padding:16px 20px;margin-bottom:20px;box-shadow:var(--shadow-sm)}

.empty-state{text-align:center;padding:56px 20px;color:var(--muted)}
.empty-state i{font-size:2.5rem;margin-bottom:12px;opacity:.3;display:block}
.empty-state h4{font-size:1rem;margin-bottom:6px;color:var(--text)}
.empty-state p{font-size:.85rem}
.message-preview{max-width:280px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.modal-header{background:var(--navy);color:#fff}
.modal-header .btn-close{filter:invert(1) brightness(2)}
.modal-content{border:none;border-radius:var(--radius-xl);box-shadow:0 20px 48px rgba(0,14,114,.18)}
.modal-footer{border-top:1px solid var(--border);padding:14px 20px}
.info-block{display:flex;flex-direction:column;gap:3px;padding:14px;background:var(--bg);border-radius:var(--radius);min-width:0}
.info-block .lbl{font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--muted)}
.info-block .val{font-size:.9rem;font-weight:500;color:var(--text)}
.resp-box{background:var(--navy-light);border-left:4px solid var(--navy);border-radius:0 var(--radius) var(--radius) 0;padding:16px 18px}
.resp-box h6{font-size:.72rem;text-transform:uppercase;letter-spacing:.6px;color:var(--navy);font-weight:700;margin-bottom:6px}
.chart-container{position:relative;height:280px;width:100%}

@media(max-width:991.98px){
  .sidebar{position:fixed;top:62px;left:-240px;width:220px;z-index:1030;height:calc(100vh - 62px);overflow-y:auto;transition:left .25s;box-shadow:var(--shadow-lg)}
  .sidebar.show{left:0}
  .overlay{display:none;position:fixed;top:62px;left:0;right:0;bottom:0;background:rgba(0,14,114,.35);z-index:1020}
  .overlay.show{display:block}
  .content-wrapper{width:100%}
  .chart-container{height:220px}
}
@media(max-width:576px){
  .table{font-size:.78rem}
  .stat-value{font-size:1.6rem}
  .message-preview{max-width:120px}
}
</style>
<style>.card-header i:first-child{color:var(--navy)}.page-header h2 .ph-icon i{color:var(--yellow)}</style>