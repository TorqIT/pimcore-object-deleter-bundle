# pimcore-object-deleter-bundle
Delete objects in bulk by class name and root directory

# Installing the package via composer

This bundle is easily installed via composer: `composer require torqit/pimcore-object-deleter-bundle`

# Steps to setting up the object deleter:
1. Make sure you register the `ObjectDeleterBundle` in your `AppKernel.php`. Registering the bundle is as easy as adding a line in the registerBundlesToCollection function, like so: `$collection->addBundle(new \TorqIT\ObjectDeleterBundle\ObjectDeleterBundle);`
2. Install the bundle, which will add the necessary procedure to your database. Run: `bin/console pimcore:bundle:install ObjectDeleterBundle`.
3. Run the bundle, with the command: `./bin/console torq:object-deleter CLASSNAME_TO_DELETE:REQUIRED FOLDER_TO_DELETE_FROM:OPTIONAL`.

CLASSNAME_TO_DELETE:REQUIRED => This is the class you are wishing to delete
FOLDER_TO_DELETE_FROM:OPTIONAL => This is the directory in the pimcore admin you would like to delete from. Default is root (`/`)

An example of this command would be: `./bin/console torq:object-deleter Product /MyProducts`

# Migration changes or new version

If the migration changes you will have to run an uninstall / reinstall the bundle so the procedure in the database update. 
Uninstall => `bin/console pimcore:bundle:uninstall ObjectDeleterBundle`
Reinstall => `bin/console pimcore:bundle:install ObjectDeleterBundle`


