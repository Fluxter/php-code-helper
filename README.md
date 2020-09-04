# PHP Helper
You wished there was an application like this *Yea its so big buhu*
## WARNING
Please use this tool on your own risk.  
Before using it, make a backup of your code (or use git!)

Using this *may* break your whole application! (Mine was fixed :))

## How to install
### Globally
Require the package 
```bash
$ composer global require fluxter/php-code-helper
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
Force sets the namespace in your folder to the according composer.json psr-4 level. 
```bash
$ pch fix-namespaces [Path to the composer.json containing folder]
```
### Fix Usings
If you e.g. refactored many things to new namespaces, the fx-namespaces command should help you with that.   
But now your code is trying to `use` all the files from the old namespace.

This command searches in alle classes the most fitting one.
```bash
$ pch fix-usings [Path to your src directory]
```

#### Example output
```
 - Using not exists! App\Core\Enum\InvoiceStatusType. Searching alternative... Found alternative: App\Plugin\ERP\InvoiceStatusType
 - Using not exists! App\Core\Enum\SubscriptionType. Searching alternative... Found alternative: App\Plugin\ERP\Form\Shared\SubscriptionType
 - Using not exists! App\Core\Helper\DateTimeHelper. Searching alternative... Found alternative: App\Platform\Helper\DateTimeHelper
 - Using not exists! App\Core\Helper\PaymentHelper. Searching alternative... Found alternative: App\Platform\Helper\PaymentHelper
```
