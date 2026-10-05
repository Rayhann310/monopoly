<?php
$dashboard = file_get_contents('app/views/admin/dashboard.php');

function extractTab($name, $dashboard) {
    $start = "        <!-- === TAB: " . strtoupper($name) . " === -->";
    $end = "        <!-- === TAB: ";
    $p1 = strpos($dashboard, $start);
    if ($p1 === false) {
        $end = "</main>";
        $p1 = strpos($dashboard, "        <!-- === TAB: " . strtoupper($name) . " === -->");
        if ($p1 === false) return "";
    }
    $p2 = strpos($dashboard, $end, $p1 + 10);
    if ($p2 === false) $p2 = strpos($dashboard, "</main>", $p1);
    
    $content = substr($dashboard, $p1, $p2 - $p1);
    
    // Remove the wrapper <div id="tab-..." class="tab-content">
    $content = preg_replace('/<div id="tab-[^"]+" class="tab-content[^"]*">\s*/', '', $content, 1);
    // Remove the closing div of the wrapper
    $content = preg_replace('/<\/div>\s*$/', '', $content);
    return trim($content);
}

$overview = extractTab('OVERVIEW', $dashboard);
$settings = extractTab('SETTINGS', $dashboard);
$sessions = extractTab('SESSIONS', $dashboard);
$database = extractTab('DATABASE', $dashboard);
$password = extractTab('PASSWORD', $dashboard);

$headerInc = "<?php include '../app/views/templates/admin_header.php'; ?>\n<?php include '../app/views/templates/admin_sidebar.php'; ?>\n\n";
$footerInc = "\n\n<?php include '../app/views/templates/admin_footer.php'; ?>\n";

file_put_contents('app/views/admin/dashboard.php', $headerInc . $overview . $footerInc);
file_put_contents('app/views/admin/settings.php', $headerInc . $settings . $footerInc);
file_put_contents('app/views/admin/sessions.php', $headerInc . $sessions . $footerInc);
file_put_contents('app/views/admin/database.php', $headerInc . $database . $footerInc);
file_put_contents('app/views/admin/password.php', $headerInc . $password . $footerInc);

// Update cards.php to use new layout
$cards = file_get_contents('app/views/admin/cards.php');
// Remove existing layout
$cards = preg_replace('/<!DOCTYPE html>.*<main class="ml-64 flex-1 p-8">/s', '', $cards);
$cards = preg_replace('/<\/main>.*<\/html>/s', '', $cards);
file_put_contents('app/views/admin/cards.php', $headerInc . trim($cards) . $footerInc);
echo "Views refactored";
