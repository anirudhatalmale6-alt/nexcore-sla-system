#!/usr/bin/env python3
"""
Generate ATP Services proposal pages 7-12 as individual A4 PDFs.
Navy blue (#0d1b3e) + Gold (#c9a54e) corporate luxury theme.
Uses weasyprint for HTML/CSS -> PDF conversion.
"""

import os
from weasyprint import HTML

OUTPUT_DIR = os.path.dirname(os.path.abspath(__file__))

# ── Shared CSS ──────────────────────────────────────────────────────────────

BASE_CSS = """
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400;1,700&family=Montserrat:wght@300;400;500;600;700&display=swap');

@page {
    size: 210mm 297mm;
    margin: 0;
}

*, *::before, *::after {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    width: 210mm;
    height: 297mm;
    margin: 0;
    padding: 0;
    font-family: 'Montserrat', sans-serif;
    color: #0d1b3e;
    background: #ffffff;
    position: relative;
    overflow: hidden;
}

.page-wrapper {
    width: 210mm;
    height: 297mm;
    position: relative;
    padding: 45mm 30mm 40mm 30mm;
    background: linear-gradient(180deg, #ffffff 0%, #faf8f3 100%);
}

/* Gold decorative top border */
.top-accent {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(90deg, #c9a54e 0%, #e8d48b 50%, #c9a54e 100%);
}

/* Subtle side accent line */
.side-accent {
    position: absolute;
    top: 35mm;
    left: 25mm;
    width: 2px;
    height: 60mm;
    background: linear-gradient(180deg, #c9a54e, transparent);
    opacity: 0.3;
}

/* Section label */
.section-label {
    font-family: 'Montserrat', sans-serif;
    font-size: 10pt;
    font-weight: 600;
    letter-spacing: 4px;
    text-transform: uppercase;
    color: #c9a54e;
    margin-bottom: 8mm;
}

/* Main heading */
.main-heading {
    font-family: 'Playfair Display', serif;
    font-size: 28pt;
    font-weight: 700;
    color: #0d1b3e;
    margin-bottom: 5mm;
    line-height: 1.2;
}

/* Gold underline */
.heading-underline {
    width: 60mm;
    height: 3px;
    background: linear-gradient(90deg, #c9a54e, #e8d48b);
    margin-bottom: 15mm;
    border-radius: 2px;
}

/* Service bullet list */
.service-list {
    list-style: none;
    padding: 0;
    margin: 0 0 0 2mm;
}

.service-list li {
    display: flex;
    align-items: center;
    margin-bottom: 7mm;
    font-family: 'Montserrat', sans-serif;
    font-size: 13pt;
    font-weight: 400;
    color: #0d1b3e;
    line-height: 1.4;
}

.service-list li .bullet {
    width: 10px;
    height: 10px;
    min-width: 10px;
    background: #c9a54e;
    border-radius: 50%;
    margin-right: 6mm;
    display: inline-block;
}

/* Bottom quote */
.bottom-quote {
    position: absolute;
    bottom: 55mm;
    left: 30mm;
    right: 30mm;
    text-align: center;
    font-family: 'Playfair Display', serif;
    font-style: italic;
    font-size: 11.5pt;
    color: #c9a54e;
    line-height: 1.5;
    padding: 8mm 10mm;
    border-top: 1px solid rgba(201, 165, 78, 0.25);
    border-bottom: 1px solid rgba(201, 165, 78, 0.25);
}

/* Page number circle */
.page-number {
    position: absolute;
    bottom: 18mm;
    left: 50%;
    transform: translateX(-50%);
    width: 36px;
    height: 36px;
    border: 2px solid #c9a54e;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Montserrat', sans-serif;
    font-size: 10pt;
    font-weight: 600;
    color: #c9a54e;
    text-align: center;
    line-height: 36px;
}

/* Decorative corner elements */
.corner-tl {
    position: absolute;
    top: 12mm;
    left: 12mm;
    width: 20mm;
    height: 20mm;
    border-top: 1.5px solid rgba(201, 165, 78, 0.2);
    border-left: 1.5px solid rgba(201, 165, 78, 0.2);
}

.corner-br {
    position: absolute;
    bottom: 12mm;
    right: 12mm;
    width: 20mm;
    height: 20mm;
    border-bottom: 1.5px solid rgba(201, 165, 78, 0.2);
    border-right: 1.5px solid rgba(201, 165, 78, 0.2);
}
"""

