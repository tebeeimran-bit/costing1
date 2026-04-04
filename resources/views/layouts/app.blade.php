<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem Costing Manufaktur') - Dharma Electrindo Mfg</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Slate colors */
            --slate-50: #f8fafc;
            --slate-100: #f1f5f9;
            --slate-200: #e2e8f0;
            --slate-300: #cbd5e1;
            --slate-400: #94a3b8;
            --slate-500: #64748b;
            --slate-600: #475569;
            --slate-700: #334155;
            --slate-800: #1e293b;
            --slate-900: #0f172a;
            --slate-950: #020617;

            /* Blue colors */
            --blue-50: #eff6ff;
            --blue-100: #dbeafe;
            --blue-200: #bfdbfe;
            --blue-300: #93c5fd;
            --blue-400: #60a5fa;
            --blue-500: #3b82f6;
            --blue-600: #2563eb;
            --blue-700: #1d4ed8;
            --blue-800: #1e40af;
            --blue-900: #1e3a8a;
            --blue-950: #172554;

            /* Accent colors */
            --orange-500: #f97316;
            --green-500: #22c55e;
            --yellow-500: #eab308;
            --red-500: #ef4444;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--slate-100);
            color: var(--slate-800);
            min-height: 100vh;
            line-height: 1.6;
        }

        /* App Wrapper */
        .app-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 260px;
            background: white;
            border-right: 1px solid var(--slate-200);
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 200;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.08);
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--slate-200);
            background: linear-gradient(135deg, var(--blue-600) 0%, var(--blue-700) 100%);
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .sidebar-logo-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, var(--blue-500) 0%, var(--blue-600) 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .sidebar-logo-icon svg {
            width: 24px;
            height: 24px;
            color: white;
        }

        .sidebar-logo-text {
            display: flex;
            flex-direction: column;
        }

        .sidebar-logo-title {
            font-size: 1rem;
            font-weight: 700;
            color: white;
        }

        .sidebar-logo-subtitle {
            font-size: 0.7rem;
            color: var(--blue-200);
            font-weight: 500;
        }

        .sidebar-nav {
            flex: 1;
            padding: 1rem 0;
            overflow-y: auto;
        }

        .sidebar-nav-section {
            padding: 0 1rem;
            margin-bottom: 1.5rem;
        }

        .sidebar-nav-title {
            font-size: 0.65rem;
            font-weight: 600;
            color: var(--slate-400);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            padding: 0 0.75rem;
            margin-bottom: 0.75rem;
        }

        .sidebar-nav-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1rem;
            color: var(--slate-600);
            text-decoration: none;
            border-radius: 0.75rem;
            transition: all 0.2s ease;
            margin-bottom: 0.25rem;
            position: relative;
        }

        .sidebar-nav-item:hover {
            background: var(--blue-50);
            color: var(--blue-600);
            transform: translateX(4px);
        }

        .sidebar-nav-item.active {
            background: linear-gradient(135deg, var(--blue-600) 0%, var(--blue-700) 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .sidebar-nav-item.active::before {
            content: '';
            position: absolute;
            left: -1rem;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 60%;
            background: var(--blue-400);
            border-radius: 0 4px 4px 0;
        }

        .sidebar-nav-item svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        .sidebar-nav-item span {
            font-size: 0.875rem;
            font-weight: 500;
        }

        .sidebar-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--slate-200);
            background: var(--slate-50);
        }

        .sidebar-footer-text {
            font-size: 0.7rem;
            color: var(--slate-500);
            text-align: center;
        }

        /* Sidebar Dropdown */
        .sidebar-dropdown {
            position: relative;
        }

        .sidebar-dropdown-toggle {
            width: 100%;
            border: none;
            background: none;
            cursor: pointer;
            text-align: left;
        }

        .sidebar-dropdown .dropdown-arrow {
            transition: transform 0.2s ease;
        }

        .sidebar-dropdown.open .dropdown-arrow {
            transform: rotate(180deg);
        }

        .sidebar-submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            padding-left: 1rem;
        }

        .sidebar-dropdown.open .sidebar-submenu {
            max-height: 560px;
        }

        .sidebar-submenu-item {
            padding: 0.625rem 1rem !important;
            font-size: 0.8rem !important;
        }

        .sidebar-submenu-item svg {
            width: 16px !important;
            height: 16px !important;
        }

        .sidebar-submenu-item span {
            font-size: 0.8rem !important;
        }

        .sidebar-submenu-item.active {
            background: linear-gradient(135deg, var(--blue-500) 0%, var(--blue-600) 100%) !important;
        }

        /* Main Content Wrapper */
        .main-wrapper {
            flex: 1;
            margin-left: 260px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            min-width: 0;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, var(--blue-600) 0%, var(--blue-700) 50%, var(--blue-800) 100%);
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }

        .header-content {
            max-width: 1600px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header-title {
            font-size: 1.75rem;
            font-weight: 800;
            color: white;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .header-subtitle {
            font-size: 0.875rem;
            color: var(--blue-200);
            font-weight: 500;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .header-filter {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.1);
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            backdrop-filter: blur(10px);
        }

        .header-filter label {
            font-size: 0.75rem;
            color: var(--blue-200);
            font-weight: 500;
        }

        .header-filter select {
            background: transparent;
            border: none;
            color: white;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            outline: none;
        }

        .header-filter select option {
            background: var(--slate-800);
            color: white;
        }

        /* Hide nav-tabs on desktop since we have sidebar */
        .nav-tabs {
            display: none;
        }

        .nav-tab {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid transparent;
            border-radius: 0.5rem;
            color: white;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .nav-tab:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .nav-tab.active {
            background: white;
            color: var(--blue-800);
            border-color: white;
        }

        .nav-tab svg {
            width: 1.25rem;
            height: 1.25rem;
        }

        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border: none;
            border-radius: 0.5rem;
            color: white;
            cursor: pointer;
            transition: background 0.2s;
        }

        .mobile-menu-toggle:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Sidebar Overlay */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 150;
        }

        .sidebar-overlay.active {
            display: block;
        }

        /* Responsive Sidebar */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-wrapper {
                margin-left: 0;
            }

            .nav-tabs {
                display: flex;
            }

            .mobile-menu-toggle {
                display: flex;
            }
        }

        /* Breadcrumb */
        .breadcrumb {
            background: white;
            padding: 0.75rem 2rem;
            border-bottom: 1px solid var(--slate-200);
        }

        .breadcrumb-content {
            max-width: 1600px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: var(--slate-500);
        }

        .breadcrumb-content a {
            color: var(--blue-600);
            text-decoration: none;
            transition: color 0.2s;
        }

        .breadcrumb-content a:hover {
            color: var(--blue-500);
        }

        .breadcrumb-separator {
            color: var(--slate-600);
        }

        /* Main Content */
        .main-content {
            max-width: 1600px;
            margin: 0 auto;
            padding: 1.5rem 2rem;
            flex: 1;
            width: 100%;
            min-width: 0;
        }

        /* Cards */
        .card {
            background: white;
            border-radius: 0;
            padding: 1.5rem;
            border: none;
            box-shadow: none;
            min-width: 0;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .card-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--slate-800);
        }

        /* KPI Cards */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .kpi-card {
            background: linear-gradient(135deg, var(--blue-800) 0%, var(--blue-900) 100%);
            border-radius: 1rem;
            padding: 1.25rem;
            border: 1px solid var(--blue-700);
            position: relative;
            overflow: hidden;
        }

        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            transform: translate(30%, -30%);
        }

        .kpi-card.highlight {
            background: linear-gradient(135deg, var(--orange-500) 0%, #ea580c 100%);
            border-color: var(--orange-500);
        }

        .kpi-label {
            font-size: 0.75rem;
            color: var(--blue-200);
            font-weight: 500;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .kpi-card.highlight .kpi-label {
            color: rgba(255, 255, 255, 0.8);
        }

        .kpi-value {
            font-size: 1.5rem;
            font-weight: 800;
            color: white;
        }

        .kpi-icon {
            position: absolute;
            bottom: 1rem;
            right: 1rem;
            opacity: 0.3;
        }

        /* Charts Grid */
        .charts-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        /* Bar Chart */
        .bar-chart {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .bar-item {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .bar-label {
            width: 80px;
            font-size: 0.875rem;
            color: var(--slate-600);
            text-align: right;
            flex-shrink: 0;
        }

        .bar-container {
            flex: 1;
            height: 28px;
            background: var(--slate-200);
            border-radius: 4px;
            overflow: hidden;
            position: relative;
        }

        .bar-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--blue-500) 0%, var(--blue-400) 100%);
            border-radius: 4px;
            transition: width 1s ease;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 0.5rem;
        }

        .bar-value {
            font-size: 0.75rem;
            color: white;
            font-weight: 600;
            white-space: nowrap;
        }

        /* Stacked Bar */
        .stacked-bar-chart {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .stacked-bar-item {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stacked-bar-container {
            flex: 1;
            height: 28px;
            background: var(--slate-200);
            border-radius: 4px;
            overflow: hidden;
            display: flex;
        }

        .stacked-segment {
            height: 100%;
            transition: width 1s ease;
        }

        .stacked-segment.material {
            background: var(--orange-500);
        }

        .stacked-segment.labor {
            background: var(--blue-500);
        }

        .stacked-segment.overhead {
            background: var(--green-500);
        }

        /* Chart Legend */
        .chart-legend {
            display: flex;
            gap: 1.5rem;
            margin-top: 1rem;
            justify-content: center;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.75rem;
            color: var(--slate-600);
        }

        .legend-color {
            width: 12px;
            height: 12px;
            border-radius: 2px;
        }

        .legend-color.material {
            background: var(--orange-500);
        }

        .legend-color.labor {
            background: var(--blue-500);
        }

        .legend-color.overhead {
            background: var(--green-500);
        }

        /* Line Chart SVG */
        .line-chart-container {
            width: 100%;
            height: 200px;
            position: relative;
        }

        .line-chart-svg {
            width: 100%;
            height: 100%;
        }

        .line-chart-grid line {
            stroke: var(--slate-200);
            stroke-width: 1;
        }

        .line-chart-line {
            fill: none;
            stroke: var(--blue-400);
            stroke-width: 3;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .line-chart-area {
            fill: url(#lineGradient);
            opacity: 0.3;
        }

        .line-chart-dot {
            fill: var(--blue-400);
            stroke: white;
            stroke-width: 2;
        }

        .line-chart-labels {
            display: flex;
            justify-content: space-between;
            margin-top: 0.5rem;
            font-size: 0.75rem;
            color: var(--slate-500);
        }

        .line-chart-y-labels {
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            font-size: 0.75rem;
            color: var(--slate-500);
            padding: 0.5rem 0;
        }

        /* Customer Bar */
        .customer-bar-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 0.75rem;
        }

        .customer-name {
            width: 80px;
            font-size: 0.875rem;
            color: var(--slate-600);
            flex-shrink: 0;
        }

        .customer-bar-container {
            flex: 1;
            height: 24px;
            background: var(--slate-200);
            border-radius: 4px;
            overflow: hidden;
        }

        .customer-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--blue-600) 0%, var(--blue-500) 100%);
            border-radius: 4px;
            transition: width 1s ease;
        }

        .customer-value {
            width: 100px;
            font-size: 0.75rem;
            color: var(--slate-600);
            text-align: right;
            flex-shrink: 0;
        }

        /* Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        .data-table th {
            background: linear-gradient(135deg, var(--blue-600) 0%, var(--blue-700) 100%);
            padding: 0.75rem 1rem;
            text-align: left;
            font-size: 0.75rem;
            font-weight: 600;
            color: white;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 2px solid var(--blue-800);
        }

        .data-table td {
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            color: var(--slate-700);
            border-bottom: 1px solid var(--slate-200);
        }

        .data-table tr:hover td {
            background: var(--slate-50);
        }

        .data-table .text-right {
            text-align: right;
        }

        /* Footer */
        .footer {
            background: white;
            border-top: 1px solid var(--slate-200);
            padding: 1.5rem 2rem;
            margin-top: auto;
        }

        .footer-content {
            max-width: 1600px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.875rem;
            color: var(--slate-500);
        }

        /* Form Styles */
        .form-section {
            margin-bottom: 2rem;
        }

        .form-section-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--slate-800);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--blue-500);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
        }

        .form-grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--slate-500);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .form-input,
        .form-select {
            background: #ffffff;
            border: 1px solid #cfe0f2;
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            color: var(--slate-800);
            font-size: 0.875rem;
            transition: all 0.2s;
            box-shadow: 0 1px 2px rgba(24, 41, 58, 0.04);
        }

        .form-input::placeholder {
            color: var(--slate-400);
        }

        .form-input:focus,
        .form-select:focus {
            outline: none;
            border-color: var(--blue-400);
            box-shadow: 0 0 0 3px rgba(82, 172, 246, 0.18);
        }

        .form-input:disabled {
            background: #eef4fb;
            color: var(--slate-500);
            cursor: not-allowed;
        }

        .form-input.w-28 {
            width: 7rem;
        }

        /* Calculation Box */
        .calc-box {
            background: linear-gradient(135deg, #f4fbff 0%, #e4f1ff 100%);
            border-radius: 1rem;
            padding: 1.5rem;
            border: 1px solid #cde0f6;
        }

        .calc-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #d8e7f7;
        }

        .calc-item:last-child {
            border-bottom: none;
        }

        .calc-label {
            font-size: 0.875rem;
            color: var(--slate-600);
        }

        .calc-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--blue-700);
        }

        .calc-value.positive {
            color: var(--green-500);
        }

        .calc-value.negative {
            color: var(--red-500);
        }

        /* Material Table */
        .material-table-container {
            overflow-x: auto;
            border-radius: 0.5rem;
            border: 1px solid #d7e5f3;
            background: white;
        }

        .material-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1600px;
        }

        .material-table th {
            background: linear-gradient(180deg, #eef6ff 0%, #dbeaff 100%);
            padding: 0.75rem 0.5rem;
            text-align: center;
            font-size: 0.625rem;
            font-weight: 600;
            color: var(--slate-700);
            text-transform: uppercase;
            letter-spacing: 0.025em;
            border-bottom: 2px solid #c7daf1;
            white-space: nowrap;
            position: sticky;
            top: 0;
        }

        .material-table td {
            padding: 0.5rem;
            font-size: 0.75rem;
            color: var(--slate-700);
            border-bottom: 1px solid #e6eef8;
            text-align: center;
            background: #fcfdff;
        }

        .material-table .form-input,
        .material-table .form-select {
            padding: 0.5rem;
            font-size: 0.75rem;
            min-width: 60px;
        }

        .material-table .w-28 {
            width: 7rem !important;
            min-width: 7rem;
        }

        .material-table .calculated {
            background: #f0f7ff;
            font-weight: 600;
            color: var(--blue-700);
        }

        /* Alert */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            margin-top: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-warning {
            background: rgba(234, 179, 8, 0.1);
            border: 1px solid var(--yellow-500);
            color: var(--yellow-500);
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid var(--green-500);
            color: var(--green-500);
        }

        /* Button */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--blue-500) 0%, var(--blue-700) 100%);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2cc099 0%, var(--blue-600) 100%);
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(22, 136, 110, 0.28);
        }

        .btn-secondary {
            background: #f2f7fc;
            color: var(--slate-700);
            border: 1px solid #d0e0f1;
        }

        .btn-secondary:hover {
            background: #e6f0fa;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .kpi-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .charts-grid {
                grid-template-columns: 1fr;
            }

            .form-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                align-items: flex-start;
            }

            .header-right {
                width: 100%;
                flex-direction: column;
                align-items: flex-start;
            }

            .kpi-grid {
                grid-template-columns: 1fr;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-grid-2 {
                grid-template-columns: 1fr;
            }
        }

        /* Bottom section grid */
        .bottom-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1.5rem;
        }

        @media (max-width: 1200px) {
            .bottom-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Products count chart */
        .products-chart {
            display: flex;
            align-items: flex-end;
            gap: 0.5rem;
            height: 120px;
            padding: 1rem 0;
        }

        .products-bar {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.25rem;
        }

        .products-bar-fill {
            width: 100%;
            background: linear-gradient(180deg, var(--blue-400) 0%, var(--green-500) 100%);
            border-radius: 4px 4px 0 0;
            position: relative;
            transition: height 1s ease;
        }

        .products-bar-value {
            position: absolute;
            top: -1.5rem;
            left: 50%;
            transform: translateX(-50%);
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--slate-300);
        }

        .products-bar-label {
            font-size: 0.625rem;
            color: var(--slate-400);
            text-align: center;
        }

        /* Material breakdown info */
        .material-info {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .material-info-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid var(--slate-700);
        }

        .material-info-product {
            font-weight: 600;
            color: var(--slate-200);
        }

        .material-info-list {
            font-size: 0.75rem;
            color: var(--slate-400);
        }
    </style>
