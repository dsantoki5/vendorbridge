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

// ── Stat counts ──────────────────────────────────────────────
$total_vendors    = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM vendors"));
$total_rfqs       = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM rfqs"));
$total_quotations = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM quotations"));
$total_pos        = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM purchase_orders"));
$total_invoices   = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM invoices"));

$spending_row  = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT SUM(total) AS total_spending FROM invoices"));
$total_spending = $spending_row['total_spending'] ?? 0;

$pending_approvals_count = mysqli_num_rows(mysqli_query($conn,
    "SELECT id FROM approvals WHERE status='Pending'"));

// ── Vendor performance ───────────────────────────────────────
$vendor_result = mysqli_query($conn,
    "SELECT vendors.vendor_name,
            COUNT(quotations.id)   AS total_quotes,
            COALESCE(SUM(invoices.total), 0) AS total_billed
     FROM vendors
     LEFT JOIN quotations ON vendors.id = quotations.vendor_id
     LEFT JOIN purchase_orders ON purchase_orders.vendor_id = vendors.id
     LEFT JOIN invoices ON invoices.po_id = purchase_orders.id
     GROUP BY vendors.id
     ORDER BY total_billed DESC"
);

$vendors = [];
while ($row = mysqli_fetch_assoc($vendor_result)) {
    $vendors[] = $row;
}

