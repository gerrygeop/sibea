<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as AuthLogin;
use Filament\Forms\Form;
// use Filament\Schemas\Components\Component;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Illuminate\Validation\ValidationException;

class Login extends AuthLogin
{
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getNIMFormComponent(),
                        $this->getPasswordFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function getNIMFormComponent(): Component
    {
        return TextInput::make('nim')
            ->label('NIM')
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getCredentialsFromFormData(#[SensitiveParameter] array $data): array
    {
        return [
            'nim' => $data['nim'],
            'password' => $data['password'],
        ];
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.nim' => __('filament-panels::pages/auth/login.messages.failed'),
        ]);
    }
}
