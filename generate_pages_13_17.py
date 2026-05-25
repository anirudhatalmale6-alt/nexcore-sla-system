#!/usr/bin/env python3
"""
Generate ATP Services Proposal Pages 13-17 as individual A4 PDFs.
Navy blue (#0d1b3e) + Gold (#c9a54e) corporate luxury theme.
Uses WeasyPrint to convert HTML/CSS to PDF.
"""

import os
from weasyprint import HTML

OUTPUT_DIR = os.path.dirname(os.path.abspath(__file__))

# ─── Shared CSS ───────────────────────────────────────────────────────────────

COMMON_CSS = """
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;800;900&family=Montserrat:wght@300;400;500;600;700&display=swap');

@page {
    size: 210mm 297mm;
    margin: 0;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    width: 210mm;
    height: 297mm;
    margin: 0;
    padding: 0;
    font-family: 'Montserrat', sans-serif;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}

.page {
    width: 210mm;
    height: 297mm;
    position: relative;
    overflow: hidden;
}

.page-number {
    position: absolute;
    bottom: 18mm;
    left: 50%;
    transform: translateX(-50%);
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #c9a54e;
    color: #ffffff;
    font-family: 'Montserrat', sans-serif;
    font-weight: 700;
    font-size: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
}

.title-section {
    text-align: center;
    padding-top: 28mm;
    margin-bottom: 8mm;
}

.title-section h1 {
    font-family: 'Playfair Display', serif;
    font-weight: 800;
    font-size: 30px;
    color: #0d1b3e;
    letter-spacing: 4px;
    margin-bottom: 8px;
    text-transform: uppercase;
}

.title-underline {
    width: 80px;
    height: 3px;
    background: #c9a54e;
    margin: 0 auto;
}
"""

# ─── SVG Building Icon (reused on page 17) ────────────────────────────────────

BUILDING_SVG = """
<svg width="70" height="80" viewBox="0 0 70 80" fill="none" xmlns="http://www.w3.org/2000/svg">
  <rect x="10" y="20" width="50" height="58" fill="#c9a54e" opacity="0.15" stroke="#c9a54e" stroke-width="1.5"/>
  <rect x="18" y="0" width="34" height="78" fill="#c9a54e" opacity="0.25" stroke="#c9a54e" stroke-width="1.5"/>
  <!-- Windows -->
  <rect x="24" y="10" width="8" height="8" fill="#c9a54e" opacity="0.6"/>
  <rect x="38" y="10" width="8" height="8" fill="#c9a54e" opacity="0.6"/>
  <rect x="24" y="24" width="8" height="8" fill="#c9a54e" opacity="0.6"/>
  <rect x="38" y="24" width="8" height="8" fill="#c9a54e" opacity="0.6"/>
  <rect x="24" y="38" width="8" height="8" fill="#c9a54e" opacity="0.6"/>
  <rect x="38" y="38" width="8" height="8" fill="#c9a54e" opacity="0.6"/>
  <rect x="24" y="52" width="8" height="8" fill="#c9a54e" opacity="0.6"/>
  <rect x="38" y="52" width="8" height="8" fill="#c9a54e" opacity="0.6"/>
  <!-- Door -->
  <rect x="29" y="64" width="12" height="14" fill="#c9a54e" opacity="0.8"/>
  <!-- Antenna -->
  <line x1="35" y1="0" x2="35" y2="-8" stroke="#c9a54e" stroke-width="1.5"/>
  <circle cx="35" cy="-10" r="2.5" fill="#c9a54e"/>
</svg>
"""

# ─────────────────────────────────────────────────────────────────────────────
# PAGE 13 - QUOTATION
# ─────────────────────────────────────────────────────────────────────────────

