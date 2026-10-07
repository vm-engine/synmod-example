<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use VmEngine\Example\Models\Example;
use VmEngine\SynAuth\Models\ApiToken;
use VmEngine\SynAuth\Models\Role;
use VmEngine\SynAuth\Models\RolePermission;
use VmEngine\SynAuth\Models\User;

uses(RefreshDatabase::class);

beforeEach(function () {
    $role = Role::factory()->admin()->create();
    RolePermission::factory()->forModule('example', 'manage')->fullCrud()->create(['role_id' => $role->id]);
    $this->user = User::factory()->create();
    $this->user->roles()->attach($role);
    $token = $this->user->createToken('signed-test');
    $this->plain = $token->plainTextToken;
    $this->apiToken = ApiToken::find($token->accessToken->id);
});

function signedCreate(string $body, array $extraHeaders, string $plainToken)
{
    $server = ['CONTENT_TYPE' => 'application/json', 'CONTENT_LENGTH' => mb_strlen($body, '8bit')];
    foreach (array_merge(['Authorization' => 'Bearer '.$plainToken, 'Accept' => 'application/json'], $extraHeaders) as $name => $value) {
        $server['HTTP_'.strtoupper(str_replace('-', '_', $name))] = $value;
    }

    return test()->call('POST', '/api/v1/example/examples', [], [], [], $server, $body);
}

function exampleSignature(string $body, string $secret, ?string $timestamp = null): array
{
    $timestamp ??= now()->toIso8601String();
    $toSign = implode("\n", ['POST', '/api/v1/example/examples', $timestamp, hash('sha256', $body)]);

    return ['X-Timestamp' => $timestamp, 'X-Signature' => base64_encode(hash_hmac('sha256', $toSign, $secret, true))];
}

it('creates an example from a correctly signed request', function () {
    $secret = $this->apiToken->generateSigningSecret();
    $body = json_encode(['text' => 'Signed create', 'email' => 'signed@example.com', 'status' => 'draft']);

    signedCreate($body, exampleSignature($body, $secret), $this->plain)
        ->assertCreated()
        ->assertJsonPath('data.text', 'Signed create');

    expect(Example::query()->where('text', 'Signed create')->value('created_by'))->toBe($this->user->id);
});

it('rejects a tampered body, an old timestamp and a token without a secret', function () {
    $secret = $this->apiToken->generateSigningSecret();
    $body = json_encode(['text' => 'Original', 'email' => 'a@example.com', 'status' => 'draft']);
    $headers = exampleSignature($body, $secret);
    $tampered = json_encode(['text' => 'Tampered', 'email' => 'a@example.com', 'status' => 'draft']);

    signedCreate($tampered, $headers, $this->plain)->assertStatus(401)->assertJsonPath('error', 'invalid_signature');
    signedCreate($body, exampleSignature($body, $secret, now()->subHour()->toIso8601String()), $this->plain)->assertStatus(401)->assertJsonPath('error', 'timestamp_expired');

    $other = $this->user->createToken('no-secret')->plainTextToken;
    // The guard caches the previous request's token within one test; a real request starts fresh.
    app('auth')->forgetGuards();
    signedCreate($body, $headers, $other)->assertStatus(403)->assertJsonPath('error', 'signing_not_configured');

    expect(Example::query()->count())->toBe(0);
});

it('validates the signed body like the editor', function () {
    $secret = $this->apiToken->generateSigningSecret();
    $body = json_encode(['text' => '', 'email' => 'not-an-email', 'status' => 'published']);

    signedCreate($body, exampleSignature($body, $secret), $this->plain)
        ->assertStatus(422)
        ->assertJsonValidationErrors(['text', 'email', 'publish_note']);
});
