<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class NotificationController extends Controller
{
    private NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Create a new notification
     * POST /notifications
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'user_id' => 'required|exists:users,id',
                'content' => 'required|string|max:1000',
            ]);

            $notification = $this->notificationService->createNotification($data);

            return response()->json([
                'message' => 'Notification created successfully',
                'data' => [
                    'id' => $notification->id,
                    'user_id' => $notification->user_id,
                    'content' => $notification->content,
                    'date' => $notification->date,
                ]
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to create notification',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get all notifications
     * GET /notifications
     */
    public function index()
    {
        try {
            $notifications = $this->notificationService->getAllNotifications();

            return response()->json([
                'message' => 'Notifications retrieved successfully',
                'count' => $notifications->count(),
                'data' => $notifications
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve notifications',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get notification by ID
     * GET /notifications/{id}
     */
    public function show($id)
    {
        try {
            $notification = $this->notificationService->getNotificationById($id);

            return response()->json([
                'message' => 'Notification retrieved successfully',
                'data' => $notification
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Notification not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete notification
     * DELETE /notifications/{id}
     */
    public function destroy($id)
    {
        try {
            $this->notificationService->deleteNotification($id);

            return response()->json([
                'message' => 'Notification deleted successfully'
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Notification not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get user's notifications
     * GET /notifications/user/{user_id}
     */
    public function getUserNotifications($userId)
    {
        try {
            $notifications = $this->notificationService->getUserNotifications($userId);

            return response()->json([
                'message' => 'User notifications retrieved',
                'count' => $notifications->count(),
                'data' => $notifications
            ], 200);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get unread notifications for user
     * GET /notifications/user/{user_id}/unread
     */
    public function getUnread($userId)
    {
        try {
            $notifications = $this->notificationService->getUnreadNotifications($userId);

            return response()->json([
                'message' => 'Unread notifications retrieved',
                'count' => $notifications->count(),
                'data' => $notifications
            ], 200);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Mark notification as read
     * PUT /notifications/{id}/read
     */
    public function markAsRead($id)
    {
        try {
            $notification = $this->notificationService->markAsRead($id);

            return response()->json([
                'message' => 'Notification marked as read',
                'data' => $notification
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Notification not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Mark all notifications as read for user
     * PUT /notifications/user/{user_id}/read-all
     */
    public function markAllAsRead($userId)
    {
        try {
            $this->notificationService->markAllAsRead($userId);

            return response()->json([
                'message' => 'All notifications marked as read'
            ], 200);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get notification statistics for user
     * GET /notifications/user/{user_id}/stats
     */
    public function getStats($userId)
    {
        try {
            $stats = $this->notificationService->getNotificationStats($userId);

            return response()->json([
                'message' => 'Notification stats retrieved',
                'data' => $stats
            ], 200);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Clear all notifications for user
     * DELETE /notifications/user/{user_id}/clear
     */
    public function clearUserNotifications($userId)
    {
        try {
            $this->notificationService->clearUserNotifications($userId);

            return response()->json([
                'message' => 'All notifications cleared for user'
            ], 200);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