def page_13():
    html = f"""<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
{COMMON_CSS}

.page {{
    background: #f5f0e8;
    padding: 0 22mm;
}}

.info-block {{
    position: absolute;
    top: 28mm;
    right: 22mm;
    text-align: right;
    font-size: 11px;
    color: #0d1b3e;
    line-height: 2;
}}

.info-block .label {{
    font-weight: 600;
    color: #0d1b3e;
}}

.info-block .value {{
    font-weight: 400;
    color: #444;
}}

.info-block .client-line {{
    border-bottom: 1px solid #c9a54e;
    display: inline-block;
    min-width: 140px;
}}

.title-section {{
    text-align: left;
    padding-top: 30mm;
}}

.title-section h1 {{
    text-align: left;
    font-size: 34px;
    letter-spacing: 5px;
}}

.title-underline {{
    margin: 0;
}}

.quotation-table {{
    width: 100%;
    margin-top: 14mm;
    border-collapse: collapse;
    font-size: 12px;
}}

.quotation-table thead th {{
    background: #0d1b3e;
    color: #ffffff;
    font-weight: 600;
    padding: 12px 16px;
    text-align: left;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-size: 11px;
    border-bottom: 3px solid #c9a54e;
}}

.quotation-table thead th:last-child {{
    text-align: right;
}}

.quotation-table tbody td {{
    padding: 14px 16px;
    border-bottom: 1px solid #ddd;
    color: #333;
    font-size: 12.5px;
}}

.quotation-table tbody td:last-child {{
    text-align: right;
    font-weight: 500;
    font-family: 'Montserrat', sans-serif;
}}

.quotation-table tbody tr:nth-child(even) {{
    background: rgba(197, 165, 78, 0.06);
}}

.quotation-table tbody tr:hover {{
    background: rgba(197, 165, 78, 0.1);
}}

.summary-table {{
    width: 100%;
    margin-top: 6mm;
    border-collapse: collapse;
    font-size: 12.5px;
}}

.summary-table tr td {{
    padding: 10px 16px;
    color: #0d1b3e;
    font-weight: 600;
}}

.summary-table tr td:first-child {{
    text-align: right;
    width: 70%;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-size: 11px;
}}

.summary-table tr td:last-child {{
    text-align: right;
    width: 30%;
}}

.summary-table .subtotal {{
    background: #0d1b3e;
    color: #ffffff;
}}

.summary-table .subtotal td {{
    color: #ffffff;
    padding: 9px 16px;
}}

.summary-table .vat-row td {{
    background: #e8e0d0;
    padding: 9px 16px;
}}

.summary-table .total-row {{
    background: #0d1b3e;
}}

.summary-table .total-row td {{
    color: #c9a54e;
    font-size: 15px;
    font-weight: 700;
    padding: 13px 16px;
    border-top: 3px solid #c9a54e;
}}

.footer-note {{
    margin-top: 12mm;
    font-size: 10.5px;
    color: #777;
    text-align: center;
    font-style: italic;
    letter-spacing: 0.3px;
}}

.gold-border-top {{
    border-top: 3px solid #c9a54e;
    margin-top: 4mm;
    margin-bottom: 4mm;
}}
</style>
</head>
<body>
<div class="page">

    <div class="info-block">
        <div><span class="label">Client:</span> <span class="client-line">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></div>
        <div><span class="label">Quotation Number:</span> <span class="value">ATP/2024/001</span></div>
        <div><span class="label">Date:</span> <span class="value">20 May 2024</span></div>
        <div><span class="label">Valid Until:</span> <span class="value">20 June 2024</span></div>
    </div>

    <div class="title-section">
        <h1>Quotation</h1>
        <div class="title-underline"></div>
    </div>

    <table class="quotation-table">
        <thead>
            <tr>
                <th>Service</th>
                <th>Frequency</th>
                <th>Price (ZAR)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Accounting Services</td>
                <td>Monthly</td>
                <td>R 2,750.00</td>
            </tr>
            <tr>
                <td>Payroll Services</td>
                <td>Monthly</td>
                <td>R 1,850.00</td>
            </tr>
            <tr>
                <td>Taxation Services</td>
                <td>Monthly</td>
                <td>R 1,950.00</td>
            </tr>
            <tr>
                <td>Business Advisory</td>
                <td>Monthly</td>
                <td>R 1,500.00</td>
            </tr>
        </tbody>
    </table>

    <table class="summary-table">
        <tr class="subtotal">
            <td>Total (Excl VAT)</td>
            <td>R 8,050.00</td>
        </tr>
        <tr class="vat-row">
            <td>VAT (15%)</td>
            <td>R 1,207.50</td>
        </tr>
        <tr class="total-row">
            <td>Total (Incl VAT)</td>
            <td>R 9,257.50</td>
        </tr>
    </table>

    <p class="footer-note">All fees are exclusive of VAT. This quotation is valid for 30 days.</p>

    <div class="page-number">13</div>
</div>
</body>
</html>"""
    return html


