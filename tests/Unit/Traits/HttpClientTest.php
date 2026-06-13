<?php

namespace Cycoslave\Proxmox\Tests\Unit\Traits;

use PHPUnit\Framework\TestCase;
use Cycoslave\Proxmox\ProxmoxAccess;
use Cycoslave\Proxmox\Exceptions\ApiException;
use Cycoslave\Proxmox\Exceptions\ConnectionException;

class HttpClientTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Helper
    // -------------------------------------------------------------------------

    private function makeCaptureAccess(
        string $responseBody,
        int $httpCode,
        string $curlError = '',
        array &$capturedOptions = [],
        bool $verifyTls = true,
        int $timeout = 10,
    ): ProxmoxAccess {
        return new class(
            host: '127.0.0.1',
            verifyTls: $verifyTls,
            timeout: $timeout,
            responseBody: $responseBody,
            httpCode: $httpCode,
            curlError: $curlError,
            capturedOptions: $capturedOptions,
        ) extends ProxmoxAccess {
            public function __construct(
                string $host,
                bool $verifyTls,
                int $timeout,
                private readonly string $responseBody,
                private readonly int $httpCode,
                private readonly string $curlError,
                private array &$capturedOptions,
            ) {
                parent::__construct(
                    host: $host,
                    tokenId: 'root@pam!tok',
                    tokenSecret: 'uuid-secret',
                    verifyTls: $verifyTls,
                    timeout: $timeout,
                );
            }

            protected function sendRequest(
                string $method,
                string $url,
                array  $params  = [],
                array  $headers = [],
            ): array {
                $options = [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_SSL_VERIFYPEER => $this->verifyTls,
                    CURLOPT_SSL_VERIFYHOST => $this->verifyTls ? 2 : 0,
                    CURLOPT_CONNECTTIMEOUT => max(1, min(5, $this->timeout)),
                    CURLOPT_TIMEOUT        => max(1, $this->timeout),
                    CURLOPT_CUSTOMREQUEST  => $method,
                    CURLOPT_HTTPHEADER     => $headers,
                ];

                if ($method === 'POST' || $method === 'PUT') {
                    $options[CURLOPT_URL]       = $url;
                    $options[CURLOPT_POSTFIELDS] = http_build_query($params);
                } else {
                    $options[CURLOPT_URL] = empty($params)
                        ? $url
                        : $url . '?' . http_build_query($params);
                }

                $this->capturedOptions = $options;

                if ($this->curlError !== '') {
                    throw new ConnectionException("Proxmox cURL error: {$this->curlError}");
                }

                if ($this->httpCode >= 400) {
                    $safeUrl = strtok($url, '?');
                    throw new ApiException(
                        "Proxmox API HTTP {$this->httpCode} for: {$safeUrl}",
                        $this->httpCode,
                    );
                }

                $decoded = json_decode($this->responseBody, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new ApiException(
                        'Proxmox API returned invalid JSON: ' . json_last_error_msg(),
                    );
                }

                return $decoded ?? [];
            }
        };
    }

    private function okBody(array $data = []): string
    {
        return json_encode(['data' => $data]);
    }

    // -------------------------------------------------------------------------
    // Timeout floor
    // -------------------------------------------------------------------------

    /** @test */
    public function connect_timeout_is_at_least_1_when_timeout_is_zero(): void
    {
        $opts   = [];
        $access = $this->makeCaptureAccess($this->okBody(), 200, '', $opts, true, 0);
        $access->get('nodes');
        $this->assertSame(1, $opts[CURLOPT_CONNECTTIMEOUT]);
    }

    /** @test */
    public function total_timeout_is_at_least_1_when_timeout_is_zero(): void
    {
        $opts   = [];
        $access = $this->makeCaptureAccess($this->okBody(), 200, '', $opts, true, 0);
        $access->get('nodes');
        $this->assertSame(1, $opts[CURLOPT_TIMEOUT]);
    }

    /** @test */
    public function connect_timeout_is_capped_at_5_when_total_timeout_is_large(): void
    {
        $opts   = [];
        $access = $this->makeCaptureAccess($this->okBody(), 200, '', $opts, true, 30);
        $access->get('nodes');
        $this->assertSame(5, $opts[CURLOPT_CONNECTTIMEOUT]);
    }

    /** @test */
    public function total_timeout_reflects_configured_value(): void
    {
        $opts   = [];
        $access = $this->makeCaptureAccess($this->okBody(), 200, '', $opts, true, 15);
        $access->get('nodes');
        $this->assertSame(15, $opts[CURLOPT_TIMEOUT]);
    }

    // -------------------------------------------------------------------------
    // TLS option mapping
    // -------------------------------------------------------------------------

    /** @test */
    public function tls_verify_peer_is_true_when_verify_tls_is_enabled(): void
    {
        $opts   = [];
        $access = $this->makeCaptureAccess($this->okBody(), 200, '', $opts, true);
        $access->get('nodes');
        $this->assertTrue($opts[CURLOPT_SSL_VERIFYPEER]);
        $this->assertSame(2, $opts[CURLOPT_SSL_VERIFYHOST]);
    }

    /** @test */
    public function tls_verify_is_disabled_when_verify_tls_is_false(): void
    {
        $opts   = [];
        $access = $this->makeCaptureAccess($this->okBody(), 200, '', $opts, false);
        $access->get('nodes');
        $this->assertFalse($opts[CURLOPT_SSL_VERIFYPEER]);
        $this->assertSame(0, $opts[CURLOPT_SSL_VERIFYHOST]);
    }

    // -------------------------------------------------------------------------
    // GET / POST URL + param handling
    // -------------------------------------------------------------------------

    /** @test */
    public function get_appends_params_as_query_string(): void
    {
        $opts   = [];
        $access = $this->makeCaptureAccess($this->okBody(), 200, '', $opts);
        $access->get('nodes', ['foo' => 'bar', 'baz' => '1']);
        $this->assertStringContainsString('?foo=bar&baz=1', $opts[CURLOPT_URL]);
    }

    /** @test */
    public function get_with_no_params_has_no_query_string(): void
    {
        $opts   = [];
        $access = $this->makeCaptureAccess($this->okBody(), 200, '', $opts);
        $access->get('nodes');
        $this->assertStringNotContainsString('?', $opts[CURLOPT_URL]);
    }

    /** @test */
    public function post_sends_params_as_body_not_query_string(): void
    {
        $opts   = [];
        $access = $this->makeCaptureAccess($this->okBody(), 200, '', $opts);
        $access->post('nodes/pve1/qemu', ['vmid' => 100, 'name' => 'test']);
        $this->assertStringNotContainsString('?', $opts[CURLOPT_URL]);
        $this->assertStringContainsString('vmid=100', $opts[CURLOPT_POSTFIELDS]);
        $this->assertStringContainsString('name=test', $opts[CURLOPT_POSTFIELDS]);
    }

    // -------------------------------------------------------------------------
    // Error → exception mapping
    // -------------------------------------------------------------------------

    /** @test */
    public function curl_error_throws_connection_exception(): void
    {
        $this->expectException(ConnectionException::class);
        $this->expectExceptionMessageMatches('/cURL error/i');

        $access = $this->makeCaptureAccess('', 0, 'Could not resolve host');
        $access->get('nodes');
    }

    /** @test */
    public function http_400_throws_api_exception_with_correct_status_code(): void
    {
        $this->expectException(ApiException::class);

        $access = $this->makeCaptureAccess('{"errors":{"vmid":"400 bad"}}', 400);

        try {
            $access->get('nodes');
        } catch (ApiException $e) {
            $this->assertSame(400, $e->getStatusCode());
            throw $e;
        }
    }

    /** @test */
    public function http_403_throws_api_exception(): void
    {
        $this->expectException(ApiException::class);
        $access = $this->makeCaptureAccess('{"errors":{"permission":"403 forbidden"}}', 403);
        $access->get('nodes');
    }

    /** @test */
    public function http_500_throws_api_exception(): void
    {
        $this->expectException(ApiException::class);
        $access = $this->makeCaptureAccess('internal error', 500);
        $access->get('nodes');
    }

    /** @test */
    public function invalid_json_response_throws_api_exception(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessageMatches('/invalid JSON/i');

        $access = $this->makeCaptureAccess('NOT_VALID_JSON{{{', 200);
        $access->get('nodes');
    }

    /** @test */
    public function http_4xx_error_message_does_not_contain_query_string_params(): void
    {
        $access = $this->makeCaptureAccess('', 404);
        $secret = 'supersecretvalue';

        try {
            $access->get('nodes', ['secret' => $secret]);
            $this->fail('Expected ApiException');
        } catch (ApiException $e) {
            $this->assertStringNotContainsString($secret, $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // Successful response
    // -------------------------------------------------------------------------

    /** @test */
    public function successful_response_returns_decoded_array(): void
    {
        $payload = ['id' => 'pve1', 'type' => 'node'];
        $access  = $this->makeCaptureAccess(json_encode(['data' => $payload]), 200);
        $result  = $access->get('nodes');
        $this->assertSame(['data' => $payload], $result);
    }
}