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

### 2. Create your items config

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

### 3. Run the migration

This will create a Git repository from your SVN repository, cloning your trunk,
branches, and tags.

Keep in mind that if you're migrating externals that exist in some sub-directory
you'll need to adjust the **url** accordingly. I suggest running the migration
for your main repository separately from your externals.

```sh
vendor/bin/git-migrate
  --items /path/to/items.php
  --dir /path/to/
  --authors /path/to/authors.txt
  --url svn://svn.yourdomain.com/
  --javalib /path/to/svn-migration-scripts.jar
```

### 4. Keeping it in sync

While you're making the transition from SVN to Git, you'll only be able to make
commits to your SVN repository, so you'll need some way to keep the repositories
in sync. You can do this with the **--sync** flag. You supply all of the same
arguments as you would for an initial migration, but append the **--sync** flag.

```sh
vendor/bin/git-migrate
  --items /path/to/items.php
  --dir /path/to/
  --authors /path/to/authors.txt
  --url svn://svn.yourdomain.com/
  --javalib /path/to/svn-migration-scripts.jar
  --sync
```

## Options

- **items** The path to your items config.
- **dir** The full system path where the Git repositories should be created.
- **authors** The full system path to the authors file. See [Atlassian's migration guide](https://www.atlassian.com/git/tutorials/migrating-prepare).
- **url** The URL to your SVN repository.
- **javalib** The full system path to [Atlassian's svn-migration-scripts.jar](https://bitbucket.org/atlassian/svn-migration-scripts/downloads) file.
- **sync** Set if you need to update an existing Git repository it with commits from the SVN repository