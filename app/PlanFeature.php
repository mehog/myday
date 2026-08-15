<?php

namespace App;

enum PlanFeature: string
{
    case SeatingPdfExport = 'seating_pdf_export';
    case PushSend = 'push_send';
    case QrPhotoAlbum = 'qr_photo_album';
}
