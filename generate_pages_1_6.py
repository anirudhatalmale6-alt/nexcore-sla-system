#!/usr/bin/env python3
"""
ATP Services Business Proposal - Pages 1-6 Generator
Generates 6 individual A4 PDF files using WeasyPrint.
"""

import os
import weasyprint

OUTPUT_DIR = os.path.dirname(os.path.abspath(__file__))

# ─── Shared CSS ────────────────────────────────────────────────────────────────

GOOGLE_FONTS_IMPORT = """
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400;1,700&family=Montserrat:wght@300;400;500;600;700&display=swap');
"""

BASE_CSS = GOOGLE_FONTS_IMPORT + """
@page {
    size: 210mm 297mm;
    margin: 0;
}
*, *::before, *::after {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}
html, body {
    width: 210mm;
    height: 297mm;
    margin: 0;
    padding: 0;
    overflow: hidden;
}
body {
    font-family: 'Montserrat', sans-serif;
    color: #0d1b3e;
    -webkit-font-smoothing: antialiased;
}
h1, h2, h3, h4 {
    font-family: 'Playfair Display', serif;
}

/* Page number gold circle */
.page-number {
    position: fixed;
    bottom: 18mm;
    width: 100%;
    text-align: center;
    z-index: 100;
}
.page-number-right {
    position: fixed;
    bottom: 18mm;
    right: 20mm;
    z-index: 100;
}
.page-num-circle {
    display: inline-block;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #c9a54e;
    color: #ffffff;
    font-family: 'Montserrat', sans-serif;
    font-weight: 700;
    font-size: 12px;
    line-height: 32px;
    text-align: center;
}

/* Gold underline */
.gold-underline {
    display: block;
    width: 60px;
    height: 3px;
    background: #c9a54e;
    margin-top: 10px;
    margin-bottom: 20px;
}
.gold-underline-long {
    display: block;
    width: 120px;
    height: 3px;
    background: #c9a54e;
    margin-top: 10px;
    margin-bottom: 20px;
}
"""

# ─── SVG ICONS ─────────────────────────────────────────────────────────────────

SVG_BUILDING = """
<svg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg">
  <rect x="15" y="18" width="30" height="32" rx="2" fill="none" stroke="#c9a54e" stroke-width="2"/>
  <rect x="10" y="14" width="40" height="6" rx="1" fill="#c9a54e" opacity="0.3"/>
  <polygon points="30,6 10,14 50,14" fill="#c9a54e"/>
  <rect x="21" y="24" width="6" height="6" rx="1" fill="#c9a54e" opacity="0.6"/>
  <rect x="33" y="24" width="6" height="6" rx="1" fill="#c9a54e" opacity="0.6"/>
  <rect x="21" y="34" width="6" height="6" rx="1" fill="#c9a54e" opacity="0.6"/>
  <rect x="33" y="34" width="6" height="6" rx="1" fill="#c9a54e" opacity="0.6"/>
  <rect x="26" y="42" width="8" height="8" rx="1" fill="#c9a54e"/>
</svg>
"""

SVG_PIN = """
<svg width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
  <path d="M8 1C5.24 1 3 3.24 3 6c0 3.75 5 9 5 9s5-5.25 5-9c0-2.76-2.24-5-5-5zm0 7.5a2.5 2.5 0 110-5 2.5 2.5 0 010 5z" fill="#c9a54e"/>
</svg>
"""

SVG_CALENDAR = """
<svg width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
  <rect x="1" y="3" width="14" height="12" rx="2" fill="none" stroke="#c9a54e" stroke-width="1.5"/>
  <line x1="1" y1="7" x2="15" y2="7" stroke="#c9a54e" stroke-width="1.5"/>
  <line x1="5" y1="1" x2="5" y2="5" stroke="#c9a54e" stroke-width="1.5"/>
  <line x1="11" y1="1" x2="11" y2="5" stroke="#c9a54e" stroke-width="1.5"/>
</svg>
"""

SVG_CHECKMARK = """
<svg width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
  <circle cx="8" cy="8" r="7" fill="none" stroke="#c9a54e" stroke-width="1.5"/>
  <polyline points="4.5,8 7,10.5 11.5,5.5" fill="none" stroke="#c9a54e" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
"""

SVG_GOLD_CHECK = """
<svg width="18" height="18" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
  <polyline points="3,9 7,13 15,5" fill="none" stroke="#c9a54e" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
"""

