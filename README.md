rollout (for php)
=================

[![Build Status](https://travis-ci.org/opensoft/rollout.svg?branch=master)](https://travis-ci.org/opensoft/rollout)

Feature flippers for PHP.  A port of ruby's [rollout](https://github.com/FetLife/rollout).

Install It
----------

    composer require opensoft/rollout

How it works
------------

Initialize a rollout object:

```php
use Opensoft\Rollout\Rollout;
use Opensoft\Rollout\Storage\ArrayStorage;

$rollout = new Rollout(new ArrayStorage());
```

Check if a feature is active for a particular user:

```php
$rollout->isActive('chat', $user);  // returns true/false
```

Check if a feature is activated globally:

```php
$rollout->isActive('chat'); // returns true/false
```

Storage
-------

There are a number of different storage implementations for where the configuration for the rollout is stored.

* ArrayStorage - default storage, not persistent
* DoctrineCacheStorageAdapter - requires `doctrine/cache`

The storage implementation must implement the `Storage\StorageInterface`'s methods.

Groups
------

Rollout ships with one group by default: `all`, which does exactly what it sounds like.

You can activate the `all` group for chat features like this:

```php
$rollout->activateGroup('chat', 'all');
```

You may also want to define your own groups.  We have one for caretakers:

```php
$rollout->defineGroup('caretakers', function(RolloutUserInterface $user) {
  return $user->isCaretaker(); // boolean
});
```

You can activate multiple groups per feature.

Deactivate groups like this:

```php
$rollout->deactivateGroup('chat');
```

Specific Users
--------------

You may want to let a specific user into a beta test or something.  If that user isn't part of an existing group, you can let them in specifically:

```php
$rollout->activateUser('chat', $user);
```

Deactivate them like this:

```php
$rollout->deactivateUser('chat', $user);
```

Rollout users must implement the `RolloutUserInterface`.

User Percentages
----------------

If you're rolling out a new feature, you may want to test the waters by slowly enabling it for a percentage of your users.

```php
$rollout->activatePercentage('chat', 20);
```

The algorithm for determining which users get let in is this:

```php
crc32($user->getRolloutIdentifier()) % 100 < $percentage
```

So, for 20%, users 0, 1, 10, 11, 20, 21, etc would be allowed in. Those users would remain in as the percentage increases.

Deactivate all percentages like this:

```php
$rollout->deactivatePercentage('chat');
```

**Note:** Activating a feature for 100% of users will also make it activate `globally`.  This is like calling `$rollout->isActive()` without a user object.

Feature is Broken
-----------------

Deactivate everybody at once:

```php
$rollout->deactivate('chat');
```

You may wish to disable features programmatically if monitoring tools detect unusually high error rates for example.

Symfony2 Bundle
---------------

A Symfony2 bundle is available to integrate rollout into Symfony2 projects.  It can be found at http://github.com/opensoft/OpensoftRolloutBundle.

Implementations in other languages
----------------------------------

* Ruby: http://github.com/FetLife/rollout
* Python: http://github.com/asenchi/proclaim

Copyright
---------

Copyright © 2010 James Golick, BitLove, Inc. See LICENSE for details.
