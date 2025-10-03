<?php
/**
 * Helper Functions
 * PHP 7.2 compatible
 */

/**
 * Escape HTML
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Format number with commas
 */
function formatNumber($number) {
    return number_format($number);
}

/**
 * Format percentage
 */
function formatPercent($number, $decimals = 1) {
    return number_format($number, $decimals) . '%';
}

/**
 * Format date
 */
function formatDate($date, $format = 'M j, Y') {
    if (is_string($date)) {
        $date = new DateTime($date);
    }
    return $date->format($format);
}

/**
 * Format datetime
 */
function formatDateTime($datetime) {
    if (is_string($datetime)) {
        $datetime = new DateTime($datetime);
    }
    return $datetime->format('M j, Y g:i A');
}

/**
 * Time ago helper
 */
function timeAgo($datetime) {
    $time = is_string($datetime) ? strtotime($datetime) : $datetime->getTimestamp();
    $diff = time() - $time;
    
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    
    return formatDate($datetime);
}

/**
 * Truncate string
 */
function truncate($string, $length = 50) {
    if (strlen($string) <= $length) {
        return $string;
    }
    return substr($string, 0, $length - 3) . '...';
}

/**
 * Get domain from URL
 */
function getDomain($url) {
    $parsed = parse_url($url);
    return $parsed['host'] ?? 'Unknown';
}

/**
 * Format change indicator
 */
function changeIndicator($change) {
    if ($change > 0) {
        return '<span class="text-green-600">+' . number_format($change, 1) . '%</span>';
    } elseif ($change < 0) {
        return '<span class="text-red-600">' . number_format($change, 1) . '%</span>';
    } else {
        return '<span class="text-gray-600">0%</span>';
    }
}

/**
 * JSON response helper
 */
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Redirect helper
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

