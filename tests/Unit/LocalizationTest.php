<?php

namespace Tests\Unit;

use Tests\TestCase;

class LocalizationTest extends TestCase
{
    public function test_application_messages_are_presented_in_brazilian_portuguese(): void
    {
        $this->assertSame('pt_BR', config('app.locale'));
        $this->assertSame('Enviamos o link de redefinição de senha para seu e-mail.', trans('passwords.sent'));
        $this->assertSame('Redefinir senha', __('Reset Password'));
        $this->assertSame('Confirmar', trans('ts-ui::messages.toast.button.confirm'));
        $this->assertSame(
            'O campo e-mail é obrigatório.',
            validator(['email' => ''], ['email' => ['required']])->errors()->first('email')
        );
    }
}
