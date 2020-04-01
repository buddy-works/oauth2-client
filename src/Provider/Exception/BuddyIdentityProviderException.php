<?php

declare(strict_types=1);

namespace Buddy\OAuth2\Client\Provider\Exception;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Psr\Http\Message\ResponseInterface;

final class BuddyIdentityProviderException extends IdentityProviderException
{
    /**
     * @param mixed[] $data
     */
    public static function clientException(ResponseInterface $response, $data): self
    {
        return static::fromResponse(
            $response,
            isset($data['errors']) ? $data['errors'][0]['message'] : $response->getReasonPhrase()
        );
    }

    private static function fromResponse(ResponseInterface $response, ?string $message = null): self
    {
        return new static((string) $message, $response->getStatusCode(), (string) $response->getBody());
    }
}
