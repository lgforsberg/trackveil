<?php
/**
 * Account Settings Page
 */

require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/db.php';
require_once __DIR__ . '/../../src/helpers.php';

requireLogin();

$user = currentUser();
$accountId = currentAccountId();

// Get account details
$account = queryOne("
    SELECT id, name, created_at
    FROM accounts
    WHERE id = ?
", [$accountId]);

// Get all users in this account
$users = queryAll("
    SELECT id, email, name, created_at, last_login_at
    FROM users
    WHERE account_id = ?
    ORDER BY created_at
", [$accountId]);

// Get site count
$siteCount = queryOne("
    SELECT COUNT(*) as count
    FROM sites
    WHERE account_id = ?
", [$accountId])['count'];

$pageTitle = 'Account';
require __DIR__ . '/../../templates/header.php';
require __DIR__ . '/../../templates/nav.php';
?>

<main class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 max-w-4xl">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Account Settings</h1>
    
    <!-- Account Information -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Account Information</h2>
        
        <div class="space-y-3">
            <div class="flex justify-between py-2 border-b border-gray-100">
                <span class="text-sm text-gray-600">Account Name:</span>
                <span class="text-sm font-semibold text-gray-900"><?php echo e($account['name']); ?></span>
            </div>
            
            <div class="flex justify-between py-2 border-b border-gray-100">
                <span class="text-sm text-gray-600">Account ID:</span>
                <code class="text-xs font-mono text-gray-700"><?php echo e($account['id']); ?></code>
            </div>
            
            <div class="flex justify-between py-2 border-b border-gray-100">
                <span class="text-sm text-gray-600">Total Sites:</span>
                <span class="text-sm font-semibold text-gray-900"><?php echo $siteCount; ?></span>
            </div>
            
            <div class="flex justify-between py-2">
                <span class="text-sm text-gray-600">Created:</span>
                <span class="text-sm text-gray-700"><?php echo formatDate($account['created_at']); ?></span>
            </div>
        </div>
    </div>
    
    <!-- Users -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Team Members</h2>
        
        <div class="space-y-3">
            <?php foreach ($users as $u): ?>
                <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
                    <div class="flex-1">
                        <div class="font-medium text-gray-900">
                            <?php echo e($u['name'] ?: 'No name'); ?>
                            <?php if ($u['id'] === $user['id']): ?>
                                <span class="ml-2 text-xs bg-sky-100 text-sky-700 px-2 py-1 rounded">You</span>
                            <?php endif; ?>
                        </div>
                        <div class="text-sm text-gray-600"><?php echo e($u['email']); ?></div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-gray-500">Joined: <?php echo formatDate($u['created_at'], 'M Y'); ?></div>
                        <?php if ($u['last_login_at']): ?>
                            <div class="text-xs text-gray-500">Last login: <?php echo timeAgo($u['last_login_at']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="mt-6 pt-6 border-t border-gray-200">
            <p class="text-sm text-gray-600">
                <strong>Phase 2:</strong> Invite team members, manage permissions, and more.
            </p>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../../templates/footer.php'; ?>

