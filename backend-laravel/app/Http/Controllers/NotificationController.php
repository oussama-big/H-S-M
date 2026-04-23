<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    private NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $userId = Auth::id();
        $notifications = $this->notificationService->getUserNotifications($userId);
        $stats = $this->notificationService->getNotificationStats($userId);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'data' => [
                    'notifications' => $notifications,
                    'stats' => $stats,
                ],
            ]);
        }

        return view('notifications.index', compact('notifications', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'contenu' => 'required|string|max:255',
        ]);

        $notification = $this->notificationService->createNotification($data);

        return response()->json([
            'success' => true,
            'data' => $notification,
        ], 201);
    }

    public function show($id)
    {
        return response()->json([
            'success' => true,
            'data' => $this->notificationService->getNotificationById($id),
        ]);
    }

    public function markAsRead($id)
    {
        try {
            $this->notificationService->markAsRead($id);

            if (request()->expectsJson() || request()->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Notification marquee comme lue.',
                ]);
            }

            return back()->with('success', 'Notification marquee comme lue.');
        } catch (Exception $e) {
            if (request()->expectsJson() || request()->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Action impossible.',
                ], 400);
            }

            return back()->with('error', 'Action impossible.');
        }
    }

    public function markAllRead()
    {
        try {
            $this->notificationService->markAllAsRead(Auth::id());

            return back()->with('success', 'Toutes les notifications sont marquees comme lues.');
        } catch (Exception $e) {
            return back()->with('error', 'Erreur lors du traitement.');
        }
    }

    public function destroy($id)
    {
        try {
            $this->notificationService->deleteNotification($id);

            if (request()->expectsJson() || request()->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Notification supprimee.',
                ]);
            }

            return back()->with('success', 'Notification supprimee.');
        } catch (Exception $e) {
            if (request()->expectsJson() || request()->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la suppression.',
                ], 400);
            }

            return back()->with('error', 'Erreur lors de la suppression.');
        }
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'contenu' => 'nullable|string|max:255',
            'is_read' => 'nullable|boolean',
        ]);

        $notification = $this->notificationService->updateNotification($id, $data);

        return response()->json([
            'success' => true,
            'data' => $notification,
        ]);
    }

    public function clearAll()
    {
        try {
            $this->notificationService->clearUserNotifications(Auth::id());

            return redirect()->route('notifications.index')->with('success', 'Boite de reception videe.');
        } catch (Exception $e) {
            return back()->with('error', 'Erreur lors du nettoyage.');
        }
    }

    public function getUserNotifications($userId)
    {
        return response()->json([
            'success' => true,
            'data' => $this->notificationService->getUserNotifications($userId),
        ]);
    }

    public function getUnread($userId)
    {
        return response()->json([
            'success' => true,
            'data' => $this->notificationService->getUnreadNotifications($userId),
        ]);
    }

    public function getStats($userId)
    {
        return response()->json([
            'success' => true,
            'data' => $this->notificationService->getNotificationStats($userId),
        ]);
    }

    public function markAllAsRead($userId)
    {
        $this->notificationService->markAllAsRead($userId);

        return response()->json([
            'success' => true,
            'message' => 'Toutes les notifications sont marquees comme lues.',
        ]);
    }

    public function clearUserNotifications($userId)
    {
        $this->notificationService->clearUserNotifications($userId);

        return response()->json([
            'success' => true,
            'message' => 'Notifications utilisateur supprimees.',
        ]);
    }
}