</head>

<body>
    <div class="app-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <div class="sidebar-logo-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2L2 7l10 5 10-5-10-5z" />
                            <path d="M2 17l10 5 10-5" />
                            <path d="M2 12l10 5 10-5" />
                        </svg>
                    </div>
                    <div class="sidebar-logo-text">
                        <span class="sidebar-logo-title">Costing System</span>
                        <span class="sidebar-logo-subtitle">Dharma Electrindo Mfg</span>
                    </div>
                </div>
            </div>
            <nav class="sidebar-nav">
                <div class="sidebar-nav-section">
                    <div class="sidebar-nav-title">Menu Utama</div>
                        <a href="{{ route('dashboard', absolute: false) }}"
                        class="sidebar-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7" rx="1" />
                            <rect x="14" y="3" width="7" height="7" rx="1" />
                            <rect x="14" y="14" width="7" height="7" rx="1" />
                            <rect x="3" y="14" width="7" height="7" rx="1" />
                        </svg>
                        <span>Dashboard</span>
                    </a>

                    <!-- Database Dropdown -->
                    <div class="sidebar-dropdown {{ request()->routeIs('database.*') ? 'open' : '' }}">
                        <button
                            class="sidebar-nav-item sidebar-dropdown-toggle {{ request()->routeIs('database.*') ? 'active' : '' }}"
                            onclick="toggleDropdown(this)">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3" />
                                <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5" />
                                <ellipse cx="12" cy="5" rx="9" ry="3" />
                            </svg>
                            <span>Database</span>
                            <svg class="dropdown-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2"
                                style="width: 16px; height: 16px; margin-left: auto; transition: transform 0.2s;">
                                <polyline points="6 9 12 15 18 9" />
                            </svg>
                        </button>
                        <div class="sidebar-submenu">
                            <a href="{{ route('database.parts', absolute: false) }}"
                                class="sidebar-nav-item sidebar-submenu-item {{ request()->routeIs('database.parts') ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path
                                        d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z" />
                                </svg>
                                <span>Part</span>
                            </a>
                            @if(Route::has('database.wires'))
                                <a href="{{ route('database.wires', absolute: false) }}"
                                    class="sidebar-nav-item sidebar-submenu-item {{ request()->routeIs('database.wires*') ? 'active' : '' }}">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M6 3v6" />
                                        <path d="M18 3v6" />
                                        <path d="M6 21v-6" />
                                        <path d="M18 21v-6" />
                                        <path d="M8 9h8" />
                                        <path d="M8 15h8" />
                                    </svg>
                                    <span>Wire</span>
                                </a>
                            @endif
                            <a href="{{ route('database.costing', absolute: false) }}"
                                class="sidebar-nav-item sidebar-submenu-item {{ request()->routeIs('database.costing') ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="1" x2="12" y2="23" />
                                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                                </svg>
                                <span>Costing</span>
                            </a>
                            <a href="{{ route('database.material-cost', absolute: false) }}"
                                class="sidebar-nav-item sidebar-submenu-item {{ request()->routeIs('database.material-cost') ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M4 7h16" />
                                    <path d="M4 12h16" />
                                    <path d="M4 17h16" />
                                    <circle cx="8" cy="7" r="1" fill="currentColor" stroke="none" />
                                    <circle cx="12" cy="12" r="1" fill="currentColor" stroke="none" />
                                    <circle cx="16" cy="17" r="1" fill="currentColor" stroke="none" />
                                </svg>
                                <span>Material Cost</span>
                            </a>
                            <a href="{{ route('database.customers', absolute: false) }}"
                                class="sidebar-nav-item sidebar-submenu-item {{ request()->routeIs('database.customers') ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                    <circle cx="9" cy="7" r="4" />
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                </svg>
                                <span>Customer</span>
                            </a>
                            <a href="{{ route('database.business-categories', absolute: false) }}"
                                class="sidebar-nav-item sidebar-submenu-item {{ request()->routeIs('database.business-categories*') ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M4 7h16" />
                                    <path d="M4 12h16" />
                                    <path d="M4 17h10" />
                                </svg>
                                <span>Business Categories</span>
                            </a>
                            <a href="{{ route('database.plants', absolute: false) }}"
                                class="sidebar-nav-item sidebar-submenu-item {{ request()->routeIs('database.plants*') ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 21h18" />
                                    <path d="M5 21V8l7-5 7 5v13" />
                                    <path d="M9 21v-6h6v6" />
                                </svg>
                                <span>Plant</span>
                            </a>
                            <a href="{{ route('database.pics', absolute: false) }}"
                                class="sidebar-nav-item sidebar-submenu-item {{ request()->routeIs('database.pics*') ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                    <circle cx="8.5" cy="7" r="4" />
                                    <path d="M20 8v6" />
                                    <path d="M23 11h-6" />
                                </svg>
                                <span>PIC</span>
                            </a>
                            <a href="{{ route('database.cycle-time-templates', absolute: false) }}"
                                class="sidebar-nav-item sidebar-submenu-item {{ request()->routeIs('database.cycle-time-templates*') ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10" />
                                    <polyline points="12 6 12 12 16 14" />
                                </svg>
                                <span>Cycle Time</span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="sidebar-nav-section">
                    <div class="sidebar-nav-title">Input Data</div>
                    <a href="{{ route('form', absolute: false) }}"
                        class="sidebar-nav-item {{ request()->routeIs('form') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                            <polyline points="14 2 14 8 20 8" />
                            <line x1="16" y1="13" x2="8" y2="13" />
                            <line x1="16" y1="17" x2="8" y2="17" />
                            <polyline points="10 9 9 9 8 9" />
                        </svg>
                        <span>Form Costing</span>
                    </a>
                    <a href="{{ route('tracking-documents.index', absolute: false) }}"
                        class="sidebar-nav-item {{ request()->routeIs('tracking-documents.*') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 3v18h18" />
                            <path d="M7 14l3-3 3 2 4-5" />
                        </svg>
                        <span>Project</span>
                    </a>
                </div>
            </nav>
            <div class="sidebar-footer">
                <p class="sidebar-footer-text">© 2025 Dharma Electrindo Mfg</p>
            </div>
        </aside>

        <!-- Sidebar Overlay for Mobile -->
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

        <!-- Main Content Wrapper -->
        <div class="main-wrapper">
            <!-- Header -->
            <header class="header">
                <div class="header-content">
                    <div class="header-left">
                        <button class="mobile-menu-toggle" onclick="toggleSidebar()">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <line x1="3" y1="12" x2="21" y2="12" />
                                <line x1="3" y1="6" x2="21" y2="6" />
                                <line x1="3" y1="18" x2="21" y2="18" />
                            </svg>
                        </button>
                        <div>
                            <h1 class="header-title">@yield('page-title', 'Costing Per Product Dashboard')</h1>
                            <span class="header-subtitle">Dharma Electrindo Mfg</span>
                        </div>
                    </div>
                    <div class="header-right">
                        @yield('header-filters')
                        <nav class="nav-tabs">
                            <a href="{{ route('dashboard', absolute: false) }}"
                                class="nav-tab {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="3" width="7" height="7" rx="1" />
                                    <rect x="14" y="3" width="7" height="7" rx="1" />
                                    <rect x="14" y="14" width="7" height="7" rx="1" />
                                    <rect x="3" y="14" width="7" height="7" rx="1" />
                                </svg>
                                Dashboard
                            </a>
                            <a href="{{ route('database', absolute: false) }}"
                                class="nav-tab {{ request()->routeIs('database') ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3" />
                                    <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5" />
                                    <ellipse cx="12" cy="5" rx="9" ry="3" />
                                </svg>
                                Database
                            </a>
                            <a href="{{ route('form', absolute: false) }}"
                                class="nav-tab {{ request()->routeIs('form') ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                    <polyline points="14 2 14 8 20 8" />
                                    <line x1="16" y1="13" x2="8" y2="13" />
                                    <line x1="16" y1="17" x2="8" y2="17" />
                                    <polyline points="10 9 9 9 8 9" />
                                </svg>
                                Form Costing
                            </a>
                        </nav>
                    </div>
                </div>
            </header>

            <!-- Breadcrumb -->
            <div class="breadcrumb">
                <div class="breadcrumb-content">
                    <a href="{{ route('dashboard', absolute: false) }}">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                            <polyline points="9 22 9 12 15 12 15 22" />
                        </svg>
                    </a>
                    <span class="breadcrumb-separator">/</span>
                    @yield('breadcrumb')
                </div>
            </div>

            <!-- Main Content -->
            <main class="main-content">
                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="footer">
                <div class="footer-content">
                    <span>&copy; 2025 Dharma Electrindo Mfg. All rights reserved.</span>
                    <span>Sistem Costing Manufaktur v1.0</span>
                </div>
            </footer>
        </div>
    </div>

    <div id="app-confirm-modal" class="app-confirm-modal is-hidden" role="dialog" aria-modal="true" aria-labelledby="app-confirm-title" onclick="closeAppConfirmOnOverlay(event)">
        <div class="app-confirm-card">
            <div class="app-confirm-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 9v4" />
                    <path d="M12 17h.01" />
                    <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                </svg>
            </div>
            <h3 id="app-confirm-title" class="app-confirm-title">Konfirmasi Aksi</h3>
            <p id="app-confirm-message" class="app-confirm-message">Apakah Anda yakin?</p>
            <div class="app-confirm-actions">
                <button type="button" class="app-confirm-btn app-confirm-btn-secondary" onclick="closeAppConfirm()">Batal</button>
                <button type="button" id="app-confirm-ok" class="app-confirm-btn app-confirm-btn-danger">Ya, Lanjutkan</button>
            </div>
        </div>
    </div>

    <div id="app-notify-modal" class="app-confirm-modal is-hidden" role="dialog" aria-modal="true" aria-labelledby="app-notify-title" onclick="closeAppNotifyOnOverlay(event)">
        <div class="app-confirm-card app-notify-card">
            <div class="app-confirm-icon app-notify-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="9" />
                    <path d="M12 9v4" />
                    <path d="M12 16h.01" />
                </svg>
            </div>
            <h3 id="app-notify-title" class="app-confirm-title">Informasi</h3>
            <p id="app-notify-message" class="app-confirm-message">Ada informasi untuk Anda.</p>
            <div class="app-confirm-actions app-notify-actions">
                <button type="button" id="app-notify-ok" class="app-confirm-btn app-confirm-btn-primary">OK</button>
            </div>
        </div>
    </div>

    <div id="app-loading-overlay" class="app-loading-overlay is-hidden" role="status" aria-live="polite" aria-label="Memuat data">
        <div class="app-loading-card">
            <div class="app-loading-spinner" aria-hidden="true"></div>
            <p id="app-loading-text" class="app-loading-text">Sedang memproses...</p>
        </div>
    </div>

    <style>
        .app-confirm-modal {
            position: fixed;
            inset: 0;
            z-index: 2100;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(2px);
        }

        .app-confirm-modal.is-hidden {
            display: none;
        }

        .app-confirm-card {
            width: min(430px, 100%);
            border-radius: 14px;
            background: #ffffff;
            border: 1px solid #dbe4f2;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.25);
            padding: 1.2rem;
            animation: appConfirmPopIn 0.2s ease;
        }

        .app-confirm-icon {
            width: 44px;
            height: 44px;
            border-radius: 999px;
            background: #fff7ed;
            color: #f97316;
            border: 1px solid #fed7aa;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.75rem;
        }

        .app-confirm-icon svg {
            width: 22px;
            height: 22px;
        }

        .app-confirm-title {
            margin: 0;
            color: #0f172a;
            font-size: 1.05rem;
            font-weight: 700;
        }

        .app-confirm-message {
            margin: 0.45rem 0 1rem;
            color: #334155;
            font-size: 0.92rem;
            line-height: 1.5;
        }

        .app-confirm-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.6rem;
        }

        .app-confirm-btn {
            border: 0;
            border-radius: 10px;
            padding: 0.62rem 0.95rem;
            font-weight: 600;
            font-size: 0.86rem;
            cursor: pointer;
        }

        .app-confirm-btn-secondary {
            background: #f1f5f9;
            color: #0f172a;
            border: 1px solid #cbd5e1;
        }

        .app-confirm-btn-secondary:hover {
            background: #e2e8f0;
        }

        .app-confirm-btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: #ffffff;
        }

        .app-confirm-btn-danger:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        }

        .app-confirm-btn-primary {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
        }

        .app-confirm-btn-primary:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
        }

        .app-notify-card {
            width: min(400px, 100%);
        }

        .app-notify-icon {
            background: #eff6ff;
            color: #2563eb;
            border-color: #bfdbfe;
        }

        .app-notify-actions {
            justify-content: flex-end;
        }

        .app-loading-overlay {
            position: fixed;
            inset: 0;
            z-index: 2300;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background: rgba(15, 23, 42, 0.32);
            backdrop-filter: blur(1.5px);
        }

        .app-loading-overlay.is-hidden {
            display: none;
        }

        .app-loading-card {
            min-width: 210px;
            max-width: min(88vw, 320px);
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.96);
            border: 1px solid #dbe4f2;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.22);
            padding: 1rem 1.1rem;
            text-align: center;
            animation: appLoadingPopIn 0.2s ease;
        }

        .app-loading-spinner {
            width: 42px;
            height: 42px;
            margin: 0 auto 0.65rem;
            border-radius: 999px;
            border: 3px solid #dbeafe;
            border-top-color: #2563eb;
            border-right-color: #60a5fa;
            animation: appLoadingSpin 0.8s linear infinite;
        }

        .app-loading-text {
            margin: 0;
            color: #1e293b;
            font-size: 0.9rem;
            font-weight: 600;
            letter-spacing: 0.01em;
        }

        @keyframes appLoadingSpin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes appLoadingPopIn {
            from {
                opacity: 0;
                transform: translateY(6px) scale(0.98);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes appConfirmPopIn {
            from {
                opacity: 0;
                transform: translateY(6px) scale(0.98);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
    </style>

    <script>
        let appConfirmCurrentOnConfirm = null;
        let appNotifyCurrentOnClose = null;
        let appLoadingVisible = false;

        function showAppLoading(message) {
            const overlay = document.getElementById('app-loading-overlay');
            const textNode = document.getElementById('app-loading-text');

            if (!overlay) {
                return;
            }

            if (textNode && message) {
                textNode.textContent = message;
            }

            if (appLoadingVisible) {
                return;
            }

            appLoadingVisible = true;
            overlay.classList.remove('is-hidden');
        }

        function hideAppLoading() {
            const overlay = document.getElementById('app-loading-overlay');
            if (!overlay) {
                return;
            }

            appLoadingVisible = false;
            overlay.classList.add('is-hidden');
        }

        function openAppConfirm(message, onConfirm) {
            const modal = document.getElementById('app-confirm-modal');
            const messageNode = document.getElementById('app-confirm-message');
            const okButton = document.getElementById('app-confirm-ok');

            messageNode.textContent = message || 'Apakah Anda yakin ingin melanjutkan?';
            appConfirmCurrentOnConfirm = onConfirm;
            modal.classList.remove('is-hidden');
            document.body.style.overflow = 'hidden';
            okButton.focus();
        }

        function closeAppConfirm() {
            const modal = document.getElementById('app-confirm-modal');
            appConfirmCurrentOnConfirm = null;
            modal.classList.add('is-hidden');
            document.body.style.overflow = '';
        }

        function openAppNotify(message, onClose) {
            const modal = document.getElementById('app-notify-modal');
            const messageNode = document.getElementById('app-notify-message');
            const okButton = document.getElementById('app-notify-ok');

            if (!modal || !messageNode || !okButton) {
                window.alert(message || 'Ada informasi untuk Anda.');
                if (typeof onClose === 'function') {
                    onClose();
                }
                return;
            }

            messageNode.textContent = message || 'Ada informasi untuk Anda.';
            appNotifyCurrentOnClose = onClose;
            modal.classList.remove('is-hidden');
            document.body.style.overflow = 'hidden';
            okButton.focus();
        }

        function closeAppNotify() {
            const modal = document.getElementById('app-notify-modal');
            if (!modal) {
                return;
            }

            const onClose = appNotifyCurrentOnClose;
            appNotifyCurrentOnClose = null;
            modal.classList.add('is-hidden');
            document.body.style.overflow = '';

            if (typeof onClose === 'function') {
                onClose();
            }
        }

        function closeAppNotifyOnOverlay(event) {
            if (event.target && event.target.id === 'app-notify-modal') {
                closeAppNotify();
            }
        }

        function closeAppConfirmOnOverlay(event) {
            if (event.target && event.target.id === 'app-confirm-modal') {
                closeAppConfirm();
            }
        }

        function shouldShowLoadingForLink(link, event) {
            if (!(link instanceof HTMLAnchorElement)) {
                return false;
            }

            if (link.dataset.skipLoadingOverlay === 'true' || event.defaultPrevented) {
                return false;
            }

            if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                return false;
            }

            if (link.target && link.target !== '_self') {
                return false;
            }

            if (link.hasAttribute('download')) {
                return false;
            }

            const hrefValue = link.getAttribute('href') || '';
            const lowerHref = hrefValue.trim().toLowerCase();
            if (!lowerHref || lowerHref === '#' || lowerHref.startsWith('javascript:') || lowerHref.startsWith('mailto:') || lowerHref.startsWith('tel:')) {
                return false;
            }

            try {
                const destination = new URL(link.href, window.location.href);
                if (destination.origin !== window.location.origin) {
                    return false;
                }

                const isSamePageAnchor = destination.pathname === window.location.pathname
                    && destination.search === window.location.search
                    && destination.hash;
                if (isSamePageAnchor) {
                    return false;
                }

                return true;
            } catch (_) {
                return false;
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const okButton = document.getElementById('app-confirm-ok');
            const notifyOkButton = document.getElementById('app-notify-ok');

            okButton.addEventListener('click', function () {
                if (typeof appConfirmCurrentOnConfirm === 'function') {
                    const callback = appConfirmCurrentOnConfirm;
                    closeAppConfirm();
                    callback();
                    return;
                }

                closeAppConfirm();
            });

            if (notifyOkButton) {
                notifyOkButton.addEventListener('click', function () {
                    closeAppNotify();
                });
            }

            document.addEventListener('submit', function (event) {
                const form = event.target;
                if (!form.classList || !form.classList.contains('js-confirm-form')) {
                    return;
                }

                if (form.dataset.confirmed === 'true') {
                    return;
                }

                event.preventDefault();
                const message = form.dataset.confirmMessage || 'Apakah Anda yakin ingin melanjutkan?';
                openAppConfirm(message, function () {
                    form.dataset.confirmed = 'true';
                    showAppLoading();
                    form.submit();
                });
            });

            document.addEventListener('submit', function (event) {
                const form = event.target;
                if (!(form instanceof HTMLFormElement)) {
                    return;
                }

                if (event.defaultPrevented) {
                    return;
                }

                if (form.dataset.skipLoadingOverlay === 'true') {
                    return;
                }

                showAppLoading();
            });

            document.addEventListener('click', function (event) {
                const link = event.target.closest('a');
                if (!shouldShowLoadingForLink(link, event)) {
                    return;
                }

                showAppLoading('Memuat halaman...');
            }, true);

            window.addEventListener('beforeunload', function () {
                showAppLoading('Memuat halaman...');
            });

            window.addEventListener('pageshow', function () {
                hideAppLoading();
            });

            document.addEventListener('keydown', function (event) {
                if (event.key !== 'Escape') {
                    return;
                }

                const modal = document.getElementById('app-confirm-modal');
                if (!modal.classList.contains('is-hidden')) {
                    closeAppConfirm();
                    return;
                }

                const notifyModal = document.getElementById('app-notify-modal');
                if (notifyModal && !notifyModal.classList.contains('is-hidden')) {
                    closeAppNotify();
                }
            });
        });

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
        }

        function toggleDropdown(button) {
            const dropdown = button.closest('.sidebar-dropdown');
            dropdown.classList.toggle('open');
        }
    </script>

    @yield('scripts')
</body>

</html></html>