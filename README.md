# UbermudaAuditBundle

An append-only audit trail for Symfony. It records who did what, to which subject,
through which channel, and whether the attempt succeeded.

The trail is a table, not a log stream. A record survives the account it points at,
survives a rolled-back transaction that never wrote the subject, and is deleted only
by the retention sweep.

## Installation

```bash
composer require ubermuda/audit-bundle
```

Register the bundle (Symfony Flex does this automatically):

```php
// config/bundles.php
return [
    // ...
    Ubermuda\AuditBundle\UbermudaAuditBundle::class => ['all' => true],
];
```

### The admin screen is off until you import its route

```yaml
# config/routes/ubermuda_audit.yaml
ubermuda_audit:
    resource: '@UbermudaAuditBundle/config/routes.php'
```

### The schema is yours

The bundle maps the `audit_log` entity and ships no migration. Generate one in your
application:

```bash
bin/console doctrine:migrations:diff
```

Every column carries an explicit name, so the table is the same whatever naming
strategy your application configures.

`audit_log.actor_id` and `audit_log.credential_id` point at interfaces, so Doctrine
needs to know which of your entities each one is:

```yaml
# config/packages/doctrine.yaml
doctrine:
    orm:
        resolve_target_entities:
            Ubermuda\AuditBundle\AuditActorInterface: App\Entity\User
            Ubermuda\AuditBundle\AuditCredentialInterface: App\Entity\ApiToken
```

Both are `ON DELETE SET NULL`. A record outlives the actor row it names, and keeps
the label it snapshotted at the time.

## Recording

Inject `Auditor` and never a sink:

```php
$this->auditor->record(
    'document.created',
    AuditOutcome::Success,
    ['documentId' => (string) $document->id],
    new AuditSubject('Document', (string) $document->id),
);
```

`AuditOutcome` has four cases: `Success`, `Unchanged`, `Refused` and `Failed`. An
operation an actor asked for and did not get is `Refused`, and it belongs in the
trail as much as one that worked.

Two sinks ship. `DoctrineAuditSink` buffers rows and writes them through the DBAL
after the unit of work, so a record survives a rollback. `MonologAuditSink` writes
each event straight to a logger, and deliberately carries no actor identity.

## Configuration

All three keys are optional; defaults shown:

```yaml
# config/packages/ubermuda_audit.yaml
ubermuda_audit:
    route_prefix: /admin/audit-log
    retention_days: 180
    purge_schedule: '45 * * * *'
```

`purge_schedule` drives an `#[AsCronTask]`. Move it off a minute your application
already uses for other work. `audit:purge` runs the same sweep by hand.

## The four ports

Each has an inert default. Alias the interface to your own class to answer it:

| Interface | Answers | Default |
|---|---|---|
| `AuditActorProviderInterface` | who is acting, and through which channel | no actor, channel `system` |
| `AuditRetentionPolicyInterface` | how long a record is kept | the `retention_days` key |
| `AuditLoggerRegistryInterface` | which logger a category writes to | none, so the sink uses its fallback |
| `AuditChannelCatalogInterface` | which channels the admin filter offers | none, so the filter is hidden |

```yaml
# config/services.yaml
services:
    Ubermuda\AuditBundle\AuditActorProviderInterface: '@App\Audit\MyActorProvider'
```

A sink is a tag, not a port. Implement `AuditSinkInterface` and autoconfiguration
tags it `ubermuda_audit.sink`.

## Authorization

The admin screen gates on `audit_log.admin`, which `AuditLogVoter` answers with
`ROLE_ADMIN`. Decorate that voter, or add another voting on the same attribute, to
change the policy without forking the controller.

## Draining the buffer

`FlushAuditSinksListener` drains the buffering sinks at the end of every unit of
work: a request, a console command, and each of the four Messenger worker events.
It runs at priority -1023, immediately before Messenger resets the services.
