# Contributing
This is the right place if you want to start contributing to this project.
Consider these guidelines or recommendations open for discussion.

Don't worry if you don't know how to put everything into practice. We're all here to learn :)

* [Contributing](#contributing)
    * [Learn & listen](#learn--listen)
    * [Adding new features](#adding-new-features)
        * [General workflow](#general-workflow)
        * [Coding style](#coding-style)
        * [Unit tests](#unit-tests)
* [Bug triage](#bug-triage)
* [Beta testing](#beta-testing)
* [Translations](#translations)
* [Documentation](#documentation)
* [Community](#community)
* [Your first bugfix](#your-first-bugfix)

## Learn & listen

To join in on discussions or ask for help, go to:

* [Issue tracker](https://github.com/phparsenal/fast-forward/issues)
    * Report bugs, suggest features and ask for help
* [Gitter](https://gitter.im/phparsenal/phparsenal)
    * General project discussion and planning
* IRC
    * More informal chat or hangout
    * IRC client: [#ormcollab @ chat.freenode.net](irc://chat.freenode.net/ormcollab)
    * Web browser: [webchat.freenode.net](http://webchat.freenode.net/?channels=%23ormcollab)

## Adding new features

### General workflow
1. Create an issue describing the changes so further details can be discussed.
2. Create your own fork of the project if you haven't already.
3. Start working on the issue
    * Either start a feature branch named after what you're working on e.g. *new-import*
    * or if your changes are minor, commit your changes directly to the develop branch
    * Do not commit on the master branch! Changes only get merged from develop into master once they're tested.
4. Once your changes are ready, create a pull request on Github from your fork/branch to our develop branch.
    * In the PR description, make sure to mention the issue you're working on:
        * `Fixes #5`
            Merging your pull request will automatically close the issue.
        * `See #5`
            Your pull request will get mentioned in issue #5.

### Coding style

We use php-fig's PSRs as they currently reflect PHP best practices.

* Follow the [PSR-2 coding style guide](http://www.php-fig.org/psr/psr-2/)
    * We have a default configuration for the [PHP Coding Standards Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer) that will fix most standard related issues:
    Simply run `vendor/bin/php-cs-fixer fix`
- Follow the [PSR-4 autoloader standard](http://www.php-fig.org/psr/psr-4/)

### Unit tests
You can look at the existing tests in folder `tests`. 
Ideally..

* when fixing bugs, try to write an appropiate unit test first. Then start fixing until tests pass.
* before pushing your changes, make sure tests still pass.

# Bug triage

Where and how to report bugs.

* You can help report bugs by filing them on our [issue tracker](https://github.com/phparsenal/fast-forward/issues).
* You can look through the existing bugs to make sure it hasn't been reported yet.
* You can help us diagnose and fix existing bugs by asking and providing answers for the following:
    * Is the bug reproducible as explained?
    * Is it reproducible in other environments (for instance, on different operating systems)?
    * Are the steps to reproduce the bug clear? If not, can you describe how you might reproduce it?
    * What tags should the bug have?
    * Is this bug something you have run into? Would you appreciate it being looked into faster?
* You can close fixed bugs by testing old tickets to see if they are still happening.
* You can remove duplicate bug reports by mentioning it to any maintainers

# Beta testing

New code gets tested on the development branch.

* You can find the roadmap and features that require testing from the [project milestones](https://github.com/phparsenal/fast-forward/milestones).

# Translations
*TODO*
~~This section includes any instructions or translation needs your project has. ~~

* ~~You can help us translate our project here: ~~

# Documentation
*TODO*
~~This section includes any help you need with the documentation and where it can be found. Code needs explanation, and sometimes those who know the code well have trouble explaining it to someone just getting into it. ~~

* ~~Help us with documentation here~~

# Community 
If you don't have any programming experience, we still need your help:

* You can help us answer questions our users have on the [issue tracker](https://github.com/phparsenal/fast-forward/issues)
* Write a blog post or tutorial
    * about how you use this project in your daily life
    * share tips, tricks, screenshots or videos

# Your first bugfix
* First get to know [git](https://git-scm.com/): [cheat sheet](http://learnxinyminutes.com/docs/git/)
* Read about our [general workflow](#workflow)

If you have any questions, feel free to contact us as [described here](#learn--listen).
