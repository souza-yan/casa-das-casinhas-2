<?php

namespace Tests\Unit;

use App\Enums\Role;
use PHPUnit\Framework\TestCase;

class RoleTest extends TestCase
{
    public function test_roles_have_portuguese_labels(): void
    {
        $this->assertSame('Administrador', Role::ADMIN->label());
        $this->assertSame('Cliente', Role::CUSTOMER->label());
    }
}
