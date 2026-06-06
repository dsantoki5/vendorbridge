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

$result = mysqli_query(
    $conn,
    "SELECT
        approvals.*,
        quotations.price,
        vendors.vendor_name,
        rfqs.rfq_title
     FROM approvals
     LEFT JOIN quotations ON approvals.quotation_id = quotations.id
     LEFT JOIN vendors    ON quotations.vendor_id   = vendors.id
     LEFT JOIN rfqs       ON quotations.rfq_id      = rfqs.id
     ORDER BY approvals.id DESC"
);

$rowCount = mysqli_num_rows($result);

$totalApproved = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM approvals WHERE status='Approved'"));
$totalRejected = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM approvals WHERE status='Rejected'"));
$totalPending  = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM approvals WHERE status='Pending'"));

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approval Workflow — VendorBridge ERP</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="dashboard-container">

    <!-- ===================== SIDEBAR ===================== -->
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
                    <a href="approval_list.php" class="active">
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
    <!-- END SIDEBAR -->

    <!-- ===================== MAIN CONTENT ===================== -->
    <div class="main-content">

        <!-- TOPBAR -->
        <div class="topbar">
            <div class="topbar-title">Approval Workflow</div>

            <div class="topbar-search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/>
                </svg>
                <input type="text" placeholder="Search approvals, vendors...">
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
                <span class="breadcrumb-sep">›</span>
                <span class="current">Approvals</span>
            </div>

            <!-- STAT MINI-CARDS -->
            <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:24px;">

                <div class="card green" style="padding:18px 20px;">
                    <div style="display:flex; align-items:center; justify-content:space-between;">
                        <div>
                            <div class="card-label">Approved</div>
                            <h1 style="font-size:26px;"><?php echo $totalApproved; ?></h1>
                        </div>
                        <div class="card-icon" style="background:var(--success-bg); margin:0;">
                            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="var(--success)" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="card orange" style="padding:18px 20px;">
                    <div style="display:flex; align-items:center; justify-content:space-between;">
                        <div>
                            <div class="card-label">Pending</div>
                            <h1 style="font-size:26px;"><?php echo $totalPending; ?></h1>
                        </div>
                        <div class="card-icon" style="background:var(--warning-bg); margin:0;">
                            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="var(--warning)" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="card red" style="padding:18px 20px;">
                    <div style="display:flex; align-items:center; justify-content:space-between;">
                        <div>
                            <div class="card-label">Rejected</div>
                            <h1 style="font-size:26px;"><?php echo $totalRejected; ?></h1>
                        </div>
                        <div class="card-icon" style="background:var(--danger-bg); margin:0;">
                            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="var(--danger)" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

            </div>

            <!-- TABLE SECTION -->
            <div class="section">

                <div class="table-header">
                    <div class="table-header-left">
                        <span class="section-title">All Approvals</span>
                        <span class="badge badge-info" style="margin-left:8px;">
                            <?php echo $rowCount; ?> total
                        </span>
                        <?php if ($totalPending > 0): ?>
                        <span class="badge badge-pending" style="margin-left:4px;">
                            <?php echo $totalPending; ?> awaiting action
                        </span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="table-wrapper">
                    <table class="vendor-table">
                        <thead>
                            <tr>
                                <th>#ID</th>
                                <th>RFQ</th>
                                <th>Vendor</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Remarks</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>

                        <?php if ($rowCount > 0):
                            while ($row = mysqli_fetch_assoc($result)):
                                $status = $row['status'];
                        ?>
                            <tr>

                                <td style="color:var(--gray-400); font-size:13px;">
                                    #<?php echo htmlspecialchars($row['id']); ?>
                                </td>

                                <td>
                                    <strong style="color:var(--gray-800);">
                                        <?php echo htmlspecialchars($row['rfq_title'] ?? '—'); ?>
                                    </strong>
                                </td>

                                <td>
                                    <span class="badge badge-info" style="font-size:11.5px;">
                                        <?php echo htmlspecialchars($row['vendor_name'] ?? '—'); ?>
                                    </span>
                                </td>

                                <td style="font-weight:600; color:var(--gray-700); font-size:13.5px;">
                                    ₹ <?php echo number_format($row['price'], 2); ?>
                                </td>

                                <td>
                                    <?php if ($status === 'Approved'): ?>
                                        <span class="badge badge-approved">Approved</span>
                                    <?php elseif ($status === 'Rejected'): ?>
                                        <span class="badge badge-rejected">Rejected</span>
                                    <?php else: ?>
                                        <span class="badge badge-pending">Pending</span>
                                    <?php endif; ?>
                                </td>

                                <td style="color:var(--gray-600); font-size:13px; max-width:200px;">
                                    <?php echo htmlspecialchars($row['remarks'] ?? '—'); ?>
                                </td>

                                <td>
                                    <?php if ($status === 'Pending'): ?>
                                    <div class="btn-group">
                                        <a href="approve.php?id=<?php echo $row['id']; ?>"
                                           class="btn-edit btn-sm"
                                           onclick="return confirm('Approve this quotation?')">
                                            <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Approve
                                        </a>
                                        <a href="reject.php?id=<?php echo $row['id']; ?>"
                                           class="btn-delete btn-sm"
                                           onclick="return confirm('Reject this quotation?')">
                                            <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            Reject
                                        </a>
                                    </div>
                                    <?php else: ?>
                                        <span style="font-size:12px; color:var(--gray-400);">No action</span>
                                    <?php endif; ?>
                                </td>

                            </tr>
                        <?php
                            endwhile;
                        else: ?>
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <h3>No approvals found</h3>
                                        <p>There are no quotations pending approval at this time.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>

                        </tbody>
                    </table>
                </div>

                <!-- Footer -->
                <div style="padding:12px 22px; border-top:1px solid var(--gray-100); display:flex; align-items:center; justify-content:space-between; font-size:13px; color:var(--gray-500);">
                    <span>
                        Showing <strong style="color:var(--navy);"><?php echo $rowCount; ?></strong>
                        record<?php echo $rowCount !== 1 ? 's' : ''; ?>
                    </span>
                    <span style="font-size:12px;">VendorBridge ERP &copy; <?php echo date('Y'); ?></span>
                </div>

            </div><!-- /.section -->

        </div><!-- /.page-body -->
    </div><!-- /.main-content -->

</div><!-- /.dashboard-container -->

</body>
</html>