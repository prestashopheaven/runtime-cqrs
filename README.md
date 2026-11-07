# prestashopheaven/runtime-cqrs

The command bus of a PrestaHaven module: passes each command or query to its handler from the Symfony container, and gives hooks and legacy controllers safe access to it.

## Use when

A module dispatches commands and queries (DDD + CQRS, ARCH-08), or a hook, widget or legacy controller needs the command bus or another service of the module without breaking the page when the container is stale.

## Install

```
composer require prestashopheaven/runtime-cqrs
```

## Usage

<!-- Examples of the public API. -->

---

Read-only split of [`prestashopheaven/mono-runtime-core`](https://github.com/prestashopheaven/mono-runtime-core): changes,
issues and pull requests go there. MIT License.