// ── Recent invoices ──────────────────────────────────────────
$recent_invoices = mysqli_query($conn,
    "SELECT invoices.invoice_number, invoices.total, invoices.status,
            purchase_orders.po_number
     FROM invoices
     LEFT JOIN purchase_orders ON invoices.po_id = purchase_orders.id
     ORDER BY invoices.id DESC
     LIMIT 5"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics — VendorBridge ERP</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
<div class="dashboard-container">

    <!-- ===================== SIDEBAR ===================== -->
    <aside class="sidebar">

        <div class="sidebar-brand">
            <div class="sidebar-brand-icon">
                <svg width="20" height="20" viewBox="0 0 28 28" fill="none">
                    <path d="M4 8h20v14a2 2 0 01-2 2H6a2 2 0 01-2-2V8z"
                          fill="rgba(255,255,255,0.85)"/>
                    <path d="M2 8h24M10 8V5a2 2 0 012-2h4a2 2 0 012 2v3"
                          stroke="white" stroke-width="1.8" stroke-linecap="round"/>
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
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001
                                     1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6
                                     0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011
                                     1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="../vendor/vendor_list.php">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10
                                     0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3
                                     3 0 015.356-1.857M7 20v-2c0-.656.126-1.283
                                     .356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3
                                     3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Vendors
                    </a>
                </li>
            </ul>

            <div class="sidebar-section-label">Procurement</div>
            <ul>
                <li>
                    <a href="../rfq/rfq_list.php">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0
                                     012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1
                                     0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        RFQs
                        <span class="sidebar-badge"><?php echo $rfqCount; ?></span>
                    </a>
                </li>
                <li>
                    <a href="../quotation/quotation_list.php">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1
                                     1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2
                                     2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002
                                     2h8a2 2 0 002-2v-2"/>
                        </svg>
                        Quotations
                    </a>
                </li>
                <li>
                    <a href="../quotation/compare_quotation.php">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0
                                     002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0
                                     012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2
                                     2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2
                                     0 01-2-2z"/>
                        </svg>
                        Compare Quotations
                    </a>
                </li>
                <li>
                    <a href="../approval/approval_list.php">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Approvals
                        <?php if ($approvals > 0): ?>
                        <span class="sidebar-badge"><?php echo $approvals; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li>
                    <a href="../purchase_order/po_list.php">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        Purchase Orders
                    </a>
                </li>
                <li>
                    <a href="../invoice/invoice_list.php">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0
                                     002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2
                                     2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2
                                     0 014 0z"/>
                        </svg>
                        Invoices
                    </a>
                </li>
            </ul>

            <div class="sidebar-section-label">Reports</div>
            <ul>
                <li>
                    <a href="analytics.php" class="active">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                        </svg>
                        Analytics
                    </a>
                </li>
                <li>
                    <a href="../logs/activity_log.php">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
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
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3
                                     3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
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
        <div class="topbar">
            <div class="topbar-title">Reports & Analytics</div>

            <div class="topbar-search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/>
                    <path stroke-linecap="round" d="M21 21l-4.35-4.35"/>
                </svg>
                <input type="text" placeholder="Search...">
            </div>

            <div class="topbar-icon-btn">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118
                             14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0
                             10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0
                             .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3
                             0 11-6 0v-1m6 0H9"/>
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
                <span class="breadcrumb-sep">›</span>
                <span class="current">Reports & Analytics</span>
            </div>

            <!-- ── STAT CARDS ── -->
            <div class="cards" style="grid-template-columns: repeat(3, 1fr);
                                      margin-bottom:28px;">

                <div class="card blue">
                    <div class="card-icon" style="background:var(--blue-light);">
                        <svg width="22" height="22" fill="none" viewBox="0 0 24 24"
                             stroke="var(--blue)" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10
                                     0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3
                                     3 0 015.356-1.857M7 20v-2c0-.656.126-1.283
                                     .356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3
                                     3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div class="card-label">Total Vendors</div>
                    <h1><?php echo $total_vendors; ?></h1>
                </div>

                <div class="card blue">
                    <div class="card-icon" style="background:var(--blue-light);">
                        <svg width="22" height="22" fill="none" viewBox="0 0 24 24"
                             stroke="var(--blue)" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0
                                     012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1
                                     0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="card-label">Total RFQs</div>
                    <h1><?php echo $total_rfqs; ?></h1>
                </div>

                <div class="card blue">
                    <div class="card-icon" style="background:var(--blue-light);">
                        <svg width="22" height="22" fill="none" viewBox="0 0 24 24"
                             stroke="var(--blue)" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1
                                     1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2
                                     2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002
                                     2h8a2 2 0 002-2v-2"/>
                        </svg>
                    </div>
                    <div class="card-label">Total Quotations</div>
                    <h1><?php echo $total_quotations; ?></h1>
                </div>

                <div class="card green">
                    <div class="card-icon" style="background:var(--success-bg);">
                        <svg width="22" height="22" fill="none" viewBox="0 0 24 24"
                             stroke="var(--success)" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </div>
                    <div class="card-label">Purchase Orders</div>
                    <h1><?php echo $total_pos; ?></h1>
                </div>

                <div class="card green">
                    <div class="card-icon" style="background:var(--success-bg);">
                        <svg width="22" height="22" fill="none" viewBox="0 0 24 24"
                             stroke="var(--success)" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0
                                     002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2
                                     2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2
                                     0 014 0z"/>
                        </svg>
                    </div>
                    <div class="card-label">Total Invoices</div>
                    <h1><?php echo $total_invoices; ?></h1>
                </div>

                <div class="card orange">
                    <div class="card-icon" style="background:var(--warning-bg);">
                        <svg width="22" height="22" fill="none" viewBox="0 0 24 24"
                             stroke="var(--warning)" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895
                                     3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599
                                     1M12 8V7m0 1v8m0 0v1m0-1c-1.11
                                     0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0
                                     0118 0z"/>
                        </svg>
                    </div>
                    <div class="card-label">Total Spending</div>
                    <h1 style="font-size:22px;">
                        ₹ <?php echo number_format($total_spending, 2); ?>
                    </h1>
                </div>

            </div><!-- /cards -->

            <!-- ── TWO-COLUMN: Vendor Performance + Recent Invoices ── -->
            <div class="analytics-grid" style="margin-bottom:24px;">

                <!-- Vendor Performance Table -->
                <div class="section">
                    <div class="section-header">
                        <span class="section-title">Vendor Performance</span>
                        <span class="badge badge-info">
                            <?php echo count($vendors); ?> vendors
                        </span>
                    </div>
                    <div class="table-wrapper">
                        <table class="vendor-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Vendor</th>
                                    <th>Quotations</th>
                                    <th>Total Billed</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($vendors)): ?>
                                <?php foreach ($vendors as $i => $row): ?>
                                <tr>
                                    <td style="color:var(--gray-400); font-size:13px;">
                                        <?php echo $i + 1; ?>
                                    </td>
                                    <td>
                                        <strong style="color:var(--gray-800);">
                                            <?php echo htmlspecialchars($row['vendor_name']); ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">
                                            <?php echo $row['total_quotes']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong style="color:var(--navy);">
                                            ₹ <?php echo number_format($row['total_billed'], 2); ?>
                                        </strong>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4">
                                        <div class="empty-state" style="padding:40px;">
                                            <h3>No vendor data</h3>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Invoices -->
                <div class="section">
                    <div class="section-header">
                        <span class="section-title">Recent Invoices</span>
                        <a href="../invoice/invoice_list.php"
                           style="font-size:12.5px; color:var(--blue); font-weight:600;">
                            View all →
                        </a>
                    </div>
                    <div class="section-body" style="padding:0;">
                        <ul class="activity-list" style="padding:0 22px;">
                        <?php
                        $has_invoices = false;
                        while ($inv = mysqli_fetch_assoc($recent_invoices)):
                            $has_invoices = true;
                            $status      = strtolower($inv['status'] ?? 'pending');
                            $badgeMap    = [
                                'paid'    => 'badge-paid',
                                'pending' => 'badge-pending',
                                'overdue' => 'badge-overdue',
                                'draft'   => 'badge-draft',
                                'sent'    => 'badge-sent',
                            ];
                            $badgeClass = $badgeMap[$status] ?? 'badge-draft';
                        ?>
                        <li class="activity-item">
                            <div class="activity-icon green">
                                <svg width="15" height="15" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="activity-content">
                                <p>
                                    <strong><?php echo htmlspecialchars($inv['invoice_number']); ?></strong>
                                    &nbsp;·&nbsp;
                                    <?php echo htmlspecialchars($inv['po_number'] ?? '—'); ?>
                                    &nbsp;
                                    <span class="badge <?php echo $badgeClass; ?>"
                                          style="font-size:11px;">
                                        <?php echo ucfirst($inv['status'] ?? 'pending'); ?>
                                    </span>
                                </p>
                                <time>
                                    ₹ <?php echo number_format($inv['total'], 2); ?>
                                </time>
                            </div>
                        </li>
                        <?php endwhile; ?>
                        <?php if (!$has_invoices): ?>
                        <li style="padding:40px 0; text-align:center;
                                   color:var(--gray-400); font-size:13.5px;">
                            No invoices yet.
                        </li>
                        <?php endif; ?>
                        </ul>
                    </div>
                </div>

            </div><!-- /analytics-grid -->

            <!-- ── EXPORT ── -->
            <div style="display:flex; justify-content:flex-end;">
                <a href="export_report.php" class="btn-add">
                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4
                                 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Export Report
                </a>
            </div>

        </div><!-- /page-body -->
    </div><!-- /main-content -->
</div><!-- /dashboard-container -->

</body>
</html>