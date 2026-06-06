<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include '../config/db.php';

/* ── Session vars for sidebar / topbar ── */
$role     = $_SESSION['role'];
$name     = $_SESSION['name'];
$parts    = explode(' ', $name);
$initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));

$approvals = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM approvals WHERE status='Pending'"));
$rfqCount  = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM rfqs"));

/* ── PO data ── */
$result = mysqli_query(
    $conn,
    "SELECT
        purchase_orders.*,
        vendors.vendor_name
     FROM purchase_orders
     LEFT JOIN vendors ON purchase_orders.vendor_id = vendors.id
     ORDER BY purchase_orders.id DESC"
);

$total_pos    = 0;
$total_amount = 0.0;
$pending      = 0;
$approved     = 0;
$rows         = [];

while ($row = mysqli_fetch_assoc($result)) {
    $rows[]        = $row;
    $total_pos++;
    $total_amount += (float) $row['amount'];
    $s = strtolower(trim($row['status']));
    if ($s === 'pending')  $pending++;
    if ($s === 'approved') $approved++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Orders — VendorBridge ERP</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* ── PO-specific styles ── */
        .po-number {
            font-family: var(--font-brand);
            font-size: 13px;
            font-weight: 600;
            color: var(--navy);
            letter-spacing: 0.3px;
        }

        .amount-cell {
            font-weight: 600;
            color: var(--gray-800);
        }

        .vendor-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .vendor-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--blue), var(--navy-light));
            color: white;
            font-size: 11px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .row-id {
            font-size: 12px;
            font-weight: 600;
            color: var(--gray-400);
        }

        /* Subtle odd-row stripe */
        .vendor-table tbody tr:nth-child(odd) td { background: var(--gray-50); }
        .vendor-table tbody tr:nth-child(odd):hover td { background: var(--blue-light); }

        /* ── Filter tabs ── */
        .filter-tabs {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
            align-items: center;
        }

        .filter-tab {
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
            border: 1.5px solid var(--gray-200);
            background: var(--bg-white);
            color: var(--gray-600);
            transition: all var(--transition);
            line-height: 1.5;
        }
        .filter-tab:hover { background: var(--gray-100); border-color: var(--gray-300); }
        .filter-tab.active {
            background: var(--navy);
            color: white;
            border-color: var(--navy);
            box-shadow: 0 2px 8px rgba(15,30,69,0.20);
        }

        /* ── Stat cards strip ── */
        .po-summary {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }
        @media (max-width:900px) { .po-summary { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width:480px) { .po-summary { grid-template-columns: 1fr; } }

        .po-stat {
            background: var(--bg-white);
            border-radius: var(--radius-lg);
            border: 1px solid var(--gray-200);
            box-shadow: var(--shadow-sm);
            padding: 18px 20px;
            display: flex;
            align-items: center;
            gap: 14px;
            position: relative;
            overflow: hidden;
            transition: transform var(--transition), box-shadow var(--transition);
        }
        .po-stat:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }

        .po-stat::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: var(--stat-accent, var(--blue));
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
        }
        .po-stat.blue   { --stat-accent: var(--blue); }
        .po-stat.green  { --stat-accent: var(--success); }
        .po-stat.orange { --stat-accent: var(--warning); }
        .po-stat.purple { --stat-accent: #7c3aed; }

        .po-stat-icon {
            width: 44px;
            height: 44px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .po-stat.blue   .po-stat-icon { background: var(--blue-light); color: var(--blue); }
        .po-stat.green  .po-stat-icon { background: var(--success-bg); color: var(--success); }
        .po-stat.orange .po-stat-icon { background: var(--warning-bg); color: var(--warning); }
        .po-stat.purple .po-stat-icon { background: #f5f3ff; color: #7c3aed; }

        .po-stat-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: var(--gray-500);
            margin-bottom: 4px;
        }
        .po-stat-value {
            font-family: var(--font-brand);
            font-size: 22px;
            font-weight: 700;
            color: var(--navy);
            line-height: 1.1;
        }

        /* ── Action cell ── */
        .action-cell { display: flex; gap: 6px; align-items: center; }

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
        .breadcrumb .current { color: var(--navy); font-weight: 600; }
    </style>
</head>
<body>

<div class="dashboard-container">

    <!-- ════════════════════ SIDEBAR ════════════════════ -->
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
                        <?php if ($rfqCount > 0): ?>
                        <span class="sidebar-badge"><?php echo $rfqCount; ?></span>
                        <?php endif; ?>
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
    <main class="main-content">

        <!-- TOPBAR -->
        <header class="topbar">
            <span class="topbar-title">Purchase Orders</span>

            <div class="topbar-search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/>
                    <path stroke-linecap="round" d="M21 21l-4.35-4.35"/>
                </svg>
                <input type="text" id="globalSearch" placeholder="Search orders…">
            </div>

            <div class="topbar-icon-btn" title="Notifications">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <?php if ($approvals > 0): ?>
                <span class="topbar-notif-dot"></span>
                <?php endif; ?>
            </div>

            <span class="badge badge-info"><?php echo htmlspecialchars($role); ?></span>
        </header>

        <!-- PAGE BODY -->
        <div class="page-body">

            <!-- BREADCRUMB -->
            <div class="breadcrumb">
                <a href="../dashboard.php">Dashboard</a>
                <span>›</span>
                <span class="current">Purchase Orders</span>
            </div>

            <!-- STAT CARDS -->
            <div class="po-summary">

                <div class="po-stat blue">
                    <div class="po-stat-icon">
                        <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="po-stat-label">Total POs</div>
                        <div class="po-stat-value"><?php echo $total_pos; ?></div>
                    </div>
                </div>

                <div class="po-stat green">
                    <div class="po-stat-icon">
                        <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="po-stat-label">Approved</div>
                        <div class="po-stat-value"><?php echo $approved; ?></div>
                    </div>
                </div>

                <div class="po-stat orange">
                    <div class="po-stat-icon">
                        <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="po-stat-label">Pending</div>
                        <div class="po-stat-value"><?php echo $pending; ?></div>
                    </div>
                </div>

                <div class="po-stat purple">
                    <div class="po-stat-icon">
                        <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="po-stat-label">Total Value</div>
                        <div class="po-stat-value">₹ <?php echo number_format($total_amount, 0, '.', ','); ?></div>
                    </div>
                </div>

            </div>
            <!-- /stat cards -->


            <!-- TABLE SECTION -->
            <div class="section">

                <div class="table-header">
                    <div class="table-header-left">
                        <span class="section-title">All Purchase Orders</span>
                        <span class="badge badge-info"><?php echo count($rows); ?> records</span>
                    </div>

                    <!-- Filter tabs -->
                    <div class="filter-tabs">
                        <button class="filter-tab active" onclick="filterTable('all', this)">All</button>
                        <button class="filter-tab" onclick="filterTable('approved', this)">Approved</button>
                        <button class="filter-tab" onclick="filterTable('pending', this)">Pending</button>
                        <button class="filter-tab" onclick="filterTable('rejected', this)">Rejected</button>
                    </div>

                    <!-- Inline search -->
                    <div class="search-box">
                        <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="var(--gray-400)" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/>
                            <path stroke-linecap="round" d="M21 21l-4.35-4.35"/>
                        </svg>
                        <input type="text" id="tableSearch" placeholder="Filter table…"
                               oninput="searchTable(this.value)">
                    </div>

                    <a href="generate_po.php" class="btn-add">
                        <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Generate PO
                    </a>
                </div>

                <!-- Table -->
                <div class="table-wrapper">
                    <table class="vendor-table" id="poTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>PO Number</th>
                                <th>Vendor</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>

                        <?php if (empty($rows)): ?>
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                        </svg>
                                        <h3>No Purchase Orders Found</h3>
                                        <p>Generate your first PO to get started.</p>
                                    </div>
                                </td>
                            </tr>

                        <?php else: ?>
                            <?php foreach ($rows as $row):
                                $s_lower = strtolower(trim($row['status']));
                                $badge   = match($s_lower) {
                                    'approved' => 'badge-approved',
                                    'pending'  => 'badge-pending',
                                    'rejected' => 'badge-rejected',
                                    'paid'     => 'badge-paid',
                                    'draft'    => 'badge-draft',
                                    'sent'     => 'badge-sent',
                                    default    => 'badge-draft',
                                };
                                $vname   = $row['vendor_name'] ?? '?';
                                $vini    = strtoupper(substr($vname, 0, 2));
                            ?>
                            <tr data-status="<?php echo htmlspecialchars($s_lower); ?>">

                                <td><span class="row-id"><?php echo $row['id']; ?></span></td>

                                <td><span class="po-number"><?php echo htmlspecialchars($row['po_number']); ?></span></td>

                                <td>
                                    <div class="vendor-chip">
                                        <div class="vendor-avatar"><?php echo $vini; ?></div>
                                        <?php echo htmlspecialchars($vname); ?>
                                    </div>
                                </td>

                                <td class="amount-cell">
                                    ₹ <?php echo number_format((float)$row['amount'], 2); ?>
                                </td>

                                <td>
                                    <span class="badge <?php echo $badge; ?>">
                                        <?php echo ucfirst(htmlspecialchars($row['status'])); ?>
                                    </span>
                                </td>

                                <td>
                                    <div class="action-cell">
                                        <a href="view_po.php?id=<?php echo $row['id']; ?>"
                                           class="btn-edit btn-sm" title="View">
                                            <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                <circle cx="12" cy="12" r="3"/>
                                            </svg>
                                            View
                                        </a>
                                        <a href="edit_po.php?id=<?php echo $row['id']; ?>"
                                           class="btn-outline btn-sm" title="Edit">
                                            <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            Edit
                                        </a>
                                        <a href="delete_po.php?id=<?php echo $row['id']; ?>"
                                           class="btn-delete btn-sm" title="Delete"
                                           onclick="return confirm('Delete PO <?php echo htmlspecialchars($row['po_number']); ?>?')">
                                            <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </a>
                                    </div>
                                </td>

                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        </tbody>
                    </table>
                </div>
                <!-- /table -->

                <!-- Footer -->
                <div style="padding:12px 22px; border-top:1px solid var(--gray-100); display:flex; align-items:center; justify-content:space-between; font-size:13px; color:var(--gray-500);">
                    <span>
                        Showing <strong style="color:var(--navy);"><?php echo count($rows); ?></strong>
                        purchase order<?php echo count($rows) !== 1 ? 's' : ''; ?>
                    </span>
                    <span style="font-size:12px;">VendorBridge ERP &copy; <?php echo date('Y'); ?></span>
                </div>

            </div>
            <!-- /table section -->

        </div>
        <!-- /page-body -->

    </main>
    <!-- /main-content -->

</div>
<!-- /dashboard-container -->


<script>
    /* ── Status filter ── */
    function filterTable(status, btn) {
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');
        document.querySelectorAll('#poTable tbody tr[data-status]').forEach(row => {
            row.style.display =
                (status === 'all' || row.dataset.status === status) ? '' : 'none';
        });
    }

    /* ── Text search ── */
    function searchTable(query) {
        const q = query.toLowerCase().trim();
        document.querySelectorAll('#poTable tbody tr[data-status]').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    }

    /* ── Sync topbar global search → table ── */
    document.getElementById('globalSearch').addEventListener('input', function () {
        const v = this.value;
        searchTable(v);
        document.getElementById('tableSearch').value = v;
    });
</script>

</body>
</html>
