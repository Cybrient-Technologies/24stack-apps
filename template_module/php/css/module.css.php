<?php
// Stylesheet for the template module
header("Content-type: text/css; charset: UTF-8");
require_once __DIR__ . '/../config.php';
?>
body {
    background: #f8f9fa;
}
.header {
    background: #e9ecef;
}
.container-fluid {
    padding: 0;
}
.pane {
    height: calc(100vh - 120px); /* Adjust for header and footer */
    overflow-y: auto;
    border-right: 1px solid #dee2e6;
    padding: 15px;
}
.single-pane {
    height: calc(100vh - 120px);
    padding: 15px;
}
.list-group-item {
    cursor: pointer;
    border-radius: 0;
}
.list-group-item:hover {
    background-color: #e9ecef;
}
.list-group-item.active {
    background-color: #007bff;
    color: #fff;
}
.card {
    border-radius: 5px;
}
.pagination {
    margin-top: 15px;
}
.form-control, .btn {
    border-radius: 3px;
}
.btn-sm {
    padding: 0.25rem 0.5rem;
}
.badge {
    border-radius: 3px;
}

/**
 * Developer Notes:
 * - Styles dynamic panes to match 24stack apps.
 * - Customize for your app’s look and feel.
 * - Ensure Bootstrap compatibility.
 */
?>