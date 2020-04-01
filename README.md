# Buddy Provider for OAuth 2.0 Client
[![Latest Version](https://img.shields.io/github/release/buddy-works/oauth2-client.svg?style=flat-square)](https://github.com/buddy-works/oauth2-client/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/buddy-works/oauth2-client.svg?style=flat-square)](https://packagist.org/packages/buddy-works/oauth2-client)

This package provides Buddy OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

## Installation

To install, use composer:

```
composer require buddy-works/oauth2-client
```

## Usage

Usage is the same as The League's OAuth client, using `Buddy\OAuth2\Client\Provider\Buddy` as the provider.

### Authorization Code Flow

```php
$provider = new Buddy\OAuth2\Client\Provider\Buddy([
    'clientId'          => '{buddy-client-id}',
    'clientSecret'      => '{buddy-client-secret}',
    'redirectUri'       => 'https://example.com/callback-url',
]);

if (!isset($_GET['code'])) {

    // If we don't have an authorization code then get one
    $authUrl = $provider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: '.$authUrl);
    exit;

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

    unset($_SESSION['oauth2state']);
    exit('Invalid state');

} else {

    // Try to get an access token (using the authorization code grant)
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    // Optional: Now you have a token you can look up a users profile data
    try {

        // We got an access token, let's now get the user's details
        $user = $provider->getResourceOwner($token);

        // Use these details to create a new profile
        printf('Hello %s!', $user->getNickname());

    } catch (Exception $e) {

        // Failed to get user details
        exit('Oh dear...');
    }

    // Use this to interact with an API on the users behalf
    echo $token->getToken();
}
```

### Managing Scopes

When creating your Buddy authorization URL, you can specify the state and scopes your application may authorize.

```php
use Buddy\OAuth2\Client\Provider\Buddy;

$options = [
    'state' => 'OPTIONAL_CUSTOM_CONFIGURED_STATE',
    'scope' => [Buddy::SCOPE_WORKSPACE, Buddy::REPOSITORY_READ] // array or string
];

$authorizationUrl = $provider->getAuthorizationUrl($options);
```
If neither are defined, the provider will utilize internal defaults.

At the time of authoring this documentation, the [following scopes are available](https://buddy.works/docs/api/getting-started/oauth2/introduction#supported-scopes).

- `WORKSPACE`	Access to basic workspace information as well as the rights to manage members, groups and member permissions
- `PROJECT_DELETE`	Permission to delete projects.
- `REPOSITORY_READ`	Access to commits and repository content. Repository checkout is allowed, too.
- `REPOSITORY_WRITE`	Permission to write in the repository. File deletion is allowed, too (contains REPOSITORY_READ rights).
- `EXECUTION_INFO`	Access to executions history.
- `EXECUTION_RUN`	Permission to run and stop executions (contains EXECUTION_INFO rights).
- `EXECUTION_MANAGE`	Permission to add/edit pipelines (contains EXECUTION_RUN rights).
- `USER_INFO`	Access to base information of the authorized user.
- `USER_KEY`	Access to public SSH keys of authorized user.
- `USER_EMAIL`	Access to email list of authorized user.
- `INTEGRATION_INFO`	Access to integration list of authorized user.
- `MEMBER_EMAIL`	Access to contact info of workspace members.
- `MANAGE_EMAILS`	Permission to view and mange user email addresses (contains USER_EMAIL rights).
- `WEBHOOK_INFO`	Access to webhooks info.
- `WEBHOOK_ADD`	Permission to get and add webhooks.
- `WEBHOOK_MANAGE`	Permission to add/edit and delete webhooks.
- `VARIABLE_ADD`	Permission to get and add environment variables.
- `VARIABLE_INFO`	Access to environment variables' info.
- `VARIABLE_MANAGE`	Permission to add/edit and delete environment variables.

## Testing

``` bash
composer tests
```

## Credits

- [Arkadiusz Kondas](https://github.com/akondas)
- [All Contributors](https://github.com/buddy-works/oauth2-client/contributors)

## License

The MIT License (MIT). Please see [License File](https://github.com/buddy-works/oauth2-client/blob/master/LICENSE) for more information.
