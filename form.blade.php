@extends('nexcore_client_manager::layouts.nerve-centre')

@section('title', isset($sla) ? 'Edit Engagement Letter' : 'New Engagement Letter')
@section('topbar_page', isset($sla) ? 'Edit Engagement Letter' : 'New Engagement Letter')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">
<style>
/* ================================================================
   ATP SERVICES — SLA / ENGAGEMENT LETTER FORM
   ITR14-style step wizard with accordions
   ================================================================ */

/* ── Step Wizard Tabs ── */
.sla-wizard {
    display: flex;
    align-items: stretch;
    gap: 0;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 16px;
    overflow: hidden;
    margin-bottom: 28px;
}
.sla-tab {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 12px 10px;
    cursor: pointer;
    position: relative;
    transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
    border-right: 1px solid rgba(255,255,255,0.06);
    background: transparent;
    border-top: none;
    border-bottom: none;
    border-left: none;
    text-align: left;
    font-family: 'Montserrat', sans-serif;
    text-decoration: none;
}
.sla-tab:last-child { border-right: none; }
.sla-tab::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 2px;
    background: transparent;
    transition: background 0.3s;
}
.sla-tab.active::after {
    background: linear-gradient(90deg, #06b6d4, #0ea5e9);
}
.sla-tab.active {
    background: rgba(6,182,212,0.08);
}
.sla-tab.active .sla-tab-num {
    background: linear-gradient(135deg, #06b6d4, #0ea5e9);
    color: #0a0e1a;
    border-color: #06b6d4;
    box-shadow: 0 0 16px rgba(6,182,212,0.4);
}
.sla-tab.active .sla-tab-label {
    color: #22d3ee;
}
.sla-tab.done .sla-tab-num {
    background: rgba(16,185,129,0.15);
    border-color: rgba(16,185,129,0.4);
    color: #34d399;
}
.sla-tab.done .sla-tab-label { color: #6ee7b7; }
.sla-tab-num {
    width: 48px;
    height: 48px;
    min-width: 48px;
    border-radius: 12px;
    border: 2px solid rgba(255,255,255,0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    color: var(--text-muted, #64748b);
    transition: all 0.3s;
    background: rgba(255,255,255,0.04);
}
.sla-tab-info { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.sla-tab-step {
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: var(--text-muted, #64748b);
}
.sla-tab-label {
    font-size: 13px;
    font-weight: 700;
    color: var(--text-secondary, #94a3b8);
    white-space: normal;
    word-wrap: break-word;
    line-height: 1.3;
    letter-spacing: 0.5px;
    transition: color 0.3s;
}

/* ── Tab Panels ── */
.sla-panel { display: none; }
.sla-panel.active { display: block; }

/* ── Section Accordion ── */
.sla-section {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.10);
    border-radius: 14px;
    margin-bottom: 12px;
    overflow: hidden;
    transition: border-color 0.3s;
    box-shadow: 0 2px 16px rgba(0,0,0,0.18);
}
.sla-section:hover { border-color: rgba(255,255,255,0.18); }
.sla-section.open { border-color: rgba(6,182,212,0.2); }

.sla-section-header {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px 20px;
    cursor: pointer;
    user-select: none;
    transition: background 0.2s;
}
.sla-section-header:hover { background: rgba(255,255,255,0.04); }
.sla-section.open .sla-section-header { background: rgba(6,182,212,0.06); }

.sla-section-code {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: #06b6d4;
    background: rgba(6,182,212,0.1);
    border: 1px solid rgba(6,182,212,0.2);
    padding: 5px 12px;
    border-radius: 6px;
    white-space: nowrap;
    font-family: 'Montserrat', sans-serif;
}
.sla-section-title {
    flex: 1;
    font-size: 15px;
    font-weight: 700;
    color: var(--text-secondary, #94a3b8);
    letter-spacing: 0.3px;
    transition: color 0.2s;
}
.sla-section.open .sla-section-title { color: #e2e8f0; }

.sla-section-chevron {
    color: var(--text-muted, #64748b);
    font-size: 13px;
    transition: transform 0.3s cubic-bezier(0.4,0,0.2,1), color 0.2s;
    width: 20px;
    text-align: center;
}
.sla-section.open .sla-section-chevron {
    transform: rotate(180deg);
    color: #06b6d4;
}

.sla-section-body {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.4s cubic-bezier(0.4,0,0.2,1);
}
.sla-section.open .sla-section-body { max-height: 9999px; }

.sla-section-inner {
    padding: 6px 20px 20px;
}

/* ── Page Header ── */
.sla-page-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 28px;
    padding-bottom: 20px;
    border-bottom: 1px solid rgba(255,255,255,0.07);
}
.sla-header-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: linear-gradient(135deg, rgba(6,182,212,0.15), rgba(6,182,212,0.05));
    border: 1px solid rgba(6,182,212,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.sla-header-icon i { color: #06b6d4; font-size: 20px; }
.sla-header-title { font-size: 24px; font-weight: 900; color: #f1f5f9; letter-spacing: 1px; margin: 0; }
.sla-header-sub { font-size: 13px; color: var(--text-muted, #64748b); margin-top: 3px; letter-spacing: 0.5px; }
.sla-header-badge {
    margin-left: auto;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: #06b6d4;
    background: rgba(6,182,212,0.1);
    border: 1px solid rgba(6,182,212,0.25);
    padding: 6px 14px;
    border-radius: 8px;
}

/* ── Form Fields ── */
.sla-form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 16px;
    margin-bottom: 16px;
}
.sla-form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.sla-form-label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    color: var(--text-muted, #64748b);
    font-family: 'Montserrat', sans-serif;
}
.sla-form-label .sla-required {
    color: #ef4444;
    margin-left: 2px;
}
.sla-form-input,
.sla-form-select,
.sla-form-textarea {
    width: 100%;
    height: 44px;
    background: rgba(0,0,0,0.2);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 10px;
    outline: none;
    color: #e2e8f0;
    font-size: 14px;
    font-weight: 600;
    padding: 0 14px;
    font-family: 'Montserrat', sans-serif;
    transition: border-color 0.2s, box-shadow 0.2s;
    box-sizing: border-box;
}
.sla-form-input:focus,
.sla-form-select:focus,
.sla-form-textarea:focus {
    border-color: rgba(6,182,212,0.5);
    box-shadow: 0 0 0 3px rgba(6,182,212,0.08);
}
.sla-form-input::placeholder { color: rgba(100,116,139,0.5); font-weight: 400; }
.sla-form-textarea {
    height: auto;
    min-height: 80px;
    padding: 12px 14px;
    resize: vertical;
    line-height: 1.6;
}
.sla-form-select {
    -webkit-appearance: none;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748b' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    padding-right: 30px;
    cursor: pointer;
}
.sla-form-select option {
    background: #0f172a;
    color: #e2e8f0;
}

/* ── Client Selector ── */
.sla-client-selector {
    background: rgba(6,182,212,0.04);
    border: 1px solid rgba(6,182,212,0.15);
    border-radius: 14px;
    padding: 20px;
    margin-bottom: 20px;
}
.sla-client-selector-title {
    font-size: 13px;
    font-weight: 800;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: #06b6d4;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.sla-client-selector-title i { font-size: 14px; }

/* ── Clause Block (for T&C) ── */
.sla-clause {
    padding: 16px 0;
    border-bottom: 1px solid rgba(255,255,255,0.06);
}
.sla-clause:last-child { border-bottom: none; }
.sla-clause-num {
    font-size: 13px;
    font-weight: 800;
    color: #06b6d4;
    margin-bottom: 6px;
    letter-spacing: 0.5px;
}
.sla-clause-title {
    font-size: 15px;
    font-weight: 700;
    color: #e2e8f0;
    margin-bottom: 10px;
}
.sla-clause-text {
    font-size: 13px;
    color: var(--text-secondary, #94a3b8);
    line-height: 1.8;
}
.sla-clause-text p { margin: 0 0 8px 0; }
.sla-sub-clause {
    padding-left: 24px;
    margin: 6px 0;
    font-size: 13px;
    color: var(--text-secondary, #94a3b8);
    line-height: 1.8;
    position: relative;
}
.sla-sub-clause::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 10px;
    width: 4px;
    height: 4px;
    border-radius: 50%;
    background: rgba(6,182,212,0.4);
}

/* ── Pricing Cards ── */
.sla-pricing-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
    margin: 20px 0;
}
.sla-price-card {
    background: rgba(255,255,255,0.03);
    border: 2px solid rgba(255,255,255,0.08);
    border-radius: 16px;
    padding: 24px 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}
.sla-price-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: var(--card-accent, rgba(255,255,255,0.1));
    transition: height 0.3s;
}
.sla-price-card:hover {
    border-color: rgba(255,255,255,0.2);
    transform: translateY(-2px);
    box-shadow: 0 8px 32px rgba(0,0,0,0.3);
}
.sla-price-card.selected {
    border-color: var(--card-accent);
    background: rgba(6,182,212,0.06);
    box-shadow: 0 0 30px rgba(6,182,212,0.15);
}
.sla-price-card.selected::before { height: 4px; }
.sla-price-card.selected .sla-price-check {
    opacity: 1;
    transform: scale(1);
}
.sla-price-check {
    position: absolute;
    top: 12px;
    right: 12px;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: var(--card-accent);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #0a0e1a;
    font-size: 13px;
    font-weight: 900;
    opacity: 0;
    transform: scale(0.5);
    transition: all 0.3s;
}
.sla-price-tier {
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: var(--card-accent);
    margin-bottom: 8px;
}
.sla-price-name {
    font-size: 18px;
    font-weight: 800;
    color: #f1f5f9;
    margin-bottom: 6px;
    letter-spacing: 0.3px;
}
.sla-price-amount {
    font-size: 28px;
    font-weight: 900;
    color: #e2e8f0;
    margin-bottom: 4px;
    font-family: 'Montserrat', sans-serif;
}
.sla-price-amount .sla-price-currency {
    font-size: 16px;
    font-weight: 700;
    color: var(--text-muted, #64748b);
    vertical-align: super;
}
.sla-price-period {
    font-size: 11px;
    color: var(--text-muted, #64748b);
    margin-bottom: 6px;
    letter-spacing: 0.5px;
}
.sla-price-turnover {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    color: var(--card-accent);
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.08);
    padding: 4px 12px;
    border-radius: 20px;
    margin-bottom: 16px;
    display: inline-block;
}
.sla-price-divider {
    width: 40px;
    height: 1px;
    background: rgba(255,255,255,0.1);
    margin: 0 auto 16px;
}
.sla-price-features {
    list-style: none;
    padding: 0;
    margin: 0;
    text-align: left;
    flex: 1;
}
.sla-price-features li {
    font-size: 12px;
    color: var(--text-secondary, #94a3b8);
    padding: 5px 0;
    display: flex;
    align-items: flex-start;
    gap: 8px;
    line-height: 1.5;
}
.sla-price-features li i {
    color: var(--card-accent);
    font-size: 10px;
    margin-top: 4px;
    flex-shrink: 0;
}
.sla-price-popular {
    position: absolute;
    top: 12px;
    left: 12px;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    color: #0a0e1a;
    background: var(--card-accent);
    padding: 3px 10px;
    border-radius: 20px;
}
.sla-price-excludes {
    font-size: 10px;
    color: #f59e0b;
    line-height: 1.5;
    margin-top: 0;
    font-style: italic;
    background: rgba(245,158,11,0.04);
    border-radius: 8px;
    padding: 10px 12px;
    border: 1px solid rgba(245,158,11,0.12);
}
.sla-price-consult {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    background: rgba(16,185,129,0.12);
    border: 1px solid rgba(16,185,129,0.3);
    color: #10b981;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.5px;
    padding: 8px 16px;
    border-radius: 20px;
    margin: 16px auto 14px;
    text-align: center;
}
.sla-price-consult i { font-size: 11px; }
.sla-price-discount {
    display: inline-block;
    white-space: nowrap;
    background: rgba(16,185,129,0.15);
    border: 1px solid rgba(16,185,129,0.3);
    color: #10b981;
    font-size: 9px;
    font-weight: 900;
    letter-spacing: 0.5px;
    padding: 2px 6px;
    border-radius: 4px;
    margin-left: 4px;
    vertical-align: middle;
}

/* ── 1% Value Proposition Block ── */
.sla-value-prop {
    background: linear-gradient(135deg, rgba(6,182,212,0.06), rgba(14,165,233,0.04));
    border: 1px solid rgba(6,182,212,0.15);
    border-radius: 16px;
    padding: 28px 24px;
    margin-bottom: 20px;
    text-align: center;
}
.sla-value-prop-title {
    font-size: 22px;
    font-weight: 900;
    color: #22d3ee;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}
.sla-value-prop-subtitle {
    font-size: 15px;
    font-weight: 700;
    color: #e2e8f0;
    margin-bottom: 14px;
    letter-spacing: 0.3px;
}
.sla-value-prop-text {
    font-size: 13px;
    color: var(--text-secondary, #94a3b8);
    line-height: 1.8;
    max-width: 640px;
    margin: 0 auto;
}

/* ── Payroll Section ── */
.sla-payroll-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin: 20px 0;
}
.sla-payroll-card {
    background: rgba(255,255,255,0.03);
    border: 2px solid rgba(255,255,255,0.08);
    border-radius: 16px;
    padding: 22px 16px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
    position: relative;
    overflow: hidden;
}
.sla-payroll-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: var(--card-accent, rgba(255,255,255,0.1));
    transition: height 0.3s;
}
.sla-payroll-card:hover {
    border-color: rgba(255,255,255,0.2);
    transform: translateY(-2px);
    box-shadow: 0 8px 32px rgba(0,0,0,0.3);
}
.sla-payroll-card.selected {
    border-color: var(--card-accent);
    background: rgba(6,182,212,0.06);
    box-shadow: 0 0 30px rgba(6,182,212,0.15);
}
.sla-payroll-card.selected::before { height: 4px; }
.sla-payroll-card.selected .sla-payroll-check {
    opacity: 1;
    transform: scale(1);
}
.sla-payroll-check {
    position: absolute;
    top: 12px;
    right: 12px;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: var(--card-accent);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #0a0e1a;
    font-size: 13px;
    font-weight: 900;
    opacity: 0;
    transform: scale(0.5);
    transition: all 0.3s;
}
.sla-payroll-emp {
    min-width: 52px;
    height: 48px;
    padding: 0 12px;
    border-radius: 14px;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.1);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 10px;
    font-size: 18px;
    font-weight: 900;
    color: var(--card-accent);
}
.sla-payroll-label {
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: var(--text-muted, #64748b);
    margin-bottom: 4px;
}
.sla-payroll-name {
    font-size: 14px;
    font-weight: 800;
    color: #f1f5f9;
    margin-bottom: 6px;
    white-space: nowrap;
}
.sla-payroll-price {
    font-size: 26px;
    font-weight: 900;
    color: #e2e8f0;
    margin-bottom: 2px;
}
.sla-payroll-price .sla-payroll-cur {
    font-size: 14px;
    font-weight: 700;
    color: var(--text-muted, #64748b);
    vertical-align: super;
}
.sla-payroll-per {
    font-size: 11px;
    color: var(--text-muted, #64748b);
    margin-bottom: 14px;
}

/* ── Payroll Included Features ── */
.sla-payroll-included {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0;
    margin: 16px 0;
    border-radius: 14px;
    overflow: hidden;
    border: 1px solid rgba(255,255,255,0.08);
}
.sla-payroll-included-left {
    background: rgba(255,255,255,0.03);
    padding: 22px 24px;
}
.sla-payroll-included-right {
    position: relative;
    background: rgba(6,182,212,0.04);
    border-left: 1px solid rgba(255,255,255,0.06);
    overflow: hidden;
    min-height: 220px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* ── Included Promo Block (premium rebuild) ── */
.sla-inc-promo {
    padding: 28px 26px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    height: 100%;
    width: 100%;
    background: linear-gradient(135deg, rgba(16,185,129,0.08) 0%, rgba(6,182,212,0.06) 50%, rgba(59,130,246,0.04) 100%);
    position: relative;
}
.sla-inc-promo::before {
    content: '';
    position: absolute;
    top: 0; right: 0;
    width: 160px; height: 160px;
    background: radial-gradient(circle, rgba(16,185,129,0.08) 0%, transparent 70%);
    border-radius: 50%;
    transform: translate(40%, -40%);
}
.sla-inc-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(16,185,129,0.15);
    border: 1px solid rgba(16,185,129,0.3);
    color: #10b981;
    font-size: 10px;
    font-weight: 900;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    padding: 6px 14px;
    border-radius: 20px;
    margin-bottom: 14px;
    width: fit-content;
}
.sla-inc-badge i { font-size: 11px; }
.sla-inc-title {
    font-size: 22px;
    font-weight: 900;
    color: #f1f5f9;
    line-height: 1.3;
    margin-bottom: 14px;
    letter-spacing: 0.5px;
}
.sla-inc-title span {
    background: linear-gradient(135deg, #10b981, #06b6d4);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.sla-inc-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 7px;
    position: relative;
    z-index: 1;
}
.sla-inc-item {
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(16,185,129,0.12);
    border-radius: 9px;
    padding: 9px 12px;
    transition: all 0.2s;
}
.sla-inc-item:hover {
    background: rgba(16,185,129,0.06);
    border-color: rgba(16,185,129,0.25);
}
.sla-inc-icon {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: rgba(16,185,129,0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.sla-inc-icon i { color: #10b981; font-size: 11px; }
.sla-inc-text {
    font-size: 13px;
    font-weight: 700;
    color: #e2e8f0;
    line-height: 1.3;
}
.sla-inc-footer {
    margin-top: 16px;
    font-size: 11px;
    font-weight: 800;
    color: #10b981;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.sla-inc-footer i { font-size: 13px; }

/* ── Unified 2x2 Promo Grid ── */
.sla-promo-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    grid-auto-rows: 1fr;
    gap: 12px;
    margin: 16px 0;
}
.sla-promo-cell {
    border-radius: 14px;
    overflow: hidden;
    border: 1px solid rgba(255,255,255,0.08);
}
.sla-promo-cell > div {
    height: 100%;
}

.sla-coida-promo {
    padding: 28px 26px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    height: 100%;
    background: linear-gradient(135deg, rgba(245,158,11,0.08) 0%, rgba(239,68,68,0.06) 50%, rgba(6,182,212,0.05) 100%);
    position: relative;
}
.sla-coida-promo::before {
    content: '';
    position: absolute;
    top: 0; right: 0;
    width: 120px; height: 120px;
    background: radial-gradient(circle, rgba(245,158,11,0.12) 0%, transparent 70%);
    border-radius: 50%;
    transform: translate(30%, -30%);
}
.sla-coida-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(245,158,11,0.15);
    border: 1px solid rgba(245,158,11,0.3);
    color: #f59e0b;
    font-size: 10px;
    font-weight: 900;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    padding: 6px 14px;
    border-radius: 20px;
    margin-bottom: 14px;
    width: fit-content;
}
.sla-coida-badge i { font-size: 11px; }
.sla-coida-title {
    font-size: 22px;
    font-weight: 900;
    color: #f1f5f9;
    line-height: 1.3;
    margin-bottom: 6px;
    letter-spacing: 0.5px;
    white-space: nowrap;
}
.sla-coida-title span {
    background: linear-gradient(135deg, #f59e0b, #ef4444);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.sla-coida-sub {
    font-size: 13px;
    color: var(--text-secondary, #94a3b8);
    line-height: 1.6;
    margin-bottom: 16px;
}
.sla-coida-items {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 18px;
}
.sla-coida-item {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 8px;
    padding: 6px 12px;
    font-size: 13px;
    font-weight: 700;
    color: #e2e8f0;
}
.sla-coida-item i { color: #f59e0b; font-size: 11px; }
.sla-coida-price-row {
    display: flex;
    align-items: baseline;
    gap: 6px;
    position: relative;
    z-index: 1;
}
.sla-coida-price {
    font-size: 36px;
    font-weight: 900;
    color: #f59e0b;
    line-height: 1;
    letter-spacing: -1px;
}
.sla-coida-price-cur {
    font-size: 18px;
    font-weight: 800;
    color: #f59e0b;
    vertical-align: super;
}
.sla-coida-price-per {
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted, #64748b);
    letter-spacing: 0.5px;
}

/* ── Registrations Promo ── */
.sla-reg-promo {
    padding: 28px 26px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    height: 100%;
    background: linear-gradient(135deg, rgba(59,130,246,0.08) 0%, rgba(139,92,246,0.06) 50%, rgba(6,182,212,0.05) 100%);
    position: relative;
}
.sla-reg-promo::before {
    content: '';
    position: absolute;
    bottom: 0; left: 0;
    width: 140px; height: 140px;
    background: radial-gradient(circle, rgba(59,130,246,0.1) 0%, transparent 70%);
    border-radius: 50%;
    transform: translate(-30%, 30%);
}
.sla-reg-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(59,130,246,0.15);
    border: 1px solid rgba(59,130,246,0.3);
    color: #3b82f6;
    font-size: 10px;
    font-weight: 900;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    padding: 6px 14px;
    border-radius: 20px;
    margin-bottom: 14px;
    width: fit-content;
}
.sla-reg-badge i { font-size: 11px; }
.sla-reg-title {
    font-size: 22px;
    font-weight: 900;
    color: #f1f5f9;
    line-height: 1.3;
    margin-bottom: 10px;
    letter-spacing: 0.5px;
}
.sla-reg-title span {
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.sla-reg-items {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 18px;
}
.sla-reg-item {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 8px;
    padding: 6px 11px;
    font-size: 13px;
    font-weight: 700;
    color: #e2e8f0;
}
.sla-reg-item i { color: #3b82f6; font-size: 10px; }
.sla-reg-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 20px;
    position: relative;
    z-index: 1;
}
.sla-reg-line {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    font-weight: 700;
    color: #e2e8f0;
    line-height: 1.3;
}
.sla-reg-line i {
    color: #3b82f6;
    font-size: 14px;
    flex-shrink: 0;
}
.sla-reg-price-row {
    display: flex;
    align-items: baseline;
    gap: 6px;
    position: relative;
    z-index: 1;
}
.sla-reg-price {
    font-size: 36px;
    font-weight: 900;
    color: #3b82f6;
    line-height: 1;
    letter-spacing: -1px;
}
.sla-reg-price-cur {
    font-size: 18px;
    font-weight: 800;
    color: #3b82f6;
    vertical-align: super;
}
.sla-reg-price-per {
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted, #64748b);
    letter-spacing: 0.5px;
}
.sla-reg-note {
    margin-top: 6px;
    font-size: 10px;
    font-weight: 700;
    color: #8b5cf6;
    letter-spacing: 0.3px;
}
.sla-payroll-included-title {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: #10b981;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.sla-payroll-included-title i { font-size: 12px; }
.sla-payroll-included-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 6px;
}
.sla-payroll-included-item {
    font-size: 12px;
    color: var(--text-secondary, #94a3b8);
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 4px 0;
}
.sla-payroll-included-item i {
    color: #10b981;
    font-size: 9px;
    flex-shrink: 0;
}

/* ── Optional Add-ons ── */
.sla-addons-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 10px;
    margin: 16px 0;
}
.sla-addon-card {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 12px;
    padding: 14px 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: all 0.2s;
}
.sla-addon-card:hover {
    border-color: rgba(255,255,255,0.15);
    background: rgba(255,255,255,0.05);
}
.sla-addon-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
}
.sla-addon-name {
    font-size: 12px;
    font-weight: 700;
    color: #e2e8f0;
    line-height: 1.3;
}
.sla-addon-price {
    font-size: 13px;
    font-weight: 800;
    color: var(--card-accent, #06b6d4);
    white-space: nowrap;
    margin-left: auto;
    flex-shrink: 0;
}

/* ── Terminations & Resignations Promo ── */
.sla-term-promo {
    padding: 28px 26px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    height: 100%;
    background: linear-gradient(135deg, rgba(239,68,68,0.08) 0%, rgba(220,38,38,0.06) 50%, rgba(245,158,11,0.04) 100%);
    position: relative;
}
.sla-term-promo::before {
    content: '';
    position: absolute;
    bottom: 0; right: 0;
    width: 140px; height: 140px;
    background: radial-gradient(circle, rgba(239,68,68,0.1) 0%, transparent 70%);
    border-radius: 50%;
    transform: translate(30%, 30%);
}
.sla-term-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(239,68,68,0.15);
    border: 1px solid rgba(239,68,68,0.3);
    color: #ef4444;
    font-size: 10px;
    font-weight: 900;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    padding: 6px 14px;
    border-radius: 20px;
    margin-bottom: 14px;
    width: fit-content;
}
.sla-term-badge i { font-size: 11px; }
.sla-term-title {
    font-size: 22px;
    font-weight: 900;
    color: #f1f5f9;
    line-height: 1.3;
    margin-bottom: 10px;
    letter-spacing: 0.5px;
}
.sla-term-title span {
    background: linear-gradient(135deg, #ef4444, #f59e0b);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.sla-term-sub {
    font-size: 13px;
    color: var(--text-secondary, #94a3b8);
    line-height: 1.6;
    margin-bottom: 14px;
}
.sla-term-items {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 18px;
}
.sla-term-item {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(239,68,68,0.15);
    border-radius: 8px;
    padding: 6px 12px;
    font-size: 13px;
    font-weight: 700;
    color: #e2e8f0;
}
.sla-term-item i { color: #ef4444; font-size: 11px; }
.sla-term-price-row {
    display: flex;
    align-items: baseline;
    gap: 6px;
    position: relative;
    z-index: 1;
}
.sla-term-price {
    font-size: 36px;
    font-weight: 900;
    color: #ef4444;
    line-height: 1;
    letter-spacing: -1px;
}
.sla-term-price-cur {
    font-size: 18px;
    font-weight: 800;
    color: #ef4444;
    vertical-align: super;
}
.sla-term-price-per {
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted, #64748b);
    letter-spacing: 0.5px;
}

@media (max-width: 1100px) {
    .sla-payroll-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 640px) {
    .sla-payroll-grid { grid-template-columns: 1fr; }
    .sla-promo-grid { grid-template-columns: 1fr; grid-auto-rows: auto; }
    .sla-inc-grid { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
    .sla-addons-grid { grid-template-columns: 1fr; }
}

/* ── Signature Area ── */
.sla-signature-pad {
    background: rgba(255,255,255,0.95);
    border: 2px solid rgba(255,255,255,0.2);
    border-radius: 12px;
    width: 100%;
    max-width: 500px;
    height: 180px;
    cursor: crosshair;
    touch-action: none;
}
.sla-signature-actions {
    display: flex;
    gap: 10px;
    margin-top: 10px;
}
.sla-sig-btn {
    padding: 8px 18px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    font-family: 'Montserrat', sans-serif;
    cursor: pointer;
    border: 1px solid rgba(255,255,255,0.15);
    background: rgba(255,255,255,0.06);
    color: var(--text-secondary, #94a3b8);
    transition: all 0.2s;
    letter-spacing: 0.5px;
}
.sla-sig-btn:hover {
    background: rgba(255,255,255,0.1);
    border-color: rgba(255,255,255,0.25);
}
.sla-sig-type-input {
    width: 100%;
    max-width: 500px;
    height: 60px;
    background: rgba(0,0,0,0.2);
    border: 2px solid rgba(255,255,255,0.12);
    border-radius: 12px;
    color: #e2e8f0;
    font-size: 28px;
    font-weight: 300;
    font-style: italic;
    font-family: 'Georgia', 'Times New Roman', serif;
    padding: 0 20px;
    outline: none;
    transition: border-color 0.2s;
}
.sla-sig-type-input:focus {
    border-color: rgba(6,182,212,0.5);
}
.sla-sig-toggle {
    display: flex;
    gap: 4px;
    margin-bottom: 14px;
}
.sla-sig-toggle-btn {
    padding: 8px 20px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    font-family: 'Montserrat', sans-serif;
    cursor: pointer;
    border: 1px solid rgba(255,255,255,0.12);
    background: rgba(255,255,255,0.04);
    color: var(--text-muted, #64748b);
    transition: all 0.2s;
    letter-spacing: 0.5px;
}
.sla-sig-toggle-btn.active {
    background: rgba(6,182,212,0.15);
    border-color: rgba(6,182,212,0.4);
    color: #22d3ee;
}

/* ── Info Banner ── */
.sla-info-banner {
    font-size: 12px;
    color: var(--text-muted, #64748b);
    line-height: 1.7;
    margin-bottom: 22px;
    padding: 14px 18px;
    background: rgba(6,182,212,0.04);
    border: 1px solid rgba(6,182,212,0.1);
    border-radius: 10px;
    display: flex;
    align-items: flex-start;
    gap: 12px;
}
.sla-info-banner i { color: rgba(6,182,212,0.5); font-size: 14px; margin-top: 1px; flex-shrink: 0; }

/* ── Checkbox ── */
.sla-checkbox-row {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 14px 0;
}
.sla-checkbox {
    width: 20px;
    height: 20px;
    min-width: 20px;
    border-radius: 5px;
    border: 2px solid rgba(255,255,255,0.2);
    background: rgba(0,0,0,0.2);
    cursor: pointer;
    -webkit-appearance: none;
    appearance: none;
    position: relative;
    transition: all 0.2s;
    margin-top: 1px;
}
.sla-checkbox:checked {
    background: rgba(6,182,212,0.3);
    border-color: #06b6d4;
}
.sla-checkbox:checked::after {
    content: '\f00c';
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    font-size: 11px;
    color: #22d3ee;
}
.sla-checkbox-label {
    font-size: 13px;
    color: var(--text-secondary, #94a3b8);
    line-height: 1.6;
    cursor: pointer;
}

/* ── Navigation Buttons ── */
.sla-nav-buttons {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 28px;
    padding-top: 20px;
    border-top: 1px solid rgba(255,255,255,0.06);
}
.sla-btn {
    padding: 12px 28px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 700;
    font-family: 'Montserrat', sans-serif;
    cursor: pointer;
    border: none;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
    letter-spacing: 0.5px;
}
.sla-btn-secondary {
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.12);
    color: var(--text-secondary, #94a3b8);
}
.sla-btn-secondary:hover {
    background: rgba(255,255,255,0.1);
    border-color: rgba(255,255,255,0.2);
}
.sla-btn-primary {
    background: linear-gradient(135deg, #06b6d4, #0ea5e9);
    color: #0a0e1a;
    box-shadow: 0 4px 16px rgba(6,182,212,0.3);
}
.sla-btn-primary:hover {
    box-shadow: 0 6px 24px rgba(6,182,212,0.45);
    transform: translateY(-1px);
}
.sla-btn-success {
    background: linear-gradient(135deg, #059669, #10b981);
    color: #fff;
    box-shadow: 0 4px 16px rgba(16,185,129,0.3);
}
.sla-btn-success:hover {
    box-shadow: 0 6px 24px rgba(16,185,129,0.45);
    transform: translateY(-1px);
}

/* ── ATP Branding Block ── */
.sla-atp-brand {
    text-align: center;
    padding: 24px 20px;
    margin-bottom: 20px;
    background: rgba(6,182,212,0.03);
    border: 1px solid rgba(6,182,212,0.08);
    border-radius: 14px;
}
.sla-atp-name {
    font-size: 20px;
    font-weight: 900;
    color: #f1f5f9;
    letter-spacing: 1px;
    margin-bottom: 4px;
}
.sla-atp-trading {
    font-size: 12px;
    font-weight: 600;
    color: var(--text-muted, #64748b);
    letter-spacing: 1.5px;
    text-transform: uppercase;
}
.sla-atp-contact {
    font-size: 12px;
    color: var(--text-muted, #64748b);
    margin-top: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
    flex-wrap: wrap;
}
.sla-atp-contact span { display: flex; align-items: center; gap: 6px; }
.sla-atp-contact i { color: rgba(6,182,212,0.5); font-size: 11px; }

/* ── Declaration Block ── */
.sla-declaration {
    background: rgba(0,0,0,0.15);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 12px;
    padding: 20px 24px;
    margin: 16px 0;
}
.sla-declaration p {
    color: var(--text-secondary, #94a3b8);
    font-size: 13px;
    line-height: 1.8;
    margin: 0 0 8px 0;
}
.sla-declaration p:last-child { margin-bottom: 0; }

/* ── Animations ── */
@@keyframes sla-fade-in {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
}
.sla-panel.active .sla-section {
    animation: sla-fade-in 0.35s ease both;
}
.sla-panel.active .sla-section:nth-child(2)  { animation-delay: 0.04s; }
.sla-panel.active .sla-section:nth-child(3)  { animation-delay: 0.08s; }
.sla-panel.active .sla-section:nth-child(4)  { animation-delay: 0.12s; }
.sla-panel.active .sla-section:nth-child(5)  { animation-delay: 0.16s; }

@@keyframes sla-card-pop {
    from { opacity: 0; transform: scale(0.95); }
    to   { opacity: 1; transform: scale(1); }
}
.sla-price-card {
    animation: sla-card-pop 0.4s ease both;
}
.sla-price-card:nth-child(2) { animation-delay: 0.06s; }
.sla-price-card:nth-child(3) { animation-delay: 0.12s; }
.sla-price-card:nth-child(4) { animation-delay: 0.18s; }
.sla-price-card:nth-child(5) { animation-delay: 0.24s; }

/* ── Responsive ── */
/* ── Addon Checkbox Toggle ── */
.sla-addon-card {
    cursor: pointer;
    position: relative;
}
.sla-addon-check { display: none; }
.sla-addon-tick {
    width: 22px;
    height: 22px;
    min-width: 22px;
    border: 2px solid rgba(255,255,255,0.15);
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: transparent;
    font-size: 10px;
    transition: all 0.25s cubic-bezier(0.4,0,0.2,1);
    flex-shrink: 0;
}
.sla-addon-card.checked {
    border-color: var(--card-accent);
    background: rgba(255,255,255,0.06);
    box-shadow: 0 0 20px rgba(0,0,0,0.2), inset 0 0 0 1px rgba(255,255,255,0.05);
}
.sla-addon-card.checked .sla-addon-tick {
    border-color: var(--card-accent);
    background: var(--card-accent);
    color: #0a0e1a;
}

/* ── Promo Block Toggle ── */
.sla-promo-toggle {
    position: absolute;
    top: 16px;
    right: 16px;
    display: flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    z-index: 5;
    background: rgba(0,0,0,0.3);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 8px;
    padding: 5px 12px 5px 8px;
    transition: all 0.25s;
}
.sla-promo-toggle:hover {
    border-color: rgba(255,255,255,0.25);
    background: rgba(0,0,0,0.5);
}
.sla-promo-check { display: none; }
.sla-promo-toggle-box {
    width: 20px;
    height: 20px;
    min-width: 20px;
    border: 2px solid rgba(255,255,255,0.2);
    border-radius: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: transparent;
    font-size: 10px;
    transition: all 0.25s;
}
.sla-promo-toggle-label {
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: rgba(255,255,255,0.5);
    transition: color 0.2s;
}
.sla-promo-toggle.checked {
    border-color: rgba(16,185,129,0.4);
    background: rgba(16,185,129,0.15);
}
.sla-promo-toggle.checked .sla-promo-toggle-box {
    border-color: #10b981;
    background: #10b981;
    color: #0a0e1a;
}
.sla-promo-toggle.checked .sla-promo-toggle-label {
    color: #10b981;
}
.sla-promo-cell .sla-reg-promo,
.sla-promo-cell .sla-coida-promo,
.sla-promo-cell .sla-term-promo {
    position: relative;
}

/* ── Payslip Toggle ── */
.sla-payslip-toggle.checked {
    border-color: rgba(16,185,129,0.4) !important;
    background: rgba(16,185,129,0.15) !important;
}
.sla-payslip-toggle .sla-promo-toggle-box {
    border: 2px solid rgba(255,255,255,0.2);
    border-radius: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: transparent;
    transition: all 0.25s;
}
.sla-payslip-toggle.checked .sla-promo-toggle-box {
    border-color: #10b981;
    background: #10b981;
    color: #0a0e1a;
}

/* ── Billing Invoice Tab ── */
.sla-billing-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 28px;
    padding-bottom: 20px;
    border-bottom: 1px solid rgba(255,255,255,0.08);
}
.sla-billing-logo {
    display: flex;
    align-items: center;
    gap: 14px;
}
.sla-billing-logo-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: linear-gradient(135deg, rgba(6,182,212,0.2), rgba(14,165,233,0.1));
    border: 1px solid rgba(6,182,212,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #06b6d4;
    font-size: 20px;
}
.sla-billing-company {
    font-size: 20px;
    font-weight: 900;
    color: #f1f5f9;
    letter-spacing: 0.5px;
}
.sla-billing-subtitle {
    font-size: 11px;
    color: var(--text-muted, #64748b);
    letter-spacing: 0.3px;
    margin-top: 2px;
}
.sla-billing-doc-type { text-align: right; }
.sla-billing-doc-label {
    font-size: 11px;
    font-weight: 900;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #06b6d4;
    background: rgba(6,182,212,0.1);
    border: 1px solid rgba(6,182,212,0.2);
    padding: 5px 14px;
    border-radius: 6px;
    display: inline-block;
    margin-bottom: 6px;
}
.sla-billing-doc-date {
    font-size: 11px;
    color: var(--text-muted, #64748b);
}

.sla-billing-empty {
    text-align: center;
    padding: 48px 20px;
    color: var(--text-muted, #64748b);
    font-size: 14px;
    line-height: 1.6;
}
.sla-billing-empty i {
    font-size: 36px;
    display: block;
    margin-bottom: 14px;
    color: rgba(255,255,255,0.08);
}

.sla-billing-section-label {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    color: #06b6d4;
    margin: 24px 0 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.sla-billing-section-label:first-of-type { margin-top: 0; }
.sla-billing-section-label i { font-size: 12px; }

.sla-billing-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 8px;
}
.sla-billing-table thead th {
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: var(--text-muted, #64748b);
    padding: 10px 14px;
    border-bottom: 1px solid rgba(255,255,255,0.08);
    text-align: left;
}
.sla-billing-table tbody td {
    font-size: 13px;
    font-weight: 600;
    color: #e2e8f0;
    padding: 12px 14px;
    border-bottom: 1px solid rgba(255,255,255,0.04);
    vertical-align: middle;
}
.sla-billing-table tbody tr:last-child td { border-bottom: none; }
.sla-billing-table tbody td:first-child {
    font-size: 11px;
    color: var(--text-muted, #64748b);
    font-weight: 700;
}
.sla-billing-table tbody td:last-child {
    text-align: right;
    font-weight: 800;
    color: #22d3ee;
    font-family: 'Montserrat', sans-serif;
}

.sla-billing-totals {
    margin-top: 20px;
    border-top: 2px solid rgba(255,255,255,0.08);
    padding-top: 16px;
}
.sla-billing-total-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 14px;
    font-size: 13px;
    font-weight: 700;
    color: var(--text-secondary, #94a3b8);
}
.sla-billing-total-row span:last-child {
    font-family: 'Montserrat', sans-serif;
    font-weight: 800;
}
.sla-billing-vat {
    border-bottom: 1px solid rgba(255,255,255,0.06);
}
.sla-billing-grand {
    margin-top: 4px;
    background: linear-gradient(135deg, rgba(6,182,212,0.08), rgba(16,185,129,0.06));
    border: 1px solid rgba(6,182,212,0.2);
    border-radius: 12px;
    font-size: 16px !important;
}
.sla-billing-grand span:first-child {
    font-weight: 900 !important;
    color: #f1f5f9 !important;
    letter-spacing: 0.3px;
}
.sla-billing-grand span:last-child {
    font-size: 20px !important;
    font-weight: 900 !important;
    color: #22d3ee !important;
}

.sla-billing-note {
    margin-top: 20px;
    font-size: 11px;
    color: var(--text-muted, #64748b);
    line-height: 1.6;
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 10px;
    padding: 14px 16px;
    display: flex;
    gap: 10px;
    align-items: flex-start;
}
.sla-billing-note i {
    color: rgba(6,182,212,0.5);
    font-size: 14px;
    margin-top: 1px;
    flex-shrink: 0;
}

/* ── Invoice Table ── */
.sla-inv-table { margin-bottom: 0; }
.sla-inv-table thead th {
    background: rgba(255,255,255,0.02);
    font-size: 9px;
}
.sla-inv-row { transition: background 0.2s; }
.sla-inv-row:hover { background: rgba(255,255,255,0.03); }
.sla-inv-row.active {
    background: rgba(6,182,212,0.06);
}
.sla-inv-row.active td { color: #f1f5f9; }
.sla-inv-row.active .sla-inv-amt { color: #22d3ee; font-weight: 900; }
.sla-inv-row:not(.active) .sla-inv-amt { color: var(--text-muted, #64748b); }

.sla-inv-check {
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    margin: 0;
}
.sla-inv-check input { display: none; }

.sla-inv-radio {
    width: 20px;
    height: 20px;
    border: 2px solid rgba(255,255,255,0.15);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.25s;
    position: relative;
}
.sla-inv-radio::after {
    content: '';
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: transparent;
    transition: background 0.25s;
}
.sla-inv-check input:checked + .sla-inv-radio {
    border-color: #06b6d4;
    box-shadow: 0 0 12px rgba(6,182,212,0.3);
}
.sla-inv-check input:checked + .sla-inv-radio::after {
    background: #06b6d4;
}

.sla-inv-cb {
    width: 20px;
    height: 20px;
    border: 2px solid rgba(255,255,255,0.15);
    border-radius: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.25s;
    color: transparent;
    font-size: 10px;
}
.sla-inv-cb::after {
    content: '\f00c';
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
}
.sla-inv-check input:checked + .sla-inv-cb {
    border-color: #10b981;
    background: #10b981;
    color: #0a0e1a;
    box-shadow: 0 0 12px rgba(16,185,129,0.3);
}

.sla-inv-tag {
    display: inline-block;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.5px;
    color: var(--text-muted, #64748b);
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.06);
    padding: 2px 8px;
    border-radius: 4px;
    margin-left: 6px;
    vertical-align: middle;
}
.sla-inv-tag-pop {
    color: #10b981;
    background: rgba(16,185,129,0.08);
    border-color: rgba(16,185,129,0.15);
}

.sla-inv-qty {
    width: 52px;
    height: 30px;
    background: rgba(0,0,0,0.25);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 6px;
    color: #e2e8f0;
    font-size: 13px;
    font-weight: 700;
    text-align: center;
    font-family: 'Montserrat', sans-serif;
    outline: none;
    transition: border-color 0.2s;
    -moz-appearance: textfield;
}
.sla-inv-qty::-webkit-outer-spin-button,
.sla-inv-qty::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
.sla-inv-qty:focus {
    border-color: rgba(6,182,212,0.5);
    box-shadow: 0 0 0 2px rgba(6,182,212,0.1);
}

@media (max-width: 900px) {
    .sla-tab-info { display: none; }
    .sla-tab { justify-content: center; padding: 16px 10px; }
    .sla-wizard { border-radius: 12px; }
    .sla-pricing-grid { grid-template-columns: 1fr 1fr; }
    .sla-billing-header { flex-direction: column; gap: 16px; }
    .sla-billing-doc-type { text-align: left; }
}
@media (max-width: 640px) {
    .sla-form-row { grid-template-columns: 1fr; }
    .sla-pricing-grid { grid-template-columns: 1fr; }
    .sla-page-header { flex-wrap: wrap; }
    .sla-header-badge { margin-left: 0; margin-top: 10px; }
}
</style>
@endpush

@section('content')

@php $sla = $sla ?? null; @endphp

{{-- Page Header --}}
<div class="sla-page-header">
    <div class="sla-header-icon">
        <i class="fas fa-file-contract"></i>
    </div>
    <div>
        <h1 class="sla-header-title">{{ $sla ? 'Edit' : 'New' }} Engagement Letter</h1>
        <div class="sla-header-sub">Accounting Taxation and Payroll (Pty) Ltd &mdash; Service Level Agreement</div>
    </div>
    <div class="sla-header-badge">
        <i class="fas fa-shield-alt" style="margin-right:6px; font-size:9px;"></i>{{ $sla ? $sla->sla_reference : ($reference ?? 'ATP-SLA-DRAFT') }}
    </div>
</div>

{{-- Step Wizard Tabs --}}
<div class="sla-wizard" id="slaWizard">
    <button class="sla-tab active" data-tab="1" onclick="slaSwitchTab(1, this)">
        <div class="sla-tab-num" id="sla-tab-num-1"><i class="fas fa-user-tie"></i></div>
        <div class="sla-tab-info">
            <span class="sla-tab-step">Step 1</span>
            <span class="sla-tab-label">Client &amp; Signatory</span>
        </div>
    </button>
    <button class="sla-tab" data-tab="2" onclick="slaSwitchTab(2, this)">
        <div class="sla-tab-num" id="sla-tab-num-2"><i class="fas fa-gavel"></i></div>
        <div class="sla-tab-info">
            <span class="sla-tab-step">Step 2</span>
            <span class="sla-tab-label">Terms &amp; Conditions</span>
        </div>
    </button>
    <button class="sla-tab" data-tab="3" onclick="slaSwitchTab(3, this)">
        <div class="sla-tab-num" id="sla-tab-num-3"><i class="fas fa-boxes-stacked"></i></div>
        <div class="sla-tab-info">
            <span class="sla-tab-step">Step 3</span>
            <span class="sla-tab-label">Service Packages</span>
        </div>
    </button>
    <button class="sla-tab" data-tab="4" onclick="slaSwitchTab(4, this)">
        <div class="sla-tab-num" id="sla-tab-num-4"><i class="fas fa-file-invoice-dollar"></i></div>
        <div class="sla-tab-info">
            <span class="sla-tab-step">Step 4</span>
            <span class="sla-tab-label">Billing Invoice</span>
        </div>
    </button>
    <button class="sla-tab" data-tab="5" onclick="slaSwitchTab(5, this)">
        <div class="sla-tab-num" id="sla-tab-num-5"><i class="fas fa-university"></i></div>
        <div class="sla-tab-info">
            <span class="sla-tab-step">Step 5</span>
            <span class="sla-tab-label">Banking &amp; Mandate</span>
        </div>
    </button>
    <button class="sla-tab" data-tab="6" onclick="slaSwitchTab(6, this)">
        <div class="sla-tab-num" id="sla-tab-num-6"><i class="fas fa-signature"></i></div>
        <div class="sla-tab-info">
            <span class="sla-tab-step">Step 6</span>
            <span class="sla-tab-label">Declaration &amp; Signature</span>
        </div>
    </button>
</div>

<form method="POST" action="{{ $sla ? route('nexcore.clients.show.sla.update', [$client->id, $sla->id]) : route('nexcore.clients.show.sla.store', $client->id) }}" id="slaForm">
    @csrf
    @if($sla) @method('PUT') @endif

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- TAB 1: CLIENT & SIGNATORY DETAILS                      --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div class="sla-panel active" id="sla-panel-1">

    <div class="sla-atp-brand">
        <div class="sla-atp-name">Accounting Taxation &amp; Payroll (Pty) Ltd</div>
        <div class="sla-atp-trading">trading as ATP Services</div>
        <div class="sla-atp-contact">
            <span><i class="fas fa-envelope"></i> clients@atpservices.co.za</span>
            <span><i class="fas fa-phone"></i> 031 101 3876</span>
            <span><i class="fas fa-mobile-alt"></i> 064 507 2274</span>
        </div>
    </div>

    <div class="sla-info-banner">
        <i class="fas fa-info-circle"></i>
        <span>This Service Level Agreement is entered into between <strong>Accounting Taxation and Payroll (Pty) Ltd</strong> (hereinafter referred to as "ATP" or "ATP Services") and the Client identified below. Select a client to auto-populate their details from NexCore.</span>
    </div>

    {{-- Client Selector --}}
    <div class="sla-client-selector">
        <div class="sla-client-selector-title"><i class="fas fa-users"></i> Select Client</div>
        <div class="sla-form-row">
            <div class="sla-form-group" style="grid-column: 1 / -1; max-width: 500px;">
                <label class="sla-form-label">Client <span class="sla-required">*</span></label>
                <select class="sla-form-select" id="slaClientSelect" onchange="slaLoadClient(this.value)">
                    <option value="">-- Select a Client --</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ $client->id == $c->id ? 'selected' : '' }}>{{ $c->company_name }} ({{ $c->client_code }})</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Signatory Details --}}
    <div class="sla-section open" id="sla-acc-signatory">
        <div class="sla-section-header" onclick="slaToggle('sla-acc-signatory')">
            <span class="sla-section-code">SIG</span>
            <span class="sla-section-title">Signatory Details</span>
            <i class="fas fa-chevron-down sla-section-chevron"></i>
        </div>
        <div class="sla-section-body">
            <div class="sla-section-inner">
                <div class="sla-form-row">
                    <div class="sla-form-group">
                        <label class="sla-form-label">Full Names &amp; Surname <span class="sla-required">*</span></label>
                        <input type="text" name="signatory_name" class="sla-form-input" id="sla_signatory_name" value="{{ $sla?->signatory_name ?? '' }}" required>
                    </div>
                    <div class="sla-form-group">
                        <label class="sla-form-label">ID / Passport Number <span class="sla-required">*</span></label>
                        <input type="text" name="signatory_id_number" class="sla-form-input" id="sla_signatory_id_number" value="{{ $sla?->signatory_id_number ?? '' }}">
                    </div>
                </div>
                <div class="sla-form-row">
                    <div class="sla-form-group">
                        <label class="sla-form-label">Email Address <span class="sla-required">*</span></label>
                        <input type="email" name="signatory_email" class="sla-form-input" id="sla_signatory_email" value="{{ $sla?->signatory_email ?? '' }}" required>
                    </div>
                    <div class="sla-form-group">
                        <label class="sla-form-label">Cellphone Number <span class="sla-required">*</span></label>
                        <input type="text" name="signatory_cellphone" class="sla-form-input" id="sla_signatory_cellphone" value="{{ $sla?->signatory_cellphone ?? '' }}" required placeholder="10 digit mobile number">
                    </div>
                </div>
                <div class="sla-form-row">
                    <div class="sla-form-group">
                        <label class="sla-form-label">Designation / Title</label>
                        <input type="text" name="signatory_designation" class="sla-form-input" id="sla_signatory_designation" value="{{ $sla?->signatory_designation ?? '' }}">
                    </div>
                    <div class="sla-form-group">
                        <label class="sla-form-label">Province</label>
                        <select name="province" class="sla-form-select" id="sla_province">
                            <option value="">Select Province</option>
                            @foreach(['Eastern Cape','Free State','Gauteng','KwaZulu-Natal','Limpopo','Mpumalanga','Northern Cape','North West','Western Cape'] as $p)
                                <option value="{{ $p }}" {{ ($sla?->province ?? '') === $p ? 'selected' : '' }}>{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Emergency Contact --}}
    <div class="sla-section" id="sla-acc-emergency">
        <div class="sla-section-header" onclick="slaToggle('sla-acc-emergency')">
            <span class="sla-section-code">EMC</span>
            <span class="sla-section-title">Emergency Contact / Next of Kin</span>
            <i class="fas fa-chevron-down sla-section-chevron"></i>
        </div>
        <div class="sla-section-body">
            <div class="sla-section-inner">
                <div class="sla-info-banner" style="margin-bottom:16px;">
                    <i class="fas fa-shield-alt"></i>
                    <span>To ensure continuity and client wellbeing, we invite you to provide a trusted individual's details for use in urgent circumstances. This information is used solely when you are unreachable and a time-sensitive matter requires attention.</span>
                </div>
                <div class="sla-form-row">
                    <div class="sla-form-group">
                        <label class="sla-form-label">Name and Surname <span class="sla-required">*</span></label>
                        <input type="text" name="emergency_name" class="sla-form-input" value="{{ $sla?->emergency_name ?? '' }}">
                    </div>
                    <div class="sla-form-group">
                        <label class="sla-form-label">Relationship <span class="sla-required">*</span></label>
                        <input type="text" name="emergency_relationship" class="sla-form-input" value="{{ $sla?->emergency_relationship ?? '' }}">
                    </div>
                </div>
                <div class="sla-form-row">
                    <div class="sla-form-group">
                        <label class="sla-form-label">Contact Number <span class="sla-required">*</span></label>
                        <input type="text" name="emergency_phone" class="sla-form-input" value="{{ $sla?->emergency_phone ?? '' }}">
                    </div>
                    <div class="sla-form-group">
                        <label class="sla-form-label">Email Address</label>
                        <input type="email" name="emergency_email" class="sla-form-input" value="{{ $sla?->emergency_email ?? '' }}">
                    </div>
                </div>
                <div class="sla-checkbox-row">
                    <input type="checkbox" name="emergency_consent" value="1" class="sla-checkbox" id="sla_emergency_consent" {{ $sla?->emergency_consent ? 'checked' : '' }}>
                    <label class="sla-checkbox-label" for="sla_emergency_consent">I authorise ATP Services to contact the individual listed above solely under the conditions stated above.</label>
                </div>
            </div>
        </div>
    </div>

    <div class="sla-nav-buttons">
        <div></div>
        <button type="button" class="sla-btn sla-btn-primary" onclick="slaSwitchTab(2)">
            Continue to Terms <i class="fas fa-arrow-right"></i>
        </button>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- TAB 2: TERMS & CONDITIONS                              --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div class="sla-panel" id="sla-panel-2">

    <div class="sla-info-banner">
        <i class="fas fa-gavel"></i>
        <span>The following Terms and Conditions govern the professional relationship between ATP Services and the Client. Please review each clause carefully before proceeding.</span>
    </div>

    {{-- Clause 1: Definitions --}}
    <div class="sla-section open" id="sla-tc-1">
        <div class="sla-section-header" onclick="slaToggle('sla-tc-1')">
            <span class="sla-section-code">CL.1</span>
            <span class="sla-section-title">Definitions</span>
            <i class="fas fa-chevron-down sla-section-chevron"></i>
        </div>
        <div class="sla-section-body">
            <div class="sla-section-inner">
                <div class="sla-clause-text">
                    <div class="sla-sub-clause">1.1 "ATP" and "ATP Services" shall refer to Accounting Taxation and Payroll (Pty) Ltd, including its subsidiaries, affiliates, and associated business entities.</div>
                    <div class="sla-sub-clause">1.2 For the purposes of calculating the monthly service fee, "Gross Revenue" is defined as the aggregate of all financial inflows into the entity. This encompasses, without limitation, all trading revenue, interest income, capital contributions, investment returns, proceeds from asset disposals, and all other monetary receipts.</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Clause 2: Agreement Duration --}}
    <div class="sla-section" id="sla-tc-2">
        <div class="sla-section-header" onclick="slaToggle('sla-tc-2')">
            <span class="sla-section-code">CL.2</span>
            <span class="sla-section-title">Agreement Duration</span>
            <i class="fas fa-chevron-down sla-section-chevron"></i>
        </div>
        <div class="sla-section-body">
            <div class="sla-section-inner">
                <div class="sla-clause-text">
                    <div class="sla-sub-clause">2.1 The service engagement detailed in clause 4.1 shall take effect for an initial period of one (1) calendar month, commencing on the first day of the month immediately following the execution date of this agreement.</div>
                    <div class="sla-sub-clause">2.2 Upon expiry of the initial term, this agreement shall automatically renew on a month-to-month basis and shall remain in force until lawfully terminated in accordance with the termination provisions set out in clause 3.</div>
                    <div class="sla-sub-clause">2.3 ATP Services accepts no responsibility or liability for any outstanding obligations, penalties, or arrears that the Client or their entity may owe to any regulatory authority, whether arising before, during, or after the term of this agreement.</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Clause 3: Termination Provisions --}}
    <div class="sla-section" id="sla-tc-3">
        <div class="sla-section-header" onclick="slaToggle('sla-tc-3')">
            <span class="sla-section-code">CL.3</span>
            <span class="sla-section-title">Termination Provisions</span>
            <i class="fas fa-chevron-down sla-section-chevron"></i>
        </div>
        <div class="sla-section-body">
            <div class="sla-section-inner">
                <div class="sla-clause-text">
                    <div class="sla-sub-clause">3.1 The Client acknowledges that this agreement shall remain binding during the initial term unless ATP Services has materially breached its obligations hereunder. In such event, the Client shall provide ATP Services with seven (7) business days' written notice to remedy the breach. Should ATP Services fail to remedy the breach within the stipulated period, the Client may terminate this agreement forthwith.</div>
                    <div class="sla-sub-clause">3.2 Following the initial term, either party may terminate this agreement by providing at least one (1) calendar month's written notice of termination.</div>
                    <div class="sla-sub-clause">3.3 Any notice of termination must be submitted in writing via electronic mail to: <strong>clients@atpservices.co.za</strong></div>
                    <div class="sla-sub-clause">3.4 ATP Services reserves the right to terminate this agreement with immediate effect where the Client is found to be non-compliant with South African legislation, or where ATP Services reasonably determines that the Client's requirements fall outside the scope of services that can be effectively delivered under this agreement.</div>
                    <div class="sla-sub-clause">3.5 All work product, deliverables, and professional outputs generated by ATP Services in relation to the Client's financial affairs shall remain the intellectual property of ATP Services until all outstanding fees and invoices have been settled in full, including any applicable software licensing fees.</div>
                    <div class="sla-sub-clause">3.6 In the event of three (3) consecutive missed payments, ATP Services reserves the right to suspend all services and initiate recovery proceedings for any amounts outstanding. Access to accounting software and systems shall be suspended until such time as the account is brought to good standing.</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Clause 4: Client Obligations --}}
    <div class="sla-section" id="sla-tc-4">
        <div class="sla-section-header" onclick="slaToggle('sla-tc-4')">
            <span class="sla-section-code">CL.4</span>
            <span class="sla-section-title">Client Obligations</span>
            <i class="fas fa-chevron-down sla-section-chevron"></i>
        </div>
        <div class="sla-section-body">
            <div class="sla-section-inner">
                <div class="sla-clause-text">
                    <p>By executing this agreement, the Client:</p>
                    <div class="sla-sub-clause">4.1 Acknowledges that where the Client's entity holds a "non-compliant" status with the Companies and Intellectual Property Commission (CIPC), it is the Client's sole responsibility to prioritise the payment and resolution of all outstanding submissions and returns.</div>
                    <div class="sla-sub-clause">4.2 Acknowledges that where the Client's SARS compliance profile reflects a "non-compliant" status, it is the Client's sole responsibility to prioritise the resolution of all outstanding submissions and returns.</div>
                    <div class="sla-sub-clause">4.3 Acknowledges that simultaneous non-compliance with both CIPC and SARS constitutes a material breach of this agreement, entitling ATP Services to terminate and resign as the appointed accounting and tax practitioner.</div>
                    <div class="sla-sub-clause">4.4 Acknowledges that ATP Services is bound by professional and ethical obligations under applicable accounting legislation and regulatory bodies. Non-adherence by the Client to these standards may result in the immediate termination of services.</div>
                    <div class="sla-sub-clause">4.5 Acknowledges that failure to provide required documentation, records, or bank statements in a timely manner may impede ATP Services' ability to perform under this agreement, and the Client shall bear responsibility for any resultant penalties.</div>
                    <div class="sla-sub-clause">4.6 Undertakes to notify ATP Services of any changes to contact information within five (5) business days of such change occurring.</div>
                    <div class="sla-sub-clause">4.7 Undertakes to provide all required documentation and information to ATP Services no later than five (5) business days prior to any submission deadline. ATP Services shall not be held liable for penalties arising from the Client's failure to comply with this provision.</div>
                    <div class="sla-sub-clause">4.8 ATP Services shall bear no liability for non-compliance with regulatory directives pertaining to the personal tax affairs of company directors.</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Clause 5: Transfer --}}
    <div class="sla-section" id="sla-tc-5">
        <div class="sla-section-header" onclick="slaToggle('sla-tc-5')">
            <span class="sla-section-code">CL.5</span>
            <span class="sla-section-title">Transfer of Agreement</span>
            <i class="fas fa-chevron-down sla-section-chevron"></i>
        </div>
        <div class="sla-section-body">
            <div class="sla-section-inner">
                <div class="sla-clause-text">
                    <div class="sla-sub-clause">5.1 In the event that the Client disposes of their business as a going concern during the subsistence of this agreement, this engagement shall transfer to and be binding upon the successor entity for the remainder of the agreement term, as though the agreement had been originally executed by the new proprietor.</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Clause 6: Confidentiality --}}
    <div class="sla-section" id="sla-tc-6">
        <div class="sla-section-header" onclick="slaToggle('sla-tc-6')">
            <span class="sla-section-code">CL.6</span>
            <span class="sla-section-title">Confidentiality</span>
            <i class="fas fa-chevron-down sla-section-chevron"></i>
        </div>
        <div class="sla-section-body">
            <div class="sla-section-inner">
                <div class="sla-clause-text">
                    <div class="sla-sub-clause">6.1 Both parties acknowledge that the performance of this agreement will necessitate the exchange of proprietary, sensitive, and confidential information, including but not limited to financial data, business strategies, and personal records. All such information and related documentation shall be treated as strictly confidential.</div>
                    <div class="sla-sub-clause">6.2 Neither party shall disclose confidential information to any third party, except where such disclosure is reasonably necessary for ATP Services to deliver the contracted services, or where required by law. The terms of this agreement shall themselves be treated as confidential.</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Clause 7: POPIA --}}
    <div class="sla-section" id="sla-tc-7">
        <div class="sla-section-header" onclick="slaToggle('sla-tc-7')">
            <span class="sla-section-code">CL.7</span>
            <span class="sla-section-title">Protection of Personal Information (POPIA)</span>
            <i class="fas fa-chevron-down sla-section-chevron"></i>
        </div>
        <div class="sla-section-body">
            <div class="sla-section-inner">
                <div class="sla-clause-text">
                    <div class="sla-sub-clause">7.1 ATP Services is committed to safeguarding the Client's privacy and ensuring that all personal information is processed lawfully, transparently, and securely in accordance with the Protection of Personal Information Act 4 of 2013 (POPIA) and all other applicable legislation. By executing this agreement, the Client consents to receiving periodic communications regarding legislative developments and updates to ATP Services' product and service offerings.</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Clause 8: Service Scope --}}
    <div class="sla-section" id="sla-tc-8">
        <div class="sla-section-header" onclick="slaToggle('sla-tc-8')">
            <span class="sla-section-code">CL.8</span>
            <span class="sla-section-title">Service Scope and Annexures</span>
            <i class="fas fa-chevron-down sla-section-chevron"></i>
        </div>
        <div class="sla-section-body">
            <div class="sla-section-inner">
                <div class="sla-clause-text">
                    <div class="sla-sub-clause">8.1 Upon execution of this agreement, the Client agrees to the applicable fees set out in the following annexures, each of which forms an integral part of this agreement:
                        <br><br>
                        <strong>Annexure A:</strong> Client Information Sheet<br>
                        <strong>Annexure B:</strong> Service Package Selection<br>
                        <strong>Annexure C:</strong> Debit Order Authorisation<br>
                        <strong>Annexure D:</strong> Monthly Fee Structure
                    </div>
                    <div class="sla-sub-clause">Any amendments to the selected services or fee structures during the contract period, requested by the Client and accepted by ATP Services, shall be documented as an addendum and shall form a binding part of this agreement upon the Client's written acceptance.</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Clause 9: Non-Solicitation --}}
    <div class="sla-section" id="sla-tc-9">
        <div class="sla-section-header" onclick="slaToggle('sla-tc-9')">
            <span class="sla-section-code">CL.9</span>
            <span class="sla-section-title">Non-Solicitation of Personnel</span>
            <i class="fas fa-chevron-down sla-section-chevron"></i>
        </div>
        <div class="sla-section-body">
            <div class="sla-section-inner">
                <div class="sla-clause-text">
                    <div class="sla-sub-clause">9.1 Both parties mutually agree, on behalf of themselves and their associated entities, that they shall not directly or indirectly solicit, recruit, or engage any employee, contractor, or agent of the other party, in any capacity whatsoever, during the term of this agreement and for a period of twenty-four (24) months following its termination. Any breach of this undertaking shall entitle the aggrieved party to enforce applicable restraint provisions and seek appropriate legal recourse.</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Clause 10: Fees & Payment --}}
    <div class="sla-section" id="sla-tc-10">
        <div class="sla-section-header" onclick="slaToggle('sla-tc-10')">
            <span class="sla-section-code">CL.10</span>
            <span class="sla-section-title">Fees and Payment Terms</span>
            <i class="fas fa-chevron-down sla-section-chevron"></i>
        </div>
        <div class="sla-section-body">
            <div class="sla-section-inner">
                <div class="sla-clause-text">
                    <div class="sla-sub-clause">10.1 The Client agrees to a minimum monthly subscription fee as specified in the selected Service Package (Annexure B). This fee is due and payable on a monthly basis for the entire duration of this agreement.</div>
                    <div class="sla-sub-clause">10.2 The Client agrees to the monthly retainer as detailed in Annexure B and the debit order mandate in Annexure C.</div>
                    <div class="sla-sub-clause">10.3 Monthly retainers are payable in advance on the last business day of each month for the following month, via the authorised debit order drawn against the Client's nominated bank account. The initial payment shall be due on the first day following the execution of this agreement.</div>
                    <div class="sla-sub-clause">10.4 All invoiced amounts must be settled on or before the stated due date.</div>
                    <div class="sla-sub-clause">10.5 ATP Services requires advance payment for all services before commencement of work.</div>
                    <div class="sla-sub-clause">10.6 Where a debit order collection is unsuccessful, a subsequent collection attempt shall be made during the following processing cycle.</div>
                    <div class="sla-sub-clause">10.7 An administration fee of R172.50 shall be levied for each returned or unpaid debit order.</div>
                    <div class="sla-sub-clause">10.8 Interest at a rate of 2% per month shall be applied to any account remaining in arrears for 30 days or more, in accordance with the National Credit Act 34 of 2005.</div>
                    <div class="sla-sub-clause">10.9 Accounts not settled within 30 days from the invoice due date shall be deemed overdue. An administrative and handling surcharge of up to 35% may be applied to the outstanding balance. ATP Services reserves the right to refer overdue accounts to a collection agency, with all associated costs borne by the Client.</div>
                    <div class="sla-sub-clause">10.10 The Client acknowledges that all payment obligations under this agreement are the joint and several responsibility of the entity and its listed directors.</div>
                    <div class="sla-sub-clause">10.11 Individual tax returns shall only be submitted to SARS upon receipt of full payment for services rendered.</div>
                    <div class="sla-sub-clause">10.12 Should SARS initiate an audit of the Client's tax return or VAT submission, an additional fee of R450.00 (excluding VAT) shall apply. The same fee applies to disputes lodged with SARS on behalf of the Client.</div>
                    <div class="sla-sub-clause">10.13 The Client agrees to utilise the accounting software platform endorsed by ATP Services for the duration of this agreement. The software subscription is a mandatory recurring charge, billed separately. ATP Services retains ownership of the software platform and grants the Client access through the ATP Services environment, contingent upon the Client's account being in good standing.</div>
                    <div class="sla-sub-clause">10.14 CIPC fees and statutory charges shall be invoiced on an ad-hoc basis and are payable in accordance with the standard payment terms of this agreement.</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Clause 11: Credit Management --}}
    <div class="sla-section" id="sla-tc-11">
        <div class="sla-section-header" onclick="slaToggle('sla-tc-11')">
            <span class="sla-section-code">CL.11</span>
            <span class="sla-section-title">Credit Management</span>
            <i class="fas fa-chevron-down sla-section-chevron"></i>
        </div>
        <div class="sla-section-body">
            <div class="sla-section-inner">
                <div class="sla-clause-text">
                    <p>The Client agrees that:</p>
                    <div class="sla-sub-clause">11.1 This engagement may be subject to a credit assessment with one or more registered credit bureaux prior to commencement of services.</div>
                    <div class="sla-sub-clause">11.2 ATP Services may periodically review the Client's payment history through relevant credit bureaux.</div>
                    <div class="sla-sub-clause">11.3 All invoiced amounts must be settled on or before the stated due date.</div>
                    <div class="sla-sub-clause">11.4 Persistent non-payment may result in the Client's information being reported to a credit bureau or equivalent body for adverse listing.</div>
                    <div class="sla-sub-clause">11.5 Overdue accounts may be referred to a collection agency, with all associated recovery costs being for the Client's account.</div>
                    <div class="sla-sub-clause">11.6 ATP Services requires advance payment before commencement of any requested services.</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Clause 12: General --}}
    <div class="sla-section" id="sla-tc-12">
        <div class="sla-section-header" onclick="slaToggle('sla-tc-12')">
            <span class="sla-section-code">CL.12</span>
            <span class="sla-section-title">General Provisions</span>
            <i class="fas fa-chevron-down sla-section-chevron"></i>
        </div>
        <div class="sla-section-body">
            <div class="sla-section-inner">
                <div class="sla-clause-text">
                    <div class="sla-sub-clause">12.1 The Client acknowledges that while ATP Services may recommend various strategies and processes during the course of this engagement, the Client retains sole responsibility for the final decision to adopt and implement any such strategies.</div>
                    <div class="sla-sub-clause">12.2 This agreement constitutes the entire agreement between the parties. No variation, representation, or warranty shall be binding unless recorded in writing and signed by authorised representatives of both parties. Only a director or senior officer of ATP Services is authorised to execute amendments to this agreement.</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Clause 13: Disclaimer --}}
    <div class="sla-section" id="sla-tc-13">
        <div class="sla-section-header" onclick="slaToggle('sla-tc-13')">
            <span class="sla-section-code">CL.13</span>
            <span class="sla-section-title">Disclaimer on Unlawful or Irregular Activities</span>
            <i class="fas fa-chevron-down sla-section-chevron"></i>
        </div>
        <div class="sla-section-body">
            <div class="sla-section-inner">
                <div class="sla-clause-text">
                    <div class="sla-sub-clause">13.1 Neither ATP Services nor its employees, agents, or affiliates shall bear any liability for business dealings, transactions, or activities conducted by the Client that are unlawful, unethical, or in contravention of applicable legislation, including but not limited to the Companies Act 71 of 2008, the Financial Intelligence Centre Act 38 of 2001 (FICA), the Prevention of Organised Crime Act 121 of 1998 (POCA), and the Tax Administration Act 28 of 2011.</div>
                    <div class="sla-sub-clause">13.2 The role of ATP Services is strictly limited to the provision of professional accounting, taxation, and compliance services based on information furnished by the Client. ATP Services does not undertake to verify the legality or ethical standing of the Client's business operations beyond its statutory obligations.</div>
                    <div class="sla-sub-clause">13.3 The Client indemnifies and holds ATP Services harmless against any claims, losses, damages, or liabilities arising from or connected to such activities, including any regulatory investigations or criminal proceedings.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="sla-nav-buttons">
        <button type="button" class="sla-btn sla-btn-secondary" onclick="slaSwitchTab(1)">
            <i class="fas fa-arrow-left"></i> Back
        </button>
        <button type="button" class="sla-btn sla-btn-primary" onclick="slaSwitchTab(3)">
            Continue to Packages <i class="fas fa-arrow-right"></i>
        </button>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- TAB 3: SERVICE PACKAGES (ADDENDUM A + B)                --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div class="sla-panel" id="sla-panel-3">

    <div class="sla-info-banner">
        <i class="fas fa-boxes-stacked"></i>
        <span>Complete the Client Information Sheet (Annexure A) and select the appropriate Service Package (Annexure B). The selected package determines the scope of services and monthly retainer fee.</span>
    </div>

    {{-- Annexure A: Client Information --}}
    <div class="sla-section open" id="sla-annex-a">
        <div class="sla-section-header" onclick="slaToggle('sla-annex-a')">
            <span class="sla-section-code">ANN.A</span>
            <span class="sla-section-title">Annexure A: Client Information Sheet</span>
            <i class="fas fa-chevron-down sla-section-chevron"></i>
        </div>
        <div class="sla-section-body">
            <div class="sla-section-inner">
                <div class="sla-form-row">
                    <div class="sla-form-group">
                        <label class="sla-form-label">Tax Reference Number</label>
                        <input type="text" name="tax_reference_number" class="sla-form-input" id="sla_tax_reference_number" value="{{ $sla?->tax_reference_number ?? '' }}">
                    </div>
                    <div class="sla-form-group">
                        <label class="sla-form-label">COIDA / RMA Registration</label>
                        <input type="text" name="coida_rma_number" class="sla-form-input" id="sla_coida_rma_number" value="{{ $sla?->coida_rma_number ?? '' }}">
                    </div>
                </div>
                <div class="sla-form-row">
                    <div class="sla-form-group">
                        <label class="sla-form-label">VAT Number</label>
                        <input type="text" name="vat_number" class="sla-form-input" id="sla_vat_number" value="{{ $sla?->vat_number ?? '' }}">
                    </div>
                    <div class="sla-form-group">
                        <label class="sla-form-label">PAYE Number</label>
                        <input type="text" name="paye_number" class="sla-form-input" id="sla_paye_number" value="{{ $sla?->paye_number ?? '' }}">
                    </div>
                </div>
                <div class="sla-form-row">
                    <div class="sla-form-group">
                        <label class="sla-form-label">UIF Number</label>
                        <input type="text" name="uif_number" class="sla-form-input" id="sla_uif_number" value="{{ $sla?->uif_number ?? '' }}">
                    </div>
                    <div class="sla-form-group">
                        <label class="sla-form-label">Applying On Behalf Of</label>
                        <select name="applying_for" class="sla-form-select">
                            <option value="company" {{ ($sla?->applying_for ?? 'company') === 'company' ? 'selected' : '' }}>A Company</option>
                            <option value="individual" {{ ($sla?->applying_for ?? '') === 'individual' ? 'selected' : '' }}>Myself (Individual)</option>
                        </select>
                    </div>
                </div>
                <div class="sla-form-row">
                    <div class="sla-form-group">
                        <label class="sla-form-label">Company Registration Number</label>
                        <input type="text" name="company_reg_number" class="sla-form-input" id="sla_company_reg_number" value="{{ $sla?->company_reg_number ?? '' }}">
                    </div>
                    <div class="sla-form-group">
                        <label class="sla-form-label">Business Name</label>
                        <input type="text" name="business_name" class="sla-form-input" id="sla_business_name" value="{{ $sla?->business_name ?? '' }}">
                    </div>
                </div>
                <div class="sla-form-row">
                    <div class="sla-form-group">
                        <label class="sla-form-label">Nature of Business</label>
                        <input type="text" name="nature_of_business" class="sla-form-input" value="{{ $sla?->nature_of_business ?? '' }}">
                    </div>
                    <div class="sla-form-group">
                        <label class="sla-form-label">Work Telephone Number</label>
                        <input type="text" name="work_telephone" class="sla-form-input" value="{{ $sla?->work_telephone ?? '' }}">
                    </div>
                </div>
                <div class="sla-form-row">
                    <div class="sla-form-group" style="grid-column: 1 / -1;">
                        <label class="sla-form-label">Physical Address</label>
                        <textarea name="physical_address" class="sla-form-textarea" id="sla_physical_address" rows="2">{{ $sla?->physical_address ?? '' }}</textarea>
                    </div>
                </div>
                <div class="sla-form-row">
                    <div class="sla-form-group" style="grid-column: 1 / -1;">
                        <label class="sla-form-label">Postal Address</label>
                        <textarea name="postal_address" class="sla-form-textarea" rows="2">{{ $sla?->postal_address ?? '' }}</textarea>
                    </div>
                </div>
                <div class="sla-form-row">
                    <div class="sla-form-group">
                        <label class="sla-form-label">Marital Status</label>
                        <select name="marital_status" class="sla-form-select">
                            <option value="">Select</option>
                            <option value="Not Married" {{ ($sla?->marital_status ?? '') === 'Not Married' ? 'selected' : '' }}>Not Married</option>
                            <option value="Married in Community of Property" {{ ($sla?->marital_status ?? '') === 'Married in Community of Property' ? 'selected' : '' }}>Married in Community of Property</option>
                            <option value="Married out of Community of Property" {{ ($sla?->marital_status ?? '') === 'Married out of Community of Property' ? 'selected' : '' }}>Married out of Community of Property</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Annexure B: Service Package Selection --}}
    <div class="sla-section open" id="sla-annex-b">
        <div class="sla-section-header" onclick="slaToggle('sla-annex-b')">
            <span class="sla-section-code">ANN.B</span>
            <span class="sla-section-title">Annexure B: Service Package Selection</span>
            <i class="fas fa-chevron-down sla-section-chevron"></i>
        </div>
        <div class="sla-section-body">
            <div class="sla-section-inner">

                <div class="sla-value-prop">
                    <div class="sla-value-prop-title">Only 1% of Your Turnover. Never More.</div>
                    <div class="sla-value-prop-subtitle">One Affordable Rate</div>
                    <div class="sla-value-prop-text">
                        We believe the days of hourly rates for premium accounting are a thing of the past. Unlike many other run-of-the-mill accounting services, we choose to steer away from the antiquated hourly model in favour of a much more flexible alternative.<br><br>
                        Our fees are based on a small fraction of your turnover and we never include any hidden fees. This approach allows small businesses to receive high-quality accounting services at a reasonable price. The smaller your turnover amount, the less you pay.<br><br>
                        <strong style="color:#22d3ee;">Simple as that.</strong>
                    </div>
                </div>

                <div class="sla-info-banner" style="margin-bottom:16px;">
                    <i class="fas fa-hand-pointer"></i>
                    <span>Select one of the following service packages. Each package is tailored to different business needs. All fees listed are exclusive of VAT and the mandatory monthly accounting software subscription.</span>
                </div>

                <input type="hidden" name="selected_package" id="sla_selected_package" value="{{ $sla?->selected_package ?? '' }}">

                <div class="sla-pricing-grid">
                    {{-- Card 1: Starter --}}
                    <div class="sla-price-card {{ ($sla?->selected_package ?? '') === 'starter' ? 'selected' : '' }}" style="--card-accent: #06b6d4;" onclick="slaSelectPackage('starter', this)">
                        <div class="sla-price-check"><i class="fas fa-check"></i></div>
                        <div class="sla-price-tier">Tier 1</div>
                        <div class="sla-price-name">Starter</div>
                        <div class="sla-price-amount"><span class="sla-price-currency">R</span>749</div>
                        <div class="sla-price-period">per month excl. VAT</div>
                        <div class="sla-price-turnover"><i class="fas fa-chart-line" style="margin-right:4px; font-size:9px;"></i> Turnover up to R750K p.a.</div>
                        <div class="sla-price-divider"></div>
                        <ul class="sla-price-features">
                            <li><i class="fas fa-check"></i> Monthly Bookkeeping</li>
                            <li><i class="fas fa-check"></i> Monthly PAYE Submission</li>
                            <li><i class="fas fa-check"></i> Annual Company Tax Return (ITR14)</li>
                            <li><i class="fas fa-check"></i> Trial Balance &amp; Report</li>
                        </ul>
                        <div class="sla-price-consult"><i class="fas fa-building"></i> 1 x 30 Min In-Office Consultation</div>
                        <div class="sla-price-excludes">* Excludes monthly accounting software fees, COIDA, CIPC Annual Return Fees &amp; Submissions.</div>
                    </div>

                    {{-- Card 2: Growth --}}
                    <div class="sla-price-card {{ ($sla?->selected_package ?? '') === 'growth' ? 'selected' : '' }}" style="--card-accent: #3b82f6;" onclick="slaSelectPackage('growth', this)">
                        <div class="sla-price-check"><i class="fas fa-check"></i></div>
                        <div class="sla-price-tier">Tier 2</div>
                        <div class="sla-price-name">Growth</div>
                        <div class="sla-price-amount"><span class="sla-price-currency">R</span>1,499</div>
                        <div class="sla-price-period">per month excl. VAT</div>
                        <div class="sla-price-turnover"><i class="fas fa-chart-line" style="margin-right:4px; font-size:9px;"></i> Turnover up to R1.5M p.a.</div>
                        <div class="sla-price-divider"></div>
                        <ul class="sla-price-features">
                            <li><i class="fas fa-check"></i> Everything in Starter</li>
                            <li><i class="fas fa-check"></i> Monthly VAT Submission</li>
                            <li><i class="fas fa-check"></i> Provisional Tax Returns (IRP6)</li>
                            <li><i class="fas fa-check"></i> Annual Financial Statements</li>
                            <li><i class="fas fa-check"></i> Monthly Tax Structuring</li>
                        </ul>
                        <div class="sla-price-consult"><i class="fas fa-building"></i> 1 x 60 Min In-Office Consultation</div>
                        <div class="sla-price-excludes">* Excludes monthly accounting software fees, COIDA, CIPC Annual Return Fees &amp; Submissions.</div>
                    </div>

                    {{-- Card 3: Professional --}}
                    <div class="sla-price-card {{ ($sla?->selected_package ?? '') === 'professional' ? 'selected' : '' }}" style="--card-accent: #10b981;" onclick="slaSelectPackage('professional', this)">
                        <div class="sla-price-check"><i class="fas fa-check"></i></div>
                        <div class="sla-price-popular">Most Popular</div>
                        <div class="sla-price-tier">Tier 3</div>
                        <div class="sla-price-name">Professional</div>
                        <div class="sla-price-amount"><span class="sla-price-currency">R</span>2,499</div>
                        <div class="sla-price-period">per month excl. VAT</div>
                        <div class="sla-price-turnover"><i class="fas fa-chart-line" style="margin-right:4px; font-size:9px;"></i> Turnover up to R2.5M p.a.</div>
                        <div class="sla-price-divider"></div>
                        <ul class="sla-price-features">
                            <li><i class="fas fa-check"></i> Everything in Growth</li>
                            <li><i class="fas fa-check"></i> CIPC Annual Returns <span class="sla-price-discount">50% OFF</span></li>
                            <li><i class="fas fa-check"></i> Beneficial Ownership Declaration <span class="sla-price-discount">50% OFF</span></li>
                            <li><i class="fas fa-check"></i> Tax Compliance Certificate</li>
                        </ul>
                        <div class="sla-price-consult"><i class="fas fa-building"></i> 2 x 60 Min In-Office Consultations</div>
                        <div class="sla-price-excludes">* Excludes monthly accounting software fees, COIDA, CIPC Annual Return Fees &amp; Submissions.</div>
                    </div>

                    {{-- Card 4: Enterprise --}}
                    <div class="sla-price-card {{ ($sla?->selected_package ?? '') === 'enterprise' ? 'selected' : '' }}" style="--card-accent: #f59e0b;" onclick="slaSelectPackage('enterprise', this)">
                        <div class="sla-price-check"><i class="fas fa-check"></i></div>
                        <div class="sla-price-tier">Tier 4</div>
                        <div class="sla-price-name">Enterprise</div>
                        <div class="sla-price-amount"><span class="sla-price-currency">R</span>4,999</div>
                        <div class="sla-price-period">per month excl. VAT</div>
                        <div class="sla-price-turnover"><i class="fas fa-chart-line" style="margin-right:4px; font-size:9px;"></i> Turnover up to R6.5M p.a.</div>
                        <div class="sla-price-divider"></div>
                        <ul class="sla-price-features">
                            <li><i class="fas fa-check"></i> Everything in Professional</li>
                            <li><i class="fas fa-check"></i> COIDA &amp; SDL Submissions <span class="sla-price-discount">50% OFF</span></li>
                            <li><i class="fas fa-check"></i> Director Individual Tax Returns <span class="sla-price-discount">50% OFF</span></li>
                            <li><i class="fas fa-check"></i> Quarterly Strategy Consultation</li>
                            <li><i class="fas fa-check"></i> Priority Support Response</li>
                            <li><i class="fas fa-check"></i> Dedicated Account Manager</li>
                        </ul>
                        <div class="sla-price-consult"><i class="fas fa-building"></i> 2 x 90 Min In-Office Consultations</div>
                        <div class="sla-price-excludes">* Excludes monthly accounting software fees, COIDA, CIPC Annual Return Fees &amp; Submissions.</div>
                    </div>

                    {{-- Card 5: Premium --}}
                    <div class="sla-price-card {{ ($sla?->selected_package ?? '') === 'premium' ? 'selected' : '' }}" style="--card-accent: #8b5cf6;" onclick="slaSelectPackage('premium', this)">
                        <div class="sla-price-check"><i class="fas fa-check"></i></div>
                        <div class="sla-price-tier">Tier 5</div>
                        <div class="sla-price-name">Premium</div>
                        <div class="sla-price-amount"><span class="sla-price-currency">R</span>9,999</div>
                        <div class="sla-price-period">per month excl. VAT</div>
                        <div class="sla-price-turnover"><i class="fas fa-chart-line" style="margin-right:4px; font-size:9px;"></i> Turnover up to R10M p.a.</div>
                        <div class="sla-price-divider"></div>
                        <ul class="sla-price-features">
                            <li><i class="fas fa-check"></i> Everything in Enterprise</li>
                            <li><i class="fas fa-check"></i> Full Audit Preparation &amp; Support</li>
                            <li><i class="fas fa-check"></i> Company Registration &amp; Changes</li>
                            <li><i class="fas fa-check"></i> Optional On-Site Consult on Request</li>
                            <li><i class="fas fa-check"></i> Dedicated Senior Accountant</li>
                        </ul>
                        <div class="sla-price-consult"><i class="fas fa-building"></i> 2 x 120 Min In-Office Consultations</div>
                        <div class="sla-price-excludes">* Excludes monthly accounting software fees, COIDA, CIPC Annual Return Fees &amp; Submissions.</div>
                    </div>
                </div>

                <div class="sla-info-banner" style="margin-top:16px; border-color: rgba(245,158,11,0.2); background: rgba(245,158,11,0.04);">
                    <i class="fas fa-calculator" style="color: rgba(245,158,11,0.6);"></i>
                    <span>All service fees are subject to a maximum of <strong>1% of the entity's monthly gross revenue</strong>. The applicable tier is determined by the entity's annual turnover as declared. Turnover thresholds are reviewed periodically and may be adjusted upon renewal.</span>
                </div>

                <div class="sla-checkbox-row" style="margin-top:12px;">
                    <input type="checkbox" name="service_consent" value="1" class="sla-checkbox" id="sla_service_consent" {{ $sla?->service_consent ? 'checked' : '' }}>
                    <label class="sla-checkbox-label" for="sla_service_consent">I, the undersigned, hereby confirm that I agree with and understand the contents of Annexure B of this agreement.</label>
                </div>

                <div class="sla-declaration" style="margin-top:16px;">
                    <p><strong>Additional Services:</strong> All services not included in the selected package are charged on an ad-hoc basis. This includes, but is not limited to: CIPC Annual Returns, UBO Declarations, COIDA submissions, Tax Compliance Certificates, Letters of Good Standing, Company Registrations, Name Changes, Director Changes, Individual Tax Returns, and VAT/PAYE/UIF/COIDA Registrations.</p>
                    <p><strong>Platform Compatibility:</strong> Should the Client elect to use an accounting or payroll platform not endorsed by ATP Services, additional charges may apply for data corrections, reconciliations, or adjustments required to ensure accurate regulatory submissions.</p>
                    <p><strong>Travel to Client Policy:</strong> Travel to the Client shall be charged at a flat rate of R500 per trip, inclusive of fuel charges, covering up to 90 minutes of travel time. Any additional meeting time beyond the included in-office consultation shall be charged at R500 per hour, excluding VAT.</p>
                    <p><strong>Response Commitments:</strong> ATP Services guarantees response to client queries within 72 hours via telephone, electronic mail, or virtual platform. On-site services are provided within 72 hours for locations within a 50km radius of the nearest designated representative, subject to a single booking request, fair usage, and no outstanding fees.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Payroll Packages --}}
    <div class="sla-section open" id="sla-payroll">
        <div class="sla-section-header" onclick="slaToggle('sla-payroll')">
            <span class="sla-section-code">PAYROLL</span>
            <span class="sla-section-title">Payroll Package Selection</span>
            <i class="fas fa-chevron-down sla-section-chevron"></i>
        </div>
        <div class="sla-section-body">
            <div class="sla-section-inner">

                <div class="sla-info-banner" style="margin-bottom:16px;">
                    <i class="fas fa-users-cog"></i>
                    <span>Select a payroll package based on the number of employees. Payroll is an independent service and is not included in the accounting packages above. All payroll packages include the full suite of features listed below.</span>
                </div>

                <input type="hidden" name="selected_payroll" id="sla_selected_payroll" value="">

                <div class="sla-payroll-grid">
                    {{-- Payroll Tier 1: 1–3 Employees --}}
                    <div class="sla-payroll-card" style="--card-accent: #06b6d4;" onclick="slaSelectPayroll('payroll_3', this)">
                        <div class="sla-payroll-check"><i class="fas fa-check"></i></div>
                        <div class="sla-payroll-emp">1–3</div>
                        <div class="sla-payroll-label">Band</div>
                        <div class="sla-payroll-name">1 – 3 Employees</div>
                        <div class="sla-payroll-price"><span class="sla-payroll-cur">R</span>600</div>
                        <div class="sla-payroll-per">per month excl. VAT</div>
                    </div>

                    {{-- Payroll Tier 2: 4–10 Employees --}}
                    <div class="sla-payroll-card" style="--card-accent: #10b981;" onclick="slaSelectPayroll('payroll_10', this)">
                        <div class="sla-payroll-check"><i class="fas fa-check"></i></div>
                        <div class="sla-payroll-emp">4–10</div>
                        <div class="sla-payroll-label">Band</div>
                        <div class="sla-payroll-name">4 – 10 Employees</div>
                        <div class="sla-payroll-price"><span class="sla-payroll-cur">R</span>1,500</div>
                        <div class="sla-payroll-per">per month excl. VAT</div>
                    </div>

                    {{-- Payroll Tier 3: 11–20 Employees --}}
                    <div class="sla-payroll-card" style="--card-accent: #8b5cf6;" onclick="slaSelectPayroll('payroll_20', this)">
                        <div class="sla-payroll-check"><i class="fas fa-check"></i></div>
                        <div class="sla-payroll-emp">11–20</div>
                        <div class="sla-payroll-label">Band</div>
                        <div class="sla-payroll-name">11 – 20 Employees</div>
                        <div class="sla-payroll-price"><span class="sla-payroll-cur">R</span>2,500</div>
                        <div class="sla-payroll-per">per month excl. VAT</div>
                    </div>

                    {{-- Payroll Tier 4: 21+ Employees --}}
                    <div class="sla-payroll-card" style="--card-accent: #f59e0b;" onclick="slaSelectPayroll('payroll_21plus', this)">
                        <div class="sla-payroll-check"><i class="fas fa-check"></i></div>
                        <div class="sla-payroll-emp">21+</div>
                        <div class="sla-payroll-label">Over 21</div>
                        <div class="sla-payroll-name">21+ Employees</div>
                        <div class="sla-payroll-price"><span class="sla-payroll-cur">R</span>125</div>
                        <div class="sla-payroll-per">per employee / per month</div>
                    </div>
                </div>

                {{-- All 4 promo blocks in a single 2x2 grid for equal heights --}}
                <div class="sla-promo-grid">
                    {{-- 1. Registrations --}}
                    <div class="sla-promo-cell">
                        <div class="sla-reg-promo">
                            <label class="sla-promo-toggle">
                                <input type="checkbox" name="promo_registrations" value="1" class="sla-promo-check" data-svc="Employer Registrations" data-amt="995" data-billing="once" onchange="slaTogglePromo(this); slaRecalcBilling();">
                                <span class="sla-promo-toggle-box"><i class="fas fa-check"></i></span>
                                <span class="sla-promo-toggle-label">Select</span>
                            </label>
                            <div class="sla-reg-badge"><i class="fas fa-clipboard-list"></i> Registrations</div>
                            <div class="sla-reg-title"><span>Employer</span> Registrations</div>
                            <div class="sla-reg-list">
                                <div class="sla-reg-line"><i class="fas fa-check-circle"></i> PAYE / UIF / SDL Registration</div>
                                <div class="sla-reg-line"><i class="fas fa-check-circle"></i> Department of Labour Registration</div>
                                <div class="sla-reg-line"><i class="fas fa-check-circle"></i> COIDA / WCA Letter of Good Standing Registration</div>
                                <div class="sla-reg-line"><i class="fas fa-check-circle"></i> Rand Mutual Assurance Registration</div>
                                <div class="sla-reg-line"><i class="fas fa-check-circle"></i> Motor Industry Bargaining Council Registration</div>
                            </div>
                            <div class="sla-reg-price-row">
                                <span class="sla-reg-price"><span class="sla-reg-price-cur">R</span>995</span>
                                <span class="sla-reg-price-per">each excl. VAT</span>
                            </div>
                            <div class="sla-reg-note"><i class="fas fa-tag"></i> Special rate with any Payroll Package</div>
                        </div>
                    </div>

                    {{-- 2. Included Features (always included - no checkbox needed) --}}
                    <div class="sla-promo-cell">
                        <div class="sla-inc-promo">
                            <div class="sla-inc-badge"><i class="fas fa-box-open"></i> What You Get</div>
                            <div class="sla-inc-title"><span>Included</span> in All Payroll Packages</div>
                            <div class="sla-inc-grid">
                                <div class="sla-inc-item">
                                    <div class="sla-inc-icon"><i class="fas fa-file-invoice"></i></div>
                                    <div class="sla-inc-text">Professional Payslips</div>
                                </div>
                                <div class="sla-inc-item">
                                    <div class="sla-inc-icon"><i class="fas fa-clock"></i></div>
                                    <div class="sla-inc-text">Time Sheet Management</div>
                                </div>
                                <div class="sla-inc-item">
                                    <div class="sla-inc-icon"><i class="fas fa-play-circle"></i></div>
                                    <div class="sla-inc-text">Processing of Pay Run</div>
                                </div>
                                <div class="sla-inc-item">
                                    <div class="sla-inc-icon"><i class="fas fa-chart-bar"></i></div>
                                    <div class="sla-inc-text">Monthly Payroll Report</div>
                                </div>
                                <div class="sla-inc-item">
                                    <div class="sla-inc-icon"><i class="fas fa-landmark"></i></div>
                                    <div class="sla-inc-text">PAYE, UIF &amp; SDL Returns</div>
                                </div>
                                <div class="sla-inc-item">
                                    <div class="sla-inc-icon"><i class="fas fa-clipboard-list"></i></div>
                                    <div class="sla-inc-text">All Statutory Reports</div>
                                </div>
                                <div class="sla-inc-item">
                                    <div class="sla-inc-icon"><i class="fas fa-money-check-alt"></i></div>
                                    <div class="sla-inc-text">Net Pay List for EFT</div>
                                </div>
                                <div class="sla-inc-item">
                                    <div class="sla-inc-icon"><i class="fas fa-envelope-open-text"></i></div>
                                    <div class="sla-inc-text">Payslips Emailed</div>
                                </div>
                            </div>
                            <label style="margin-top:10px; display:inline-flex; align-items:center; gap:8px; background:rgba(245,158,11,0.1); border:1px solid rgba(245,158,11,0.25); border-radius:10px; padding:9px 14px; cursor:pointer; transition: all 0.2s;" class="sla-payslip-toggle">
                                <input type="checkbox" name="addon_payslips" value="1" class="sla-addon-check" data-svc="Professional Printed Payslips" data-amt="55" data-billing="per-emp" onchange="slaRecalcBilling();" style="display:none;">
                                <span class="sla-promo-toggle-box" style="width:18px; height:18px; min-width:18px;"><i class="fas fa-check" style="font-size:9px;"></i></span>
                                <div style="width:28px; height:28px; border-radius:8px; background:rgba(245,158,11,0.15); display:flex; align-items:center; justify-content:center; flex-shrink:0;"><i class="fas fa-print" style="color:#f59e0b; font-size:11px;"></i></div>
                                <div style="font-size:11px; font-weight:700; color:#e2e8f0; line-height:1.3;">Professional Printed Payslip</div>
                                <div style="font-size:12px; font-weight:900; color:#f59e0b; white-space:nowrap; margin-left:auto;">R55 each</div>
                            </label>
                            <div class="sla-inc-footer"><i class="fas fa-check-double"></i> All features included at no extra cost</div>
                        </div>
                    </div>

                    {{-- 3. COIDA / WCA --}}
                    <div class="sla-promo-cell">
                        <div class="sla-coida-promo">
                            <label class="sla-promo-toggle">
                                <input type="checkbox" name="promo_coida" value="1" class="sla-promo-check" data-svc="COIDA / WCA Compliance" data-amt="350" data-billing="monthly" onchange="slaTogglePromo(this); slaRecalcBilling();">
                                <span class="sla-promo-toggle-box"><i class="fas fa-check"></i></span>
                                <span class="sla-promo-toggle-label">Select</span>
                            </label>
                            <div class="sla-coida-badge"><i class="fas fa-shield-alt"></i> Compliance Service</div>
                            <div class="sla-coida-title"><span>COIDA / WCA</span> &bull; LETTER OF GOOD STANDING</div>
                            <div class="sla-coida-sub">Stay fully compliant with the Department of Employment and Labour. We handle your annual submissions and keep your business protected.</div>
                            <div class="sla-coida-items">
                                <div class="sla-coida-item"><i class="fas fa-check"></i> Letter of Good Standing</div>
                                <div class="sla-coida-item"><i class="fas fa-check"></i> Returns of Earnings</div>
                                <div class="sla-coida-item"><i class="fas fa-check"></i> COIDA Registration</div>
                                <div class="sla-coida-item"><i class="fas fa-check"></i> WCA Claims Assistance</div>
                            </div>
                            <div class="sla-coida-price-row">
                                <span class="sla-coida-price"><span class="sla-coida-price-cur">R</span>350</span>
                                <span class="sla-coida-price-per">per month excl. VAT</span>
                            </div>
                        </div>
                    </div>

                    {{-- 4. Terminations & Resignations --}}
                    <div class="sla-promo-cell">
                        <div class="sla-term-promo">
                            <label class="sla-promo-toggle">
                                <input type="checkbox" name="promo_terminations" value="1" class="sla-promo-check" data-svc="Terminations &amp; Resignations" data-amt="500" data-billing="per-emp" onchange="slaTogglePromo(this); slaRecalcBilling();">
                                <span class="sla-promo-toggle-box"><i class="fas fa-check"></i></span>
                                <span class="sla-promo-toggle-label">Select</span>
                            </label>
                            <div class="sla-term-badge"><i class="fas fa-user-slash"></i> Exit Management</div>
                            <div class="sla-term-title"><span>Terminations</span> &amp; Resignations</div>
                            <div class="sla-term-sub">Complete employee exit processing handled professionally. All documentation, final calculations, and regulatory submissions taken care of.</div>
                            <div class="sla-term-items">
                                <div class="sla-term-item"><i class="fas fa-check"></i> Termination Notice</div>
                                <div class="sla-term-item"><i class="fas fa-check"></i> Resignation Acceptance</div>
                                <div class="sla-term-item"><i class="fas fa-check"></i> UI19 Certificate</div>
                                <div class="sla-term-item"><i class="fas fa-check"></i> MIBCO Withdrawal</div>
                                <div class="sla-term-item"><i class="fas fa-check"></i> Final Pay Calculation</div>
                                <div class="sla-term-item"><i class="fas fa-check"></i> Settlement Agreement</div>
                            </div>
                            <div class="sla-term-price-row">
                                <span class="sla-term-price"><span class="sla-term-price-cur">R</span>500</span>
                                <span class="sla-term-price-per">per employee excl. VAT</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Optional Add-ons --}}
                <div style="margin-top:16px;">
                    <div style="font-size:11px; font-weight:800; letter-spacing:1px; text-transform:uppercase; color:#f59e0b; margin-bottom:12px; display:flex; align-items:center; gap:8px;">
                        <i class="fas fa-puzzle-piece" style="font-size:12px;"></i> Optional Payroll Add-Ons
                    </div>
                    <div class="sla-addons-grid">
                        <label class="sla-addon-card" style="--card-accent: #f59e0b;">
                            <input type="checkbox" name="addon_mibco" value="1" class="sla-addon-check" data-svc="MIBCO Compliance" data-amt="500" data-billing="monthly" onchange="slaToggleAddon(this); slaRecalcBilling();">
                            <div class="sla-addon-tick"><i class="fas fa-check"></i></div>
                            <div class="sla-addon-icon" style="background:rgba(245,158,11,0.1); color:#f59e0b;"><i class="fas fa-hard-hat"></i></div>
                            <div><div class="sla-addon-name">MIBCO</div></div>
                            <div class="sla-addon-price">R500/mo</div>
                        </label>
                        <label class="sla-addon-card" style="--card-accent: #3b82f6;">
                            <input type="checkbox" name="addon_leave" value="1" class="sla-addon-check" data-svc="Leave Management" data-amt="400" data-billing="monthly" onchange="slaToggleAddon(this); slaRecalcBilling();">
                            <div class="sla-addon-tick"><i class="fas fa-check"></i></div>
                            <div class="sla-addon-icon" style="background:rgba(59,130,246,0.1); color:#3b82f6;"><i class="fas fa-calendar-week"></i></div>
                            <div><div class="sla-addon-name">Leave Management</div></div>
                            <div class="sla-addon-price">R400/mo</div>
                        </label>
                        <label class="sla-addon-card" style="--card-accent: #10b981;">
                            <input type="checkbox" name="addon_contracts" value="1" class="sla-addon-check" data-svc="Employment Contracts" data-amt="385" data-billing="per-emp" onchange="slaToggleAddon(this); slaRecalcBilling();">
                            <div class="sla-addon-tick"><i class="fas fa-check"></i></div>
                            <div class="sla-addon-icon" style="background:rgba(16,185,129,0.1); color:#10b981;"><i class="fas fa-file-contract"></i></div>
                            <div><div class="sla-addon-name">Employment Contracts</div></div>
                            <div class="sla-addon-price">R385/emp</div>
                        </label>
                        <label class="sla-addon-card" style="--card-accent: #8b5cf6;">
                            <input type="checkbox" name="addon_payroll_file" value="1" class="sla-addon-check" data-svc="Payroll File Setup" data-amt="350" data-billing="per-emp" onchange="slaToggleAddon(this); slaRecalcBilling();">
                            <div class="sla-addon-tick"><i class="fas fa-check"></i></div>
                            <div class="sla-addon-icon" style="background:rgba(139,92,246,0.1); color:#8b5cf6;"><i class="fas fa-folder-open"></i></div>
                            <div><div class="sla-addon-name">Payroll File</div></div>
                            <div class="sla-addon-price">R350/emp</div>
                        </label>
                        <label class="sla-addon-card" style="--card-accent: #ef4444;">
                            <input type="checkbox" name="addon_notices" value="1" class="sla-addon-check" data-svc="Notices" data-amt="95" data-billing="per-emp" onchange="slaToggleAddon(this); slaRecalcBilling();">
                            <div class="sla-addon-tick"><i class="fas fa-check"></i></div>
                            <div class="sla-addon-icon" style="background:rgba(239,68,68,0.1); color:#ef4444;"><i class="fas fa-bullhorn"></i></div>
                            <div><div class="sla-addon-name">Notices</div></div>
                            <div class="sla-addon-price">R95/emp</div>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="sla-nav-buttons">
        <button type="button" class="sla-btn sla-btn-secondary" onclick="slaSwitchTab(2)">
            <i class="fas fa-arrow-left"></i> Back
        </button>
        <button type="button" class="sla-btn sla-btn-primary" onclick="slaSwitchTab(4)">
            Continue to Billing <i class="fas fa-arrow-right"></i>
        </button>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- TAB 4: BILLING INVOICE                                  --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div class="sla-panel" id="sla-panel-4">

    <div class="sla-info-banner">
        <i class="fas fa-file-invoice-dollar"></i>
        <span>Select the services required below. Tick the checkbox to include a service, adjust the quantity where applicable. All fees are exclusive of VAT at 15% and are fixed for the current tax year.</span>
    </div>

    <div class="sla-section open" id="sla-billing-summary">
        <div class="sla-section-header" onclick="slaToggle('sla-billing-summary')">
            <span class="sla-section-code">BILL</span>
            <span class="sla-section-title">Pro Forma Billing Invoice</span>
            <i class="fas fa-chevron-down sla-section-chevron"></i>
        </div>
        <div class="sla-section-body">
            <div class="sla-section-inner">

                <div class="sla-billing-header">
                    <div class="sla-billing-logo">
                        <div class="sla-billing-logo-icon"><i class="fas fa-calculator"></i></div>
                        <div>
                            <div class="sla-billing-company">ATP Services</div>
                            <div class="sla-billing-subtitle">Accounting Taxation &amp; Payroll (Pty) Ltd</div>
                        </div>
                    </div>
                    <div class="sla-billing-doc-type">
                        <div class="sla-billing-doc-label">PRO FORMA</div>
                        <div class="sla-billing-doc-date" id="slaBillingDate"></div>
                    </div>
                </div>

                {{-- Section 1: Service Packages --}}
                <div class="sla-billing-section-label">
                    <i class="fas fa-boxes-stacked"></i> Service Packages <span style="font-weight:400; letter-spacing:0; text-transform:none; font-size:10px; color:var(--text-muted);">(select one)</span>
                </div>
                <table class="sla-billing-table sla-inv-table">
                    <thead>
                        <tr>
                            <th style="width:36px;"></th>
                            <th style="width:36px;">#</th>
                            <th>Service Description</th>
                            <th style="width:110px; text-align:right;">Unit Price</th>
                            <th style="width:70px; text-align:center;">Qty</th>
                            <th style="width:120px; text-align:right;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="sla-inv-row" data-group="package" data-key="starter" data-price="749">
                            <td><label class="sla-inv-check"><input type="radio" name="inv_package" value="starter" onchange="slaInvSelectPackage(this)"><span class="sla-inv-radio"></span></label></td>
                            <td>1</td>
                            <td>Starter Package <span class="sla-inv-tag">Tier 1 &bull; Up to R750K p.a.</span></td>
                            <td style="text-align:right;">R749.00</td>
                            <td style="text-align:center;">1</td>
                            <td style="text-align:right;" class="sla-inv-amt">-</td>
                        </tr>
                        <tr class="sla-inv-row" data-group="package" data-key="growth" data-price="1499">
                            <td><label class="sla-inv-check"><input type="radio" name="inv_package" value="growth" onchange="slaInvSelectPackage(this)"><span class="sla-inv-radio"></span></label></td>
                            <td>2</td>
                            <td>Growth Package <span class="sla-inv-tag">Tier 2 &bull; Up to R1.5M p.a.</span></td>
                            <td style="text-align:right;">R1,499.00</td>
                            <td style="text-align:center;">1</td>
                            <td style="text-align:right;" class="sla-inv-amt">-</td>
                        </tr>
                        <tr class="sla-inv-row" data-group="package" data-key="professional" data-price="2499">
                            <td><label class="sla-inv-check"><input type="radio" name="inv_package" value="professional" onchange="slaInvSelectPackage(this)"><span class="sla-inv-radio"></span></label></td>
                            <td>3</td>
                            <td>Professional Package <span class="sla-inv-tag sla-inv-tag-pop">Most Popular &bull; Tier 3 &bull; Up to R2.5M p.a.</span></td>
                            <td style="text-align:right;">R2,499.00</td>
                            <td style="text-align:center;">1</td>
                            <td style="text-align:right;" class="sla-inv-amt">-</td>
                        </tr>
                        <tr class="sla-inv-row" data-group="package" data-key="enterprise" data-price="4999">
                            <td><label class="sla-inv-check"><input type="radio" name="inv_package" value="enterprise" onchange="slaInvSelectPackage(this)"><span class="sla-inv-radio"></span></label></td>
                            <td>4</td>
                            <td>Enterprise Package <span class="sla-inv-tag">Tier 4 &bull; Up to R6.5M p.a.</span></td>
                            <td style="text-align:right;">R4,999.00</td>
                            <td style="text-align:center;">1</td>
                            <td style="text-align:right;" class="sla-inv-amt">-</td>
                        </tr>
                        <tr class="sla-inv-row" data-group="package" data-key="premium" data-price="9999">
                            <td><label class="sla-inv-check"><input type="radio" name="inv_package" value="premium" onchange="slaInvSelectPackage(this)"><span class="sla-inv-radio"></span></label></td>
                            <td>5</td>
                            <td>Premium Package <span class="sla-inv-tag">Tier 5 &bull; Up to R10M p.a.</span></td>
                            <td style="text-align:right;">R9,999.00</td>
                            <td style="text-align:center;">1</td>
                            <td style="text-align:right;" class="sla-inv-amt">-</td>
                        </tr>
                    </tbody>
                </table>

                {{-- Section 2: Payroll Packages --}}
                <div class="sla-billing-section-label">
                    <i class="fas fa-users-cog"></i> Payroll Packages <span style="font-weight:400; letter-spacing:0; text-transform:none; font-size:10px; color:var(--text-muted);">(select one)</span>
                </div>
                <table class="sla-billing-table sla-inv-table">
                    <thead>
                        <tr>
                            <th style="width:36px;"></th>
                            <th style="width:36px;">#</th>
                            <th>Service Description</th>
                            <th style="width:110px; text-align:right;">Unit Price</th>
                            <th style="width:70px; text-align:center;">Qty</th>
                            <th style="width:120px; text-align:right;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="sla-inv-row" data-group="payroll" data-key="payroll_3" data-price="600">
                            <td><label class="sla-inv-check"><input type="radio" name="inv_payroll" value="payroll_3" onchange="slaInvSelectPayroll(this)"><span class="sla-inv-radio"></span></label></td>
                            <td>1</td>
                            <td>Payroll: 1 – 3 Employees <span class="sla-inv-tag">Band 1 &bull; Flat Rate</span></td>
                            <td style="text-align:right;">R600.00</td>
                            <td style="text-align:center;">1</td>
                            <td style="text-align:right;" class="sla-inv-amt">-</td>
                        </tr>
                        <tr class="sla-inv-row" data-group="payroll" data-key="payroll_10" data-price="1500">
                            <td><label class="sla-inv-check"><input type="radio" name="inv_payroll" value="payroll_10" onchange="slaInvSelectPayroll(this)"><span class="sla-inv-radio"></span></label></td>
                            <td>2</td>
                            <td>Payroll: 4 – 10 Employees <span class="sla-inv-tag">Band 2 &bull; Flat Rate</span></td>
                            <td style="text-align:right;">R1,500.00</td>
                            <td style="text-align:center;">1</td>
                            <td style="text-align:right;" class="sla-inv-amt">-</td>
                        </tr>
                        <tr class="sla-inv-row" data-group="payroll" data-key="payroll_20" data-price="2500">
                            <td><label class="sla-inv-check"><input type="radio" name="inv_payroll" value="payroll_20" onchange="slaInvSelectPayroll(this)"><span class="sla-inv-radio"></span></label></td>
                            <td>3</td>
                            <td>Payroll: 11 – 20 Employees <span class="sla-inv-tag">Band 3 &bull; Flat Rate</span></td>
                            <td style="text-align:right;">R2,500.00</td>
                            <td style="text-align:center;">1</td>
                            <td style="text-align:right;" class="sla-inv-amt">-</td>
                        </tr>
                        <tr class="sla-inv-row" data-group="payroll" data-key="payroll_21plus" data-price="125">
                            <td><label class="sla-inv-check"><input type="radio" name="inv_payroll" value="payroll_21plus" onchange="slaInvSelectPayroll(this)"><span class="sla-inv-radio"></span></label></td>
                            <td>4</td>
                            <td>Payroll: 21+ Employees <span class="sla-inv-tag">Band 4 &bull; Per Employee</span></td>
                            <td style="text-align:right;">R125.00</td>
                            <td style="text-align:center;"><input type="number" class="sla-inv-qty" id="slaInvPayrollQty" value="21" min="21" onchange="slaInvRecalc()" style="width:52px;"></td>
                            <td style="text-align:right;" class="sla-inv-amt">-</td>
                        </tr>
                    </tbody>
                </table>

                {{-- Section 3: Additional Monthly Services --}}
                <div class="sla-billing-section-label">
                    <i class="fas fa-puzzle-piece"></i> Additional Monthly Services <span style="font-weight:400; letter-spacing:0; text-transform:none; font-size:10px; color:var(--text-muted);">(select as needed)</span>
                </div>
                <table class="sla-billing-table sla-inv-table">
                    <thead>
                        <tr>
                            <th style="width:36px;"></th>
                            <th style="width:36px;">#</th>
                            <th>Service Description</th>
                            <th style="width:110px; text-align:right;">Unit Price</th>
                            <th style="width:70px; text-align:center;">Qty</th>
                            <th style="width:120px; text-align:right;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="sla-inv-row" data-group="addon" data-price="500">
                            <td><label class="sla-inv-check"><input type="checkbox" name="inv_addon_mibco" value="1" onchange="slaInvToggleAddon(this)"><span class="sla-inv-cb"></span></label></td>
                            <td>1</td>
                            <td>MIBCO Compliance <span class="sla-inv-tag">Monthly</span></td>
                            <td style="text-align:right;">R500.00</td>
                            <td style="text-align:center;">1</td>
                            <td style="text-align:right;" class="sla-inv-amt">-</td>
                        </tr>
                        <tr class="sla-inv-row" data-group="addon" data-price="400">
                            <td><label class="sla-inv-check"><input type="checkbox" name="inv_addon_leave" value="1" onchange="slaInvToggleAddon(this)"><span class="sla-inv-cb"></span></label></td>
                            <td>2</td>
                            <td>Leave Management <span class="sla-inv-tag">Monthly</span></td>
                            <td style="text-align:right;">R400.00</td>
                            <td style="text-align:center;">1</td>
                            <td style="text-align:right;" class="sla-inv-amt">-</td>
                        </tr>
                        <tr class="sla-inv-row" data-group="addon" data-price="350">
                            <td><label class="sla-inv-check"><input type="checkbox" name="inv_addon_coida" value="1" onchange="slaInvToggleAddon(this)"><span class="sla-inv-cb"></span></label></td>
                            <td>3</td>
                            <td>COIDA / WCA Compliance <span class="sla-inv-tag">Monthly</span></td>
                            <td style="text-align:right;">R350.00</td>
                            <td style="text-align:center;">1</td>
                            <td style="text-align:right;" class="sla-inv-amt">-</td>
                        </tr>
                        <tr class="sla-inv-row" data-group="addon" data-price="385" data-per-unit="1">
                            <td><label class="sla-inv-check"><input type="checkbox" name="inv_addon_contracts" value="1" onchange="slaInvToggleAddon(this)"><span class="sla-inv-cb"></span></label></td>
                            <td>4</td>
                            <td>Employment Contracts <span class="sla-inv-tag">Per Employee</span></td>
                            <td style="text-align:right;">R385.00</td>
                            <td style="text-align:center;"><input type="number" class="sla-inv-qty" value="1" min="1" onchange="slaInvRecalc()" style="width:52px;"></td>
                            <td style="text-align:right;" class="sla-inv-amt">-</td>
                        </tr>
                        <tr class="sla-inv-row" data-group="addon" data-price="350" data-per-unit="1">
                            <td><label class="sla-inv-check"><input type="checkbox" name="inv_addon_payrollfile" value="1" onchange="slaInvToggleAddon(this)"><span class="sla-inv-cb"></span></label></td>
                            <td>5</td>
                            <td>Payroll File Setup <span class="sla-inv-tag">Per Employee</span></td>
                            <td style="text-align:right;">R350.00</td>
                            <td style="text-align:center;"><input type="number" class="sla-inv-qty" value="1" min="1" onchange="slaInvRecalc()" style="width:52px;"></td>
                            <td style="text-align:right;" class="sla-inv-amt">-</td>
                        </tr>
                        <tr class="sla-inv-row" data-group="addon" data-price="95" data-per-unit="1">
                            <td><label class="sla-inv-check"><input type="checkbox" name="inv_addon_notices" value="1" onchange="slaInvToggleAddon(this)"><span class="sla-inv-cb"></span></label></td>
                            <td>6</td>
                            <td>Notices <span class="sla-inv-tag">Per Employee</span></td>
                            <td style="text-align:right;">R95.00</td>
                            <td style="text-align:center;"><input type="number" class="sla-inv-qty" value="1" min="1" onchange="slaInvRecalc()" style="width:52px;"></td>
                            <td style="text-align:right;" class="sla-inv-amt">-</td>
                        </tr>
                        <tr class="sla-inv-row" data-group="addon" data-price="55" data-per-unit="1">
                            <td><label class="sla-inv-check"><input type="checkbox" name="inv_addon_payslips" value="1" onchange="slaInvToggleAddon(this)"><span class="sla-inv-cb"></span></label></td>
                            <td>7</td>
                            <td>Professional Printed Payslips <span class="sla-inv-tag">Per Payslip</span></td>
                            <td style="text-align:right;">R55.00</td>
                            <td style="text-align:center;"><input type="number" class="sla-inv-qty" value="1" min="1" onchange="slaInvRecalc()" style="width:52px;"></td>
                            <td style="text-align:right;" class="sla-inv-amt">-</td>
                        </tr>
                        <tr class="sla-inv-row" data-group="addon" data-price="995" data-per-unit="1">
                            <td><label class="sla-inv-check"><input type="checkbox" name="inv_addon_registrations" value="1" onchange="slaInvToggleAddon(this)"><span class="sla-inv-cb"></span></label></td>
                            <td>8</td>
                            <td>Employer Registrations <span class="sla-inv-tag">Per Registration</span></td>
                            <td style="text-align:right;">R995.00</td>
                            <td style="text-align:center;"><input type="number" class="sla-inv-qty" value="1" min="1" onchange="slaInvRecalc()" style="width:52px;"></td>
                            <td style="text-align:right;" class="sla-inv-amt">-</td>
                        </tr>
                        <tr class="sla-inv-row" data-group="addon" data-price="500" data-per-unit="1">
                            <td><label class="sla-inv-check"><input type="checkbox" name="inv_addon_terminations" value="1" onchange="slaInvToggleAddon(this)"><span class="sla-inv-cb"></span></label></td>
                            <td>9</td>
                            <td>Terminations &amp; Resignations <span class="sla-inv-tag">Per Employee</span></td>
                            <td style="text-align:right;">R500.00</td>
                            <td style="text-align:center;"><input type="number" class="sla-inv-qty" value="1" min="1" onchange="slaInvRecalc()" style="width:52px;"></td>
                            <td style="text-align:right;" class="sla-inv-amt">-</td>
                        </tr>
                    </tbody>
                </table>

                {{-- Totals --}}
                <div class="sla-billing-totals">
                    <div class="sla-billing-total-row">
                        <span>Subtotal (excl. VAT)</span>
                        <span id="slaBillingSubtotal">R0.00</span>
                    </div>
                    <div class="sla-billing-total-row sla-billing-vat">
                        <span>VAT @ 15%</span>
                        <span id="slaBillingVat">R0.00</span>
                    </div>
                    <div class="sla-billing-total-row sla-billing-grand">
                        <span>Total (incl. VAT)</span>
                        <span id="slaBillingTotal">R0.00</span>
                    </div>
                </div>

                {{-- Ad Hoc Note --}}
                <div class="sla-billing-note" style="margin-top:24px;">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <strong style="color:#e2e8f0;">Ad Hoc Services:</strong> All services not listed above are charged separately per the ATP Services Ad Hoc Rate Card. This includes but is not limited to: CIPC Annual Returns, Beneficial Ownership Declarations, Tax Compliance Certificates, Letters of Good Standing, Company Registrations, Name &amp; Director Changes, Individual Tax Returns, SARS Audit Support, Historical Catch-Up Work, and Travel Expenses (charged at SARS kilometre rates).
                    </div>
                </div>

                <div class="sla-billing-note" style="margin-top:8px; border-color:rgba(245,158,11,0.15); background:rgba(245,158,11,0.03);">
                    <i class="fas fa-lock" style="color:rgba(245,158,11,0.5);"></i>
                    <div>
                        All prices listed are fixed for the current tax year and will not change during the engagement period. Price adjustments, if any, will only take effect upon annual renewal of this agreement.
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="sla-nav-buttons">
        <button type="button" class="sla-btn sla-btn-secondary" onclick="slaSwitchTab(3)">
            <i class="fas fa-arrow-left"></i> Back
        </button>
        <button type="button" class="sla-btn sla-btn-primary" onclick="slaSwitchTab(5)">
            Continue to Banking <i class="fas fa-arrow-right"></i>
        </button>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- TAB 5: BANKING & DEBIT ORDER MANDATE                   --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div class="sla-panel" id="sla-panel-5">

    <div class="sla-info-banner">
        <i class="fas fa-university"></i>
        <span>Complete the banking details and debit order mandate (Annexure C) and review the fee structure (Annexure D).</span>
    </div>

    {{-- Annexure C: Debit Order --}}
    <div class="sla-section open" id="sla-annex-c">
        <div class="sla-section-header" onclick="slaToggle('sla-annex-c')">
            <span class="sla-section-code">ANN.C</span>
            <span class="sla-section-title">Annexure C: Debit Order Authorisation</span>
            <i class="fas fa-chevron-down sla-section-chevron"></i>
        </div>
        <div class="sla-section-body">
            <div class="sla-section-inner">
                <div class="sla-form-row">
                    <div class="sla-form-group">
                        <label class="sla-form-label">Account Holder <span class="sla-required">*</span></label>
                        <input type="text" name="bank_account_holder" class="sla-form-input" value="{{ $sla?->bank_account_holder ?? '' }}">
                    </div>
                    <div class="sla-form-group">
                        <label class="sla-form-label">Bank <span class="sla-required">*</span></label>
                        <select name="bank_name" class="sla-form-select">
                            <option value="">Select Bank</option>
                            @foreach(['First National Bank','Absa Bank','Standard Bank','Nedbank','Capitec','Bidvest Bank','Discovery Bank','TymeBank','Investec Bank','African Bank','Grindrod Bank','Sasfin Bank'] as $bank)
                                <option value="{{ $bank }}" {{ ($sla?->bank_name ?? '') === $bank ? 'selected' : '' }}>{{ $bank }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="sla-form-row">
                    <div class="sla-form-group">
                        <label class="sla-form-label">Branch Code <span class="sla-required">*</span></label>
                        <input type="text" name="bank_branch_code" class="sla-form-input" value="{{ $sla?->bank_branch_code ?? '' }}">
                    </div>
                    <div class="sla-form-group">
                        <label class="sla-form-label">Account Number <span class="sla-required">*</span></label>
                        <input type="text" name="bank_account_number" class="sla-form-input" value="{{ $sla?->bank_account_number ?? '' }}">
                    </div>
                </div>
                <div class="sla-form-row">
                    <div class="sla-form-group">
                        <label class="sla-form-label">Account Type <span class="sla-required">*</span></label>
                        <select name="bank_account_type" class="sla-form-select">
                            <option value="">Select</option>
                            <option value="Current" {{ ($sla?->bank_account_type ?? '') === 'Current' ? 'selected' : '' }}>Current (Cheque)</option>
                            <option value="Savings" {{ ($sla?->bank_account_type ?? '') === 'Savings' ? 'selected' : '' }}>Savings</option>
                            <option value="Transmission" {{ ($sla?->bank_account_type ?? '') === 'Transmission' ? 'selected' : '' }}>Transmission</option>
                        </select>
                    </div>
                    <div class="sla-form-group">
                        <label class="sla-form-label">Preferred Debit Order Date <span class="sla-required">*</span></label>
                        <select name="debit_order_date" class="sla-form-select">
                            <option value="">Select</option>
                            <option value="7th" {{ ($sla?->debit_order_date ?? '') === '7th' ? 'selected' : '' }}>7th of Each Month</option>
                            <option value="25th" {{ ($sla?->debit_order_date ?? '') === '25th' ? 'selected' : '' }}>25th of Each Month</option>
                        </select>
                    </div>
                </div>

                <div class="sla-declaration" style="margin-top:16px;">
                    <p><strong>Debit Order Mandate:</strong> I/We hereby authorise ATP Services to issue and deliver payment instructions to the nominated bank for collection against the above-mentioned account, on condition that the sum of such instructions shall not exceed the obligations agreed under this engagement. This mandate shall continue until terminated by the Client with no fewer than twenty (20) business days' written notice delivered to ATP Services.</p>
                    <p>The individual payment instructions shall be issued monthly. Should the payment date fall on a Sunday or recognised South African public holiday, the collection will be processed on the preceding business day.</p>
                    <p>I/We acknowledge that all payment instructions issued by ATP Services shall be treated by the nominated bank as though issued personally by the account holder(s). Cancellation of this mandate does not cancel the underlying agreement.</p>
                </div>

                <div class="sla-checkbox-row" style="margin-top:12px;">
                    <input type="checkbox" name="debit_order_consent" value="1" class="sla-checkbox" id="sla_debit_consent" {{ $sla?->debit_order_consent ? 'checked' : '' }}>
                    <label class="sla-checkbox-label" for="sla_debit_consent">I, the undersigned, hereby confirm that I agree with and understand the contents of this Debit Order Mandate. I acknowledge that upon submission of this agreement I will be liable for the monthly recurring accounting software subscription fee. I am aware that this is a month-to-month agreement and that 30 days' written notice is required for cancellation.</label>
                </div>
            </div>
        </div>
    </div>

    {{-- Annexure D: Fee Structure --}}
    <div class="sla-section" id="sla-annex-d">
        <div class="sla-section-header" onclick="slaToggle('sla-annex-d')">
            <span class="sla-section-code">ANN.D</span>
            <span class="sla-section-title">Annexure D: Monthly Fee Structure</span>
            <i class="fas fa-chevron-down sla-section-chevron"></i>
        </div>
        <div class="sla-section-body">
            <div class="sla-section-inner">
                <div class="sla-declaration">
                    <p><strong>1.</strong> The Client shall be invoiced a minimum monthly accounting software subscription fee upon execution of this agreement.</p>
                    <p><strong>2.</strong> The monthly invoice is calculated based on the entity's preceding three (3) months' turnover as evidenced by bank statements received, and shall be issued to the Client for settlement prior to commencement of work.</p>
                    <p><strong>3.</strong> Where there is no activity on the bank account, a minimum monthly fee of R330.00 (excluding VAT) shall apply. Payment is required before any work commences.</p>
                    <p><strong>4.</strong> This subscription is designed for corporate clients and is based on business turnover. Personal income tax services for individuals are governed by a separate fee schedule not covered by this subscription.</p>
                </div>
                <div class="sla-declaration" style="margin-top:12px;">
                    <p><strong>Credit Policy:</strong> All payments for services rendered by ATP Services are non-refundable. In the event of service discontinuation, any applicable adjustments shall be issued as an account credit rather than a monetary refund. Upon termination, any remaining credit balance is forfeited.</p>
                    <p><strong>Non-Compliance Termination:</strong> Should the Client remain non-compliant with regulatory requirements for a period exceeding 90 days due to their own oversight or negligence, ATP Services reserves the right to terminate services without further obligation. The Client assumes full responsibility for any regulatory consequences arising from non-compliance.</p>
                    <p><strong>SARS Profile Transfer:</strong> Upon cancellation, the Client must formally request the transfer of their SARS profile within seven (7) business days. Failure to do so may result in additional administrative fees. If the Client's SARS profile remains linked to ATP Services and correspondence continues to be received, an administrative fee will be charged for managing such communications.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="sla-nav-buttons">
        <button type="button" class="sla-btn sla-btn-secondary" onclick="slaSwitchTab(4)">
            <i class="fas fa-arrow-left"></i> Back
        </button>
        <button type="button" class="sla-btn sla-btn-primary" onclick="slaSwitchTab(6)">
            Continue to Signature <i class="fas fa-arrow-right"></i>
        </button>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- TAB 6: DECLARATION & SIGNATURE                          --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div class="sla-panel" id="sla-panel-6">

    <div class="sla-info-banner">
        <i class="fas fa-signature"></i>
        <span>Review the declaration below and provide your signature to finalise this Service Level Agreement.</span>
    </div>

    <div class="sla-section open" id="sla-declaration-section">
        <div class="sla-section-header" onclick="slaToggle('sla-declaration-section')">
            <span class="sla-section-code">DEC</span>
            <span class="sla-section-title">Declaration &amp; Acceptance</span>
            <i class="fas fa-chevron-down sla-section-chevron"></i>
        </div>
        <div class="sla-section-body">
            <div class="sla-section-inner">
                <div class="sla-declaration">
                    <p>I, the undersigned, hereby acknowledge receipt of this Service Level Agreement and confirm that I have read, understood, and agree to be bound by the terms and conditions set out herein, together with the provisions of Annexure A, B, C, and D which form an integral part of this agreement.</p>
                    <p>The undersigned confirms that he/she is duly authorised to execute this agreement on behalf of the Client entity and that all information furnished is true, complete, and accurate.</p>
                    <p>Upon submission of this agreement, Accounting Taxation and Payroll (Pty) Ltd automatically co-signs and agrees to all terms and conditions contained herein.</p>
                </div>

                <div class="sla-form-row" style="margin-top:20px;">
                    <div class="sla-form-group">
                        <label class="sla-form-label">Signed At (Location) <span class="sla-required">*</span></label>
                        <input type="text" name="signed_at_location" class="sla-form-input" value="{{ $sla?->signed_at_location ?? '' }}" placeholder="City / Town">
                    </div>
                    <div class="sla-form-group">
                        <label class="sla-form-label">Date <span class="sla-required">*</span></label>
                        <input type="text" name="signed_date" class="sla-form-input sla-date-picker" id="sla_signed_date" value="{{ $sla?->signed_date ? $sla->signed_date->format('Y-m-d') : '' }}" placeholder="Select date">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="sla-section open" id="sla-signature-section">
        <div class="sla-section-header" onclick="slaToggle('sla-signature-section')">
            <span class="sla-section-code">SIG</span>
            <span class="sla-section-title">Signature</span>
            <i class="fas fa-chevron-down sla-section-chevron"></i>
        </div>
        <div class="sla-section-body">
            <div class="sla-section-inner">
                <div class="sla-sig-toggle">
                    <button type="button" class="sla-sig-toggle-btn active" onclick="slaToggleSigMode('draw', this)">
                        <i class="fas fa-pen-nib" style="margin-right:6px;"></i> Draw Signature
                    </button>
                    <button type="button" class="sla-sig-toggle-btn" onclick="slaToggleSigMode('type', this)">
                        <i class="fas fa-keyboard" style="margin-right:6px;"></i> Type Name
                    </button>
                </div>

                <input type="hidden" name="signature_type" id="sla_signature_type" value="{{ $sla?->signature_type ?? 'drawn' }}">
                <input type="hidden" name="signature_data" id="sla_signature_data" value="{{ $sla?->signature_data ?? '' }}">

                <div id="sla-sig-draw-area">
                    <canvas id="slaSignaturePad" class="sla-signature-pad" width="500" height="180"></canvas>
                    <div class="sla-signature-actions">
                        <button type="button" class="sla-sig-btn" onclick="slaClearSignature()">
                            <i class="fas fa-eraser" style="margin-right:6px;"></i> Clear
                        </button>
                    </div>
                </div>

                <div id="sla-sig-type-area" style="display:none;">
                    <input type="text" class="sla-sig-type-input" id="slaTypedSignature" placeholder="Type your full name" value="{{ $sla?->signature_type === 'typed' ? $sla?->signature_data : '' }}" oninput="slaUpdateTypedSig()">
                    <div style="font-size:11px; color:var(--text-muted); margin-top:8px;">Either signature method is legally binding under this agreement.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="sla-nav-buttons">
        <button type="button" class="sla-btn sla-btn-secondary" onclick="slaSwitchTab(5)">
            <i class="fas fa-arrow-left"></i> Back
        </button>
        <div style="display:flex; gap:10px;">
            <button type="submit" name="status" value="draft" class="sla-btn sla-btn-secondary">
                <i class="fas fa-save"></i> Save as Draft
            </button>
            <button type="submit" class="sla-btn sla-btn-success" onclick="slaPrepareSig()">
                <i class="fas fa-file-signature"></i> Submit Engagement Letter
            </button>
        </div>
    </div>
</div>

</form>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    flatpickr('.sla-date-picker', {
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'j M Y',
        theme: 'dark'
    });

    initSignaturePad();
    slaInvSetDate();
});

/* ── Tab Switching ── */
function slaSwitchTab(num, btn) {
    document.querySelectorAll('.sla-tab').forEach(function(t) { t.classList.remove('active'); });
    document.querySelectorAll('.sla-panel').forEach(function(p) { p.classList.remove('active'); });

    var tabBtn = btn || document.querySelector('.sla-tab[data-tab="'+num+'"]');
    if (tabBtn) tabBtn.classList.add('active');
    var panel = document.getElementById('sla-panel-'+num);
    if (panel) panel.classList.add('active');

    for (var i = 1; i < num; i++) {
        var prev = document.querySelector('.sla-tab[data-tab="'+i+'"]');
        if (prev && !prev.classList.contains('done')) prev.classList.add('done');
    }

    window.scrollTo({ top: 0, behavior: 'smooth' });
}

/* ── Accordion Toggle ── */
function slaToggle(id) {
    var el = document.getElementById(id);
    if (el) el.classList.toggle('open');
}

/* ── Package Selection (Tab 3 cards → sync to Tab 4 invoice) ── */
function slaSelectPackage(pkg, el) {
    document.querySelectorAll('.sla-price-card').forEach(function(c) { c.classList.remove('selected'); });
    el.classList.add('selected');
    document.getElementById('sla_selected_package').value = pkg;

    var invRadio = document.querySelector('input[name="inv_package"][value="'+pkg+'"]');
    if (invRadio) {
        invRadio.checked = true;
        document.querySelectorAll('.sla-inv-row[data-group="package"]').forEach(function(r) {
            r.classList.remove('active');
            r.querySelector('.sla-inv-amt').textContent = '-';
        });
        var row = invRadio.closest('.sla-inv-row');
        row.classList.add('active');
        var price = parseFloat(row.getAttribute('data-price'));
        row.querySelector('.sla-inv-amt').textContent = slaFmt(price);
    }
    slaInvRecalc();
}

/* ── Payroll Selection (Tab 3 cards → sync to Tab 4 invoice) ── */
function slaSelectPayroll(pkg, el) {
    document.querySelectorAll('.sla-payroll-card').forEach(function(c) { c.classList.remove('selected'); });
    el.classList.add('selected');
    document.getElementById('sla_selected_payroll').value = pkg;

    var invRadio = document.querySelector('input[name="inv_payroll"][value="'+pkg+'"]');
    if (invRadio) {
        invRadio.checked = true;
        document.querySelectorAll('.sla-inv-row[data-group="payroll"]').forEach(function(r) {
            r.classList.remove('active');
            r.querySelector('.sla-inv-amt').textContent = '-';
        });
        var row = invRadio.closest('.sla-inv-row');
        row.classList.add('active');
    }
    slaInvRecalc();
}

/* ── Addon Toggle ── */
function slaToggleAddon(cb) {
    var card = cb.closest('.sla-addon-card');
    if (cb.checked) {
        card.classList.add('checked');
    } else {
        card.classList.remove('checked');
    }
}

/* ── Promo Toggle ── */
function slaTogglePromo(cb) {
    var toggle = cb.closest('.sla-promo-toggle');
    if (cb.checked) {
        toggle.classList.add('checked');
    } else {
        toggle.classList.remove('checked');
    }
}

/* ── Payslip Toggle (inline) ── */
document.addEventListener('change', function(e) {
    if (e.target.name === 'addon_payslips') {
        var label = e.target.closest('.sla-payslip-toggle');
        if (label) {
            if (e.target.checked) { label.classList.add('checked'); }
            else { label.classList.remove('checked'); }
        }
    }
});

/* ── Invoice Calculation Engine ── */
function slaFmt(amount) {
    return 'R' + amount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function slaInvSetDate() {
    var el = document.getElementById('slaBillingDate');
    if (!el) return;
    var now = new Date();
    var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    el.textContent = now.getDate() + ' ' + months[now.getMonth()] + ' ' + now.getFullYear();
}

function slaInvSelectPackage(radio) {
    document.querySelectorAll('.sla-inv-row[data-group="package"]').forEach(function(r) {
        r.classList.remove('active');
        r.querySelector('.sla-inv-amt').textContent = '-';
    });
    var row = radio.closest('.sla-inv-row');
    row.classList.add('active');
    var price = parseFloat(row.getAttribute('data-price'));
    row.querySelector('.sla-inv-amt').textContent = slaFmt(price);

    document.getElementById('sla_selected_package').value = radio.value;
    document.querySelectorAll('.sla-price-card').forEach(function(c) { c.classList.remove('selected'); });
    var cardIndex = {starter:0, growth:1, professional:2, enterprise:3, premium:4};
    var cards = document.querySelectorAll('.sla-price-card');
    if (cards[cardIndex[radio.value]]) cards[cardIndex[radio.value]].classList.add('selected');

    slaInvRecalc();
}

function slaInvSelectPayroll(radio) {
    document.querySelectorAll('.sla-inv-row[data-group="payroll"]').forEach(function(r) {
        r.classList.remove('active');
        r.querySelector('.sla-inv-amt').textContent = '-';
    });
    var row = radio.closest('.sla-inv-row');
    row.classList.add('active');

    document.getElementById('sla_selected_payroll').value = radio.value;
    document.querySelectorAll('.sla-payroll-card').forEach(function(c) { c.classList.remove('selected'); });
    var cardIndex = {payroll_3:0, payroll_10:1, payroll_20:2, payroll_21plus:3};
    var cards = document.querySelectorAll('.sla-payroll-card');
    if (cards[cardIndex[radio.value]]) cards[cardIndex[radio.value]].classList.add('selected');

    slaInvRecalc();
}

function slaInvToggleAddon(cb) {
    var row = cb.closest('.sla-inv-row');
    if (cb.checked) {
        row.classList.add('active');
    } else {
        row.classList.remove('active');
        row.querySelector('.sla-inv-amt').textContent = '-';
    }
    slaInvRecalc();
}

function slaInvRecalc() {
    var total = 0;

    document.querySelectorAll('.sla-inv-row.active').forEach(function(row) {
        var price = parseFloat(row.getAttribute('data-price'));
        var qtyInput = row.querySelector('.sla-inv-qty');
        var qty = qtyInput ? parseInt(qtyInput.value) || 1 : 1;
        var amount = price * qty;
        row.querySelector('.sla-inv-amt').textContent = slaFmt(amount);
        total += amount;
    });

    var vat = total * 0.15;
    var grand = total + vat;

    document.getElementById('slaBillingSubtotal').textContent = slaFmt(total);
    document.getElementById('slaBillingVat').textContent = slaFmt(vat);
    document.getElementById('slaBillingTotal').textContent = slaFmt(grand);
}

function slaRecalcBilling() { slaInvRecalc(); }

/* ── Client Data Auto-Populate ── */
function slaLoadClient(clientId) {
    if (!clientId) return;
    var url = '{{ url("nexcore/clients") }}/' + clientId + '/sla/client-data';
    fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        var map = {
            'sla_signatory_name': d.signatory_name,
            'sla_signatory_email': d.signatory_email,
            'sla_signatory_cellphone': d.signatory_cellphone,
            'sla_signatory_id_number': d.signatory_id_number,
            'sla_signatory_designation': d.signatory_designation,
            'sla_tax_reference_number': d.tax_number,
            'sla_coida_rma_number': d.coida_number,
            'sla_vat_number': d.vat_number,
            'sla_paye_number': d.paye_number,
            'sla_uif_number': d.uif_number,
            'sla_company_reg_number': d.registration_number,
            'sla_business_name': d.company_name,
            'sla_physical_address': d.physical_address
        };
        for (var id in map) {
            var el = document.getElementById(id);
            if (el && map[id]) el.value = map[id];
        }
    })
    .catch(function(e) { console.error('Failed to load client data:', e); });
}

/* ── Signature Pad ── */
var sigCanvas, sigCtx, sigDrawing = false;

function initSignaturePad() {
    sigCanvas = document.getElementById('slaSignaturePad');
    if (!sigCanvas) return;
    sigCtx = sigCanvas.getContext('2d');
    sigCtx.strokeStyle = '#1a1a2e';
    sigCtx.lineWidth = 2;
    sigCtx.lineCap = 'round';
    sigCtx.lineJoin = 'round';

    sigCanvas.addEventListener('mousedown', sigStart);
    sigCanvas.addEventListener('mousemove', sigMove);
    sigCanvas.addEventListener('mouseup', sigEnd);
    sigCanvas.addEventListener('mouseleave', sigEnd);
    sigCanvas.addEventListener('touchstart', function(e) { e.preventDefault(); sigStart(e.touches[0]); });
    sigCanvas.addEventListener('touchmove', function(e) { e.preventDefault(); sigMove(e.touches[0]); });
    sigCanvas.addEventListener('touchend', sigEnd);
}

function sigStart(e) {
    sigDrawing = true;
    sigCtx.beginPath();
    var r = sigCanvas.getBoundingClientRect();
    sigCtx.moveTo(e.clientX - r.left, e.clientY - r.top);
}

function sigMove(e) {
    if (!sigDrawing) return;
    var r = sigCanvas.getBoundingClientRect();
    sigCtx.lineTo(e.clientX - r.left, e.clientY - r.top);
    sigCtx.stroke();
}

function sigEnd() {
    sigDrawing = false;
}

function slaClearSignature() {
    if (sigCtx) sigCtx.clearRect(0, 0, sigCanvas.width, sigCanvas.height);
}

function slaToggleSigMode(mode, btn) {
    document.querySelectorAll('.sla-sig-toggle-btn').forEach(function(b) { b.classList.remove('active'); });
    btn.classList.add('active');
    document.getElementById('sla_signature_type').value = mode === 'draw' ? 'drawn' : 'typed';
    document.getElementById('sla-sig-draw-area').style.display = mode === 'draw' ? 'block' : 'none';
    document.getElementById('sla-sig-type-area').style.display = mode === 'type' ? 'block' : 'none';
}

function slaUpdateTypedSig() {
    document.getElementById('sla_signature_data').value = document.getElementById('slaTypedSignature').value;
}

function slaPrepareSig() {
    var sigType = document.getElementById('sla_signature_type').value;
    if (sigType === 'drawn' && sigCanvas) {
        document.getElementById('sla_signature_data').value = sigCanvas.toDataURL('image/png');
    } else {
        slaUpdateTypedSig();
    }
}
</script>
@endpush
