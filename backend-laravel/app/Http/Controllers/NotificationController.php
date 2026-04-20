<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class NotificationController extends Controller
{
    private NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Affiche toutes les notifications de l'utilisateur connecté
     */
    public function index()
    {
        $userId = Auth::id();
        $notifications = $this->notificationService->getUserNotifications($userId);
        $stats = $this->notificationService->getNotificationStats($userId);

        return view('notifications.index', compact('notifications', 'stats'));
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead($id)
    {
        try {
            $this->notificationService->markAsRead($id);
            return back()->with('success', 'Notification marquée comme lue.');
        } catch (Exception $e) {
            return back()->with('error', 'Action impossible.');
        }
    }

    /**
     * Tout marquer comme lu
     */
    public function markAllRead()
    {
        try {
            $this->notificationService->markAllAsRead(Auth::id());
            return back()->with('success', 'Toutes les notifications sont marquées comme lues.');
        } catch (Exception $e) {
            return back()->with('error', 'Erreur lors du traitement.');
        }
    }

    /**
     * Supprimer une notification
     */
    public function destroy($id)
    {
        try {
            $this->notificationService->deleteNotification($id);
            return back()->with('success', 'Notification supprimée.');
        } catch (Exception $e) {
            return back()->with('error', 'Erreur lors de la suppression.');
        }
    }

    /**
     * Vider toutes les notifications
     */
    public function clearAll()
    {
        try {
            $this->notificationService->clearUserNotifications(Auth::id());
            return redirect()->route('notifications.index')->with('success', 'Boîte de réception vidée.');
        } catch (Exception $e) {
            return back()->with('error', 'Erreur lors du nettoyage.');
        }
    }
}