SVG_CHART = """
<svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
  <rect x="4" y="22" width="7" height="14" rx="1" fill="#c9a54e"/>
  <rect x="16" y="14" width="7" height="22" rx="1" fill="#c9a54e" opacity="0.8"/>
  <rect x="28" y="6" width="7" height="30" rx="1" fill="#c9a54e" opacity="0.6"/>
</svg>
"""

SVG_LIGHTBULB = """
<svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
  <path d="M20 4c-5.52 0-10 4.48-10 10 0 3.7 2.02 6.93 5 8.66V26a2 2 0 002 2h6a2 2 0 002-2v-3.34c2.98-1.73 5-4.96 5-8.66 0-5.52-4.48-10-10-10z" fill="#c9a54e" opacity="0.8"/>
  <rect x="16" y="30" width="8" height="2" rx="1" fill="#c9a54e"/>
  <rect x="17" y="34" width="6" height="2" rx="1" fill="#c9a54e"/>
</svg>
"""

SVG_SHIELD = """
<svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
  <path d="M20 3L6 10v9c0 8.28 5.97 16.01 14 17.93C28.03 35.01 34 27.28 34 19v-9L20 3z" fill="#c9a54e" opacity="0.8"/>
  <polyline points="14,20 18,24 26,16" fill="none" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
"""

SVG_ARROW_UP = """
<svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
  <circle cx="20" cy="20" r="16" fill="#c9a54e" opacity="0.8"/>
  <polyline points="14,22 20,14 26,22" fill="none" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
  <line x1="20" y1="14" x2="20" y2="28" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round"/>
</svg>
"""

SVG_INTEGRITY = """
<svg width="36" height="36" viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg">
  <path d="M18 3L4 10v8c0 7.45 5.37 14.41 14 16 8.63-1.59 14-8.55 14-16v-8L18 3z" fill="none" stroke="#c9a54e" stroke-width="2"/>
  <polyline points="12,18 16,22 24,14" fill="none" stroke="#c9a54e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
"""

SVG_EXCELLENCE = """
<svg width="36" height="36" viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg">
  <polygon points="18,3 22,13 33,13 24,20 27,31 18,24 9,31 12,20 3,13 14,13" fill="none" stroke="#c9a54e" stroke-width="2" stroke-linejoin="round"/>
</svg>
"""

SVG_ACCOUNTABILITY = """
<svg width="36" height="36" viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg">
  <circle cx="18" cy="18" r="14" fill="none" stroke="#c9a54e" stroke-width="2"/>
  <polyline points="18,8 18,18 26,22" fill="none" stroke="#c9a54e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
"""

SVG_INNOVATION = """
<svg width="36" height="36" viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg">
  <circle cx="18" cy="14" r="10" fill="none" stroke="#c9a54e" stroke-width="2"/>
  <line x1="18" y1="4" x2="18" y2="8" stroke="#c9a54e" stroke-width="2" stroke-linecap="round"/>
  <line x1="18" y1="20" x2="18" y2="24" stroke="#c9a54e" stroke-width="2" stroke-linecap="round"/>
  <rect x="14" y="26" width="8" height="2" rx="1" fill="#c9a54e"/>
  <rect x="15" y="30" width="6" height="2" rx="1" fill="#c9a54e"/>
  <line x1="26" y1="8" x2="23" y2="10" stroke="#c9a54e" stroke-width="2" stroke-linecap="round"/>
  <line x1="10" y1="8" x2="13" y2="10" stroke="#c9a54e" stroke-width="2" stroke-linecap="round"/>
</svg>
"""

SVG_PARTNERSHIP = """
<svg width="36" height="36" viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg">
  <path d="M6 18c0 0 4-6 12-6s12 6 12 6" fill="none" stroke="#c9a54e" stroke-width="2" stroke-linecap="round"/>
  <circle cx="13" cy="10" r="4" fill="none" stroke="#c9a54e" stroke-width="2"/>
  <circle cx="23" cy="10" r="4" fill="none" stroke="#c9a54e" stroke-width="2"/>
  <path d="M10 22v6M18 20v8M26 22v6" stroke="#c9a54e" stroke-width="2" stroke-linecap="round"/>
</svg>
"""

