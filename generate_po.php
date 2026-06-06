<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include '../config/db.php';

$role     = $_SESSION['role'];
$name     = $_SESSION['name'];
$parts    = explode(' ', $name);
$initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));

$approvals = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM approvals WHERE status='Pending'"));

/* ── Generate PO on submit ── */
$error   = '';
$success = '';

if (isset($_POST['generate'])) {
    $quotation_id = (int) $_POST['quotation_id'];          // cast to int — kills SQL injection

    $stmt = mysqli_prepare($conn, "SELECT * FROM quotations WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $quotation_id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if ($row) {
        $vendor_id = $row['vendor_id'];
        $amount    = $row['price'];
        $po_number = 'PO-' . date('Ymd') . '-' . rand(100, 999);

        $ins = mysqli_prepare($conn,
            "INSERT INTO purchase_orders (po_number, quotation_id, vendor_id, amount)
             VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($ins, 'siid', $po_number, $quotation_id, $vendor_id, $amount);

        if (mysqli_stmt_execute($ins)) {
            header("Location: po_list.php");
            exit();
        } else {
            $error = 'Database error — could not create PO. Please try again.';
        }
    } else {
        $error = 'Invalid quotation selected. Please choose a valid quotation.';
    }
}

/* ── Load all quotations for the dropdown ── */
$quotations = mysqli_query($conn,
    "SELECT quotations.id,
            quotations.price,
            quotations.delivery_days,
            quotations.status,
            rfqs.rfq_title,
            vendors.vendor_name
     FROM quotations
     LEFT JOIN rfqs    ON quotations.rfq_id   = rfqs.id
     LEFT JOIN vendors ON quotations.vendor_id = vendors.id
     ORDER BY quotations.id DESC"
);
$quotation_rows = [];
while ($q = mysqli_fetch_assoc($quotations)) {
    $quotation_rows[] = $q;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Purchase Order — VendorBridge</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* ── Preview Card ── */
        .po-preview {
            background: var(--blue-light);
            border: 1.5px solid var(--blue-soft);
            border-radius: var(--radius-lg);
            padding: 20px 22px;
            margin-top: 4px;
            margin-bottom: 22px;
            display: none;                   /* shown by JS */
            animation: fadeUp .22s ease both;
        }
        .po-preview-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .7px;
            color: var(--blue);
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .po-preview-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px 20px;
        }
        .po-preview-item label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: .5px;
            margin-bottom: 3px;
        }
        .po-preview-item p {
            font-size: 14px;
            font-weight: 600;
            color: var(--navy);
        }
        /* ── PO Number pill ── */
        .po-number-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: linear-gradient(135deg, var(--navy), var(--navy-mid));
            color: white;
            border-radius: var(--radius-md);
            padding: 8px 16px;
            font-family: var(--font-brand);
            font-size: 13.5px;
            font-weight: 600;
            letter-spacing: .3px;
            margin-bottom: 22px;
        }
        /* ── Select with custom arrow ── */
        .select-wrapper {
            position: relative;
        }
        .select-wrapper select {
            appearance: none;
            padding-right: 38px !important;
        }
        .select-wrapper .select-arrow {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: var(--gray-400);
        }
        /* ── Form action bar ── */
        .form-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 22px;
            border-top: 1px solid var(--gray-100);
            background: var(--gray-50);
        }
        /* ── Breadcrumb ── */
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: var(--gray-400);
            margin-bottom: 22px;
        }
        .breadcrumb a { color: var(--gray-500); font-weight: 500; }
        .breadcrumb a:hover { color: var(--blue); }
        .breadcrumb .sep { font-size: 11px; }
        .breadcrumb .current { color: var(--navy); font-weight: 600; }
    </style>
</head>
<body>

