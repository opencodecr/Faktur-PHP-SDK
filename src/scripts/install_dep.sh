#!/bin/bash
sudo sudo apt-get install -y ppa-purge
sudo ppa-purge -y ppa:ubuntu-wine/ppa
sudo apt-get update
sudo add-apt-repository -y ppa:ricotz/unstable
sudo apt install -y wine-stable

sudo apt-get install -y p7zip-full mdbtools mdbtools-gmdb 
sudo dpkg --add-architecture i386 && sudo apt-get update && sudo apt-get -y install wine32