SVG_CALCULATOR = """
<svg width="44" height="44" viewBox="0 0 44 44" xmlns="http://www.w3.org/2000/svg">
  <rect x="8" y="4" width="28" height="36" rx="3" fill="none" stroke="#c9a54e" stroke-width="2"/>
  <rect x="12" y="8" width="20" height="8" rx="1" fill="#c9a54e" opacity="0.3"/>
  <circle cx="16" cy="22" r="2" fill="#c9a54e"/>
  <circle cx="22" cy="22" r="2" fill="#c9a54e"/>
  <circle cx="28" cy="22" r="2" fill="#c9a54e"/>
  <circle cx="16" cy="28" r="2" fill="#c9a54e"/>
  <circle cx="22" cy="28" r="2" fill="#c9a54e"/>
  <circle cx="28" cy="28" r="2" fill="#c9a54e"/>
  <circle cx="16" cy="34" r="2" fill="#c9a54e"/>
  <rect x="21" y="33" width="9" height="4" rx="1" fill="#c9a54e"/>
</svg>
"""

SVG_DOCUMENT = """
<svg width="44" height="44" viewBox="0 0 44 44" xmlns="http://www.w3.org/2000/svg">
  <path d="M12 4h14l10 10v26a2 2 0 01-2 2H12a2 2 0 01-2-2V6a2 2 0 012-2z" fill="none" stroke="#c9a54e" stroke-width="2"/>
  <path d="M26 4v10h10" fill="none" stroke="#c9a54e" stroke-width="2"/>
  <line x1="16" y1="22" x2="28" y2="22" stroke="#c9a54e" stroke-width="1.5"/>
  <line x1="16" y1="27" x2="28" y2="27" stroke="#c9a54e" stroke-width="1.5"/>
  <line x1="16" y1="32" x2="24" y2="32" stroke="#c9a54e" stroke-width="1.5"/>
</svg>
"""

SVG_PEOPLE = """
<svg width="44" height="44" viewBox="0 0 44 44" xmlns="http://www.w3.org/2000/svg">
  <circle cx="16" cy="14" r="5" fill="none" stroke="#c9a54e" stroke-width="2"/>
  <circle cx="30" cy="14" r="5" fill="none" stroke="#c9a54e" stroke-width="2"/>
  <path d="M6 34c0-6 4-10 10-10s10 4 10 10" fill="none" stroke="#c9a54e" stroke-width="2"/>
  <path d="M20 34c0-6 4-10 10-10s10 4 10 10" fill="none" stroke="#c9a54e" stroke-width="2"/>
</svg>
"""

SVG_BRIEFCASE = """
<svg width="44" height="44" viewBox="0 0 44 44" xmlns="http://www.w3.org/2000/svg">
  <rect x="4" y="14" width="36" height="24" rx="3" fill="none" stroke="#c9a54e" stroke-width="2"/>
  <path d="M16 14V10a2 2 0 012-2h8a2 2 0 012 2v4" fill="none" stroke="#c9a54e" stroke-width="2"/>
  <line x1="4" y1="24" x2="40" y2="24" stroke="#c9a54e" stroke-width="2"/>
  <rect x="18" y="21" width="8" height="6" rx="1" fill="#c9a54e" opacity="0.4"/>
</svg>
"""

# ─── Geometric pattern SVG for decorative panels ──────────────────────────────

GEOMETRIC_PATTERN = """
<svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" opacity="0.08">
  <defs>
    <pattern id="geo" x="0" y="0" width="60" height="60" patternUnits="userSpaceOnUse">
      <rect x="0" y="0" width="60" height="60" fill="none"/>
      <line x1="0" y1="0" x2="60" y2="60" stroke="#c9a54e" stroke-width="0.5"/>
      <line x1="60" y1="0" x2="0" y2="60" stroke="#c9a54e" stroke-width="0.5"/>
      <line x1="30" y1="0" x2="30" y2="60" stroke="#c9a54e" stroke-width="0.3"/>
      <line x1="0" y1="30" x2="60" y2="30" stroke="#c9a54e" stroke-width="0.3"/>
      <circle cx="30" cy="30" r="8" fill="none" stroke="#c9a54e" stroke-width="0.5"/>
    </pattern>
  </defs>
  <rect width="100%" height="100%" fill="url(#geo)"/>
</svg>
"""


# ─── PAGE 01 - COVER ──────────────────────────────────────────────────────────

