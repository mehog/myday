<?php

namespace App\Filament\Resources\DiscountEmailTemplates\Pages;

use App\Filament\Resources\DiscountEmailTemplates\DiscountEmailTemplateResource;
use App\Models\DiscountEmailTemplate;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditDiscountEmailTemplate extends EditRecord
{
    protected static string $resource = DiscountEmailTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function (DeleteAction $action): void {
                    /** @var DiscountEmailTemplate $record */
                    $record = $this->getRecord();

                    if ($record->isInUse()) {
                        Notification::make()
                            ->title(__('discounts.delete_template_in_use'))
                            ->danger()
                            ->send();

                        $action->cancel();
                    }
                }),
        ];
    }
}
