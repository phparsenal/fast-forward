# fast-forward

[![Join the chat at https://gitter.im/phparsenal/fast-forward](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/phparsenal/fast-forward?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

[![Build Status](https://travis-ci.org/phparsenal/fast-forward.svg?branch=master)](https://travis-ci.org/phparsenal/fast-forward) [![Dependency Status](https://www.versioneye.com/user/projects/558dbe19316338002400001c/badge.svg?style=flat)](https://www.versioneye.com/user/projects/558dbe19316338002400001c)

**fast-forward** lets you remember, find and open your most used commands and folders.

* [fast-forward](#fast-forward)
    * [Setup](#setup)
        * [Windows](#windows)
        * [Linux](#linux)
        * [Mac](#mac)
    * [Usage](#usage)

## Setup

### Windows
1. Download and extract https://github.com/phparsenal/fast-forward/archive/master.zip
2. Install composer using the [Windows installer](https://getcomposer.org/Composer-Setup.exe)
3. Make sure dependencies are up to date:

        composer install

4. Edit the file `ff.bat` and change `ffpath` to the folder you put fast-forward in.
5. Copy `ff.bat` to a global path so that it is always available on the command line.

### Linux

1. Download the project:

        cd ~
        git clone https://github.com/phparsenal/fast-forward.git

2. [Install composer](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx):

        curl -sS https://getcomposer.org/installer | php
        mv composer.phar /usr/local/bin/composer

3. Make sure dependencies are up to date:

        composer install

4. Afterwards make the `ff` command available globally by adding this to your `~/.bashrc` or `~/.bash_aliases`:

        alias ff='. /path/to/fast-forward/ff.sh'

### Mac
n/a

## Usage
Add a new command in one line:  

    ff add [-c cmd] [-d desc] [-s shortcut]

List all available commands and execute the selection:

    ff

Searching for _htd*_
If the only result is _htd_ it will be executed, otherwise all matches will be displayed first.

    ff htd