def page_01_cover():
    css = BASE_CSS + """
body {
    background: #0d1b3e;
    color: #ffffff;
    position: relative;
}
.bg-pattern {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    z-index: 0;
    opacity: 0.06;
}
.content {
    position: relative;
    z-index: 1;
    padding: 40mm 22mm 30mm 22mm;
    height: 297mm;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.logo-section {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    margin-bottom: 8mm;
}
.logo-icon {
    flex-shrink: 0;
    margin-top: 2px;
}
.logo-text .atp {
    font-family: 'Playfair Display', serif;
    font-weight: 900;
    font-size: 42px;
    color: #c9a54e;
    letter-spacing: 6px;
    line-height: 1;
}
.logo-text .services {
    font-family: 'Montserrat', sans-serif;
    font-weight: 600;
    font-size: 14px;
    color: #ffffff;
    letter-spacing: 8px;
    margin-top: 2px;
}
.logo-text .full-name {
    font-family: 'Montserrat', sans-serif;
    font-weight: 300;
    font-size: 8.5px;
    color: rgba(255,255,255,0.65);
    letter-spacing: 2px;
    margin-top: 6px;
    text-transform: uppercase;
}
.gold-line {
    width: 80px;
    height: 3px;
    background: #c9a54e;
    margin: 10mm 0 8mm 0;
}
.main-title {
    font-family: 'Playfair Display', serif;
    font-weight: 700;
    font-size: 30px;
    line-height: 1.3;
    color: #ffffff;
    max-width: 400px;
}
.tagline {
    font-family: 'Playfair Display', serif;
    font-style: italic;
    font-size: 16px;
    color: #c9a54e;
    margin-top: 6mm;
    letter-spacing: 1px;
}
.bottom-section {
    margin-top: auto;
}
.info-row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 5px;
    font-size: 9px;
    font-weight: 400;
    color: rgba(255,255,255,0.8);
    letter-spacing: 0.5px;
}
.info-row svg {
    flex-shrink: 0;
}
.confidential {
    margin-top: 8mm;
    padding-top: 4mm;
    border-top: 1px solid rgba(201,165,78,0.3);
    font-size: 7px;
    color: rgba(255,255,255,0.45);
    letter-spacing: 1px;
    text-transform: uppercase;
}
.page-number-right {
    position: fixed;
    bottom: 18mm;
    right: 22mm;
}
"""
    html = f"""<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><style>{css}</style></head>
<body>
<div class="bg-pattern">
{GEOMETRIC_PATTERN}
</div>
<div class="content">
    <div>
        <div class="logo-section">
            <div class="logo-icon">{SVG_BUILDING}</div>
            <div class="logo-text">
                <div class="atp">ATP</div>
                <div class="services">SERVICES</div>
                <div class="full-name">Accounting Taxation and Payroll (Pty) Ltd<br>Trading as ATP Services</div>
            </div>
        </div>
        <div class="gold-line"></div>
        <div class="main-title">PROPOSAL FOR<br>PROFESSIONAL<br>ACCOUNTING,<br>TAXATION &amp;<br>PAYROLL SERVICES</div>
        <div class="tagline">Precision. Compliance. Growth.</div>
    </div>
    <div class="bottom-section">
        <div class="info-row">{SVG_PIN} 29 Crestmore Road, Belvlei, 4094, Durban, KwaZulu-Natal, South Africa</div>
        <div class="info-row">{SVG_CHECKMARK} Proposal Number: ATP/2024/001</div>
        <div class="info-row">{SVG_CALENDAR} Issue Date: 20 May 2024</div>
        <div class="info-row">{SVG_CALENDAR} Valid Until: 20 June 2024</div>
        <div class="confidential">Confidential &mdash; This proposal is confidential and intended solely for the use of the named recipient.</div>
    </div>
</div>
<div class="page-number-right"><span class="page-num-circle">01</span></div>
</body>
</html>"""
    return html


# ─── PAGE 02 - WELCOME LETTER ────────────────────────────────────────────────