# ─────────────────────────────────────────────────────────────────────────────
# PAGE 14 - SERVICE LEVEL AGREEMENT
# ─────────────────────────────────────────────────────────────────────────────

def page_14():
    sections = [
        ("1", "Scope of Services", "ATP Services will provide the accounting, taxation, payroll, and business advisory services as detailed in this proposal and quotation, tailored to the specific needs of the client."),
        ("2", "Client Responsibilities", "The client shall provide all necessary financial records, supporting documents, and information required for service delivery within agreed timeframes."),
        ("3", "ATP Responsibilities", "ATP Services commits to delivering all services with professionalism, accuracy, and in compliance with applicable South African legislation and standards."),
        ("4", "Delivery Timeframes", "Monthly deliverables will be completed within 10 business days of receiving all required documentation. Annual returns will follow SARS deadlines."),
        ("5", "Confidentiality", "All client information is treated as strictly confidential and will not be disclosed to third parties without prior written consent, except as required by law."),
        ("6", "Service Exclusions", "This agreement does not cover legal representation, forensic investigations, company secretarial services, or any services not explicitly listed in the scope."),
        ("7", "Escalation Process", "Any service concerns should be raised with the assigned accountant first. Unresolved issues will be escalated to a senior partner within 48 hours."),
    ]

    sections_html = ""
    for num, title, desc in sections:
        sections_html += f"""
        <div class="sla-item">
            <div class="sla-num">{num}</div>
            <div class="sla-content">
                <h3>{title.upper()}</h3>
                <p>{desc}</p>
            </div>
        </div>
        """

    html = f"""<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
{COMMON_CSS}

.page {{
    background: #f5f0e8;
    padding: 0 22mm;
}}

.sla-list {{
    margin-top: 6mm;
}}

.sla-item {{
    display: flex;
    align-items: flex-start;
    margin-bottom: 5mm;
    gap: 14px;
}}

.sla-num {{
    flex-shrink: 0;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: linear-gradient(135deg, #c9a54e, #dbb960);
    color: #0d1b3e;
    font-family: 'Playfair Display', serif;
    font-weight: 700;
    font-size: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 2px;
    box-shadow: 0 2px 6px rgba(201, 165, 78, 0.3);
}}

.sla-content h3 {{
    font-family: 'Playfair Display', serif;
    font-weight: 700;
    font-size: 13px;
    color: #0d1b3e;
    letter-spacing: 1.5px;
    margin-bottom: 3px;
    text-transform: uppercase;
}}

.sla-content p {{
    font-size: 10.5px;
    color: #444;
    line-height: 1.55;
    letter-spacing: 0.2px;
}}
</style>
</head>
<body>
<div class="page">

    <div class="title-section">
        <h1>Service Level Agreement</h1>
        <div class="title-underline"></div>
    </div>

    <div class="sla-list">
        {sections_html}
    </div>

    <div class="page-number">14</div>
</div>
</body>
</html>"""
    return html


# ─────────────────────────────────────────────────────────────────────────────
# PAGE 15 - TERMS AND CONDITIONS
# ─────────────────────────────────────────────────────────────────────────────

