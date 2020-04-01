<?php

declare(strict_types=1);

namespace Buddy\OAuth2\Client\Test\Provider;

use Buddy\OAuth2\Client\Provider\Buddy;
use Buddy\OAuth2\Client\Provider\BuddyResourceOwner;
use Buddy\OAuth2\Client\Provider\Exception\BuddyIdentityProviderException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use League\OAuth2\Client\Token\AccessToken;
use PHPUnit\Framework\TestCase;

final class BuddyTest extends TestCase
{
    /**
     * @var Buddy
     */
    private $provider;

    protected function setUp(): void
    {
        $this->provider = new Buddy([
            'clientId' => 'client_id',
            'clientSecret' => 'secret',
        ]);
    }

    public function testBaseAuthorizationUrl(): void
    {
        self::assertEquals('https://api.buddy.works/oauth2/authorize', $this->provider->getBaseAuthorizationUrl());
    }

    public function testBaseAccessTokenUrl(): void
    {
        self::assertEquals('https://api.buddy.works/oauth2/token', $this->provider->getBaseAccessTokenUrl([]));
    }

    public function testResourceOwnerDetailsUrl(): void
    {
        self::assertEquals('https://api.buddy.works/user', $this->provider->getResourceOwnerDetailsUrl(new AccessToken(['access_token' => 'token'])));
    }

    public function testLoadResourceOwner(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('send')->willReturn(new Response(200, [], (string) json_encode($userData = [
            'url' => 'https://api.buddy.works/user',
            'html_url' => 'https://app.buddy.works/my-id',
            'id' => 1,
            'name' => 'Mike Benson',
            'avatar_url' => 'https://app.buddy.works/image-server/user/0/0/0/0/0/0/1/d643744fbe5ebf2906a4d075a5b97110/w/32/32/AVATAR.png',
            'title' => 'Creative director',
            'workspaces_url' => 'https://api.buddy.works/workspaces',
        ])));
        $this->provider->setHttpClient($client);

        /** @var BuddyResourceOwner $user */
        $user = $this->provider->getResourceOwner(new AccessToken(['access_token' => 'some-token']));

        self::assertEquals($userData['id'], $user->getId());
        self::assertEquals($userData['url'], $user->getUrl());
        self::assertEquals($userData['name'], $user->getName());
        self::assertEquals($userData['avatar_url'], $user->getAvatarUrl());
        self::assertEquals($userData['title'], $user->getTitle());
        self::assertEquals($userData['workspaces_url'], $user->getWorkspaceUrl());
        self::assertEquals($userData, $user->toArray());
    }

    public function testThrowExceptionOnInvalidResponseCode(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('send')->willReturn(new Response(400));
        $this->provider->setHttpClient($client);

        $this->expectException(BuddyIdentityProviderException::class);
        $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }

    public function testThrowExceptionOnResponseErrors(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('send')->willReturn(new Response(200, [], (string) json_encode(['errors' => [['message' => 'API is disabled in this workspace.']]])));
        $this->provider->setHttpClient($client);

        $this->expectException(BuddyIdentityProviderException::class);
        $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }
}
