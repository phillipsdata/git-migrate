# Git Migrate

A utility for migrating from SVN to Git.

Uses [Atlassian's svn-migration-scripts](https://bitbucket.org/atlassian/svn-migration-scripts/downloads)
to migrate various SVN repositories to Git. This utility is particularly useful
when you have many externals grouped under a single SVN URL.

## How it Works

1. GitMigrate will execute `git` on your machine to clone an SVN repository to a local path.
**Note**: Your repositories **MUST** be in standard SVN format (/trunk, /branches, /tags).
2. GitMigrate will then execute the svn-migration-scripts to place tags in their correct location.


## Usage

### 0. Install git on your machine

If you don't already have git installed, [install it](https://git-scm.com/book/en/v2/Getting-Started-Installing-Git).
Then make sure it can be executed via `git`.

You'll also need to ensure you have a somewhat modern version of java as well.

### 1. Install via composer

```sh
composer require phillipsdata/git-migrate
```

### 2. Create your config

The config is a JSON file that represents the repositories that you wish to operate on,
as well as any other options that are available as parameters to the `git-migrate` utility.

It looks like this:

```js
{
  "repositories": [
    {
      "path": "my-external-dir/another-dir/repo1",
      "origin": "https://domain.com/repo1.git"
    },
    {
      "path": "my-external-dir/another-dir/repo2"
    }
  ],
  "authors": "/path/to/authors.txt",
  "dir": "/path/to/",
  "javalib": "/path/to/svn-migration-scripts.jar",
  "url": "svn://svn.yourdomain.com/"
}
```

Each repository's **path** is a URI of the **url** option specified.

Notice how our repo2 has no origin. The **origin** is optional for all repositories.
Setting an origin allows us to `push` the repository to that remote repository using the `--push` flag.

See the **Options** section for an explanation of the various other options.


#### Items File (deprecated)

The items config is a file that returns the directory structure of individual repositories.
If you're simply migrating a single repository that might look like:

```php
<?php
return [
    'my-repo-name'
];

```

If, on the other hand, you're migrating a bunch of externals into their own
separate git repos (which is probably why you're using this), your config would be structured more like:

```php
<?php
return [
    'my-external-dir' => [
        'another-dir' => [
            'repo1',
            'repo2'
        ]
    ],
    'repo3',
    'my-other-external-dir' => [
        'repo4'
    ]
];

```

This method is deprecated in favor of specifying the repositories using the JSON
config file described above.

### 3. Run the migration

This will create a Git repository from your SVN repository, cloning your trunk,
branches, and tags.

Keep in mind that if you're migrating externals that exist in some sub-directory
you'll need to adjust the **url** accordingly. I suggest running the migration
for your main repository separately from your externals.

```sh
vendor/bin/git-migrate --config=/path/to/config.json
```

### 4. Keeping it in sync

While you're making the transition from SVN to Git, you'll only be able to make
commits to your SVN repository, so you'll need some way to keep the repositories
in sync. You can do this by appending the `--sync` flag.

```sh
vendor/bin/git-migrate --config=/path/to/config.json --sync
```

### 5. Pushing to a remote origin

The final step in migrating to Git is sharing your repository. You do this by
pushing it to a remote repository with the `--push` flag (be sure you've defined
the origin for each repository in your config you wish to push).

```sh
vendor/bin/git-migrate --config=/path/to/config.json --push
```

## Options

- **config** The JSON file to use for setting the configurable options.
- **items** The path to your items config (deprecated, use **config**).
- **dir** The full system path where the Git repositories should be created.
- **authors** The full system path to the authors file. See [Atlassian's migration guide](https://www.atlassian.com/git/tutorials/migrating-prepare).
- **url** The URL to your Subversion repository.
- **javalib** The full system path to [Atlassian's svn-migration-scripts.jar](https://bitbucket.org/atlassian/svn-migration-scripts/downloads) file.
- **clone** Set if you want to clone the Subversion repositories into Git repositories (**default**).
- **sync** Set if you need to update the existing Git repositories with commits from their Subversion repository.
- **push** Set if you want to push the existing Git repositories to their remote origins.