def page_15():
    terms = [
        ("01", "Payment Terms", "All invoices are due within 7 days of issue. Monthly retainer fees are payable in advance by the 1st of each month. Late payments may incur interest at 2% per month on outstanding balances."),
        ("02", "POPIA Compliance", "ATP Services is fully compliant with the Protection of Personal Information Act (POPIA). All personal and financial data is processed lawfully, stored securely, and only used for stated purposes."),
        ("03", "Cancellation", "Either party may terminate this agreement with 30 days' written notice. Outstanding fees for services already rendered remain payable upon cancellation."),
        ("04", "Limitation of Liability", "ATP Services' total liability is limited to the fees paid for the specific service giving rise to the claim. We are not liable for indirect or consequential losses."),
        ("05", "Confidentiality", "Both parties agree to maintain strict confidentiality of all proprietary and financial information shared during the engagement, surviving termination of this agreement."),
        ("06", "Dispute Resolution", "Any disputes arising from this agreement will first be addressed through good-faith negotiation. If unresolved within 14 days, the matter will be referred to mediation or arbitration."),
    ]

    terms_html = ""
    for num, title, desc in terms:
        terms_html += f"""
        <div class="term-item">
            <div class="term-num">{num}</div>
            <div class="term-content">
                <h3>{title.upper()}</h3>
                <p>{desc}</p>
            </div>
        </div>
        """

    html = f"""<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
{COMMON_CSS}

.page {{
    background: #f5f0e8;
    padding: 0 22mm;
}}

.terms-list {{
    margin-top: 8mm;
}}

.term-item {{
    display: flex;
    align-items: flex-start;
    margin-bottom: 7mm;
    gap: 16px;
}}

.term-num {{
    flex-shrink: 0;
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: linear-gradient(135deg, #c9a54e, #dbb960);
    color: #0d1b3e;
    font-family: 'Montserrat', sans-serif;
    font-weight: 700;
    font-size: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(201, 165, 78, 0.3);
}}

.term-content h3 {{
    font-family: 'Playfair Display', serif;
    font-weight: 700;
    font-size: 14px;
    color: #0d1b3e;
    letter-spacing: 1.5px;
    margin-bottom: 4px;
    text-transform: uppercase;
}}

.term-content p {{
    font-size: 10.5px;
    color: #444;
    line-height: 1.6;
    letter-spacing: 0.2px;
}}

.gold-divider {{
    width: 100%;
    height: 1px;
    background: linear-gradient(90deg, transparent, #c9a54e, transparent);
    margin-top: 5mm;
}}
</style>
</head>
<body>
<div class="page">

    <div class="title-section">
        <h1>Terms &amp; Conditions</h1>
        <div class="title-underline"></div>
    </div>

    <div class="terms-list">
        {terms_html}
    </div>

    <div class="page-number">15</div>
</div>
</body>
</html>"""
    return html


# ─────────────────────────────────────────────────────────────────────────────
# PAGE 16 - ACCEPTANCE AND SIGNATURE
# ─────────────────────────────────────────────────────────────────────────────

