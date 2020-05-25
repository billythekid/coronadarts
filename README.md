# Please note, this is a work in progress and a personal project. 
Feel free to raise PRs or issues if you find it helpful.

----

<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->
**Table of Contents**  *generated with [DocToc](https://github.com/thlorenz/doctoc)*

- [Corona Darts](#corona-darts)
  - [Installation](#installation)
  - [Players](#players)
  - [Competitions Overview](#competitions-overview)
    - [Round Robin](#round-robin)
    - [Elimination](#elimination)
    - [Winner Stays On](#winner-stays-on)
  - [Games Channel](#games-channel)
    - [Competitions](#competitions)
    - [Games](#games)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->

# Corona Darts

This is a [Craft CMS](https://craftcms.com) powered web app designed for running/recording your own darts leagues and competitions.

## Installation 

1. Clone or download the master branch. 
2. Copy .env.example to .env and replace the DB_DSN dbname to your own database name as well as any other connection requirements. 
3. Set the DEFAULT_SITE_URL to match your base url.
4. Run `composer install` in the terminal.
5. Hit /admin and set your user account.

It should be noted that this is the free solo version of Craft CMS. If you require more users, such as admin or other league administrators, you will need to upgrade the site to Pro. More information about Craft CMS versions and pricing can be found on the [Craft CMS website](https://craftcms.com/pricing).  

## Players

Players are entry elements in the players structure. A player consists ony of a title (the player's name probably)

## Competitions Overview

There are three types of competition so far. Round Robin, Elimination, and Winner Stays On.

### Round Robin

A round robin competition is a "league" format. Each player plays every other player a number of times. 3 points are awarded for a win, 0 for a loss.
At the end of the competition the winner is the player with the most wins(points). If two players have the same wins then leg difference is taken into account.

### Elimination

An elimination competition is a knockout tournament where the winner of each game progresses to the next round and the defeated player does not progress.
The winner of the competition is the player who wins the final game.

### Winner Stays On

Players decide who goes first by whichever means they prefer (bulling up is generally the way this is done) When the first game is determined gameplay commences.
The winner of the game must immediately play the next player (at this time the next player is not automatically determined by the system, it's up to the official or players to decide who's next to play)
The competition ends by agreement among the players or official. The winner is the player who ranked up the most wins over the competition.

## Games Channel

The Games channel holds all competitions and games within them. 

### Competitions

Competitions are set up with the competition name, the players taking part in it and any special information about the competition, usually a short description along the lines of "Games in this competition are 501, double out, best of 3 legs." etc.
Competitions also hold information used to create the competition. 

If you select Winner Stays On as the competition type there is no more setup required.

If you select the competition type to be Round Robin you can choose the number of rounds (the number of times players meet each other) and the system will generate all the rounds for that competition.

If you select Elimination as the competition type the system will generate all the games to the final for you. If you wish to do a blind draw at each stage of teh competition you can set that up here and record each round here.

Competitions are top-level entries in the structure with games being children of the competition.

### Games

Games are the default entry type in the games channel.

Each game consists of 2 players, and their legs won. A game also holds some statistical information about high checkouts (where the player won the leg by hitting over 100 points) and high scores (where the player hit 140 points or more with 3 darts)

Games will require all the following to pass validation:

* Both players must be selected and unique
* The game must be a child of a competition
* The game must have a winner
* The competition the game is in must still have outstanding games to be played.
* The game must not already have been played (round robin games)

