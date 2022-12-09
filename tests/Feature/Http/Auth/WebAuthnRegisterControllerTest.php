<?php

namespace Tests\Feature\Http\Auth;

use App\Models\User;
use Tests\FeatureTestCase;
use Laragear\WebAuthn\Http\Requests\AttestedRequest;
use Laragear\WebAuthn\Http\Requests\AttestationRequest;
use Illuminate\Support\Facades\Config;
use Laragear\WebAuthn\WebAuthn;
use Laragear\WebAuthn\JsonTransport;

/**
 * @covers  \App\Http\Controllers\Auth\WebAuthnRegisterController
 */
class WebAuthnRegisterControllerTest extends FeatureTestCase
{

    /**
     * @var \App\Models\User
     */
    protected $user;

    /**
     * @test
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    /**
     * @test
     */
    public function test_uses_attestation_with_fastRegistration_request(): void
    {
        Config::set('webauthn.user_verification', WebAuthn::USER_VERIFICATION_DISCOURAGED);

        $request = $this->mock(AttestationRequest::class);

        $request->expects('fastRegistration')->andReturnSelf();
        $request->expects('toCreate')->andReturn(new JsonTransport());

        $this->actingAs($this->user, 'web-guard')
            ->json('POST', '/webauthn/register/options')
            ->assertOk();
    }

    /**
     * @test
     */
    public function test_uses_attestation_with_secureRegistration_request(): void
    {
        Config::set('webauthn.user_verification', WebAuthn::USER_VERIFICATION_REQUIRED);

        $request = $this->mock(AttestationRequest::class);

        $request->expects('secureRegistration')->andReturnSelf();
        $request->expects('toCreate')->andReturn(new JsonTransport());

        $this->actingAs($this->user, 'web-guard')
            ->json('POST', '/webauthn/register/options')
            ->assertOk();
    }

    /**
     * @test
     */
    public function test_register_uses_attested_request(): void
    {
        $this->mock(AttestedRequest::class)->expects('save')->andReturn();

        $this->actingAs($this->user, 'web-guard')
            ->json('POST', '/webauthn/register')
            ->assertNoContent();
    }
}