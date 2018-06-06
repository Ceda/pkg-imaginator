# How to require a specific Git tag?

* Change the version requirement to `dev-master`, followed by a hash # and the Git tag name, e.g. `v0.5.0`, like so:

~~~
...
"require": {
    "vendor/package": "dev-master#v0.5.0"
}
...
~~~

# How to require a specific Git commit?

* Change the version requirement to `dev-master`, followed by a hash # and the Git commit reference, e.g. `dd6ed3c8`, like so:

~~~
...
"require": {
    "vendor/package": "dev-master#dd6ed3c8"
}
...
~~~

* Referencing: https://getcomposer.org/doc/04-schema.md#package-links

# Define your own package and set version and reference

* An alternative to working with repositories of `"type": "vcs"` is to define a custom package `"type": "package"` inside repositories and work with a reference.

* The reference is either a Git commit hash, or a tag or branch name, like origin/master.

* This will tie the version to a specific commit reference, in this case `dd6ed3c8`.

~~~
...
"repositories": [
  # ...
  {
    "type": "package",
    "package": {
      "name": "vendor/package",
      "version": "v0.5.0",
      "source": {
        "url": "git@gitlab.server.com:vendor/project.git",
        "type": "git",
        "reference": "dd6ed3c8"
      }
    }
  }
]
...
~~~