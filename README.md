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

```sh
vendor/bin/git-migrate --items ./items.php --dir /path/to/ --authors /path/to/authors.txt --url svn://svn.yourdomain.com --javalib /path/to/svn-migration-scripts.jar
```


## Options

- **items** The path to your items config.
- **dir** The full system path where the Git repositories should be created.
- **authors** The full system path to the authors file. See [Atlassian's migration guide](https://www.atlassian.com/git/tutorials/migrating-prepare).
- **url** The URL to your SVN repository.
- **javalib** The fully system path to [Atlassian's svn-migration-scripts.jar](https://bitbucket.org/atlassian/svn-migration-scripts/downloads) file.
