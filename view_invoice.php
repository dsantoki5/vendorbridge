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
$rfqCount  = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM rfqs"));

$id = intval($_GET['id']);

$result = mysqli_query(
    $conn,
    "SELECT
        invoices.*,
        purchase_orders.po_number,
        purchase_orders.created_at AS po_date,
        vendors.vendor_name,
        vendors.email AS vendor_email,
        vendors.phone AS vendor_phone,
        vendors.address AS vendor_address
     FROM invoices
     LEFT JOIN purchase_orders ON invoices.po_id       = purchase_orders.id
     LEFT JOIN vendors         ON purchase_orders.vendor_id = vendors.id
     WHERE invoices.id = '$id'"
);

$row = mysqli_fetch_assoc($result);

if (!$row) {
    header("Location: invoice_list.php");
    exit();
}

$statusClass = match(strtolower($row['status'])) {
    'paid'    => 'badge-paid',
    'unpaid'  => 'badge-inactive',
    'overdue' => 'badge-overdue',
    default   => 'badge-draft',
};

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice <?php echo htmlspecialchars($row['invoice_number']); ?> — VendorBridge ERP</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="dashboard-container">

    <!-- ===================== SIDEBAR ===================== -->
    <aside class="sidebar no-print">

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
                        <span class="sidebar-badge"><?php echo $rfqCount; ?></span>
                    </a>
                </li>
                <li>
                    <a href="../quotation/quotation_list.php">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/>
                        </svg>
                        Quotations
                    </a>
                </li>
                <li>
                    <a href="../quotation/compare_quotation.php">
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
                    <a href="../purchase_order/po_list.php">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        Purchase Orders
                    </a>
                </li>
                <li>
                    <a href="invoice_list.php" class="active">
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
    <!-- END SIDEBAR -->

    <!-- ===================== MAIN CONTENT ===================== -->
    <div class="main-content">

        <!-- TOPBAR -->
        <div class="topbar no-print">
            <div class="topbar-title">View Invoice</div>

            <div class="topbar-search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/>
                </svg>
                <input type="text" placeholder="Search invoices...">
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
            <div class="breadcrumb no-print">
                <a href="../dashboard.php">Dashboard</a>
                <span class="breadcrumb-sep">›</span>
                <a href="invoice_list.php">Invoices</a>
                <span class="breadcrumb-sep">›</span>
                <span class="current"><?php echo htmlspecialchars($row['invoice_number']); ?></span>
            </div>

            <!-- ACTION BAR -->
            <div class="no-print" style="display:flex; align-items:center; gap:10px; margin-bottom:20px;">
                <button onclick="window.print()" class="btn-save">
                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/>
                    </svg>
                    Print Invoice
                </button>
                <a href="invoice_list.php" class="btn-back">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
                    </svg>
                    Back to Invoices
                </a>
                <span class="badge <?php echo $statusClass; ?>" style="font-size:13px; padding:6px 14px;">
                    <?php echo htmlspecialchars($row['status']); ?>
                </span>
            </div>

            <!-- INVOICE DOCUMENT -->
            <div class="invoice-doc">

                <!-- Invoice Header -->
                <div class="invoice-header">
                    <div>
                        <div style="display:flex; align-items:center; gap:12px; margin-bottom:10px;">
                            <div style="
                                width:42px; height:42px;
                                background:rgba(255,255,255,0.15);
                                border-radius:10px;
                                display:flex; align-items:center; justify-content:center;
                            ">
                                <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <div>
                                <div style="font-family:var(--font-brand); font-size:22px; font-weight:700; color:white; letter-spacing:-0.3px;">
                                    VendorBridge ERP
                                </div>
                                <div style="font-size:12px; color:rgba(255,255,255,0.55);">
                                    Procurement Management System
                                </div>
                            </div>
                        </div>
                        <div style="font-size:13px; color:rgba(255,255,255,0.60); line-height:1.7;">
                            <?php if (!empty($row['vendor_address'])): ?>
                                <?php echo nl2br(htmlspecialchars($row['vendor_address'])); ?>
                            <?php else: ?>
                                123 Business Park, Mumbai, Maharashtra
                            <?php endif; ?>
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-family:var(--font-brand); font-size:32px; font-weight:700; color:white; letter-spacing:-1px; margin-bottom:6px;">
                            INVOICE
                        </div>
                        <div style="font-size:15px; color:rgba(255,255,255,0.75); font-weight:600; margin-bottom:4px;">
                            # <?php echo htmlspecialchars($row['invoice_number']); ?>
                        </div>
                        <div style="margin-top:10px;">
                            <span style="
                                background:rgba(255,255,255,0.15);
                                color:white;
                                font-size:12px;
                                font-weight:700;
                                padding:4px 14px;
                                border-radius:20px;
                                letter-spacing:0.5px;
                                text-transform:uppercase;
                            ">
                                <?php echo htmlspecialchars($row['status']); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Invoice Meta Row -->
                <div class="invoice-info-row">
                    <div class="invoice-info-cell">
                        <label>Invoice Number</label>
                        <p><?php echo htmlspecialchars($row['invoice_number']); ?></p>
                    </div>
                    <div class="invoice-info-cell">
                        <label>PO Reference</label>
                        <p><?php echo htmlspecialchars($row['po_number'] ?? '—'); ?></p>
                        <?php if (!empty($row['po_date'])): ?>
                        <small>PO Date: <?php echo date('d M Y', strtotime($row['po_date'])); ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="invoice-info-cell">
                        <label>Vendor</label>
                        <p><?php echo htmlspecialchars($row['vendor_name'] ?? '—'); ?></p>
                        <?php if (!empty($row['vendor_email'])): ?>
                        <small><?php echo htmlspecialchars($row['vendor_email']); ?></small>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Line Items Table -->
                <div class="invoice-table-wrap">
                    <table class="invoice-table">
                        <thead>
                            <tr>
                                <th style="width:50%;">Description</th>
                                <th>Amount</th>
                                <th>Tax</th>
                                <th style="text-align:right;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <strong style="color:var(--gray-800);">
                                        <?php echo !empty($row['po_number']) ? 'Services / Goods per ' . htmlspecialchars($row['po_number']) : 'Goods & Services'; ?>
                                    </strong>
                                    <div style="font-size:12px; color:var(--gray-400); margin-top:2px;">
                                        As per purchase order agreement
                                    </div>
                                </td>
                                <td>₹ <?php echo number_format($row['subtotal'], 2); ?></td>
                                <td>₹ <?php echo number_format($row['tax'], 2); ?></td>
                                <td style="text-align:right; font-weight:600; color:var(--gray-800);">
                                    ₹ <?php echo number_format($row['total'], 2); ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Totals -->
                <div class="invoice-totals">
                    <div class="invoice-totals-box">
                        <div class="totals-row">
                            <span>Subtotal</span>
                            <span>₹ <?php echo number_format($row['subtotal'], 2); ?></span>
                        </div>
                        <div class="totals-row">
                            <span>Tax (GST)</span>
                            <span>₹ <?php echo number_format($row['tax'], 2); ?></span>
                        </div>
                        <div class="totals-row total-final">
                            <span>Total Payable</span>
                            <span>₹ <?php echo number_format($row['total'], 2); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="invoice-footer-actions no-print">
                    <button onclick="window.print()" class="btn-save">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/>
                        </svg>
                        Print Invoice
                    </button>
                    <a href="invoice_list.php" class="btn-back">
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
                        </svg>
                        Back to List
                    </a>
                </div>

            </div><!-- /.invoice-doc -->

        </div><!-- /.page-body -->
    </div><!-- /.main-content -->

</div><!-- /.dashboard-container -->

</body>
</html>