def page_02_welcome():
    css = BASE_CSS + """
body {
    background: #f5f0e8;
    color: #0d1b3e;
}
.header-bar {
    background: #0d1b3e;
    height: 8mm;
    width: 100%;
}
.content {
    padding: 14mm 24mm 28mm 24mm;
}
.title {
    font-family: 'Playfair Display', serif;
    font-weight: 700;
    font-size: 28px;
    color: #0d1b3e;
    letter-spacing: 3px;
    margin-bottom: 4px;
}
.gold-underline {
    width: 60px;
    height: 3px;
    background: #c9a54e;
    margin-bottom: 18px;
}
.body-text {
    font-family: 'Montserrat', sans-serif;
    font-size: 11px;
    line-height: 1.85;
    color: #0d1b3e;
    margin-bottom: 14px;
}
.commitment-intro {
    font-family: 'Montserrat', sans-serif;
    font-size: 11px;
    line-height: 1.85;
    color: #0d1b3e;
    margin-bottom: 10px;
}
.bullet-item {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 6px;
    font-family: 'Montserrat', sans-serif;
    font-size: 11px;
    font-weight: 600;
    color: #0d1b3e;
}
.closing {
    margin-top: 18px;
    font-family: 'Montserrat', sans-serif;
    font-size: 11px;
    line-height: 1.85;
    color: #0d1b3e;
}
.signature-name {
    font-family: 'Playfair Display', serif;
    font-style: italic;
    font-size: 22px;
    color: #0d1b3e;
    margin-top: 14px;
    margin-bottom: 4px;
}
.signature-title {
    font-family: 'Montserrat', sans-serif;
    font-size: 10px;
    color: #0d1b3e;
    font-weight: 500;
}
.signature-company {
    font-family: 'Montserrat', sans-serif;
    font-size: 10px;
    color: #c9a54e;
    font-weight: 600;
    letter-spacing: 1px;
}
.side-accent {
    position: fixed;
    left: 0;
    top: 8mm;
    width: 4px;
    height: 60mm;
    background: #c9a54e;
}
"""
    html = f"""<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><style>{css}</style></head>
<body>
<div class="header-bar"></div>
<div class="side-accent"></div>
<div class="content">
    <div class="title">WELCOME LETTER</div>
    <div class="gold-underline"></div>

    <div class="body-text">Dear [Client Name],</div>

    <div class="body-text">
        Thank you for allowing ATP Services the opportunity to present this proposal.
    </div>

    <div class="body-text">
        At ATP Services, we understand that selecting an accounting and compliance partner is more than choosing a service provider &mdash; it is choosing a trusted adviser who protects your business, supports strategic growth, and creates confidence through accurate financial management.
    </div>

    <div class="commitment-intro">Our commitment is simple:</div>

    <div class="bullet-item">{SVG_GOLD_CHECK} Professional Excellence</div>
    <div class="bullet-item">{SVG_GOLD_CHECK} Practical Business Advice</div>
    <div class="bullet-item">{SVG_GOLD_CHECK} Reliable Compliance</div>
    <div class="bullet-item">{SVG_GOLD_CHECK} Exceptional Service Delivery</div>

    <div class="body-text" style="margin-top: 16px;">
        This proposal outlines how ATP Services can support your business journey.
    </div>

    <div class="closing">Kind Regards,</div>

    <div class="signature-name">Krish Moodley</div>
    <div class="signature-title">Sales Manager</div>
    <div class="signature-company">ATP SERVICES</div>
</div>
<div class="page-number"><span class="page-num-circle">02</span></div>
</body>
</html>"""
    return html


# ─── PAGE 03 - TABLE OF CONTENTS ─────────────────────────────────────────────

def page_03_contents():
    items = [
        ("01", "Executive Summary", "04"),
        ("02", "About ATP Services", "05"),
        ("03", "Our Philosophy", "06"),
        ("04", "Our Services", "07"),
        ("05", "Why Choose ATP Services", "11"),
        ("06", "Client Journey", "12"),
        ("07", "Proposed Solution", "13"),
        ("08", "Pricing & Quotation", "13"),
        ("09", "Service Level Agreement", "14"),
        ("10", "Terms & Conditions", "15"),
        ("11", "Acceptance & Signature", "16"),
    ]

    rows = ""
    for num, name, page in items:
        rows += f"""
        <div class="toc-row">
            <div class="toc-num-circle">{num}</div>
            <div class="toc-name">{name}</div>
            <div class="toc-dots"></div>
            <div class="toc-page">{page}</div>
        </div>"""

    css = BASE_CSS + """
body {
    background: #f5f0e8;
    color: #0d1b3e;
}
.content {
    padding: 36mm 28mm 30mm 28mm;
}
.title {
    font-family: 'Playfair Display', serif;
    font-weight: 700;
    font-size: 28px;
    color: #0d1b3e;
    letter-spacing: 3px;
    margin-bottom: 4px;
}
.gold-underline-long {
    width: 120px;
    height: 3px;
    background: #c9a54e;
    margin-bottom: 32px;
}
.toc-row {
    display: flex;
    align-items: center;
    margin-bottom: 14px;
    gap: 14px;
}
.toc-num-circle {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #c9a54e;
    color: #ffffff;
    font-family: 'Montserrat', sans-serif;
    font-weight: 700;
    font-size: 11px;
    line-height: 30px;
    text-align: center;
    flex-shrink: 0;
}
.toc-name {
    font-family: 'Montserrat', sans-serif;
    font-weight: 600;
    font-size: 12px;
    color: #0d1b3e;
    white-space: nowrap;
}
.toc-dots {
    flex: 1;
    border-bottom: 1px dotted rgba(13,27,62,0.25);
    margin-bottom: 3px;
    min-width: 20px;
}
.toc-page {
    font-family: 'Montserrat', sans-serif;
    font-weight: 700;
    font-size: 12px;
    color: #c9a54e;
    flex-shrink: 0;
    width: 24px;
    text-align: right;
}
.left-accent {
    position: fixed;
    left: 0;
    top: 0;
    width: 6px;
    height: 100%;
    background: #0d1b3e;
}
"""
    html = f"""<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><style>{css}</style></head>
<body>
<div class="left-accent"></div>
<div class="content">
    <div class="title">TABLE OF CONTENTS</div>
    <div class="gold-underline-long"></div>
    {rows}
</div>
<div class="page-number"><span class="page-num-circle">03</span></div>
</body>
</html>"""
    return html


