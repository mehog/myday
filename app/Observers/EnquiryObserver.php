<?php

namespace App\Observers;

use App\Models\Enquiry;
use App\Services\WeddingScheduledNotificationService;

class EnquiryObserver
{
    public function __construct(
        private readonly WeddingScheduledNotificationService $scheduledNotifications,
    ) {}

    public function created(Enquiry $enquiry): void
    {
        $this->scheduledNotifications->notifyAdminsOfNewEnquiry($enquiry);
        $this->scheduledNotifications->scheduleEnquiryFollowUp($enquiry);
    }
}
