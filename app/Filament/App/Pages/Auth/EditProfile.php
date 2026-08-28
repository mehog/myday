<?php

namespace App\Filament\App\Pages\Auth;

use App\Filament\App\Pages\AppDashboard;
use App\Support\Locale;
use App\Support\UnverifiedEmail;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use SensitiveParameter;

class EditProfile extends BaseEditProfile
{
    protected string $view = 'filament.app.pages.edit-profile';

    protected function getLocaleFormComponent(): Component
    {
        return Select::make('locale')
            ->label(__('locale.label'))
            ->options(Locale::options())
            ->required();
    }

    protected function getEmailFormComponent(): Component
    {
        $user = auth()->user();
        $isVerified = $user?->hasVerifiedEmail() ?? true;

        return TextInput::make('email')
            ->label(__('filament-panels::auth/pages/edit-profile.form.email.label'))
            ->email()
            ->disabled($isVerified)
            ->dehydrated(! $isVerified)
            ->required(! $isVerified)
            ->helperText($isVerified ? __('app.email_readonly') : __('app.email_unverified_editable'));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getLocaleFormComponent(),
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                $this->getCurrentPasswordFormComponent(),
            ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (empty($data['locale'])) {
            $data['locale'] = app()->getLocale();
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(#[SensitiveParameter] array $data): array
    {
        $user = auth()->user();

        if ($user?->hasVerifiedEmail()) {
            unset($data['email']);
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, #[SensitiveParameter] array $data): Model
    {
        if (! $record->hasVerifiedEmail() && isset($data['email']) && is_string($data['email'])) {
            if (strcasecmp($data['email'], $record->email) !== 0) {
                UnverifiedEmail::update($record, $data['email']);
            }

            unset($data['email']);
        }

        $record = parent::handleRecordUpdate($record, $data);

        if (isset($data['locale']) && is_string($data['locale']) && Locale::isSupported($data['locale'])) {
            session(['locale' => $data['locale']]);
            Locale::apply($data['locale']);
        }

        return $record;
    }

    protected function getRedirectUrl(): ?string
    {
        return AppDashboard::getUrl();
    }
}
