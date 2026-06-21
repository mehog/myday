<?php

namespace App\Livewire;

use App\InvitationTheme;
use App\Models\Enquiry;
use Livewire\Component;

class ContactForm extends Component
{
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $groomName = '';

    public string $brideName = '';

    public ?string $weddingDate = null;

    public ?string $theme = null;

    public string $notes = '';

    public bool $submitted = false;

    public function submit(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'groomName' => ['nullable', 'string', 'max:255'],
            'brideName' => ['nullable', 'string', 'max:255'],
            'weddingDate' => ['nullable', 'date'],
            'theme' => ['nullable', 'string', 'in:'.implode(',', array_column(InvitationTheme::cases(), 'value'))],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'name.required' => __('landing.form_name_required'),
            'email.required' => __('landing.form_email_required'),
            'email.email' => __('landing.form_email_invalid'),
        ]);

        Enquiry::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?: null,
            'groom_name' => $validated['groomName'] ?: null,
            'bride_name' => $validated['brideName'] ?: null,
            'wedding_date' => $validated['weddingDate'] ?: null,
            'theme' => $validated['theme'] ?: null,
            'notes' => $validated['notes'] ?: null,
        ]);

        $this->submitted = true;
        $this->reset(['name', 'email', 'phone', 'groomName', 'brideName', 'weddingDate', 'theme', 'notes']);
    }

    public function render()
    {
        return view('livewire.contact-form', [
            'themes' => InvitationTheme::cases(),
        ]);
    }
}
