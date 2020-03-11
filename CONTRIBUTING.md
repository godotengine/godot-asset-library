# Contributing to the Godot Asset Library

First off, thanks for taking the time to contribute! :tada::+1:

The following is a set of guidelines for contributing to the Godot Engine's [official asset library web front end](https://godotengine.org/asset-library) and its REST API. These are mostly guidelines, not rules. Use your best judgment, and feel free to propose changes to this document in a pull request.

### :warning: Two important notes before we dive into the details

1. Issues with the asset library front end included in the Godot Engine should be reported in the [Godot Engine repository](https://github.com/godotengine/godot/issues?q=is%3Aissue+is%3Aopen+asset+label%3Atopic%3Aassetlib).
2. Issues with individual assets should be reported in the separate issue trackers of the respective assets, usually linked on the asset pages under the "Submit an issue" button.

## Table Of Contents

- [Code of Conduct](#code-of-conduct)

- [I don't want to read this whole thing, I just have a question!](#i-dont-want-to-read-this-whole-thing-i-just-have-a-question)

- [What should I know before I get started?](#what-should-i-know-before-i-get-started)
  - [The Godot Asset Library](#the-godot-asset-library)

- [How Can I Contribute?](#how-can-i-contribute)
  - [Reporting Bugs](#reporting-bugs-and-suggesting-enhancements)
  * [Code Contributions and Pull Requests](#code-contributions-and-pull-requests)

## Code of Conduct

This project and everyone participating in it is governed by the [Godot Code of Conduct](https://godotengine.org/code-of-conduct). By participating, you are expected to uphold this code. Please report unacceptable behavior to [Godot's Code of Conduct team](mailto:conduct@godotengine.org).

## I don't want to read this whole thing I just have a question!

> **Note:** Please don't file an issue to ask a question. You'll get faster results by using the resources below.

We have several forums, chats and groups where the community chimes in with helpful advice if you have questions:

* [Questions & Answers](https://godotengine.org/qa/) &mdash; The official Godot message board
* [Forum](https://godotforums.org/) &mdash; Community forum for all Godot developers

Plus several groups at [Facebook](https://www.facebook.com/groups/godotengine/), [Reddit](https://www.reddit.com/r/godot), [Steam](https://steamcommunity.com/app/404790) and others. See [Godot Communities](https://godotengine.org/community) for a more complete list. And if chat is more your speed, you can join us at [IRC](http://webchat.freenode.net/?channels=#godotengine), [Discord](https://discord.gg/zH7NUgz) or [Matrix](https://matrix.to/#/#godotengine:matrix.org).

## What should I know before I get started?

### The Godot Asset Library

The Godot Asset Library, otherwise known as the AssetLib, is a repository of user-submitted Godot addons, scripts, tools and other resources, collectively referred to as assets. They’re available to all Godot users for download directly from within the engine, but it can also be accessed at [Godot’s official website](https://godotengine.org/asset-library) via the PHP scripts and API functions maintained in this repository. 

Please note that the AssetLib is relatively young &mdash; it may have various pain points, bugs and usability issues.

## How can I contribute?

### Reporting Bugs and Suggesting Enhancements

The golden rule is to **always open *one* issue for *one* bug**. If you notice
several bugs and want to report them, make sure to create one new issue for
each of them.

If you're reporting a new bug, you'll make our life simpler (and the
fix will come sooner) by following these guidelines:

### Search first in the existing database

Issues are often reported several times by various users. It's good practice to
**search first in the [issue tracker](https://github.com/godotengine/godot-asset-library/issues)
before reporting your issue**. If you don't find a relevant match or if you're
unsure, don't hesitate to **open a new issue**. The bugsquad will handle it
from there if it's a duplicate.

### Code Contributions and Pull Requests

If you want to add or improve asset library features, please make sure that:

- This functionality is desired, which means that it solves a common use case
  that several users will need.
- You talked to other developers on how to implement it best.
- Even if it doesn't get merged, your PR is useful for future work by another
  developer.

Similar rules can be applied when contributing bug fixes - it's always best to
discuss the implementation in the bug report first if you are not 100% about
what would be the best fix.

The general [Godot Contributing docs](https://docs.godotengine.org/en/latest/community/contributing/index.html)
also have important information on the PR workflow and the code style we use in this project.
