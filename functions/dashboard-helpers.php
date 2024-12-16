<?php

function getActivityIcon($actionType) {
    $icons = [
        'Profile_Update' => '<svg class="w-5 h-5 text-idafu-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
        </svg>',
        'Status_Change' => '<svg class="w-5 h-5 text-idafu-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>',
        'default' => '<svg class="w-5 h-5 text-idafu-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>'
    ];

    return $icons[$actionType] ?? $icons['default'];
}

function formatActivityMessage($activity) {
    $actionMessages = [
        // User Management
        'Profile_Update' => 'updated their profile',
        'Status_Change' => 'status was changed',
        'Account_Deletion' => 'account was deleted',
        
        // Consultant Related
        'Consultant_Application' => 'applied to be a consultant',
        'Certification_Approval' => 'certification was approved',
        'Certification_Rejection' => 'certification was rejected',
        'Specialization_Approval' => 'specialization was approved',
        'Specialization_Rejection' => 'specialization was rejected',
        'Consultant_Activation' => 'was activated as consultant',
        'Consultant_Rejection' => 'was rejected as consultant',
        'Consultant_Deactivation' => 'was deactivated as consultant',
        
        // Client Related
        'Client_Ban' => 'was banned',
        'Client_Unban' => 'was unbanned',
        'Client_Profile_Update' => 'profile was updated',
        'Client_Account_Deletion' => 'account was deleted',
        
        // Booking Related
        'Booking_Creation' => 'created a new booking',
        'Booking_Cancellation' => 'booking was cancelled',
        'Booking_Completion' => 'completed a session',
        'Booking_Refund' => 'booking was refunded',
        
        // Rating Related
        'Rating_Submission' => 'submitted a rating',
        
        // System Related
        'System_Update' => 'system settings were updated',
        'Price_Update' => 'pricing was updated'
    ];

    $action = $actionMessages[$activity['action_type']] ?? 'performed an action';
    $userName = htmlspecialchars($activity['affected_user_name'] ?? 'A user');
    
    return "{$userName} {$action}";
}

function timeAgo($timestamp) {
    $datetime = new DateTime($timestamp);
    $now = new DateTime();
    $interval = $now->diff($datetime);
    
    if ($interval->y > 0) {
        return $interval->y . ' year' . ($interval->y > 1 ? 's' : '') . ' ago';
    }
    if ($interval->m > 0) {
        return $interval->m . ' month' . ($interval->m > 1 ? 's' : '') . ' ago';
    }
    if ($interval->d > 0) {
        return $interval->d . ' day' . ($interval->d > 1 ? 's' : '') . ' ago';
    }
    if ($interval->h > 0) {
        return $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
    }
    if ($interval->i > 0) {
        return $interval->i . ' minute' . ($interval->i > 1 ? 's' : '') . ' ago';
    }
    
    return 'just now';
} 