<?php

namespace Cycoslave\Proxmox\Support;

/**
 * ResponseHelper — normalises all API responses into a consistent envelope.
 *
 * Every public method in this package returns:
 *   ['success' => bool, 'message' => string, 'data' => mixed]
 *
 * Callers should always check $response['success'] before consuming $response['data'].
 */
class ResponseHelper
{
    /**
     * Build a standard response envelope.
     *
     * @param  bool                $success  Whether the operation succeeded
     * @param  string              $message  Human-readable status message
     * @param  array|string|null   $data     Payload from the Proxmox API
     * @return array{success: bool, message: string, data?: array|string}
     */
    public static function generate(
        bool $success,
        string $message,
        array|string|null $data = null,
    ): array {
        $response = [
            'success' => $success,
            'message' => $message,
        ];

        if (! is_null($data)) {
            $response['data'] = $data;
        }

        return $response;
    }
}