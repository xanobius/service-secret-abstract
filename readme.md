# Service Secrets
An approach to handle webhooks and API calls for multiple tenants.

Thanks to [stefanzweifel](https://github.com/stefanzweifel) for the motivation to scribble this together ;-)

## Abstract

The goal of this pattern is to not pollute the .env file with all 
credentials, but also keep them save and encrypted. 

A lot of stuff is handled via enums, that follows the rule of thumb that things shall
reside in the database, as long as no code change would be necessary if new rows are added.
Since most uf the use cases are handled individually, let's say a new implementation for
a tenant to react on pull-request (tenant a. wants to shot to asana and send a notification to
a web-app, but tenant b. wants a chat message in google meet), this will be handled in 
the code in the end. For this reason tenants reside in an Enum and are not added in the DB.

**Note:** The paragraph above should implicitly describe it, nonetheless I want to point out, 
that the solution is not meant for a system _used_ by multiple tenants, just to serve them. 
It's not a multi tenancy approach for platforms, where tenants can log in and do their shenanigans, it 
is a tool with whom you can provide services tailored for them in a easy maintainable way.

## Pieces

### ServiceSecret
See the structure in the [migration](database/migrations/20xx_xx_xx_xxxxxx_create_service_secrets_table.php).

It has a Service (eg. github), a tenant (eg your own company or a client), an env (eg if you work with pipelines and 
want to distinguish between prod, stage etc.), a scope (eg master key, read only token) and the secrets.

The secrets are saved as encrypted json, so you can have a single password, a bunch of credentials or even
an url if you need that.

**Note**: Be aware that encrypted fields depending on the security key of your app! If you change the key, 
you render all your encrypted data unreadable! If you rotate your key, as possible since laravel 11 and presented
in Laracon 2024, make sure to re-encrypt your data. First, I wanted to hint that a rotation package with
reflection to all models with encryption casting would be a shiny idea for a package, but in short I found
[this](https://github.com/rawilk/laravel-app-key-rotator), so it seems someone already thought of that. Nice!

### Enums

- [ServiceScope](app/Enums/SecretScopes.php)
- [ServiceEnvs](app/Enums/ServiceEnvs.php)
- [Services](app/Enums/Services.php)
- [Tenants](app/Enums/Tenants.php)

## Examples

- [One single URL for a webhook for the same system](webhook-middleware.md)

## As a package?

The thought arose to make a package out of this pattern. But since it is exactly that so far, 
only a "pattern", I don't see much sense in it, since it is replicated with a few little
classes added to a project.

However, I'm currently expanding in a project the functionality with a ServiceMappings part.
In a lot of cases, you don't just want to transform and transmit data, you also want to have
it properly assigned, eg. a pull request from System 1 has a user. The user also exists in
System 2, where you want to send the info to, but id's are different. For such cases, a flexibel
mapping is needed. 

If the solution for that is ready (and pleases myself), the idea of a package may appear once
again with more reason to start it.