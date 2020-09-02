# PHP Helper
You wished there was an application like this *Yea its so big buhu*

## How to install
### Globally
Require the package 
```bash
$ composer global require fluxter/php-code-helper:dev-master
```

After that, make sure composer vendor bin is inside your path!
```bash
$ export PATH="$PATH:$HOME/.composer/vendor/bin"
```

Voila!
```bash
$ pch
```
should work now

## How to update
```bash
$ composer global update fluxter/php-code-helper
```
## How to use
### Fix Namespaces
This small command sets the namespace in all files according to your psr-4 level
```bash
$ pch fix-namespaces [Path to your composer.json]
```