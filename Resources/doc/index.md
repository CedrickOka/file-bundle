Getting Started With OkaFileBundle
==================================

This bundle provides a flexible management of the media.

Prerequisites
=============

The OkaFileBundle has the following requirements:

 - PHP 5.5
 - Symfony 2.8+
 - Imagine library
 - OkaPaginationBundle

Installation
============

Installation is a quick (I promise!) 6 step process:

1. Download OkaFileBundle
2. Enable the Bundle
3. Create your Image class
4. Configure the OkaFileBundle
5. Import OkaFileBundle routing
6. Update your database schema

Step 1: Download the Bundle
---------------------------

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ composer require coka/file-bundle
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Step 2: Enable the Bundle
-------------------------

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
	public function registerBundles()
	{
		$bundles = array(
			// ...
			
			new Oka\ApiBundle\OkaFileBundle(),
		);
		
		// ...
	}
	
	// ...
}
```

Step 3: Create your Image class
-------------------------------

The goal of this bundle is to  persist some 'Image' or 'Video' or 'Document' class to a database (MySql). 
Your first job, then, is to create the `Image` class for you application. 
This class can look and act however you want: add any
properties or methods you find useful. This is *your* `Image ` class.

The bundle provides base classes which are already mapped for most fields
to make it easier to create your entity. Here is how you use it:

1. Extend the base `Image` class (from the ``Model`` folder)
2. Map the `id` field. It must be protected as it is inherited from the parent class.

**Warning:**

> When you extend from the mapped superclass provided by the bundle, don't
> redefine the mapping for the other fields as it is provided by the bundle.

Your `Image` class can live inside any bundle in your application. For example,
if you work at "Acme" company, then you might create a bundle called `AcmeFileBundle`
and place your `Image` class in it.

In the following sections, you'll see examples of how your `Image` class should
look, depending on how you're storing your pictures.

**Note:**

> The doc uses a bundle named `AcmeFileBundle`. If you want to use the same
> name, you need to register it in your kernel. But you can of course place
> your picture class in the bundle you want.

**Warning:**

> If you override the __construct() method in your Image  class, be sure
> to call parent::__construct(), as the base User class depends on
> this to initialize some fields.

#### Doctrine ORM Image class

you must persisting your pictures via the Doctrine ORM, then your `Image` class
should live in the `Entity` namespace of your bundle and look like this to
start:

##### Annotations

```php
<?php
// src/Acme/FileBundle/Entity/User.php

namespace Acme\FileBundle\Entity;

use Oka\FileBundle\Model\Image as BaseImage;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="oka_image")
 */
class Image extends BaseImage
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    public function __construct()
    {
        parent::__construct();
        
        // your own logic
    }
}
```

##### YAML

If you use yml to configure Doctrine you must add two files. The Entity and the orm.yml:

```php
<?php
// src/Acme/FileBundle/Entity/User.php
namespace Acme\FileBundle\Entity;

use Oka\FileBundle\Model\Image as BaseImage;

/**
 * Image
 */
class Image extends BaseImage
{
	public function __construct()
	{
		parent::__construct();
		
		// your own logic
	}
}
```

```yaml
# src/Acme/FileBundle/Resources/config/doctrine/Image.orm.yml
Acme\FileBundle\Entity\Image:
    type:  entity
    table: oka_image
    id:
        id:
            type: integer
            generator:
                strategy: AUTO
```

Step 4: Configure the OkaFileBundle
-----------------------------------

Add the following configuration to your `config.yml`.

``` yaml
# app/config/config.yml
oka_file:
    model\_manager\_name: default
    object\_default\_class:
        image: Acme\FileBundle\Entity\Image
    container_config:
        root_path: /var/www/container
        data_dirnames:
            image: images
        web_server:
            host: aystorage
            port: ~
            secure: false
    behaviors:
        reflection:
            enable_recursive: true
        picture_coverizable:
            mappings:
                Acme\FileBundle\Entity\User:
                    target_entity: Acme\FileBundle\Entity\Image
                    fetch: EAGER
        avatarizable:
            mappings:
                Acme\FileBundle\Entity\User:
                    target_entity: Acme\FileBundle\Entity\Image
                    fetch: EAGER
                    options:
                        default\_avatar\_uri: 'http://acme.com/images/default-avatar.png'
    image:
        uploaded:
            detect\_dominant\_color:
                method: k-means																				#Available methods are 'k-means' and 'quantize'
            thumbnail_factory:
                Acme\FileBundle\Entity\Image: [{ width: 100, height: 100 }, { width: 200, height: 200 }]
        thumbnail:
            quality: 100
            mode: ratio
    routing:
        bot\_service\_image:
            file_class: Acme\FileBundle\Entity\Image
            host: aystorage
            scheme: ~
            prefix: '/image'
            defaults: { mode: 'ratio', quality: 100, size: '100x100' }
```

Step 5: Import OkaFileBundle routing
------------------------------------

Now that you have activated and configured the bundle, all that is left to do is
import the OkaFileBundle routing files.

By importing the routing files you will have ready made pages for things such as
uploading, cropping pictures, etc.

In YAML:

``` yaml
# app/config/routing.yml
oka_file:
   resource: "@OkaFileBundle/Resources/config/routing.yml"
```

OR

``` yaml
# app/config/routing.yml
oka_file_image:
   resource: "@OkaFileBundle/Resources/config/routing/image.yml"

oka_file_image_manipulator:
    resource: "@OkaFileBundle/Resources/config/routing/image_manipulator.yml"
```

Step 6: Update your database schema
-----------------------------------

Now that the bundle is configured, the last thing you need to do is update your
database schema because you have added a new entity, the `Image` class which you
created in Step 4.

Run the following command.

```bash
$ php app/console doctrine:schema:update --force
```

You now can access at the index page `http://app.com/app_dev.php/image/list`!
