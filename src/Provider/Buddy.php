<?php

declare(strict_types=1);

namespace Buddy\OAuth2\Client\Provider;

use Buddy\OAuth2\Client\Provider\Exception\BuddyIdentityProviderException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

final class Buddy extends AbstractProvider
{
    public const SCOPE_USER_INFO = 'USER_INFO';

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @param mixed[] $options
     * @param mixed[] $collaborators
     */
    public function __construct(array $options = [], array $collaborators = [])
    {
        parent::__construct($options, $collaborators);
        $this->baseUrl = rtrim($options['baseApiUrl'] ?? 'https://api.buddy.works', '/');
    }

    public function getBaseAuthorizationUrl(): string
    {
        return $this->baseUrl.'/oauth2/authorize';
    }

    /**
     * @param mixed[] $params
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return $this->baseUrl.'/oauth2/token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return $this->baseUrl.'/user';
    }

    /**
     * @return string[]
     */
    protected function getDefaultScopes(): array
    {
        return [self::SCOPE_USER_INFO];
    }

    /**
     * @param mixed[] $data
     */
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if ($response->getStatusCode() >= 400 || isset($data['errors'])) {
            throw BuddyIdentityProviderException::clientException($response, $data);
        }
    }

    /**
     * @param mixed[] $response
     *
     * @return BuddyResourceOwner
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new BuddyResourceOwner($response);
    }
}
