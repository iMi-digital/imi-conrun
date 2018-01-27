============================
iMi digital conrun CLI tools
============================

.. image:: https://badges.gitter.im/iMi-digital/imi-conrun.svg
   :alt: Join the chat at https://gitter.im/iMi-digital/imi-conrun
   :target: https://gitter.im/iMi-digital/imi-conrun?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge
   
The imi conrun cli tools provides some handy tools to work with Contao from command line.

It is based on n98-magerun.


Installation
------------

There are two ways to install the tools:


Use Phive
"""""""""

.. code-block:: sh

    sudo phive install -g iMi-digital/imi-conrun


See https://phar.io for details on PhiVE

Download phar file
""""""""""""""""""

Download the latest phar file from https://github.com/iMi-digital/imi-conrun/releases and save it was
/usr/local/bin/imi-conrun and make it executable


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


How can you help?
-----------------

* Add new commands
* Send me some proposals if you miss anything
* Create issues if you find a bug or missing a feature.

Thanks to
---------

* netz98 Team for n98-magerun
