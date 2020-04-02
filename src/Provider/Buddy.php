<?php

declare(strict_types=1);

namespace Buddy\OAuth2\Client\Provider;

use Buddy\OAuth2\Client\Provider\Exception\BuddyIdentityProviderException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

final class Buddy extends AbstractProvider
{
    use BearerAuthorizationTrait;

    public const SCOPE_WORKSPACE = 'SCOPE_WORKSPACE';
    public const SCOPE_PROJECT_DELETE = 'SCOPE_PROJECT_DELETE';
    public const SCOPE_REPOSITORY_READ = 'SCOPE_REPOSITORY_READ';
    public const SCOPE_REPOSITORY_WRITE = 'SCOPE_REPOSITORY_WRITE';
    public const SCOPE_EXECUTION_INFO = 'SCOPE_EXECUTION_INFO';
    public const SCOPE_EXECUTION_RUN = 'SCOPE_EXECUTION_RUN';
    public const SCOPE_EXECUTION_MANAGE = 'SCOPE_EXECUTION_MANAGE';
    public const SCOPE_USER_INFO = 'SCOPE_USER_INFO';
    public const SCOPE_USER_KEY = 'SCOPE_USER_KEY';
    public const SCOPE_USER_EMAIL = 'SCOPE_USER_EMAIL';
    public const SCOPE_INTEGRATION_INFO = 'SCOPE_INTEGRATION_INFO';
    public const SCOPE_MEMBER_EMAIL = 'SCOPE_MEMBER_EMAIL';
    public const SCOPE_MANAGE_EMAILS = 'SCOPE_MANAGE_EMAILS';
    public const SCOPE_WEBHOOK_INFO = 'SCOPE_WEBHOOK_INFO';
    public const SCOPE_WEBHOOK_ADD = 'SCOPE_WEBHOOK_ADD';
    public const SCOPE_WEBHOOK_MANAGE = 'SCOPE_WEBHOOK_MANAGE';
    public const SCOPE_VARIABLE_ADD = 'SCOPE_VARIABLE_ADD';
    public const SCOPE_VARIABLE_INFO = 'SCOPE_VARIABLE_INFO';
    public const SCOPE_VARIABLE_MANAGE = 'SCOPE_VARIABLE_MANAGE';

    private const SCOPE_SEPARATOR = ' '; // will be encoded as + (Buddy use + as scope separator)

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

    protected function getScopeSeparator()
    {
        return self::SCOPE_SEPARATOR;
    }
}