<div class="dashboard-container">

    <!-- ════════════════════ SIDEBAR ════════════════════ -->
    <aside class="sidebar">

        <div class="sidebar-brand">
            <div class="sidebar-brand-icon">
                <svg width="20" height="20" viewBox="0 0 28 28" fill="none">
                    <path d="M4 8h20v14a2 2 0 01-2 2H6a2 2 0 01-2-2V8z" fill="rgba(255,255,255,0.85)"/>
                    <path d="M2 8h24M10 8V5a2 2 0 012-2h4a2 2 0 012 2v3" stroke="white" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </div>
            <div>
                <h2>VendorBridge</h2>
                <span>ERP Platform</span>
            </div>
        </div>

        <nav class="sidebar-nav">

            <div class="sidebar-section-label">Main Menu</div>
            <ul>
                <li>
                    <a href="../dashboard.php">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="../vendor/vendor_list.php">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Vendors
                    </a>
                </li>
            </ul>

            <div class="sidebar-section-label">Procurement</div>
            <ul>
                <li>
                    <a href="../rfq/rfq_list.php">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        RFQs
                    </a>
                </li>
                <li>
                    <a href="quotation_list.php">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/>
                        </svg>
                        Quotations
                    </a>
                </li>
                <li>
                    <a href="compare_quotation.php">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Compare Quotations
                    </a>
                </li>
                <li>
                    <a href="../approval/approval_list.php">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Approvals
                        <?php if ($approvals > 0): ?>
                        <span class="sidebar-badge"><?php echo $approvals; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li>
                    <a href="po_list.php" class="active">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        Purchase Orders
                    </a>
                </li>
                <li>
                    <a href="../invoice/invoice_list.php">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Invoices
                    </a>
                </li>
            </ul>

            <div class="sidebar-section-label">Reports</div>
            <ul>
                <li>
                    <a href="../reports/analytics.php">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                        </svg>
                        Analytics
                    </a>
                </li>
                <li>
                    <a href="../logs/activity_log.php">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Activity Logs
                    </a>
                </li>
            </ul>

        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="sidebar-avatar"><?php echo $initials; ?></div>
                <div class="sidebar-user-info">
                    <strong><?php echo htmlspecialchars($name); ?></strong>
                    <small><?php echo htmlspecialchars($role); ?></small>
                </div>
            </div>
            <ul style="margin-top:6px;">
                <li>
                    <a href="../logout.php" style="color:rgba(255,100,100,0.80);">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Logout
                    </a>
                </li>
            </ul>
        </div>

    </aside>
    <!-- /sidebar -->


    <!-- ════════════════════ MAIN CONTENT ════════════════════ -->
    <div class="main-content">

        <!-- TOPBAR -->
        <div class="topbar">
            <div class="topbar-title">Generate Purchase Order</div>

            <div class="topbar-search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/>
                </svg>
                <input type="text" placeholder="Search vendors, RFQs...">
            </div>

            <div class="topbar-icon-btn">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <?php if ($approvals > 0): ?>
                <span class="topbar-notif-dot"></span>
                <?php endif; ?>
            </div>

            <span class="badge badge-info"><?php echo htmlspecialchars($role); ?></span>
        </div>

        <!-- PAGE BODY -->
        <div class="page-body">

            <!-- BREADCRUMB -->
            <div class="breadcrumb">
                <a href="../dashboard.php">Dashboard</a>
                <span class="sep">›</span>
                <a href="po_list.php">Purchase Orders</a>
                <span class="sep">›</span>
                <span class="current">Generate PO</span>
            </div>

            <!-- Two-column layout: form left, info right -->
            <div style="display:grid; grid-template-columns:1fr 340px; gap:22px; align-items:start;">

                <!-- ── MAIN FORM CARD ── -->
                <div class="form-container">

                    <!-- Gradient header -->
                    <div class="form-header">
                        <div class="form-header-icon">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="form-title">Generate Purchase Order</div>
                            <div class="form-subtitle">Select an approved quotation to create a new PO</div>
                        </div>
                    </div>

                    <div class="form-body">

                        <!-- Error / success alerts -->
                        <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                        <?php endif; ?>

                        <!-- Auto-generated PO number preview -->
                        <div style="margin-bottom:22px;">
                            <div style="font-size:12px; font-weight:600; color:var(--gray-500); text-transform:uppercase; letter-spacing:.5px; margin-bottom:8px;">
                                PO Number will be assigned as:
                            </div>
                            <div class="po-number-badge">
                                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                                </svg>
                                PO-<?php echo date('Ymd'); ?>-<span style="opacity:.65;">XXX</span>
                            </div>
                        </div>

                        <form method="POST" id="poForm">

                            <!-- Quotation Selector -->
                            <div class="form-row">
                                <label class="required">Select Quotation</label>
                                <div class="select-wrapper">
                                    <select name="quotation_id" id="quotationSelect" required>
                                        <option value="" disabled selected>— Choose a quotation —</option>
                                        <?php foreach ($quotation_rows as $q): ?>
                                        <option value="<?php echo $q['id']; ?>"
                                                data-vendor="<?php echo htmlspecialchars($q['vendor_name']); ?>"
                                                data-rfq="<?php echo htmlspecialchars($q['rfq_title']); ?>"
                                                data-price="<?php echo number_format($q['price'], 2); ?>"
                                                data-days="<?php echo htmlspecialchars($q['delivery_days']); ?>"
                                                data-status="<?php echo htmlspecialchars($q['status']); ?>">
                                            #<?php echo $q['id']; ?> — <?php echo htmlspecialchars($q['vendor_name']); ?> — ₹<?php echo number_format($q['price'], 2); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <svg class="select-arrow" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </div>

                            <!-- Dynamic preview -->
                            <div class="po-preview" id="poPreview">
                                <div class="po-preview-title">
                                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Quotation Details
                                </div>
                                <div class="po-preview-grid">
                                    <div class="po-preview-item">
                                        <label>RFQ Title</label>
                                        <p id="prev-rfq">—</p>
                                    </div>
                                    <div class="po-preview-item">
                                        <label>Vendor</label>
                                        <p id="prev-vendor">—</p>
                                    </div>
                                    <div class="po-preview-item">
                                        <label>Amount</label>
                                        <p id="prev-price" style="color:var(--success);">—</p>
                                    </div>
                                    <div class="po-preview-item">
                                        <label>Delivery</label>
                                        <p id="prev-days">—</p>
                                    </div>
                                    <div class="po-preview-item">
                                        <label>Status</label>
                                        <p id="prev-status">—</p>
                                    </div>
                                </div>
                            </div>

                        </form>
                    </div>

                    <!-- Form action bar (outside form-body padding) -->
                    <div class="form-actions">
                        <a href="po_list.php" class="btn-back">
                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Back to PO List
                        </a>
                        <button type="submit" form="poForm" name="generate" class="btn-save" id="submitBtn" disabled>
                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Generate PO
                        </button>
                    </div>

                </div>
                <!-- /form card -->


                <!-- ── RIGHT INFO PANEL ── -->
                <div>

                    <!-- How it works -->
                    <div class="section" style="margin-bottom:16px;">
                        <div class="section-header">
                            <span class="section-title">How It Works</span>
                        </div>
                        <div class="section-body" style="padding:16px 20px;">
                            <?php
                            $steps = [
                                ['num'=>'1', 'color'=>'var(--blue)',    'text'=>'Select an approved quotation from the dropdown.'],
                                ['num'=>'2', 'color'=>'var(--warning)', 'text'=>'Review the quotation details in the preview card.'],
                                ['num'=>'3', 'color'=>'var(--success)', 'text'=>'Click <strong>Generate PO</strong> — a unique PO number is auto-assigned and the order is saved.'],
                            ];
                            foreach ($steps as $s): ?>
                            <div style="display:flex; gap:12px; margin-bottom:14px; align-items:flex-start;">
                                <div style="width:26px; height:26px; border-radius:50%; background:<?php echo $s['color']; ?>22; color:<?php echo $s['color']; ?>; font-size:12px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; margin-top:1px;">
                                    <?php echo $s['num']; ?>
                                </div>
                                <p style="font-size:13.5px; color:var(--gray-600); line-height:1.5; margin:0;"><?php echo $s['text']; ?></p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="section">
                        <div class="section-header">
                            <span class="section-title">Quotation Summary</span>
                        </div>
                        <div class="section-body" style="padding:16px 20px;">
                            <?php
                            $total     = count($quotation_rows);
                            $approved  = count(array_filter($quotation_rows, fn($r) => strtolower($r['status']) === 'approved'));
                            $pending   = count(array_filter($quotation_rows, fn($r) => strtolower($r['status']) === 'pending'));
                            $rejected  = count(array_filter($quotation_rows, fn($r) => strtolower($r['status']) === 'rejected'));
                            $stats = [
                                ['label'=>'Total Quotations', 'val'=>$total,    'color'=>'var(--blue)'],
                                ['label'=>'Approved',         'val'=>$approved, 'color'=>'var(--success)'],
                                ['label'=>'Pending',          'val'=>$pending,  'color'=>'var(--warning)'],
                                ['label'=>'Rejected',         'val'=>$rejected, 'color'=>'var(--danger)'],
                            ];
                            foreach ($stats as $st): ?>
                            <div style="display:flex; align-items:center; justify-content:space-between; padding:8px 0; border-bottom:1px solid var(--gray-100);">
                                <span style="font-size:13px; color:var(--gray-600);"><?php echo $st['label']; ?></span>
                                <strong style="font-size:14px; color:<?php echo $st['color']; ?>;"><?php echo $st['val']; ?></strong>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>
                <!-- /right panel -->

            </div>
            <!-- /two-column grid -->

        </div>
        <!-- /page-body -->

    </div>
    <!-- /main-content -->

