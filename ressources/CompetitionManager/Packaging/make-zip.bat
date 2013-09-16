@ECHO off

if exist export mkdir export

if exist competitionmanager.zip DEL competitionmanager.zip

echo "Exporting ManiaLive"

svn export --force http://manialive.googlecode.com/svn/trunk/ export/manialive/

echo "Exporting Plugin"

svn export --force http://maniaplanet-competition-manager.googlecode.com/svn/trunk/ressources/ManiaLivePlugins/CompetitionManager/ export/manialive/libraries/ManiaLivePlugins/CompetitionManager

echo "Exporting app"

svn export --force http://maniaplanet-competition-manager.googlecode.com/svn/trunk/ export/competitionmanager/

7za a -tzip -mx9 competitionmanager.zip ./export/*

RD /S /Q export

PAUSE