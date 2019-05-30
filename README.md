Symfony 4 API Platform
======================
The project is example of as implement API Platform in Symfony and use Testing.
Initial commands
----------------
Database
~~~~~~~~
.. code-block:: bash
    php bin/console doctrine:database:drop --force
    php bin/console doctrine:database:create
    php bin/console doctrine:schema:create
    php bin/console hautelook:fixtures:load

Test
~~~~
.. code-block:: bash
    php bin/phpunit

## Use
- [Symfony](https://symfony.com)
- [API Platform](https://api-platform.com)
- [Alice Bundle](https://github.com/hautelook/AliceBundle)
- [Easy Admin Bundle](https://symfony.com/doc/master/bundles/EasyAdminBundle/index.html)
- [Vich Uploader Bundle](https://github.com/dustin10/VichUploaderBundle)

## Author
Gonzalo Alonso - gonkpo@gmail.com