# ── Service page template (pages 07-10) ────────────────────────────────────

def service_page_html(section_label, heading, items, quote, page_num):
    bullets = ""
    for item in items:
        bullets += f"""
            <li>
                <span class="bullet"></span>
                <span>{item}</span>
            </li>"""

    return f"""<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
{BASE_CSS}
</style>
</head>
<body>
<div class="page-wrapper">
    <div class="top-accent"></div>
    <div class="side-accent"></div>
    <div class="corner-tl"></div>
    <div class="corner-br"></div>

    <div class="section-label">{section_label}</div>
    <h1 class="main-heading">{heading}</h1>
    <div class="heading-underline"></div>

    <ul class="service-list">
        {bullets}
    </ul>

    <div class="bottom-quote">"{quote}"</div>

    <div class="page-number">{page_num}</div>
</div>
</body>
</html>"""


# ── Page 11 - Why Choose ATP Services? ─────────────────────────────────────

def page_11_html():
    items = [
        ("01", "DEDICATED RELATIONSHIP MANAGEMENT",
         "One point of contact who understands your business."),
        ("02", "FASTER TURNAROUND",
         "Clear deadlines. Consistent communication."),
        ("03", "COMPLIANCE CONFIDENCE",
         "We keep you compliant and reduce risk."),
        ("04", "MODERN TECHNOLOGY",
         "Secure cloud systems. Real-time access."),
        ("05", "TAILORED SOLUTIONS",
         "Solutions designed for your business."),
    ]

    items_html = ""
    for num, title, desc in items:
        items_html += f"""
        <div class="choose-item">
            <div class="choose-num">{num}</div>
            <div class="choose-text">
                <div class="choose-title">{title}</div>
                <div class="choose-desc">{desc}</div>
            </div>
        </div>"""

    return f"""<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
{BASE_CSS}

.choose-item {{
    display: flex;
    align-items: flex-start;
    margin-bottom: 10mm;
}}

.choose-num {{
    width: 42px;
    height: 42px;
    min-width: 42px;
    background: linear-gradient(135deg, #c9a54e, #e8d48b);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Montserrat', sans-serif;
    font-size: 13pt;
    font-weight: 700;
    color: #ffffff;
    margin-right: 7mm;
    text-align: center;
    line-height: 42px;
}}

.choose-text {{
    padding-top: 1mm;
}}

.choose-title {{
    font-family: 'Montserrat', sans-serif;
    font-size: 12.5pt;
    font-weight: 700;
    color: #0d1b3e;
    letter-spacing: 1.5px;
    margin-bottom: 2mm;
}}

.choose-desc {{
    font-family: 'Montserrat', sans-serif;
    font-size: 10.5pt;
    font-weight: 400;
    color: #3a4a6b;
    line-height: 1.5;
}}
</style>
</head>
<body>
<div class="page-wrapper">
    <div class="top-accent"></div>
    <div class="side-accent"></div>
    <div class="corner-tl"></div>
    <div class="corner-br"></div>

    <h1 class="main-heading">WHY CHOOSE<br>ATP SERVICES?</h1>
    <div class="heading-underline"></div>

    {items_html}

    <div class="page-number">11</div>
</div>
</body>
</html>"""


# ── Page 12 - The Client Journey ───────────────────────────────────────────

