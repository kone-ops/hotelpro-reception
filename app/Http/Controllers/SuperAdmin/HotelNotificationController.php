<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Services\ClientNotificationService;
use Illuminate\Http\Request;

class HotelNotificationController extends Controller
{
    public function __construct(
        protected ClientNotificationService $notificationService
    ) {}

    /**
     * Liste des hôtels avec accès à la configuration des notifications client.
     */
    public function index()
    {
        $hotels = Hotel::orderBy('name')->get(['id', 'name', 'logo', 'city', 'country']);
        return view('super.hotels.notifications-index', compact('hotels'));
    }

    /**
     * Afficher la page de configuration des notifications client (email, SMS, WhatsApp) pour un hôtel.
     */
    public function show(Hotel $hotel)
    {
        $config = $hotel->getNotificationConfig();
        $placeholders = ClientNotificationService::placeholderList();
        return view('super.hotels.notifications', compact('hotel', 'config', 'placeholders'));
    }

    /**
     * Enregistrer la configuration des notifications client.
     */
    public function update(Request $request, Hotel $hotel)
    {
        $request->validate([
            'notification_settings' => 'nullable|array',
            'notification_settings.email' => 'nullable|array',
            'notification_settings.email.enabled' => 'nullable|boolean',
            'notification_settings.email.from_name' => 'nullable|string|max:255',
            'notification_settings.sms' => 'nullable|array',
            'notification_settings.sms.enabled' => 'nullable|boolean',
            'notification_settings.sms.api_key' => 'nullable|string|max:500',
            'notification_settings.sms.sender' => 'nullable|string|max:100',
            'notification_settings.whatsapp' => 'nullable|array',
            'notification_settings.whatsapp.enabled' => 'nullable|boolean',
            'notification_settings.whatsapp.api_key' => 'nullable|string|max:500',
            'notification_settings.whatsapp.phone_number_id' => 'nullable|string|max:255',
            'notification_settings.whatsapp.sender_number' => 'nullable|string|max:50',
            'notification_settings.whatsapp.sender_name' => 'nullable|string|max:255',
        ]);

        $settings = $request->input('notification_settings', []);
        $current = $hotel->getNotificationConfig();

        // Email
        $merged = [
            'email' => array_merge($current['email'], $settings['email'] ?? []),
            'sms' => array_merge($current['sms'], $settings['sms'] ?? []),
            'whatsapp' => array_merge($current['whatsapp'], $settings['whatsapp'] ?? []),
        ];
        $merged['email']['enabled'] = (bool) ($request->input('notification_settings.email.enabled') ?? false);
        $merged['email']['from_name'] = $request->input('notification_settings.email.from_name') ?? ($current['email']['from_name'] ?? null);

        $emailEvents = ['created', 'validated', 'rejected', 'check_in', 'check_out'];
        foreach ($emailEvents as $event) {
            $merged['email']['templates'][$event] = array_merge(
                $current['email']['templates'][$event] ?? [],
                $settings['email']['templates'][$event] ?? []
            );
            $merged['email']['templates'][$event]['use_system_default'] = (bool) ($request->input("notification_settings.email.templates.{$event}.use_system_default") ?? false);
            $merged['email']['templates'][$event]['subject'] = $request->input("notification_settings.email.templates.{$event}.subject") ?? ($merged['email']['templates'][$event]['subject'] ?? '');
            $merged['email']['templates'][$event]['body_html'] = $request->input("notification_settings.email.templates.{$event}.body_html") ?? ($merged['email']['templates'][$event]['body_html'] ?? '');
        }

        // SMS : champs API et templates enregistrés explicitement
        $merged['sms']['enabled'] = (bool) ($request->input('notification_settings.sms.enabled') ?? false);
        $merged['sms']['api_key'] = $request->input('notification_settings.sms.api_key') ?? ($current['sms']['api_key'] ?? '');
        $merged['sms']['sender'] = $request->input('notification_settings.sms.sender') ?? ($current['sms']['sender'] ?? '');
        foreach (['created', 'validated', 'rejected', 'check_in', 'check_out'] as $event) {
            $merged['sms']['templates'][$event] = $request->input("notification_settings.sms.templates.{$event}") ?? ($current['sms']['templates'][$event] ?? '');
        }

        // WhatsApp : champs API et templates enregistrés explicitement
        $merged['whatsapp']['enabled'] = (bool) ($request->input('notification_settings.whatsapp.enabled') ?? false);
        $merged['whatsapp']['api_key'] = $request->input('notification_settings.whatsapp.api_key') ?? ($current['whatsapp']['api_key'] ?? '');
        $merged['whatsapp']['phone_number_id'] = $request->input('notification_settings.whatsapp.phone_number_id') ?? ($current['whatsapp']['phone_number_id'] ?? '');
        $merged['whatsapp']['sender_number'] = $request->input('notification_settings.whatsapp.sender_number') ?? ($current['whatsapp']['sender_number'] ?? '');
        $merged['whatsapp']['sender_name'] = $request->input('notification_settings.whatsapp.sender_name') ?? ($current['whatsapp']['sender_name'] ?? '');
        foreach (['created', 'validated', 'rejected', 'check_in', 'check_out'] as $event) {
            $merged['whatsapp']['templates'][$event] = $request->input("notification_settings.whatsapp.templates.{$event}") ?? ($current['whatsapp']['templates'][$event] ?? '');
        }

        $hotel->notification_settings = $merged;
        $hotel->save();

        return redirect()->route('super.hotels.notifications.show', $hotel)
            ->with('success', 'Configuration des notifications client enregistrée.');
    }
}
