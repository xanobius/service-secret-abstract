# Webhook resolving

## Goal

To have a single endpoint, that can be added to a system for multiple accounts.

Scenario: You have several customers working with hubspot. They all want different stuff to happen, when
certain events, supported by hubspot, are detected, eg the change the state of a deal: 
- Deal changes from "maybe" to "yeah baby, let's do business" -> create a task in your task tool
- Deal changes from "in progress" to "finished" -> grab times from timetracker, shoot them to finance tool and create an invoice

## Prepare the secrets

First of all, we have to fill in the secrets. In our example, hubspot, the would look like this

```php

# seeder
<?php
//  [...]

    ServiceSecret::factory()->create([
        'service' => Services::HUBSPOT,
        'tenant' => Tenants::TENANT_ONE,
        'env' => ServiceEnvs::PROD,
        'scope' => SecretScopes::DEFAULT,
        'secrets' => [
            'access_token' => 'pat-na1-.....',
            'client_secret' => '446b4699-....'
        ]
    ]);

    ServiceSecret::factory()->create([
        'service' => Services::HUBSPOT,
        'tenant' => Tenants::MY_COMPANY,
        'env' => ServiceEnvs::TEST,
        'scope' => SecretScopes::DEFAULT,
        'secrets' => [
            'access_token' => 'pat-eu1-...',
            'client_secret' => 'c0e7c40c-...'
        ]
    ]);
```

## Create the route

Since we're working with calls from another system, we put the endpoint in the api routing file.
We also want to keep it clean, as described in the goal, one system, one endpoint. This file has to be adjusted
for one service one single time, after that it stays clean. Happy structure!

```php

# routes/api.php

<?php

//  [...]

Route::post('/hubspot',HubspotWebhookController::class)
    ->middleware(HubspotWebhook::class)
    ->name('hubspot.webhook');
```

## The middleware

Heart of this mechanism is the middleware, that consumes the requests, validates the signature and by this validation
also resolves the service secret. By this resolving, we got all the needed info, so we now which tenant in which environment
shot at the endpoint and can make our app act accordingly

```php
 
<?php

class HubspotWebhook
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(! $request->hasHeader('x-hubspot-signature')) {
            throw new InvalidHubspotWebhookException('Missing signature');
        }

        if(! $this->validateSignature($request)) {
            throw new InvalidHubspotWebhookException('Invalid signature');
        }

        return $next($request);
    }

    private function validateSignature(Request $request): bool
    {
        $signatures = ServiceSecret::whereService(Services::HUBSPOT)->get();

        $matchingSecret = $signatures->first(fn(ServiceSecret $ss) =>
            $this->checkSignatureV1(
                signature: $request->header('x-hubspot-signature'),
                clientSecret: $ss->secrets['client_secret'],
                payload: $request->getcontent()
            ));

        if(! $matchingSecret) return false;

        // add tenancy data to request
        $request->merge(['serviceSecret' => $matchingSecret]);

        return true;
    }

    private function checkSignatureV1(
        string $signature,
        string $clientSecret,
        string $payload
    ): bool
    {
        /**
         * @see https://legacydocs.hubspot.com/docs/faq/v1-request-validation
         */
        return \hash('sha256', $clientSecret.$payload) === $signature;
    }

}
```

The important stuff here happens in the validateSignature function: We know the service, we want to check and we are
also sure, that a valid signature can only be hashed by the correct client secret. Because of that, we use "first" and
don't process unneeded secrets. Already scoped to only Hubspot Secrets, that should happen pretty quick.

- No hit: Invalid call!
- Hit: Grab the Service Secret and merge it into the request for later usage 

One could also reduce the information merged in the request, eg only take the tenant and the env, that is up to the 
implementation needs.