</div>
<!-- /dashboard-container -->


<script>
(function () {
    const select    = document.getElementById('quotationSelect');
    const preview   = document.getElementById('poPreview');
    const submitBtn = document.getElementById('submitBtn');

    const fields = {
        rfq:    document.getElementById('prev-rfq'),
        vendor: document.getElementById('prev-vendor'),
        price:  document.getElementById('prev-price'),
        days:   document.getElementById('prev-days'),
        status: document.getElementById('prev-status'),
    };

    const statusClass = {
        approved : 'badge-approved',
        pending  : 'badge-pending',
        rejected : 'badge-rejected',
    };

    select.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        if (!opt.value) { preview.style.display = 'none'; submitBtn.disabled = true; return; }

        fields.rfq.textContent    = opt.dataset.rfq    || '—';
        fields.vendor.textContent = opt.dataset.vendor || '—';
        fields.price.textContent  = opt.dataset.price ? '₹ ' + opt.dataset.price : '—';
        fields.days.textContent   = opt.dataset.days  ? opt.dataset.days + ' Days' : '—';

        // Status badge
        const s   = (opt.dataset.status || '').toLowerCase();
        const cls = statusClass[s] || 'badge-draft';
        fields.status.innerHTML = `<span class="badge ${cls}">${opt.dataset.status}</span>`;

        preview.style.display = 'block';
        submitBtn.disabled    = false;
    });
})();
</script>

</body>
</html>
