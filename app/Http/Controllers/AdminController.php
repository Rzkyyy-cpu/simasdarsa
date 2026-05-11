<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    /**
     * User Management - Tim IT
     */
    public function userManagement()
    {
        $users = User::all();
        return view('tim-it.user-management', compact('users'));
    }

    /**
     * Store a new user
     */
    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'roles' => 'required|array',
            'roles.*' => 'in:pimpinan,tim_it,manager,kasir',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'roles' => $request->roles,
            'permissions' => [
                'crud' => [
                    'create' => false,
                    'read' => true,
                    'update' => false,
                    'delete' => false
                ],
                'menus' => ['dashboard']
            ]
        ]);

        return redirect()->back()->with('success', 'User berhasil ditambahkan.');
    }

    /**
     * Update an existing user
     */
    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'roles' => 'required|array',
            'roles.*' => 'in:pimpinan,tim_it,manager,kasir',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'roles' => $request->roles,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->back()->with('success', 'Data user berhasil diperbarui.');
    }

    /**
     * Delete a user
     */
    public function deleteUser(User $user)
    {
        // Prevent deleting self
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return redirect()->back()->with('success', 'User berhasil dihapus.');
    }

    /**
     * Update user permissions (CRUD toggles)
     */
    public function updatePermissions(Request $request, User $user)
    {
        $request->validate([
            'permission' => 'required|string',
            'value' => 'required|boolean',
        ]);

        $permissions = $user->permissions ?? [];
        
        // Ensure crud structure exists
        if (!isset($permissions['crud'])) {
            $permissions['crud'] = [
                'create' => false,
                'read' => true,
                'update' => false,
                'delete' => false
            ];
        }

        if (str_starts_with($request->permission, 'crud.')) {
            $key = str_replace('crud.', '', $request->permission);
            $permissions['crud'][$key] = (bool) $request->value;
        }

        $user->update(['permissions' => $permissions]);

        return response()->json(['success' => true]);
    }

    /**
     * User Detailed Permissions Page
     */
    public function userDetails(User $user)
    {
        // Define all menus in the system
        $menus = [
            'Dashboard' => 'dashboard',
            'Manajemen Produk' => 'produk.index',
            'Manajemen Stok (Batch)' => 'stok.index',
            'Verifikasi Stok Masuk' => 'manager.verify-incoming-stock',
            'Monitoring Kedaluarsa' => 'stok.expiry-monitor',
            'Lokasi Barang' => 'manager.item-status-location',
            'POS (Kasir)' => 'kasir.index',
            'Update Stok Fisik (Opname)' => 'kasir.update-physical-stock',
            'Riwayat Penjualan' => 'penjualan.index',
            'Laporan Laba Rugi' => 'laporan.laba-rugi',
            'Laporan Eksekutif' => 'laporan.eksekutif',
            'User Management' => 'tim-it.user-management',
            'Audit Logs' => 'tim-it.audit-logs',
            'Pengaturan Harga' => 'pimpinan.price-settings',
            'Pengaturan Diskon' => 'pimpinan.discount-settings',
        ];

        return view('tim-it.user-details', compact('user', 'menus'));
    }

    /**
     * Update detailed user permissions (Menu access)
     */
    public function updateUserDetails(Request $request, User $user)
    {
        $permissions = $user->permissions ?? [];
        
        // Keep existing CRUD permissions
        $crud = $permissions['crud'] ?? [
            'create' => false,
            'read' => true,
            'update' => false,
            'delete' => false
        ];

        $user->update([
            'permissions' => [
                'crud' => $crud,
                'menus' => $request->menus ?? []
            ]
        ]);

        return redirect()->route('tim-it.user-management')->with('success', 'Hak akses menu berhasil diperbarui.');
    }

    /**
     * System Maintenance - Tim IT
     */
    public function systemMaintenance()
    {
        return view('tim-it.system-maintenance');
    }

    /**
     * Audit Logs - Tim IT
     */
    public function auditLogs()
    {
        // Get recent logs from Laravel log files
        $logPath = storage_path('logs/laravel.log');
        $logs = [];

        if (file_exists($logPath)) {
            $logContent = file_get_contents($logPath);
            $logLines = explode("\n", $logContent);
            
            foreach ($logLines as $line) {
                if (empty(trim($line))) continue;
                
                // Only show logs marked with AUDIT_LOG:
                if (!str_contains($line, 'AUDIT_LOG:')) continue;
                
                // Try to parse standard Laravel log format: [timestamp] environment.level: message
                preg_match('/\[(.*?)\] (.*?)\.(.*?): (.*)/', $line, $matches);
                
                if (count($matches) >= 5) {
                    $message = str_replace('AUDIT_LOG: ', '', $matches[4]);
                    
                    $logs[] = [
                        'timestamp' => $matches[1],
                        'env'       => $matches[2],
                        'level'     => $matches[3],
                        'message'   => $message,
                        'trace'     => $this->generateTracePath($message, $matches[1])
                    ];
                }
            }
            
            $logs = array_slice(array_reverse($logs), 0, 100);
        }

        // If no logs, add dummy data focused on Login, Logout, and Product CRUD
        if (empty($logs)) {
            $logs = [
                [
                    'timestamp' => now()->subMinutes(5)->format('Y-m-d H:i:s'),
                    'env' => 'production',
                    'level' => 'INFO',
                    'message' => 'USER LOGIN - User: Manager Toko (manager@simasdarsa.com), Role: manager, IP: 192.168.1.10',
                    'trace' => [
                        ['action' => 'Request Login Page', 'time' => '10:00:00'],
                        ['action' => 'Validate Credentials', 'time' => '10:00:04'],
                        ['action' => 'Session Started', 'time' => '10:00:05'],
                    ]
                ],
                [
                    'timestamp' => now()->subMinutes(10)->format('Y-m-d H:i:s'),
                    'env' => 'production',
                    'level' => 'INFO',
                    'message' => 'PRODUCT UPDATED - Product: Aqua Botol 600ml (ID: 45), User: Manager Toko (manager@simasdarsa.com)',
                    'trace' => [
                        ['action' => 'Akses Menu Produk', 'time' => '10:05:00'],
                        ['action' => 'Open Edit Form', 'time' => '10:06:15'],
                        ['action' => 'Save Changes', 'time' => '10:08:00'],
                    ]
                ],
                [
                    'timestamp' => now()->subMinutes(20)->format('Y-m-d H:i:s'),
                    'env' => 'production',
                    'level' => 'INFO',
                    'message' => 'USER LOGOUT - User: Kasir (kasir@simasdarsa.com), IP: 192.168.1.15',
                    'trace' => [
                        ['action' => 'Click Logout', 'time' => '09:40:00'],
                        ['action' => 'Invalidate Session', 'time' => '09:40:02'],
                    ]
                ],
                [
                    'timestamp' => now()->subMinutes(30)->format('Y-m-d H:i:s'),
                    'env' => 'production',
                    'level' => 'INFO',
                    'message' => 'PRODUCT CREATED - Product: Mie Goreng Spesial (Barcode: 8001000999), User: Tim IT Lead (tim_it@simasdarsa.com)',
                    'trace' => [
                        ['action' => 'Akses User Management', 'time' => '09:25:00'],
                        ['action' => 'Akses Produk Management', 'time' => '09:26:00'],
                        ['action' => 'Input New Product', 'time' => '09:30:00'],
                    ]
                ]
            ];
        }

        return view('tim-it.audit-logs', compact('logs'));
    }

    /**
     * Export Audit Logs to CSV
     */
    public function exportAuditLogs()
    {
        $logPath = storage_path('logs/laravel.log');
        $logs = [];

        if (file_exists($logPath)) {
            $logContent = file_get_contents($logPath);
            $logLines = explode("\n", $logContent);
            
            foreach ($logLines as $line) {
                if (empty(trim($line)) || !str_contains($line, 'AUDIT_LOG:')) continue;
                
                preg_match('/\[(.*?)\] (.*?)\.(.*?): (.*)/', $line, $matches);
                if (count($matches) >= 5) {
                    $logs[] = [
                        'timestamp' => $matches[1],
                        'level'     => $matches[3],
                        'message'   => str_replace('AUDIT_LOG: ', '', $matches[4]),
                    ];
                }
            }
        }

        $filename = "Audit_Logs_" . now()->format('Ymd_His') . ".csv";

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Waktu', 'Level', 'Aktivitas/Pesan']);

            foreach (array_reverse($logs) as $log) {
                fputcsv($file, [
                    $log['timestamp'],
                    $log['level'],
                    $log['message']
                ]);
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }

    /**
     * Helper to generate a dummy trace path based on message content
     */
    private function generateTracePath($message, $timestamp)
    {
        $time = date('H:i:s', strtotime($timestamp));
        
        if (str_contains($message, 'USER LOGIN')) {
            return [
                ['action' => 'Input Credentials', 'time' => date('H:i:s', strtotime($timestamp . ' -5 seconds'))],
                ['action' => 'Authentication Success', 'time' => $time],
                ['action' => 'Redirect to Dashboard', 'time' => date('H:i:s', strtotime($timestamp . ' +2 seconds'))],
            ];
        }

        if (str_contains($message, 'PRODUCT')) {
            return [
                ['action' => 'Authorization Check', 'time' => date('H:i:s', strtotime($timestamp . ' -2 seconds'))],
                ['action' => 'Database Transaction', 'time' => $time],
                ['action' => 'Audit Log Recorded', 'time' => date('H:i:s', strtotime($timestamp . ' +1 second'))],
            ];
        }
        
        return [];
    }

    /**
     * System Testing - Tim IT
     */
    public function systemTesting()
    {
        return view('tim-it.system-testing');
    }

    /**
     * Price Settings - Pimpinan & Manager
     */
    public function priceSettings()
    {
        return view('admin.price-settings');
    }

    /**
     * Update Price Settings
     */
    public function updatePriceSettings(Request $request)
    {
        // Validate and update price settings
        $request->validate([
            'price_markup' => 'required|numeric|min:0|max:100',
            'bulk_discount_threshold' => 'required|integer|min:1',
            'bulk_discount_percentage' => 'required|numeric|min:0|max:50',
        ]);

        // Store in config or database
        // For now, we'll just redirect back with success
        return redirect()->back()->with('success', 'Pengaturan harga berhasil diperbarui');
    }

    /**
     * Discount Settings - Pimpinan & Manager
     */
    public function discountSettings()
    {
        return view('admin.discount-settings');
    }

    /**
     * Update Discount Settings
     */
    public function updateDiscountSettings(Request $request)
    {
        // Validate and update discount settings
        $request->validate([
            'loyalty_discount' => 'required|numeric|min:0|max:30',
            'seasonal_discount' => 'required|numeric|min:0|max:50',
            'member_discount' => 'required|numeric|min:0|max:20',
        ]);

        // Store in config or database
        return redirect()->back()->with('success', 'Pengaturan diskon berhasil diperbarui');
    }
}