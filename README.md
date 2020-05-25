# Corona Darts

This is a Craft CMS installation designed for running/recording your own darts leagues and tournaments.

[toc]

## Installation 

1. Clone or download the master branch. 
2. Copy .env.example to .env and replace the DB_DSN dbname to your own database name as well as any other connection requirements. 
3. Set the DEFAULT_SITE_URL to match your base url.
4. Run `composer install` in the terminal.
5. Hit /admin and set your user account.

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

