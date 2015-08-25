# Git Migrate

A utility for migrating from SVN to Git. Uses
[Atlassian's svn-migration-scripts.jar](https://bitbucket.org/atlassian/svn-migration-scripts/downloads).

## Usage

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
vendor/bin/git-migrate --items ./items.php --dir ./ --authors ./authors.txt --url svn://svn.yourdomain.com --javalib ./svn-migration-scripts.jar > output.out
```


## Options

- **items** The path to your items config.
- **dir** The path where the Git repositories should be created. Use `./` for the current directory.
- **authors** The path to the authors file. See [Atlassian's migration guide](https://www.atlassian.com/git/tutorials/migrating-prepare).
- **url** The URL to your SVN repository.
- **javalib** The path to [Atlassian's svn-migration-scripts.jar](https://bitbucket.org/atlassian/svn-migration-scripts/downloads) file.
