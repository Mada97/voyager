<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Notification;

class NotificationController extends Controller
{
    // show notifications for the logged in user.
    public function show()
    {
        $user = Auth::user();
        return response()->json(['notifications' => $user->notifications]);
    }

    public function markAsRead()
    {
        $user = Auth::user();
        $user->unreadNotifications()->update(['read_at' => now()]);
        return response()->json(['success' => true]);
    }
}