# ─── PAGE 04 - EXECUTIVE SUMMARY ─────────────────────────────────────────────

def page_04_executive():
    css = BASE_CSS + """
body {
    background: #f5f0e8;
    color: #0d1b3e;
    position: relative;
}
.layout {
    display: flex;
    height: 297mm;
}
.left-panel {
    width: 60%;
    padding: 34mm 18mm 32mm 24mm;
    display: flex;
    flex-direction: column;
}
.right-panel {
    width: 40%;
    background: #0d1b3e;
    position: relative;
    overflow: hidden;
}
.right-pattern {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
}
.title {
    font-family: 'Playfair Display', serif;
    font-weight: 700;
    font-size: 24px;
    color: #0d1b3e;
    letter-spacing: 2px;
    margin-bottom: 4px;
}
.subtitle {
    font-family: 'Playfair Display', serif;
    font-style: italic;
    font-size: 14px;
    color: #c9a54e;
    margin-top: 16px;
    margin-bottom: 16px;
}
.body-text {
    font-family: 'Montserrat', sans-serif;
    font-size: 10.5px;
    line-height: 1.85;
    color: #0d1b3e;
    margin-bottom: 14px;
}
.icon-blocks {
    display: flex;
    gap: 10px;
    margin-top: auto;
    flex-wrap: wrap;
}
.icon-block {
    text-align: center;
    width: 68px;
}
.icon-block-label {
    font-family: 'Montserrat', sans-serif;
    font-weight: 700;
    font-size: 6.5px;
    color: #0d1b3e;
    letter-spacing: 0.5px;
    margin-top: 6px;
    line-height: 1.3;
}
.right-gold-line {
    position: absolute;
    left: 0;
    top: 30%;
    width: 3px;
    height: 40%;
    background: #c9a54e;
}
"""
    html = f"""<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><style>{css}</style></head>
<body>
<div class="layout">
    <div class="left-panel">
        <div class="title">EXECUTIVE SUMMARY</div>
        <div class="gold-underline-long"></div>
        <div class="subtitle">Accounting That Moves Business Forward</div>

        <div class="body-text">
            Managing compliance, reporting, payroll and taxation should not distract business owners from growth. These functions are critical &mdash; but they should empower, not encumber.
        </div>
        <div class="body-text">
            ATP Services provides integrated professional services designed to simplify administration, reduce risk, improve reporting accuracy and support long-term decision making.
        </div>
        <div class="body-text">
            Whether your business is emerging, growing or established, our team provides structured support and responsive service tailored to your unique requirements.
        </div>

        <div class="icon-blocks">
            <div class="icon-block">
                {SVG_CHART}
                <div class="icon-block-label">ACCURATE<br>REPORTING</div>
            </div>
            <div class="icon-block">
                {SVG_LIGHTBULB}
                <div class="icon-block-label">SMART<br>ADVICE</div>
            </div>
            <div class="icon-block">
                {SVG_SHIELD}
                <div class="icon-block-label">COMPLIANCE<br>CONFIDENCE</div>
            </div>
            <div class="icon-block">
                {SVG_ARROW_UP}
                <div class="icon-block-label">BUSINESS<br>GROWTH</div>
            </div>
        </div>
    </div>
    <div class="right-panel">
        <div class="right-pattern">{GEOMETRIC_PATTERN}</div>
        <div class="right-gold-line"></div>
    </div>
</div>
<div class="page-number"><span class="page-num-circle">04</span></div>
</body>
</html>"""
    return html


