<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;

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
            $logs = array_slice(array_reverse($logLines), 0, 100); // Last 100 lines
        }

        return view('tim-it.audit-logs', compact('logs'));
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