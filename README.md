FOSUserBundleMandrillMailer
===========================

Provides a mailer service to use in combination with the FOSUserBundle and Mandrill.

## Installation and configuration:

Pretty simple with [Composer](http://packagist.org), add:

```json
{
    "require": {
        "wrep/fosuserbundle-mandrill-mailer": "dev-master"
    }
}
```

### Add FOSUserBundleMandrillMailer to your application kernel

```php
// app/AppKernel.php
public function registerBundles()
{
    return array(
        // ...
        new Wrep\FOSUserBundleMandrillMailer\FOSUserBundleMandrillMailerBundle(),
        // ...
    );
}
```

### Setup the config.yml file 

```yml
// app/config/config.yml
fos_user:
    service:
        mailer: wrep.fosuserbundlemandrillmailer
```        