# ─── PAGE 05 - ABOUT ATP SERVICES ────────────────────────────────────────────

def page_05_about():
    css = BASE_CSS + """
body {
    background: #f5f0e8;
    color: #0d1b3e;
}
.header-section {
    background: #0d1b3e;
    padding: 28mm 28mm 12mm 28mm;
}
.header-title {
    font-family: 'Playfair Display', serif;
    font-weight: 700;
    font-size: 28px;
    color: #ffffff;
    letter-spacing: 3px;
    margin-bottom: 4px;
}
.header-gold-line {
    width: 80px;
    height: 3px;
    background: #c9a54e;
    margin-top: 8px;
}
.header-subtitle {
    font-family: 'Playfair Display', serif;
    font-style: italic;
    font-size: 14px;
    color: #c9a54e;
    margin-top: 10px;
}
.content {
    padding: 14mm 28mm 30mm 28mm;
}
.body-text {
    font-family: 'Montserrat', sans-serif;
    font-size: 11px;
    line-height: 1.85;
    color: #0d1b3e;
    margin-bottom: 14px;
}
.values-header {
    font-family: 'Playfair Display', serif;
    font-weight: 700;
    font-size: 18px;
    color: #c9a54e;
    margin-top: 10mm;
    margin-bottom: 3px;
}
.values-gold-line {
    width: 50px;
    height: 2px;
    background: #c9a54e;
    margin-bottom: 16px;
}
.value-item {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 14px;
}
.value-text {
    display: flex;
    flex-direction: column;
}
.value-name {
    font-family: 'Montserrat', sans-serif;
    font-weight: 700;
    font-size: 11px;
    color: #0d1b3e;
    letter-spacing: 2px;
    text-transform: uppercase;
}
.value-desc {
    font-family: 'Montserrat', sans-serif;
    font-weight: 400;
    font-size: 10px;
    color: #0d1b3e;
    opacity: 0.75;
    margin-top: 2px;
}
.bottom-gold-stripe {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: #c9a54e;
}
"""
    html = f"""<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><style>{css}</style></head>
<body>
<div class="header-section">
    <div class="header-title">ABOUT ATP SERVICES</div>
    <div class="header-gold-line"></div>
    <div class="header-subtitle">Who We Are</div>
</div>
<div class="content">
    <div class="body-text">
        ATP Services is a professional accounting, taxation and payroll practice dedicated to helping businesses operate with confidence. Founded on the principles of accuracy, integrity and service excellence, we partner with businesses across all sectors.
    </div>
    <div class="body-text">
        We combine technical expertise with practical business understanding to deliver services that create measurable value. Our clients trust us because we consistently deliver beyond expectations and treat every engagement with the attention it deserves.
    </div>

    <div class="values-header">Core Values</div>
    <div class="values-gold-line"></div>

    <div class="value-item">
        {SVG_INTEGRITY}
        <div class="value-text">
            <div class="value-name">Integrity</div>
            <div class="value-desc">We do what is right.</div>
        </div>
    </div>
    <div class="value-item">
        {SVG_EXCELLENCE}
        <div class="value-text">
            <div class="value-name">Excellence</div>
            <div class="value-desc">We deliver quality.</div>
        </div>
    </div>
    <div class="value-item">
        {SVG_ACCOUNTABILITY}
        <div class="value-text">
            <div class="value-name">Accountability</div>
            <div class="value-desc">We take responsibility.</div>
        </div>
    </div>
    <div class="value-item">
        {SVG_INNOVATION}
        <div class="value-text">
            <div class="value-name">Innovation</div>
            <div class="value-desc">We embrace better ways.</div>
        </div>
    </div>
    <div class="value-item">
        {SVG_PARTNERSHIP}
        <div class="value-text">
            <div class="value-name">Client Partnership</div>
            <div class="value-desc">We grow together.</div>
        </div>
    </div>
</div>
<div class="bottom-gold-stripe"></div>
<div class="page-number"><span class="page-num-circle">05</span></div>
</body>
</html>"""
    return html


# ─── PAGE 06 - OUR SERVICES ──────────────────────────────────────────────────

