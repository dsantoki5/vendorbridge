<?php

session_start();

if(!isset($_SESSION['user_id']))
{
    header("Location: login.php");
    exit();
}

include 'config/db.php';

$vendors   = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM vendors"));
$rfqs      = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM rfqs"));
$approvals = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM approvals WHERE status='Pending'"));
$invoices  = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM invoices"));

$notifications = mysqli_num_rows(
mysqli_query(
$conn,
"SELECT * FROM notifications"
));

$role = $_SESSION['role'];
$name = $_SESSION['name'];

// Get initials for avatar
$parts    = explode(' ', $name);
$initials = strtoupper(substr($parts[0],0,1) . (isset($parts[1]) ? substr($parts[1],0,1) : ''));

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — VendorBridge</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="dashboard-container">

    <!-- ===================== SIDEBAR ===================== -->
    <aside class="sidebar">

        <!-- Brand -->
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

        <!-- Navigation -->
        <nav class="sidebar-nav">

            <div class="sidebar-section-label">Main Menu</div>
            <ul>
                <li>
                    <a href="dashboard.php" class="active">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="vendor/vendor_list.php">
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
                    <a href="rfq/rfq_list.php">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        RFQs
                    </a>
                </li>
                <li>
                    <a href="quotation/quotation_list.php">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/>
                        </svg>
                        Quotations
                    </a>
                </li>
                <li>
                    <a href="quotation/compare_quotation.php">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Compare Quotations
                    </a>
                </li>
                <li>
                    <a href="approval/approval_list.php">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Approvals
                        <?php if($approvals > 0): ?>
                        <span class="sidebar-badge"><?php echo $approvals; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li>
                    <a href="purchase_order/po_list.php">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        Purchase Orders
                    </a>
                </li>
                <li>
                    <a href="invoice/invoice_list.php">
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
                    <a href="reports/analytics.php">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                        </svg>
                        Analytics
                    </a>
                </li>
                <li>
                    <a href="logs/activity_log.php">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Activity Logs
                    </a>
                </li>
            </ul>

        </nav>

        <!-- Sidebar Footer / User -->
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
                    <a href="logout.php" style="color:rgba(255,100,100,0.80);">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Logout
                    </a>
                </li>
            </ul>
        </div>

    </aside>

    <!-- ===================== MAIN CONTENT ===================== -->
    <div class="main-content">

        <!-- TOPBAR -->
        <div class="topbar">
            <div class="topbar-title">
                👋 Welcome back, <?php echo htmlspecialchars($name); ?>
            </div>

            <!-- Search -->
            <div class="topbar-search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/>
                </svg>
                <input type="text" placeholder="Search vendors, RFQs...">
            </div>

            <!-- Notification Bell -->
            <div class="topbar-icon-btn">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <?php if($approvals > 0): ?>
                <span class="topbar-notif-dot"></span>
                <?php endif; ?>
            </div>

            <!-- Role Badge -->
            <span class="badge badge-info"><?php echo htmlspecialchars($role); ?></span>
        </div>

        <!-- PAGE BODY -->
        <div class="page-body">

            <!-- BREADCRUMB -->
            <div class="breadcrumb">
                <span class="current">Dashboard</span>
            </div>

            <!-- ---- STAT CARDS ---- -->
            <div class="cards">

                <div class="card blue">
                    <div class="card-icon" style="background:#eff4ff;">
                        <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#2563eb" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div class="card-label">Total Vendors</div>
                    <h1><?php echo $vendors; ?></h1>
                    <span class="card-change up">↑ Registered</span>
                </div>

                <div class="card green">
                    <div class="card-icon" style="background:#f0fdf4;">
                        <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="card-label">Active RFQs</div>
                    <h1><?php echo $rfqs; ?></h1>
                    <span class="card-change up">↑ Active</span>
                </div>

                <div class="card orange">
                    <div class="card-icon" style="background:#fffbeb;">
                        <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#d97706" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="card-label">Pending Approvals</div>
                    <h1><?php echo $approvals; ?></h1>
                    <?php if($approvals > 0): ?>
                    <span class="card-change down">⚠ Needs Action</span>
                    <?php else: ?>
                    <span class="card-change up">✓ All Clear</span>
                    <?php endif; ?>
                </div>

                <div class="card blue">
                    <div class="card-icon" style="background:#eff4ff;">
                        <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#2563eb" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div class="card-label">Total Invoices</div>
                    <h1><?php echo $invoices; ?></h1>
                    <span class="card-change up">↑ Generated</span>
                </div>
                        <div class="card">
                        <h3>Notifications</h3>
                        <h1><?php echo $notifications; ?></h1>
                        </div>


            </div>

                        

            <!-- ---- QUICK ACTIONS ---- -->
            <div class="section">
                <div class="section-header">
                    <span class="section-title">⚡ Quick Actions</span>
                </div>
                <div class="section-body">
                    <div class="btn-group">
                        <a href="vendor/add_vendor.php" class="btn-add">
                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add Vendor
                        </a>
                        <a href="rfq/create_rfq.php" class="btn-action">
                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                            Create RFQ
                        </a>
                        <a href="quotation/submit_quotation.php" class="btn-outline">
                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            Submit Quotation
                        </a>
                        <a href="purchase_order/create_po.php" class="btn-outline">
                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                            Create PO
                        </a>
                        <a href="invoice/generate_invoice.php" class="btn-outline">
                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Generate Invoice
                        </a>
                    </div>
                </div>
            </div>

            <!-- ---- PENDING APPROVALS TABLE ---- -->
            <div class="section">
                <div class="section-header">
                    <span class="section-title">🕐 Pending Approvals</span>
                    <a href="approval/approval_list.php" class="btn-edit btn-sm">View All</a>
                </div>

                <div class="table-wrapper">
                    <table class="vendor-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Approval ID</th>
                                <th>Quotation ID</th>
                                <th>Requested By</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php

                        $check = mysqli_query($conn, "SHOW TABLES LIKE 'approvals'");

                        if(mysqli_num_rows($check) > 0)
                        {
                                $result = mysqli_query(
                        $conn,
                        "SELECT * FROM approvals
                        WHERE status = 'Pending'
                        ORDER BY id DESC
                        LIMIT 5"
                    );

                            if(mysqli_num_rows($result) > 0)
                            {
                                $i = 1;
                                while($row = mysqli_fetch_assoc($result))
                                {
                        ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td><strong>#<?php echo $row['id']; ?></strong></td>
                                <td><?php echo isset($row['quotation_id']) ? '#'.$row['quotation_id'] : '—'; ?></td>
                                <td><?php echo htmlspecialchars($row['requester'] ?? '—'); ?></td>
                                <td><span class="badge badge-pending"><?php echo $row['status']; ?></span></td>
                                <td>
                                    <a href="approval/approval_list.php?id=<?php echo $row['id']; ?>" class="btn-edit btn-sm">Review</a>
                                </td>
                            </tr>
                        <?php
                                }
                            }
                            else
                            {
                        ?>
                            <tr>
                                <td colspan="6" style="text-align:center; padding:40px; color:#94a3b8;">
                                    ✅ No pending approvals right now
                                </td>
                            </tr>
                        <?php
                            }
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ---- RECENT ACTIVITY ---- -->
            <div class="section">
                <div class="section-header">
                    <span class="section-title">📋 Recent Activity</span>
                    <a href="logs/activity_log.php" class="btn-edit btn-sm">View All</a>
                </div>
                <div class="section-body">
                    <ul class="activity-list">

                        <?php
                        // Show last 5 logs if table exists
                        $logCheck = mysqli_query($conn, "SHOW TABLES LIKE 'activity_logs'");
                        if(mysqli_num_rows($logCheck) > 0)
                        {
                            $logs = mysqli_query($conn,
                                "SELECT l.*, u.name FROM activity_logs l
                                 LEFT JOIN users u ON l.user_id = u.id
                                 ORDER BY l.created_at DESC LIMIT 5"
                            );
                            while($log = mysqli_fetch_assoc($logs))
                            {
                        ?>
                        <li class="activity-item">
                            <div class="activity-icon blue">
                                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="activity-content">
                                <p><?php echo htmlspecialchars($log['action'] ?? $log['description'] ?? 'Activity'); ?></p>
                                <time><?php echo isset($log['created_at']) ? date('d M Y, h:i A', strtotime($log['created_at'])) : ''; ?></time>
                            </div>
                        </li>
                        <?php
                            }
                        }
                        else
                        {
                        ?>
                        <li class="activity-item">
                            <div class="activity-icon green">
                                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="activity-content">
                                <p>VendorBridge system initialized successfully</p>
                                <time>Today</time>
                            </div>
                        </li>
                        <li class="activity-item">
                            <div class="activity-icon blue">
                                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div class="activity-content">
                                <p><?php echo htmlspecialchars($name); ?> logged in as <?php echo htmlspecialchars($role); ?></p>
                                <time>Just now</time>
                            </div>
                        </li>
                        <?php } ?>

                    </ul>
                </div>
            </div>

        </div>
        <!-- end page-body -->

    </div>
    <!-- end main-content -->

</div>
<!-- end dashboard-container -->

</body>
</html>
