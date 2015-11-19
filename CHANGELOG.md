2.0.0
-----

(BC Break) The `StorageInterface` interface had a `remove` function added to its definition.  If you have your own implementation of this interface, you'll need to update your implementation to include this functionality.

- Added support for removing a feature

1.0.4
-----

- Added redis support [PR #9](https://github.com/opensoft/rollout/pull/9)
