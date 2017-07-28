WorldEditArt ![WorldEditArt](plugin_icon.png) [![Build Status](https://travis-ci.org/LegendOfMCPE/WorldEditArt.svg?branch=master)](https://travis-ci.org/LegendOfMCPE/WorldEditArt) [![Poggit-CI](https://poggit.pmmp.io/ci.badge/LegendOfMCPE/WorldEditArt/WorldEditArt-Epsilon)](https://poggit.pmmp.io/ci/LegendOfMCPE/WorldEditArt/WorldEditArt-Epsilon)
============================================================================================================================================================================================================================================================================================================================================================

## Supported API Versions
WorldEditArt explicitly does not support running on unofficial builds of PocketMine. WorldEditArt can only be used on
PocketMine servers with API 3.0.0-ALPHA7 onward.

## User Guide (for both admins and builders)
This user guide is intended for builders using WorldEditArt.

> `ADMIN` Paragraphs starting with `ADMIN` contain non-builder information (e.g. permissions, config). You may ignore
these paragraphs if you are only a builder, not a server owner/admin.

> `PROTIP` Paragraphs starting with `PROTIP` explain why some features would be useful and provide suggestions/tips on
using them. If you are only looking for reference and don't want any suggestions here, skip these paragraphs.

### Start using WorldEditArt
> `ADMIN` A config file will be generated upon the plugin's first run. You may restart the server after editing the
config file to apply the changes.

Type the command `//status` to see basic information about the server setup. It should show you the WorldEditArt version
as well as your current session information (if you have permission to start a session). If the server has enabled
implicit builder sessions, you should have already started a builder session automatically. Otherwise, you have to run
the `//session start` command to start a builder session explicitly. Some servers may require an extra passphrase to
unlock a builder session; in that case, run `//session start <password>`, where `<password>` is the passphrase.

> `ADMIN` To start builder sessions automatically, set `implicit builder session` to `true` in config.yml. If it is set
> to `false`, builder sessions have to be started by typing commands ("explicit builder sessions").

> `PROTIP` Explicit builder sessions can protect the server by preventing builders from accidentally executing world-
> editing operations. The session can also be closed with `//session close`.

> `ADMIN` If `implicit builder session` is set to `false`, you may setup a global passphrase for starting builder
> sessions. To disable this passphrase, leave it empty (`""`).

> `PROTIP` Passphrases can provide an extra safety layer &mdash; even if someone else managed to login as a builder
> (e.g. ~~his brother~~ ~~his cat~~ [the spider in his house](https://xkcd.com/1530) is using his phone), if they don't
> get this passphrase, they still can't destroy your server using WorldEditArt.

### Construction zones
> `ADMIN` Construction zones are disabled by default. Set `construction zone check` in config.yml to `true` to enable.

If the server enables construction zones, builders cannot use WorldEditArt to change blocks outside construction zones.

> `ADMIN` Servers may mark whole worlds as construction zones by adding a world name below `construction zone worlds` in
config.yml.

> `ADMIN` Builders with the `worldeditart.admin.czone.bypass` permission can build outside construction zones.

Builders can check the information of the construction zone they are in using the `/cz check` command. To gain exclusive
access to this construction zone, the builder can use the `/cz lock <name>` command to stop other builders from using
WorldEditArt in this construction zone. Adding the `blocks` argument (i.e. `/cz lock <name> blocks`) will additionally
block players from breaking/placing blocks in the construction zone (but does not affect block updates like water
flowing, TNT, etc.), while adding the `entry` argument (i.e. `/cz lock <name> entry`) will additionally block entry,
block breaking/placement and world editing. They can later be unlocked with the `/cz unlock <name>` command, but they
are automatically unlocked when the builder session is closed (when the player quits, when the session is explicitly
closed, when the server restarts, etc.).

> `PROTIP` World-editing operations may cause industrial accidents such as trapping players inside a wall, overlapping
> other builders' constructions, etc.
