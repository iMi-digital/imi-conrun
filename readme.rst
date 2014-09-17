============================
iMi digital conrun CLI tools
============================

The imi conrun cli tools provides some handy tools to work with Contao from command line.

It is based on n98-magerun.

For the current status check changes.txt

Installation
------------

There are two ways to install the tools:

Download phar file
""""""""""""""""""

.. code-block:: sh

    wget https://raw.githubusercontent.com/imi-digital/imi-conrun/master/imi-conrun.phar

or if you have problems with SSL certificate:

.. code-block:: sh

   curl -o imi-conrun.phar https://raw.githubusercontent.com/imi-digital/imi-conrun/master/imi-conrun.phar

You can make the .phar file executable.

.. code-block:: sh

    chmod +x ./imi-conrun.phar

If you want to use command system wide you can copy it to `/usr/local/bin`.

.. code-block:: sh

    sudo cp ./imi-conrun.phar /usr/local/bin/

**Debian / suhosin:**

On some debian systems with compiled in suhosin the phar extension must be added to a whitelist.

Add this to your php.ini file:

.. code-block:: ini

   suhosin.executor.include.whitelist="phar"


**You don't like the filename?**

Just rename it to whatever you want.

Update
------

We deliver a self-update script within the phar file::

   $ imi-conrun.phar self-update

If file was installed system wide do not forget "sudo".

Usage / Commands
----------------

All commands try to detect the current Contao root directory.
If you have multiple Contao installation you must change your working directory to
the preferred installation.

You can list all available commands by::

   $ imi-conrun.phar list


If you don't have installed the .phar file system wide you can call it with the php cli interpreter::

   php imi-conrun.phar list

You can get help for a command unsing

   imi-conrun.phar help COMMAND-NAME

imi-conrun Script
""""""""""""""""""

Run multiple commands from a script file.

.. code-block:: sh

   $ imi-conrun.phar [-d|--define[="..."]] [--stop-on-error] [filename]

Example:

.. code-block::

   # Set multiple config
   config:set "web/cookie/cookie_domain" example.com

   # Set with multiline values with "\n"
   config:set "general/store_information/address" "First line\nSecond line\nThird line"

   # This is a comment
   cache:flush


Optionally you can work with unix pipes.

.. code-block:: sh

   $ echo "cache:flush" | imi-conrun-dev script

.. code-block:: sh

   $ imi-conrun.phar script < filename

It is even possible to create executable scripts:

Create file `test.conrun` and make it executable (`chmod +x test.conrun`):

.. code-block:: sh

   #!/usr/bin/env imi-conrun.phar script

   config:set "web/cookie/cookie_domain" example.com
   cache:flush

   # Run a shell script with "!" as first char
   ! ls -l

   # Register your own variable (only key = value currently supported)
   ${my.var}=bar

   # Let conrun ask for variable value - add a question mark
   ${my.var}=?

   ! echo ${my.var}

   # Use resolved variables from imi-conrun in shell commands
   ! ls -l ${contao.root}/code/local

Pre-defined variables:

* ${contao.root}    -> Contao Root-Folder
* ${contao.version} -> Contao Version i.e. 1.7.0.2
* ${contao.edition} -> Contao Edition -> Community or Enterprise
* ${conrun.version} -> Contrun version i.e. 1.66.0
* ${php.version}     -> PHP Version
* ${script.file}     -> Current script file path
* ${script.dir}      -> Current script file dir

Variables can be passed to a script with "--define (-d)" option.

Example:

.. code-block:: sh

   $ imi-conrun.phar script -d foo=bar filename

   # This will register the variable ${foo} with value bar.

It's possible to define multiple values by passing more than one option.


imi-conrun Script Repository
"""""""""""""""""""""""""""""
You can organize your scripts in a repository.
Simply place a script in folder */usr/local/share/imi-conrun/scripts* or in your home dir
in folder *<HOME>/.imi-conrun/scripts*.

Scripts must have the file extension *.conrun*.

After that you can list all scripts with the *script:repo:list* command.
The first line of the script can contain a comment (line prefixed with #) which will be displayed as description.

.. code-block:: sh

   $ imi-conrun.phar script:repo:list [--format[="..."]]

If you want to execute a script from repository this can be done by *script:repo:run* command.

.. code-block:: sh

   $ imi-conrun.phar script:repo:run [-d|--define[="..."]] [--stop-on-error] [script]

Script argument is optional. If you don't specify any you can select one from a list.

PHPStorm
""""""""

An commandline tool autocompletion XML file for PHPStorm exists in subfolder **autocompletion/phpstorm**.
Copy **imi_conrun.xml** in your phpstorm config folder.

Linux: ~/.WebIde50/config/commandlinetools

You can also add the XML content over settings menu.
For further instructions read this blog post: http://blog.jetbrains.com/webide/2012/10/integrating-composer-command-line-tool-with-phpstorm/

Advanced usage
--------------

Add your own commands
"""""""""""""""""""""

https://github.com/netz98/n98-magerun/wiki/Add-custom-commands

Overwrite default settings
""""""""""""""""""""""""""

Create the yaml config file **~/.imi-conrun.yaml**.
Now you can define overwrites. The original config file is **config.yaml** in the source root folder.

Change of i.e. default currency and admin users:

.. code-block:: yaml

    commands:
      IMI\Contao\Command\Installer\InstallCommand:
        installation:
          defaults:
            currency: USD
            admin_username: myadmin
            admin_firstname: Firstname
            admin_lastname: Lastname
            admin_password: mydefaultSecret
            admin_email: defaultemail@example.com


Add own Contao repositories
""""""""""""""""""""""""""""

Create the yaml config file **~/.imi-conrun.yaml**.
Now you can define overwrites. The original config file is **config.yaml** in the source root folder.

Add you repo. The keys in the config file following the composer package structure.

Example::

    commands:
      IMI\Contao\Command\Installer\InstallCommand:
        contao-packages:
          - name: my-contao-git-repository
            version: 1.x.x.x
            source:
              url: git://myserver/myrepo.git
              type: git
              reference: 1.x.x.x
            extra:
              sample-data: sample-data-1.6.1.0

          - name: my-zipped-contao
            version: 1.7.0.0
            dist:
              url: http://www.myserver.example.com/contao-1.7.0.0.tar.gz
              type: tar
            extra:
              sample-data: sample-data-1.6.1.0

How can you help?
-----------------

* Add new commands
* Send me some proposals if you miss anything
* Create issues if you find a bug or missing a feature.

Thanks to
---------

* netz98 Team for n98-magerun