def page_16():
    html = f"""<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
{COMMON_CSS}

.page {{
    background: #f5f0e8;
    padding: 0 22mm;
}}

.intro-text {{
    margin-top: 6mm;
    font-size: 12px;
    color: #333;
    line-height: 1.7;
    text-align: center;
    padding: 0 10mm;
    letter-spacing: 0.2px;
}}

.signature-columns {{
    display: flex;
    gap: 12mm;
    margin-top: 12mm;
}}

.sig-column {{
    flex: 1;
    border: 1.5px solid #0d1b3e;
    border-radius: 4px;
    padding: 18px 20px;
    background: rgba(255,255,255,0.6);
}}

.sig-column-title {{
    font-family: 'Playfair Display', serif;
    font-weight: 700;
    font-size: 14px;
    color: #0d1b3e;
    letter-spacing: 2px;
    text-transform: uppercase;
    text-align: center;
    margin-bottom: 5mm;
    padding-bottom: 3mm;
    border-bottom: 2px solid #c9a54e;
}}

.sig-field {{
    margin-bottom: 6mm;
}}

.sig-field label {{
    display: block;
    font-size: 10px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 1.2px;
    font-weight: 600;
    margin-bottom: 4px;
}}

.sig-field .line {{
    border-bottom: 1px solid #0d1b3e;
    height: 22px;
    width: 100%;
}}

.sig-field .sig-box {{
    border: 1.5px solid #0d1b3e;
    border-radius: 3px;
    height: 55px;
    width: 100%;
    background: rgba(255,255,255,0.8);
}}

.gold-stamp {{
    text-align: center;
    margin-top: 14mm;
}}

.gold-stamp .stamp-circle {{
    display: inline-block;
    width: 60px;
    height: 60px;
    border: 3px solid #c9a54e;
    border-radius: 50%;
    position: relative;
}}

.gold-stamp .stamp-inner {{
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-family: 'Playfair Display', serif;
    font-weight: 800;
    font-size: 14px;
    color: #c9a54e;
    letter-spacing: 1px;
}}

.stamp-label {{
    font-size: 9px;
    color: #999;
    text-transform: uppercase;
    letter-spacing: 2px;
    margin-top: 5px;
}}

.agreement-footer {{
    text-align: center;
    margin-top: 10mm;
    font-size: 9.5px;
    color: #888;
    font-style: italic;
}}
</style>
</head>
<body>
<div class="page">

    <div class="title-section">
        <h1>Acceptance &amp; Signature</h1>
        <div class="title-underline"></div>
    </div>

    <p class="intro-text">
        By signing below, you confirm that you accept this proposal, the quotation,
        the Service Level Agreement and the Terms &amp; Conditions as set out in this document.
    </p>

    <div class="signature-columns">
        <!-- Client -->
        <div class="sig-column">
            <div class="sig-column-title">Client Details</div>
            <div class="sig-field">
                <label>Client Company</label>
                <div class="line"></div>
            </div>
            <div class="sig-field">
                <label>Authorised Representative</label>
                <div class="line"></div>
            </div>
            <div class="sig-field">
                <label>Signature</label>
                <div class="sig-box"></div>
            </div>
            <div class="sig-field">
                <label>Date</label>
                <div class="line"></div>
            </div>
        </div>

        <!-- ATP -->
        <div class="sig-column">
            <div class="sig-column-title">ATP Services</div>
            <div class="sig-field">
                <label>Authorised Representative</label>
                <div class="line"></div>
            </div>
            <div class="sig-field">
                <label>Signature</label>
                <div class="sig-box"></div>
            </div>
            <div class="sig-field">
                <label>Date</label>
                <div class="line"></div>
            </div>
        </div>
    </div>

    <div class="gold-stamp">
        <div class="stamp-circle">
            <span class="stamp-inner">ATP</span>
        </div>
        <div class="stamp-label">Official Document</div>
    </div>

    <p class="agreement-footer">This agreement is governed by the laws of the Republic of South Africa.</p>

    <div class="page-number">16</div>
</div>
</body>
</html>"""
    return html


# ─────────────────────────────────────────────────────────────────────────────
# PAGE 17 - THANK YOU / BACK COVER
# ─────────────────────────────────────────────────────────────────────────────

