<?php
session_start();
require_once 'db.php';

$adminName = "System Admin";
$adminRole = "admin";

if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT full_name, role FROM users WHERE id=?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $u = $res->fetch_assoc();
        $adminName = $u['full_name'];
        $adminRole = $u['role'];
    }
}

// fetch all users
$usersResult = $conn->query("SELECT id, full_name, email, role FROM users ORDER BY id ASC");
$users = [];
while ($row = $usersResult->fetch_assoc()) {
    $users[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>IslandLink | User Management</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800">

<!-- TOPBAR -->
<header class="h-16 bg-white border-b flex items-center justify-between px-6">
    <div class="flex items-center gap-3">
        <div class="w-8 h-8 bg-blue-600 text-white rounded-lg flex items-center justify-center font-bold">I</div>
        <span class="font-bold text-lg">IslandLink</span>
    </div>
    <div class="flex items-center gap-6">
        <div class="relative">🔔</div>
        <div class="text-right">
            <p class="text-sm font-medium"><?= htmlspecialchars($adminName) ?></p>
            <p class="text-xs text-slate-500"><?= htmlspecialchars($adminRole) ?></p>
        </div>
    </div>
</header>

<div class="flex min-h-[calc(100vh-64px)]">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-white border-r p-4 space-y-2">
        <a class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-blue-50 text-blue-600 font-medium" href="#">Dashboard</a>
        <a class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-100" href="#">Analytics</a>
        <a class="flex items-center gap-3 px-3 py-2 rounded-lg bg-blue-50 text-blue-600 font-medium" href="#">User Management</a>
        <a class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-100" href="../php/reports.php">Reports</a>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 p-8">

        <h1 class="text-2xl font-bold mb-6">User Management</h1>

        <div class="overflow-x-auto bg-white border rounded-xl shadow">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Role</th>
                        
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($user['full_name']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($user['email']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($user['role']) ?></td>
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="edit_user.php?id=<?= $user['id'] ?>" class="text-blue-600 hover:text-blue-900 mr-4">Edit</a>
                            <a href="delete_user.php?id=<?= $user['id'] ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($users)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-slate-500">No users found</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>
</div>

</body>
</html>
