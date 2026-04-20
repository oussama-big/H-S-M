<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class NotificationService
{
    // =============================
    // NOTIFICATION MANAGEMENT
    // =============================

    public function createNotification(array $data)
    {
        return Notification::create([
            'user_id' => $data['user_id'],
            'content' => $data['content'],
            'date' => now(),
        ]);
    }

    public function getNotificationById($notificationId)
    {
        $notification = Notification::find($notificationId);
        
        if (!$notification) {
            throw new ModelNotFoundException('Notification not found');
        }

        return $notification;
    }

    public function getAllNotifications()
    {
        return Notification::with('user')->get();
    }

    public function getUserNotifications($userId)
    {
        return Notification::where('user_id', $userId)
            ->orderBy('date', 'desc')
            ->get();
    }

    public function getUnreadNotifications($userId)
    {
        return Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->orderBy('date', 'desc')
            ->get();
    }

    public function markAsRead($notificationId)
    {
        $notification = Notification::find($notificationId);
        
        if (!$notification) {
            throw new ModelNotFoundException('Notification not found');
        }

        $notification->update(['is_read' => true]);
        return $notification;
    }

    public function markAllAsRead($userId)
    {
        return Notification::where('user_id', $userId)
            ->update(['is_read' => true]);
    }

    public function getRecentNotifications($userId, $days = 7)
    {
        return Notification::where('user_id', $userId)
            ->where('date', '>=', now()->subDays($days))
            ->orderBy('date', 'desc')
            ->get();
    }

    public function sendAppointmentNotification($userId, $appointmentDetails)
    {
        return Notification::create([
            'user_id' => $userId,
            'content' => 'Appointment scheduled: ' . $appointmentDetails,
            'date' => now(),
        ]);
    }

    public function sendConsultationNotification($userId, $consultationDetails)
    {
        return Notification::create([
            'user_id' => $userId,
            'content' => 'Consultation completed: ' . $consultationDetails,
            'date' => now(),
        ]);
    }

    public function deleteNotification($notificationId)
    {
        $notification = Notification::find($notificationId);
        
        if (!$notification) {
            throw new ModelNotFoundException('Notification not found');
        }

        return $notification->delete();
    }

    public function clearUserNotifications($userId)
    {
        return Notification::where('user_id', $userId)->delete();
    }

    public function getNotificationStats($userId)
    {
        return [
            'total' => Notification::where('user_id', $userId)->count(),
            'unread' => Notification::where('user_id', $userId)->where('is_read', false)->count(),
            'read' => Notification::where('user_id', $userId)->where('is_read', true)->count(),
        ];
    }
}