def page_17():
    html = f"""<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
{COMMON_CSS}

.page {{
    background: #0d1b3e;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 0 25mm;
}}

.logo-area {{
    margin-bottom: 10mm;
}}

.logo-area svg {{
    margin-bottom: 8px;
}}

.logo-text {{
    font-family: 'Playfair Display', serif;
    font-weight: 900;
    font-size: 42px;
    color: #c9a54e;
    letter-spacing: 10px;
    margin-top: 6px;
}}

.logo-sub {{
    font-family: 'Montserrat', sans-serif;
    font-weight: 400;
    font-size: 13px;
    color: #ffffff;
    letter-spacing: 6px;
    text-transform: uppercase;
    margin-top: 2px;
}}

.company-full {{
    font-family: 'Montserrat', sans-serif;
    font-size: 8.5px;
    color: rgba(255,255,255,0.45);
    letter-spacing: 3px;
    text-transform: uppercase;
    margin-top: 8mm;
    line-height: 1.8;
}}

.thank-you {{
    font-family: 'Playfair Display', serif;
    font-weight: 900;
    font-size: 52px;
    color: #c9a54e;
    letter-spacing: 8px;
    margin-top: 15mm;
    margin-bottom: 6mm;
    text-transform: uppercase;
}}

.tagline {{
    font-family: 'Montserrat', sans-serif;
    font-size: 12px;
    color: rgba(255,255,255,0.8);
    letter-spacing: 2px;
    line-height: 1.9;
    font-weight: 300;
    font-style: italic;
    max-width: 400px;
    margin: 0 auto;
}}

.gold-divider {{
    width: 60px;
    height: 2px;
    background: #c9a54e;
    margin: 12mm auto 10mm auto;
}}

.pillars {{
    display: flex;
    justify-content: center;
    gap: 18px;
    margin-top: 2mm;
}}

.pillar {{
    text-align: center;
    width: 100px;
}}

.pillar-icon {{
    width: 38px;
    height: 38px;
    border: 1.5px solid #c9a54e;
    border-radius: 50%;
    margin: 0 auto 6px auto;
    display: flex;
    align-items: center;
    justify-content: center;
}}

.pillar-icon svg {{
    width: 18px;
    height: 18px;
}}

.pillar-label {{
    font-family: 'Montserrat', sans-serif;
    font-size: 7.5px;
    color: #c9a54e;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    font-weight: 600;
    line-height: 1.4;
}}
</style>
</head>
<body>
<div class="page">

    <div class="logo-area">
        {BUILDING_SVG}
        <div class="logo-text">ATP</div>
        <div class="logo-sub">Services</div>
    </div>

    <div class="company-full">
        Accounting Taxation and Payroll (Pty) Ltd<br>Trading as ATP Services
    </div>

    <div class="thank-you">Thank You</div>

    <div class="tagline">
        Your Business. Our Priority.<br>
        Your Success. Our Commitment.
    </div>

    <div class="gold-divider"></div>

    <div class="pillars">
        <div class="pillar">
            <div class="pillar-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="#c9a54e" stroke-width="1.5">
                    <path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 16.8l-6.2 4.5 2.4-7.4L2 9.4h7.6z"/>
                </svg>
            </div>
            <div class="pillar-label">Professional<br>Excellence</div>
        </div>
        <div class="pillar">
            <div class="pillar-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="#c9a54e" stroke-width="1.5">
                    <path d="M16 4h2a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2h2"/>
                    <path d="M9 2h6v4H9z"/>
                    <path d="M9 14l2 2 4-4"/>
                </svg>
            </div>
            <div class="pillar-label">Trusted<br>Partner</div>
        </div>
        <div class="pillar">
            <div class="pillar-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="#c9a54e" stroke-width="1.5">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 6v6l4 2"/>
                </svg>
            </div>
            <div class="pillar-label">Lasting<br>Value</div>
        </div>
        <div class="pillar">
            <div class="pillar-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="#c9a54e" stroke-width="1.5">
                    <path d="M3 17l6-6 4 4 8-8"/>
                    <path d="M17 7h4v4"/>
                </svg>
            </div>
            <div class="pillar-label">Your<br>Growth</div>
        </div>
    </div>

</div>
</body>
</html>"""
    return html


# ─────────────────────────────────────────────────────────────────────────────
# GENERATE ALL PAGES
# ─────────────────────────────────────────────────────────────────────────────

def generate_pdf(html_content, filename):
    filepath = os.path.join(OUTPUT_DIR, filename)
    HTML(string=html_content).write_pdf(filepath)
    size_kb = os.path.getsize(filepath) / 1024
    print(f"  ✓ {filename}  ({size_kb:.1f} KB)")
    return filepath


def main():
    print("=" * 60)
    print("  ATP SERVICES PROPOSAL - Pages 13-17")
    print("  Navy + Gold Corporate Luxury Theme")
    print("=" * 60)
    print()

    pages = [
        (page_13(), "page-13-quotation.pdf"),
        (page_14(), "page-14-sla.pdf"),
        (page_15(), "page-15-terms.pdf"),
        (page_16(), "page-16-signature.pdf"),
        (page_17(), "page-17-thankyou.pdf"),
    ]

    for html_content, filename in pages:
        generate_pdf(html_content, filename)

    print()
    print("All 5 PDFs generated successfully.")
    print(f"Output directory: {OUTPUT_DIR}")


if __name__ == "__main__":
    main()
