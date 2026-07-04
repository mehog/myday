<?php

return [

    // Dashboard
    'dashboard_label' => 'Übersicht',
    'dashboard_title' => 'Übersicht',
    'pending_activation_title' => 'Einladung wartet auf Freischaltung',
    'pending_activation_body' => 'Der Link wird freigeschaltet, sobald die Zahlung bestätigt und von einem Admin freigegeben wurde. In der Zwischenzeit können Sie eine Vorschau ansehen.',
    'edit_invitation' => 'Einladung bearbeiten',
    'preview_invitation' => 'Einladung ansehen',
    'no_invitation' => 'Ihre Einladung wurde noch nicht erstellt. Kontaktieren Sie das NasDan-Team, um sie einzurichten.',
    'invitation_inactive_suffix' => '— Link noch nicht aktiv',
    'email_readonly' => 'Die E-Mail-Adresse kann hier nicht geändert werden.',

    // Resource
    'nav_my_wedding' => 'Meine Hochzeit',
    'model_label_wedding' => 'Hochzeit',

    // Wedding form — Couple section
    'section_couple' => 'Paar',
    'groom_name' => 'Name des Bräutigams',
    'bride_name' => 'Name der Braut',
    'invitation_link' => 'Einladungslink',
    'your_link' => 'Ihr Link: ',
    'wedding_datetime' => 'Hochzeitsdatum und -uhrzeit',

    // Wedding form — Design section
    'section_design' => 'Design',
    'theme' => 'Thema',
    'template' => 'Layout',
    'template_classic' => 'Klassisch',
    'template_editorial' => 'Editorial',
    'template_story' => 'Story',
    'reveal_animation' => 'Einstiegsanimation',
    'reveal_none' => 'Keine Animation',
    'reveal_envelope' => 'Umschlag',
    'reveal_wax_seal' => 'Wachssiegel',
    'reveal_curtain' => 'Vorhang',
    'reveal_polaroid' => 'Polaroid-Fall',
    'share_mode' => 'Freigabemodus',
    'hero_image' => 'Titelbild',
    'youtube_song' => 'YouTube-Lied',
    'youtube_helper' => 'Fügen Sie den YouTube-Link Ihres Liedes ein (z. B. https://www.youtube.com/watch?v=... oder https://youtu.be/...)',
    'motto' => 'Hochzeitsmotto',
    'motto_helper' => 'Ein kurzes Zitat oder eine Botschaft auf der Einladung (max. 2 Sätze).',

    // Wedding form — Location section
    'section_location' => 'Location',
    'location_name' => 'Name der Location',
    'location_address' => 'Adresse',
    'section_coordinates' => 'Koordinaten',
    'coordinates_description' => 'Optional. Nur verwenden, wenn Sie einen genauen Pin auf der Karte wünschen.',
    'latitude' => 'Breitengrad',
    'longitude' => 'Längengrad',

    // Wedding form — RSVP section
    'section_rsvp' => 'RSVP',
    'rsvp_deadline' => 'RSVP-Frist',
    'guest_message' => 'Nachricht für Gäste',
    'guest_message_helper' => 'Verwenden Sie {name} für den Namen des Gastes und {link} für den persönlichen Link.',
    'guest_message_placeholder' => "Z. B.: Liebe/r {name}, wir laden Sie herzlich zu unserer Hochzeit ein!\nIhr RSVP-Link: {link}",

    // Wedding overview widget
    'stat_guests' => 'Gäste',
    'stat_guests_desc' => 'Eingeladene Gäste insgesamt',
    'stat_guests_desc_plus_ones' => 'Eingeladene Gäste insgesamt · :count mit Begleitung',
    'stat_confirmed' => 'Bestätigt',
    'stat_confirmed_desc' => 'Gäste + Begleitung',
    'stat_confirmed_desc_plus_ones' => 'Gäste + Begleitung · :count Begleitungen bestätigt',
    'stat_responded' => 'Geantwortet',
    'stat_responded_desc' => ':responded von :total Gästen',
    'stat_days_until' => 'Bis zur Hochzeit',
    'stat_days_value' => ':days Tage',
    'stat_days_passed' => 'Vorbei',

    // Visit stats widget
    'stat_total_opens' => 'Öffnungen gesamt',
    'stat_total_opens_desc' => 'Alle Einladungsöffnungen',
    'stat_this_month' => 'Diesen Monat',
    'stat_this_month_desc' => 'Öffnungen diesen Monat',
    'stat_unique_visitors' => 'Eindeutige Besucher',
    'stat_unique_visitors_desc' => 'Diesen Monat',
    'stat_personal_opens' => 'Persönliche Links',
    'stat_personal_opens_desc' => 'Öffnungen persönlicher Links',

    // Visit chart widget
    'chart_heading' => 'Link-Öffnungen',
    'chart_description' => 'Einladungsbesuche in den letzten 30 Tagen',
    'chart_dataset_label' => 'Öffnungen',

    // Push notifications
    'nav_push_notifications' => 'Push-Benachrichtigungen',
    'push_notifications_title' => 'Benachrichtigung',
    'push_notifications_allow' => 'Benachrichtigungen erlauben',
    'push_notifications_maybe_later' => 'Vielleicht später',
    'push_notifications_prompt_body' => 'Erlauben Sie :couple, Ihnen wichtige Updates zur Hochzeit zu senden.',
    'push_notifications_compose' => 'Benachrichtigung verfassen',
    'push_notifications_send' => 'Benachrichtigung senden',
    'push_notifications_field_title' => 'Titel',
    'push_notifications_field_body' => 'Nachricht',
    'push_notifications_field_recipients' => 'Empfänger',
    'push_notifications_field_select_guests' => 'Gäste auswählen',
    'push_notifications_recipients_all' => 'Alle Abonnenten',
    'push_notifications_recipients_unanswered' => 'Gäste, die noch nicht geantwortet haben',
    'push_notifications_recipients_selected' => 'Bestimmte Gäste auswählen',
    'push_notifications_subscriber_count' => ':count Gast/Gäste haben Push-Benachrichtigungen aktiviert.',
    'push_notifications_sent_to' => 'Gesendet an',
    'push_notifications_sent_at' => 'Gesendet am',
    'push_notifications_created_at' => 'Erstellt am',
    'push_notifications_sent_success' => 'Benachrichtigung erfolgreich gesendet.',
    'push_notifications_queued_success' => 'Benachrichtigung zur Zustellung in die Warteschlange gestellt.',
    'push_notifications_status' => 'Status',
    'push_notifications_status_queued' => 'In Warteschlange',
    'push_notifications_status_sent' => 'Gesendet',
    'push_notifications_status_failed' => 'Fehlgeschlagen',
    'push_notifications_failed_reason' => 'Fehlergrund',
    'push_notifications_no_subscribers' => 'Keine Gäste mit aktivierten Push-Benachrichtigungen entsprechen Ihrer Auswahl.',
    'push_notifications_no_wedding' => 'Sie benötigen ein Hochzeitsereignis, bevor Sie Benachrichtigungen senden können.',
    'push_notifications_rsvp_pending' => 'RSVP ausstehend',

    'push_install_title' => 'Auf dem iPhone aktivieren',
    'push_install_step1' => '1. Tippen Sie unten in Safari auf Teilen.',
    'push_install_step2' => '2. Wählen Sie „Zum Home-Bildschirm“.',
    'push_install_step3' => '3. Öffnen Sie die App vom Home-Bildschirm und aktivieren Sie Benachrichtigungen.',
    'push_ios_update' => 'Aktualisieren Sie auf iOS 16.4 oder neuer, um Benachrichtigungen zu erhalten.',
    'push_enable_notifications' => 'Benachrichtigungen aktivieren',
    'push_error_not_supported' => 'Ihr Browser unterstützt keine Push-Benachrichtigungen.',
    'push_error_denied' => 'Sie haben Benachrichtigungen blockiert. Bitte in den Browsereinstellungen erlauben.',
    'push_error_config' => 'Konfigurationsfehler bei Push-Benachrichtigungen.',
    'push_error_server' => 'Abonnement konnte nicht gespeichert werden. Bitte erneut versuchen.',
    'push_error_unknown' => 'Ein unerwarteter Fehler ist aufgetreten. Bitte erneut versuchen.',

];