def page_12_html():
    steps = [
        ("1", "DISCOVERY",
         "We understand your business and requirements."),
        ("2", "ONBOARDING",
         "We gather information and set up your profile."),
        ("3", "IMPLEMENTATION",
         "We streamline your processes and systems."),
        ("4", "MONTHLY SUPPORT",
         "We manage your accounting, taxation and payroll."),
        ("5", "REPORTING",
         "We deliver accurate and timely reports."),
        ("6", "CONTINUOUS IMPROVEMENT",
         "We analyse, advise and help you grow."),
    ]

    steps_html = ""
    for i, (num, title, desc) in enumerate(steps):
        connector = ""
        if i < len(steps) - 1:
            connector = '<div class="journey-connector"></div>'

        steps_html += f"""
        <div class="journey-step-row">
            <div class="journey-left">
                <div class="journey-num">{num}</div>
                {connector}
            </div>
            <div class="journey-right">
                <div class="journey-title">{title}</div>
                <div class="journey-desc">{desc}</div>
            </div>
        </div>"""

    return f"""<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
{BASE_CSS}

.page-wrapper {{
    padding-top: 40mm;
}}

.journey-step-row {{
    display: flex;
    align-items: flex-start;
    margin-bottom: 2mm;
    min-height: 28mm;
}}

.journey-left {{
    width: 42px;
    min-width: 42px;
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-right: 7mm;
}}

.journey-num {{
    width: 38px;
    height: 38px;
    min-height: 38px;
    background: linear-gradient(135deg, #c9a54e, #e8d48b);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Montserrat', sans-serif;
    font-size: 14pt;
    font-weight: 700;
    color: #ffffff;
    text-align: center;
    line-height: 38px;
}}

.journey-connector {{
    width: 2px;
    height: 14mm;
    background: linear-gradient(180deg, #c9a54e, rgba(201, 165, 78, 0.2));
    margin-top: 2mm;
}}

.journey-right {{
    padding-top: 1mm;
}}

.journey-title {{
    font-family: 'Montserrat', sans-serif;
    font-size: 12pt;
    font-weight: 700;
    color: #0d1b3e;
    letter-spacing: 2px;
    margin-bottom: 2mm;
}}

.journey-desc {{
    font-family: 'Montserrat', sans-serif;
    font-size: 10.5pt;
    font-weight: 400;
    color: #3a4a6b;
    line-height: 1.5;
}}
</style>
</head>
<body>
<div class="page-wrapper">
    <div class="top-accent"></div>
    <div class="side-accent"></div>
    <div class="corner-tl"></div>
    <div class="corner-br"></div>

    <h1 class="main-heading">THE CLIENT JOURNEY</h1>
    <div class="heading-underline"></div>

    {steps_html}

    <div class="page-number">12</div>
</div>
</body>
</html>"""


# ── Generate all pages ─────────────────────────────────────────────────────

def generate_pdf(html_string, filename):
    filepath = os.path.join(OUTPUT_DIR, filename)
    HTML(string=html_string).write_pdf(filepath)
    size_kb = os.path.getsize(filepath) / 1024
    print(f"  {filename:40s} {size_kb:8.1f} KB")
    return filepath


def main():
    print("Generating ATP Services proposal pages 7-12...\n")

    # Page 07 - Accounting Services
    generate_pdf(
        service_page_html(
            "OUR SERVICES",
            "Accounting Services",
            [
                "Monthly Accounting",
                "Management Accounts",
                "Financial Reporting",
                "Cash Flow Monitoring",
                "Bank Reconciliations",
                "Budgeting &amp; Forecasting",
                "Asset Register Management",
            ],
            "Accurate financial information empowers better decisions.",
            "07",
        ),
        "page-07-accounting.pdf",
    )

    # Page 08 - Taxation Services
    generate_pdf(
        service_page_html(
            "OUR SERVICES",
            "Taxation Services",
            [
                "Income Tax",
                "VAT",
                "PAYE",
                "Tax Planning",
                "SARS Compliance",
                "Tax Returns &amp; Provisional Tax",
                "Tax Consulting",
            ],
            "Smart tax strategies. Full compliance. Peace of mind.",
            "08",
        ),
        "page-08-taxation.pdf",
    )

    # Page 09 - Payroll Services
    generate_pdf(
        service_page_html(
            "OUR SERVICES",
            "Payroll Services",
            [
                "Payroll Processing",
                "EMP201 Submissions",
                "EMP501 Submissions",
                "Leave &amp; Attendance Management",
                "Pension &amp; UIF Submissions",
                "Employee Administration",
                "IRP5 / IT3(a) Certificates",
            ],
            "Accurate payroll. Happy employees. Compliant business.",
            "09",
        ),
        "page-09-payroll.pdf",
    )

    # Page 10 - Business Advisory
    generate_pdf(
        service_page_html(
            "OUR SERVICES",
            "Business Advisory",
            [
                "KPI Reporting",
                "Growth Planning",
                "Business Consulting",
                "Process Improvement",
                "Financial Strategy",
                "Risk Management",
                "Performance Analysis",
            ],
            "Insightful advice. Strategic advantage. Sustainable growth.",
            "10",
        ),
        "page-10-advisory.pdf",
    )

    # Page 11 - Why Choose ATP Services?
    generate_pdf(page_11_html(), "page-11-why-choose.pdf")

    # Page 12 - The Client Journey
    generate_pdf(page_12_html(), "page-12-journey.pdf")

    print("\nAll 6 pages generated successfully.")


if __name__ == "__main__":
    main()