def page_06_services():
    css = BASE_CSS + """
body {
    background: #0d1b3e;
    color: #ffffff;
    position: relative;
}
.bg-pattern {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    z-index: 0;
    opacity: 0.04;
}
.content {
    position: relative;
    z-index: 1;
    padding: 40mm 22mm 30mm 22mm;
    height: 297mm;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.title {
    font-family: 'Playfair Display', serif;
    font-weight: 700;
    font-size: 32px;
    color: #ffffff;
    letter-spacing: 4px;
    text-align: center;
    margin-bottom: 4px;
}
.gold-underline-center {
    width: 80px;
    height: 3px;
    background: #c9a54e;
    margin: 10px auto 16px auto;
}
.subtitle {
    font-family: 'Playfair Display', serif;
    font-style: italic;
    font-size: 14px;
    color: #c9a54e;
    text-align: center;
    margin-bottom: 24mm;
    letter-spacing: 1px;
}
.cards-row {
    display: flex;
    gap: 14px;
    justify-content: center;
    width: 100%;
}
.service-card {
    background: rgba(255,255,255,0.06);
    border-top: 3px solid #c9a54e;
    border-radius: 4px;
    padding: 22px 16px 20px 16px;
    width: 24%;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.card-icon {
    margin-bottom: 14px;
}
.card-title {
    font-family: 'Montserrat', sans-serif;
    font-weight: 700;
    font-size: 10px;
    color: #ffffff;
    letter-spacing: 2px;
    margin-bottom: 8px;
    line-height: 1.4;
}
.card-tagline {
    font-family: 'Montserrat', sans-serif;
    font-weight: 400;
    font-size: 9px;
    color: #c9a54e;
    font-style: italic;
}
.top-gold-stripe {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: #c9a54e;
}
"""
    html = f"""<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><style>{css}</style></head>
<body>
<div class="top-gold-stripe"></div>
<div class="bg-pattern">{GEOMETRIC_PATTERN}</div>
<div class="content">
    <div class="title">OUR SERVICES</div>
    <div class="gold-underline-center"></div>
    <div class="subtitle">Comprehensive Solutions. Exceptional Results.</div>

    <div class="cards-row">
        <div class="service-card">
            <div class="card-icon">{SVG_CALCULATOR}</div>
            <div class="card-title">ACCOUNTING<br>SERVICES</div>
            <div class="card-tagline">Accuracy you can trust.</div>
        </div>
        <div class="service-card">
            <div class="card-icon">{SVG_DOCUMENT}</div>
            <div class="card-title">TAXATION<br>SERVICES</div>
            <div class="card-tagline">Compliance you can rely on.</div>
        </div>
        <div class="service-card">
            <div class="card-icon">{SVG_PEOPLE}</div>
            <div class="card-title">PAYROLL<br>SERVICES</div>
            <div class="card-tagline">People managed with care.</div>
        </div>
        <div class="service-card">
            <div class="card-icon">{SVG_BRIEFCASE}</div>
            <div class="card-title">BUSINESS<br>ADVISORY</div>
            <div class="card-tagline">Advice that drives growth.</div>
        </div>
    </div>
</div>
<div class="page-number"><span class="page-num-circle">06</span></div>
</body>
</html>"""
    return html


# ─── MAIN ─────────────────────────────────────────────────────────────────────

def generate_pdf(html_content, filename):
    filepath = os.path.join(OUTPUT_DIR, filename)
    wp = weasyprint.HTML(string=html_content)
    wp.write_pdf(filepath)
    size = os.path.getsize(filepath)
    print(f"  {filename:30s} {size:>8,} bytes")
    return filepath


def main():
    pages = [
        (page_01_cover(), "page-01-cover.pdf"),
        (page_02_welcome(), "page-02-welcome.pdf"),
        (page_03_contents(), "page-03-contents.pdf"),
        (page_04_executive(), "page-04-executive.pdf"),
        (page_05_about(), "page-05-about.pdf"),
        (page_06_services(), "page-06-services.pdf"),
    ]

    print("=" * 50)
    print("ATP Services Proposal - Generating Pages 1-6")
    print("=" * 50)

    for html_content, filename in pages:
        generate_pdf(html_content, filename)

    print("=" * 50)
    print("All 6 pages generated successfully!")
    print(f"Output directory: {OUTPUT_DIR}")
    print("=" * 50)


if __name__ == "__main__":
    main()
